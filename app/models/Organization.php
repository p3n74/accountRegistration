<?php

class Organization extends Model {
    protected $table = 'organizations';
    private $fileStorage;

    public function __construct() {
        parent::__construct();
        
        // Handle different path contexts
        $fileStoragePath = file_exists('../app/core/FileStorage.php') 
            ? '../app/core/FileStorage.php' 
            : 'app/core/FileStorage.php';
        
        require_once $fileStoragePath;
        $this->fileStorage = new FileStorage();

        // Ensure all required columns exist before any queries
        $this->addMissingColumns();
    }

    private function generateGUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private function createSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        while ($this->getOrganizationBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    // ==================== BASIC CRUD OPERATIONS ====================

    public function createOrganization($data) {
        $orgId = $this->generateGUID();
        $slug = $this->createSlug($data['name']);
        
        // Program / department / school linkage
        $programId = isset($data['program_id']) && $data['program_id'] !== '' ? intval($data['program_id']) : null;
        $departmentId = isset($data['department_id']) && $data['department_id'] !== '' ? intval($data['department_id']) : null;
        $schoolId = isset($data['school_id']) && $data['school_id'] !== '' ? intval($data['school_id']) : null;

        // If program is provided but department/school not, derive them
        if ($programId && (!$departmentId || !$schoolId)) {
            require_once __DIR__ . '/Program.php';
            $progModel = new Program();
            $program = $progModel->getProgramById($programId);
            if ($program) {
                $departmentId = $program['department_id'];
                // Fetch school_id based on department
                $stmtTmp = $this->db->prepare("SELECT school_id FROM department WHERE department_id = ? LIMIT 1");
                $stmtTmp->bind_param("i", $departmentId);
                $stmtTmp->execute();
                $resTmp = $stmtTmp->get_result()->fetch_assoc();
                $schoolId = $resTmp['school_id'] ?? null;
            }
        }

        // If program_id still null, attempt to inherit from creator's record
        if (!$programId) {
            $stmtUsr = $this->db->prepare("SELECT program_id FROM user_credentials WHERE uid = ? LIMIT 1");
            $stmtUsr->bind_param("s", $data['created_by']);
            $stmtUsr->execute();
            $rowUsr = $stmtUsr->get_result()->fetch_assoc();
            if ($rowUsr && !empty($rowUsr['program_id'])) {
                $programId = intval($rowUsr['program_id']);
            }
        }

        // Re-derive department and school once we have a program_id
        if ($programId && (!$departmentId || !$schoolId)) {
            // get dept & school via joins
            $stmtTmp = $this->db->prepare("SELECT d.department_id, s.school_id FROM program_list pl JOIN department d ON pl.department_id = d.department_id JOIN school s ON d.school_id = s.school_id WHERE pl.program_id = ? LIMIT 1");
            $stmtTmp->bind_param("i", $programId);
            $stmtTmp->execute();
            $rowTmp = $stmtTmp->get_result()->fetch_assoc();
            if ($rowTmp) {
                $departmentId = $rowTmp['department_id'] ?? $departmentId;
                $schoolId = $rowTmp['school_id'] ?? $schoolId;
            }
        }

        $sql = "INSERT INTO {$this->table} (org_id, org_name, org_slug, org_description, org_type, program_id, department_id, school_id, contact_email, contact_phone, website_url, is_public, allow_join_requests, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)";
        $stmt = $this->db->prepare($sql);
        
        // Store values in variables to pass by reference
        $name = $data['name'];
        $description = $data['description'] ?? '';
        $type = $data['type'] ?? 'student';
        $contactEmail = $data['contact_email'] ?? '';
        $contactPhone = $data['contact_phone'] ?? '';
        $website = $data['website'] ?? '';
        $isPublic = $data['is_public'] ?? 1;
        $allowJoinRequests = $data['allow_join_requests'] ?? 1;
        $createdBy = $data['created_by'];
        
        $stmt->bind_param("sssssiiisssiis",
            $orgId,
            $name,
            $slug,
            $description,
            $type,
            $programId,
            $departmentId,
            $schoolId,
            $contactEmail,
            $contactPhone,
            $website,
            $isPublic,
            $allowJoinRequests,
            $createdBy
        );
        
        if ($stmt->execute()) {
            // Create the creator as the owner
            require_once __DIR__ . '/OrganizationMember.php';
            $memberModel = new OrganizationMember();
            $memberModel->addMember($orgId, $data['created_by'], 'owner', 'active');
            
            // Create default financial account
            $this->createFinancialAccount($orgId, 'Main Account', 'checking');
            
            // Create organization file for extended data
            $this->syncOrganizationToFile($orgId);
            
            return ['success' => true, 'organization_id' => $orgId, 'slug' => $slug];
        }
        return ['success' => false, 'message' => 'Failed to create organization'];
    }
    
    private function addMissingColumns() {
        // Add is_public column if it doesn't exist
        $sql = "SHOW COLUMNS FROM {$this->table} LIKE 'is_public'";
        $result = $this->db->query($sql);
        if ($result->num_rows === 0) {
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN is_public TINYINT(1) DEFAULT 1 AFTER website_url");
        }
        
        // Add allow_join_requests column if it doesn't exist
        $sql = "SHOW COLUMNS FROM {$this->table} LIKE 'allow_join_requests'";
        $result = $this->db->query($sql);
        if ($result->num_rows === 0) {
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN allow_join_requests TINYINT(1) DEFAULT 1 AFTER is_public");
        }
        
        // Add short_description column if it doesn't exist
        $sql = "SHOW COLUMNS FROM {$this->table} LIKE 'short_description'";
        $result = $this->db->query($sql);
        if ($result->num_rows === 0) {
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN short_description TEXT AFTER org_description");
        }
        
        // Add program_id column if it doesn't exist
        $sql = "SHOW COLUMNS FROM {$this->table} LIKE 'program_id'";
        $result = $this->db->query($sql);
        if ($result->num_rows === 0) {
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN program_id INT NULL AFTER org_type");
            $this->db->query("ALTER TABLE {$this->table} ADD INDEX idx_program_id (program_id)");
        }
        
        // Add department_id column if it doesn't exist
        $sql = "SHOW COLUMNS FROM {$this->table} LIKE 'department_id'";
        $result = $this->db->query($sql);
        if ($result->num_rows === 0) {
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN department_id INT NULL AFTER program_id");
            $this->db->query("ALTER TABLE {$this->table} ADD INDEX idx_department_id (department_id)");
        }
        
        // Add school_id column if it doesn't exist
        $sql = "SHOW COLUMNS FROM {$this->table} LIKE 'school_id'";
        $result = $this->db->query($sql);
        if ($result->num_rows === 0) {
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN school_id INT NULL AFTER department_id");
            $this->db->query("ALTER TABLE {$this->table} ADD INDEX idx_school_id (school_id)");
        }
    }

    public function getOrganizationById($orgId) {
        $sql = "SELECT o.*, 
                        COUNT(DISTINCT om.membership_id) as member_count,
                        COUNT(DISTINCT e.eventid) as event_count,
                        pl.program_name,
                        d.department_name,
                        s.school_name
                 FROM {$this->table} o
                 LEFT JOIN organization_members om ON o.org_id = om.org_id AND om.status = 'active'
                 LEFT JOIN events e ON o.org_id = e.org_id
                 LEFT JOIN program_list pl ON o.program_id = pl.program_id
                 LEFT JOIN department d ON o.department_id = d.department_id
                 LEFT JOIN school s ON o.school_id = s.school_id
                 WHERE o.org_id = ?
                 GROUP BY o.org_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $orgId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getOrganizationBySlug($slug) {
        $sql = "SELECT o.*, 
                        COUNT(DISTINCT om.membership_id) as member_count,
                        COUNT(DISTINCT e.eventid) as event_count,
                        pl.program_name,
                        d.department_name,
                        s.school_name
                 FROM {$this->table} o
                 LEFT JOIN organization_members om ON o.org_id = om.org_id AND om.status = 'active'
                 LEFT JOIN events e ON o.org_id = e.org_id
                 LEFT JOIN program_list pl ON o.program_id = pl.program_id
                 LEFT JOIN department d ON o.department_id = d.department_id
                 LEFT JOIN school s ON o.school_id = s.school_id
                 WHERE o.org_slug = ?
                 GROUP BY o.org_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateOrganization($orgId, $data) {
        $fields = [];
        $values = [];
        $types = "";
        
        if (isset($data['org_name'])) {
            $fields[] = "org_name = ?";
            $values[] = $data['org_name'];
            $types .= "s";
            
            // Update slug if name changed
            $fields[] = "org_slug = ?";
            $values[] = $this->createSlug($data['org_name']);
            $types .= "s";
        }
        
        if (isset($data['org_description'])) {
            $fields[] = "org_description = ?";
            $values[] = $data['org_description'];
            $types .= "s";
        }
        
        if (isset($data['org_type'])) {
            $fields[] = "org_type = ?";
            $values[] = $data['org_type'];
            $types .= "s";
        }
        
        if (isset($data['contact_email'])) {
            $fields[] = "contact_email = ?";
            $values[] = $data['contact_email'];
            $types .= "s";
        }
        
        if (isset($data['contact_phone'])) {
            $fields[] = "contact_phone = ?";
            $values[] = $data['contact_phone'];
            $types .= "s";
        }
        
        if (isset($data['website_url'])) {
            $fields[] = "website_url = ?";
            $values[] = $data['website_url'];
            $types .= "s";
        }
        
        if (isset($data['logo_path'])) {
            $fields[] = "logo_path = ?";
            $values[] = $data['logo_path'];
            $types .= "s";
        }
        
        if (isset($data['banner_path'])) {
            $fields[] = "banner_path = ?";
            $values[] = $data['banner_path'];
            $types .= "s";
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $orgId;
        $types .= "s";
        
        $sql = "UPDATE {$this->table} SET " . implode(", ", $fields) . " WHERE org_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            $this->syncOrganizationToFile($orgId);
            return true;
        }
        return false;
    }

    public function deleteOrganization($orgId) {
        $sql = "DELETE FROM {$this->table} WHERE org_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $orgId);
        
        if ($stmt->execute()) {
            // Clean up file storage
            $this->fileStorage->deleteOrganizationData($orgId);
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Failed to delete organization'];
    }

    // ==================== MEMBER MANAGEMENT ====================

    public function addMember($orgId, $userId, $role = 'member', $status = 'pending', $invitedBy = null) {
        $membershipId = $this->generateGUID();
        
        $sql = "INSERT INTO organization_members (membership_id, org_id, user_id, role, status, invited_by) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssss", $membershipId, $orgId, $userId, $role, $status, $invitedBy);
        
        return $stmt->execute() ? $membershipId : false;
    }

    public function removeMember($orgId, $userId) {
        // Don't allow removing the last owner
        if ($this->getUserRole($orgId, $userId) === 'owner') {
            $ownerCount = $this->getMembersByRole($orgId, 'owner');
            if (count($ownerCount) <= 1) {
                return ['success' => false, 'message' => 'Cannot remove the last owner'];
            }
        }
        
        $sql = "DELETE FROM organization_members WHERE org_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $orgId, $userId);
        
        return $stmt->execute() ? ['success' => true] : ['success' => false, 'message' => 'Failed to remove member'];
    }

    public function updateMemberRole($orgId, $userId, $newRole) {
        // Don't allow changing the last owner's role
        if ($this->getUserRole($orgId, $userId) === 'owner') {
            $ownerCount = $this->getMembersByRole($orgId, 'owner');
            if (count($ownerCount) <= 1 && $newRole !== 'owner') {
                return ['success' => false, 'message' => 'Cannot change the last owner\'s role'];
            }
        }
        
        $sql = "UPDATE organization_members SET role = ?, updated_at = CURRENT_TIMESTAMP WHERE org_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $newRole, $orgId, $userId);
        
        return $stmt->execute() ? ['success' => true] : ['success' => false, 'message' => 'Failed to update member role'];
    }

    public function updateMemberStatus($orgId, $userId, $status) {
        $sql = "UPDATE organization_members SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE org_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $status, $orgId, $userId);
        
        return $stmt->execute();
    }

    public function getOrganizationMembers($orgId, $status = null) {
        $sql = "SELECT om.*, u.fname, u.lname, u.email, u.username, u.profilepicture
                FROM organization_members om
                JOIN user_credentials u ON om.user_id = u.uid
                WHERE om.org_id = ?";
        
        if ($status) {
            $sql .= " AND om.status = ?";
        }
        
        $sql .= " ORDER BY 
                    CASE om.role 
                        WHEN 'owner' THEN 1 
                        WHEN 'admin' THEN 2 
                        WHEN 'executive' THEN 3 
                        WHEN 'treasurer' THEN 4 
                        ELSE 5 
                    END, 
                    om.joined_at ASC";
        
        $stmt = $this->db->prepare($sql);
        if ($status) {
            $stmt->bind_param("ss", $orgId, $status);
        } else {
            $stmt->bind_param("s", $orgId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getMembersByRole($orgId, $role) {
        $sql = "SELECT om.*, u.fname, u.lname, u.email, u.username, u.profilepicture
                FROM organization_members om
                JOIN user_credentials u ON om.user_id = u.uid
                WHERE om.org_id = ? AND om.role = ? AND om.status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $orgId, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserRole($orgId, $userId) {
        $sql = "SELECT role FROM organization_members WHERE org_id = ? AND user_id = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $orgId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['role'] : null;
    }

    public function isUserMember($orgId, $userId) {
        $sql = "SELECT 1 FROM organization_members WHERE org_id = ? AND user_id = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $orgId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function getUserOrganizations($userId, $role = null) {
        $sql = "SELECT o.*, om.role, om.joined_at,
                       COUNT(DISTINCT om2.membership_id) as member_count,
                       COUNT(DISTINCT e.eventid) as event_count
                FROM {$this->table} o
                JOIN organization_members om ON o.org_id = om.org_id
                LEFT JOIN organization_members om2 ON o.org_id = om2.org_id AND om2.status = 'active'
                LEFT JOIN events e ON o.org_id = e.org_id
                WHERE om.user_id = ? AND om.status = 'active'";
        
        if ($role) {
            $sql .= " AND om.role = ?";
        }
        
        $sql .= " GROUP BY o.org_id ORDER BY om.joined_at DESC";
        
        $stmt = $this->db->prepare($sql);
        if ($role) {
            $stmt->bind_param("ss", $userId, $role);
        } else {
            $stmt->bind_param("s", $userId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAllOrganizations($type = null, $limit = 50) {
        $sql = "SELECT o.*, 
                       COUNT(DISTINCT om.membership_id) as member_count,
                       COUNT(DISTINCT e.eventid) as event_count
                FROM {$this->table} o
                LEFT JOIN organization_members om ON o.org_id = om.org_id AND om.status = 'active'
                LEFT JOIN events e ON o.org_id = e.org_id
                WHERE o.status = 'active'";
        
        $params = [];
        $types = "";
        
        if ($type) {
            $sql .= " AND o.org_type = ?";
            $params[] = $type;
            $types .= "s";
        }
        
        $sql .= " GROUP BY o.org_id ORDER BY o.updated_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param("i", $limit);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getOrganizationsByOwner($userId) {
        $sql = "SELECT o.*, 
                       COUNT(DISTINCT om.membership_id) as member_count,
                       COUNT(DISTINCT e.eventid) as event_count
                FROM {$this->table} o
                LEFT JOIN organization_members om ON o.org_id = om.org_id AND om.status = 'active'
                LEFT JOIN events e ON o.org_id = e.org_id
                WHERE o.org_id IN (SELECT org_id FROM organization_members WHERE user_id = ? AND role = 'owner' AND status = 'active')
                GROUP BY o.org_id 
                ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ==================== INVITATION MANAGEMENT ====================

    public function createInvitation($orgId, $email, $role, $invitedBy, $message = null) {
        // Check if user is already a member
        $userModel = new User();
        $existingUser = $userModel->getUserByEmail($email);
        if ($existingUser && $this->isUserMember($orgId, $existingUser['uid'])) {
            return ['success' => false, 'message' => 'User is already a member'];
        }
        
        // Check for existing pending invitation
        $existingInvitation = $this->getPendingInvitation($orgId, $email);
        if ($existingInvitation) {
            return ['success' => false, 'message' => 'Invitation already pending'];
        }
        
        $invitationId = $this->generateGUID();
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
        
        $sql = "INSERT INTO organization_invitations (invitation_id, org_id, email, invited_by, role, invitation_token, message, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssssss", $invitationId, $orgId, $email, $invitedBy, $role, $token, $message, $expiresAt);
        
        if ($stmt->execute()) {
            return ['success' => true, 'invitation_id' => $invitationId, 'token' => $token];
        }
        return ['success' => false, 'message' => 'Failed to create invitation'];
    }

    public function getInvitationByToken($token) {
        $sql = "SELECT oi.*, o.org_name, u.fname, u.lname
                FROM organization_invitations oi
                JOIN organizations o ON oi.org_id = o.org_id
                JOIN user_credentials u ON oi.invited_by = u.uid
                WHERE oi.invitation_token = ? AND oi.status = 'pending' AND oi.expires_at > NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function acceptInvitation($token, $userId) {
        $invitation = $this->getInvitationByToken($token);
        if (!$invitation) {
            return ['success' => false, 'message' => 'Invalid or expired invitation'];
        }
        
        $this->db->beginTransaction();
        
        try {
            // Add user as member
            $membershipId = $this->addMember($invitation['org_id'], $userId, $invitation['role'], 'active', $invitation['invited_by']);
            
            if (!$membershipId) {
                throw new Exception('Failed to add member');
            }
            
            // Update invitation status
            $sql = "UPDATE organization_invitations SET status = 'accepted', responded_at = CURRENT_TIMESTAMP WHERE invitation_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $invitation['invitation_id']);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update invitation');
            }
            
            $this->db->commit();
            return ['success' => true, 'org_id' => $invitation['org_id']];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function declineInvitation($token) {
        $sql = "UPDATE organization_invitations SET status = 'declined', responded_at = CURRENT_TIMESTAMP WHERE invitation_token = ? AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $token);
        
        return $stmt->execute() ? ['success' => true] : ['success' => false, 'message' => 'Failed to decline invitation'];
    }

    private function getPendingInvitation($orgId, $email) {
        $sql = "SELECT * FROM organization_invitations WHERE org_id = ? AND email = ? AND status = 'pending' AND expires_at > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $orgId, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // ==================== FINANCIAL MANAGEMENT ====================

    public function createFinancialAccount($orgId, $accountName = 'Main Account', $accountType = 'checking') {
        $financeId = $this->generateGUID();
        
        $sql = "INSERT INTO organization_finances (finance_id, org_id, account_name, account_type) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", $financeId, $orgId, $accountName, $accountType);
        
        return $stmt->execute() ? $financeId : false;
    }

    public function getFinancialAccounts($orgId) {
        $sql = "SELECT * FROM organization_finances WHERE org_id = ? AND is_active = 1 ORDER BY account_name";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $orgId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function recordTransaction($orgId, $data) {
        $transactionId = $this->generateGUID();
        
        $sql = "INSERT INTO organization_transactions (transaction_id, org_id, finance_id, event_id, transaction_type, category, amount, currency, description, payment_method, payment_reference, processed_by, metadata, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $metadata = json_encode($data['metadata'] ?? []);
        $stmt->bind_param("ssssssdsssssss", 
            $transactionId,
            $orgId,
            $data['finance_id'] ?? null,
            $data['event_id'] ?? null,
            $data['transaction_type'],
            $data['category'] ?? '',
            $data['amount'],
            $data['currency'] ?? 'USD',
            $data['description'],
            $data['payment_method'] ?? '',
            $data['payment_reference'] ?? '',
            $data['processed_by'] ?? null,
            $metadata,
            $data['status'] ?? 'pending'
        );
        
        return $stmt->execute() ? $transactionId : false;
    }

    public function getTransactions($orgId, $limit = 50, $offset = 0, $filters = []) {
        $sql = "SELECT ot.*, u.fname, u.lname, e.eventname
                FROM organization_transactions ot
                LEFT JOIN user_credentials u ON ot.processed_by = u.uid
                LEFT JOIN events e ON ot.event_id = e.eventid
                WHERE ot.org_id = ?";
        
        $params = [$orgId];
        $types = "s";
        
        if (!empty($filters['transaction_type'])) {
            $sql .= " AND ot.transaction_type = ?";
            $params[] = $filters['transaction_type'];
            $types .= "s";
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND ot.category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND ot.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY ot.transaction_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ==================== SEARCH AND DISCOVERY ====================

    public function searchOrganizations($query, $type = null, $limit = 20) {
        $sql = "SELECT o.*, 
                       COUNT(DISTINCT om.membership_id) as member_count,
                       COUNT(DISTINCT e.eventid) as event_count
                FROM {$this->table} o
                LEFT JOIN organization_members om ON o.org_id = om.org_id AND om.status = 'active'
                LEFT JOIN events e ON o.org_id = e.org_id
                WHERE o.status = 'active' AND (o.org_name LIKE ? OR o.org_description LIKE ?)";
        
        $searchTerm = "%{$query}%";
        $params = [$searchTerm, $searchTerm];
        $types = "ss";
        
        if ($type) {
            $sql .= " AND o.org_type = ?";
            $params[] = $type;
            $types .= "s";
        }
        
        $sql .= " GROUP BY o.org_id ORDER BY o.org_name LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPublicOrganizations($type = null, $limit = 20) {
        $sql = "SELECT o.*, 
                       COUNT(DISTINCT om.membership_id) as member_count,
                       COUNT(DISTINCT e.eventid) as event_count
                FROM {$this->table} o
                LEFT JOIN organization_members om ON o.org_id = om.org_id AND om.status = 'active'
                LEFT JOIN events e ON o.org_id = e.org_id
                WHERE o.status = 'active'";
        
        $params = [];
        $types = "";
        
        if ($type) {
            $sql .= " AND o.org_type = ?";
            $params[] = $type;
            $types .= "s";
        }
        
        $sql .= " GROUP BY o.org_id ORDER BY o.updated_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param("i", $limit);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ==================== HELPER METHODS ====================

    private function getActiveEventsCount($orgId) {
        $sql = "SELECT COUNT(*) as count FROM events WHERE org_id = ? AND enddate > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $orgId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    private function syncOrganizationToFile($orgId) {
        $org = $this->getOrganizationById($orgId);
        if ($org) {
            $fileData = [
                'org_id' => $orgId,
                'org_name' => $org['org_name'],
                'org_slug' => $org['org_slug'],
                'org_description' => $org['org_description'],
                'org_type' => $org['org_type'],
                'settings' => [
                    'allow_public_events' => true,
                    'require_member_approval' => false,
                    'allow_paid_events' => true,
                    'auto_approve_students' => true
                ],
                'branding' => [
                    'logo_path' => $org['logo_path'],
                    'banner_path' => $org['banner_path'],
                    'primary_color' => '#007bff',
                    'secondary_color' => '#6c757d'
                ],
                'last_updated' => date('Y-m-d H:i:s')
            ];
            
            $this->fileStorage->saveOrganizationData($orgId, $fileData);
        }
    }

    // ==================== PERMISSION CHECKING ====================

    public function canUserManageOrganization($orgId, $userId) {
        $role = $this->getUserRole($orgId, $userId);
        return in_array($role, ['owner', 'admin']);
    }

    public function canUserManageMembers($orgId, $userId) {
        $role = $this->getUserRole($orgId, $userId);
        return in_array($role, ['owner', 'admin', 'executive']);
    }

    public function canUserManageFinances($orgId, $userId) {
        $role = $this->getUserRole($orgId, $userId);
        return in_array($role, ['owner', 'admin', 'treasurer']);
    }

    public function canUserCreateEvents($orgId, $userId) {
        $role = $this->getUserRole($orgId, $userId);
        return in_array($role, ['owner', 'admin', 'executive', 'member']);
    }
} 