<?php

class OrganizationMember extends Model {
    protected $table = 'organization_members';

    public function __construct() {
        parent::__construct();
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

    // ==================== MEMBER MANAGEMENT ====================

    public function addMember($orgId, $userId, $role = 'member', $status = 'pending', $invitedBy = null) {
        $membershipId = $this->generateGUID();
        
        $sql = "INSERT INTO {$this->table} (membership_id, org_id, user_id, role, status, invited_by) VALUES (?, ?, ?, ?, ?, ?)";
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
        
        $sql = "DELETE FROM {$this->table} WHERE org_id = ? AND user_id = ?";
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
        
        $sql = "UPDATE {$this->table} SET role = ?, updated_at = CURRENT_TIMESTAMP WHERE org_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $newRole, $orgId, $userId);
        
        return $stmt->execute() ? ['success' => true] : ['success' => false, 'message' => 'Failed to update member role'];
    }

    public function updateMemberStatus($orgId, $userId, $status) {
        $sql = "UPDATE {$this->table} SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE org_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $status, $orgId, $userId);
        
        return $stmt->execute();
    }

    public function getOrganizationMembers($orgId, $status = null) {
        $sql = "SELECT om.*, u.fname, u.lname, u.email, u.username, u.profilepicture
                FROM {$this->table} om
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
                FROM {$this->table} om
                JOIN user_credentials u ON om.user_id = u.uid
                WHERE om.org_id = ? AND om.role = ? AND om.status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $orgId, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserRole($orgId, $userId) {
        $sql = "SELECT role FROM {$this->table} WHERE org_id = ? AND user_id = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $orgId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['role'] : null;
    }

    public function isUserMember($orgId, $userId) {
        $sql = "SELECT 1 FROM {$this->table} WHERE org_id = ? AND user_id = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $orgId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    public function isUserMemberOfOrganization($orgId, $userId) {
        return $this->isUserMember($orgId, $userId);
    }
    
    public function hasPendingJoinRequest($orgId, $userId) {
        // This would check if user has a pending join request
        // For now, return false since we haven't implemented join requests table
        return false;
    }
    
    public function getPendingInvitations($orgId) {
        $sql = "SELECT oi.*, u.fname, u.lname
                FROM organization_invitations oi
                LEFT JOIN user_credentials u ON oi.invited_by = u.uid
                WHERE oi.org_id = ? AND oi.status = 'pending' AND oi.expires_at > NOW()
                ORDER BY oi.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $orgId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getPendingJoinRequests($orgId) {
        // This would get pending join requests
        // For now, return empty array since we haven't implemented join requests table
        return [];
    }
    
    public function createJoinRequest($orgId, $userId) {
        // This would create a join request
        // For now, return success false since we haven't implemented join requests table
        return ['success' => false, 'message' => 'Join requests not yet implemented'];
    }
    
    public function acceptJoinRequest($orgId, $userId) {
        // This would accept a join request
        // For now, return success false since we haven't implemented join requests table
        return ['success' => false, 'message' => 'Join requests not yet implemented'];
    }
    
    public function declineJoinRequest($orgId, $userId) {
        // This would decline a join request
        // For now, return success false since we haven't implemented join requests table
        return ['success' => false, 'message' => 'Join requests not yet implemented'];
    }

    public function getUserOrganizations($userId, $role = null) {
        $sql = "SELECT o.*, om.role, om.joined_at,
                       COUNT(DISTINCT om2.membership_id) as member_count,
                       COUNT(DISTINCT e.eventid) as event_count
                FROM organizations o
                JOIN {$this->table} om ON o.org_id = om.org_id
                LEFT JOIN {$this->table} om2 ON o.org_id = om2.org_id AND om2.status = 'active'
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

    // ==================== INVITATION MANAGEMENT ====================

    public function createInvitation($orgId, $email, $role, $invitedBy, $message = null) {
        // Check if user is already a member
        require_once '../app/models/User.php';
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