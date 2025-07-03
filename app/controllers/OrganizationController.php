<?php

class OrganizationController extends Controller {
    
    public function __construct() {
        $this->requireAuth();
    }
    
    // ==================== ORGANIZATION CRUD ====================
    
    public function index() {
        $organizationModel = $this->model('Organization');
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        // Get user's organizations
        $userMemberships = $memberModel->getUserOrganizations($uid);
        
        // Get all public organizations
        $allOrganizations = $organizationModel->getAllOrganizations();
        
        // Add membership status to each organization
        foreach ($allOrganizations as &$org) {
            $org['is_member'] = $memberModel->isUserMemberOfOrganization($org['org_id'], $uid);
        }
        
        // Get organizations owned by user
        $ownedOrganizations = $organizationModel->getOrganizationsByOwner($uid);
        
        $this->view('organizations/index', [
            'userMemberships' => $userMemberships,
            'allOrganizations' => $allOrganizations,
            'ownedOrganizations' => $ownedOrganizations
        ]);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $contact_email = trim($_POST['contact_email'] ?? '');
            $contact_phone = trim($_POST['contact_phone'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $short_description = trim($_POST['short_description'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $program_id = $_POST['program_id'] ?? '';
            $department_id = $_POST['department_id'] ?? '';
            $school_id = $_POST['school_id'] ?? '';
            $is_public = isset($_POST['is_public']) ? 1 : 0;
            $allow_requests = isset($_POST['allow_requests']) ? 1 : 0;
            
            // If program not provided, use creating user's program
            if (empty($program_id)) {
                $userModel = $this->model('User');
                $creator = $userModel->getUserById($this->getCurrentUserId());
                if ($creator && !empty($creator['program_id'])) {
                    $program_id = $creator['program_id'];
                }
            }
            
            // Validation
            if (empty($name) || empty($type) || empty($contact_email)) {
                $this->setFlash('error', 'Name, type, and contact email are required');
                $this->view('organizations/create', $_POST);
                return;
            }
            
            if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Please enter a valid contact email');
                $this->view('organizations/create', $_POST);
                return;
            }
            
            $organizationModel = $this->model('Organization');
            $uid = $this->getCurrentUserId();
            
            $orgData = [
                'name' => $name,
                'type' => $type,
                'contact_email' => $contact_email,
                'contact_phone' => $contact_phone,
                'website' => $website,
                'short_description' => $short_description,
                'description' => $description,
                'program_id' => $program_id,
                'department_id' => $department_id,
                'school_id' => $school_id,
                'is_public' => $is_public,
                'allow_join_requests' => $allow_requests,
                'created_by' => $uid
            ];
            
            $result = $organizationModel->createOrganization($orgData);
            
            if ($result['success']) {
                // Handle initial invitations if provided
                $memberModel = $this->model('OrganizationMember');
                $orgId = $result['organization_id'];
                
                // Process admin invitations
                if (!empty($_POST['admin_invites'])) {
                    $adminEmails = array_map('trim', preg_split('/[,\n]/', $_POST['admin_invites']));
                    foreach ($adminEmails as $email) {
                        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $memberModel->createInvitation($orgId, $email, 'admin', $uid, $_POST['invite_message'] ?? '');
                        }
                    }
                }
                
                // Process member invitations
                if (!empty($_POST['member_invites'])) {
                    $memberEmails = array_map('trim', preg_split('/[,\n]/', $_POST['member_invites']));
                    foreach ($memberEmails as $email) {
                        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $memberModel->createInvitation($orgId, $email, 'member', $uid, $_POST['invite_message'] ?? '');
                        }
                    }
                }
                
                $this->setFlash('success', 'Organization created successfully!');
                $this->redirect("/organizations/{$result['slug']}");
            } else {
                $this->setFlash('error', $result['message']);
                $this->view('organizations/create', $_POST);
            }
        } else {
            $this->view('organizations/create');
        }
    }
    
    public function show($orgSlug = null) {
        if (!$orgSlug) {
            $this->redirect('/organizations');
        }
        
        $organizationModel = $this->model('Organization');
        $organization = $organizationModel->getOrganizationBySlug($orgSlug);
        
        if (!$organization) {
            $this->setFlash('error', 'Organization not found');
            $this->redirect('/organizations');
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        // Get organization members
        $members = $memberModel->getOrganizationMembers($organization['org_id']);
        
        // Fetch organization events (upcoming and recent)
        $eventModel = $this->model('Event');
        $events = $eventModel->getEventsByOrganization($organization['org_id']);
        $upcomingEvents = [];
        $pastEvents = [];
        $now = date('Y-m-d H:i:s');
        foreach ($events as $evt) {
            if ($evt['startdate'] >= $now) {
                $upcomingEvents[] = $evt;
            } else {
                $pastEvents[] = $evt;
            }
        }
        // Sort upcoming ascending by startdate, past descending
        usort($upcomingEvents, fn($a,$b)=>strcmp($a['startdate'],$b['startdate']));
        usort($pastEvents, fn($a,$b)=>strcmp($b['startdate'],$a['startdate']));
        $upcomingEvents = array_slice($upcomingEvents,0,5);
        $recentPastEvents = array_slice($pastEvents,0,5);
        
        // Get user's role in this organization
        $userRole = $memberModel->getUserRole($organization['org_id'], $uid);
        
        // Check if user has pending request
        $hasPendingRequest = $memberModel->hasPendingJoinRequest($organization['org_id'], $uid);
        
        $this->view('organizations/show', [
            'organization' => $organization,
            'members' => $members,
            'userRole' => $userRole,
            'hasPendingRequest' => $hasPendingRequest,
            'upcomingEvents' => $upcomingEvents,
            'recentPastEvents' => $recentPastEvents
        ]);
    }
    
    public function manage($orgSlug = null) {
        if (!$orgSlug) {
            $this->redirect('/organizations');
        }
        
        $organizationModel = $this->model('Organization');
        $organization = $organizationModel->getOrganizationBySlug($orgSlug);
        
        if (!$organization) {
            $this->setFlash('error', 'Organization not found');
            $this->redirect('/organizations');
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        // Check if user can manage this organization
        $userRole = $memberModel->getUserRole($organization['org_id'], $uid);
        if (!in_array($userRole, ['owner', 'admin'])) {
            $this->setFlash('error', 'You do not have permission to manage this organization');
            $this->redirect("/organizations/{$orgSlug}");
        }
        
        // Get organization data
        $members = $memberModel->getOrganizationMembers($organization['org_id']);
        $pendingInvitations = $memberModel->getPendingInvitations($organization['org_id']);
        $pendingRequests = $memberModel->getPendingJoinRequests($organization['org_id']);
        
        $this->view('organizations/manage', [
            'organization' => $organization,
            'members' => $members,
            'pendingInvitations' => $pendingInvitations,
            'pendingRequests' => $pendingRequests,
            'userRole' => $userRole
        ]);
    }
    
    public function edit($orgId = null) {
        if (!$orgId) {
            $this->redirect('/dashboard');
        }
        
        $organizationModel = $this->model('Organization');
        $organization = $organizationModel->getOrganizationById($orgId);
        
        if (!$organization) {
            $this->setFlash('error', 'Organization not found');
            $this->redirect('/dashboard');
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        if (!$memberModel->canUserManageOrganization($orgId, $uid)) {
            $this->setFlash('error', 'You do not have permission to edit this organization');
            $this->redirect("/organizations/show/{$organization['org_slug']}");
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orgName = trim($_POST['org_name'] ?? '');
            $orgDescription = trim($_POST['org_description'] ?? '');
            $contactEmail = trim($_POST['contact_email'] ?? '');
            $contactPhone = trim($_POST['contact_phone'] ?? '');
            $websiteUrl = trim($_POST['website_url'] ?? '');
            
            // Validation
            if (empty($orgName)) {
                $this->setFlash('error', 'Organization name is required');
                $this->view('organizations/edit', ['organization' => $organization]);
                return;
            }
            
            if (strlen($orgName) < 3) {
                $this->setFlash('error', 'Organization name must be at least 3 characters');
                $this->view('organizations/edit', ['organization' => $organization]);
                return;
            }
            
            // Validate email if provided
            if (!empty($contactEmail) && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Please enter a valid contact email');
                $this->view('organizations/edit', ['organization' => $organization]);
                return;
            }
            
            $orgData = [
                'org_name' => $orgName,
                'org_description' => $orgDescription,
                'contact_email' => $contactEmail,
                'contact_phone' => $contactPhone,
                'website_url' => $websiteUrl
            ];
            
            if ($organizationModel->updateOrganization($orgId, $orgData)) {
                $this->setFlash('success', 'Organization updated successfully!');
                $this->redirect("/organizations/manage/{$orgId}");
            } else {
                $this->setFlash('error', 'Failed to update organization');
                $this->view('organizations/edit', ['organization' => $organization]);
            }
        } else {
            $this->view('organizations/edit', ['organization' => $organization]);
        }
    }
    
    public function delete($orgId = null) {
        if (!$orgId) {
            $this->redirect('/dashboard');
        }
        
        $organizationModel = $this->model('Organization');
        $organization = $organizationModel->getOrganizationById($orgId);
        
        if (!$organization) {
            $this->setFlash('error', 'Organization not found');
            $this->redirect('/dashboard');
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        // Only owners can delete organizations
        if ($memberModel->getUserRole($orgId, $uid) !== 'owner') {
            $this->setFlash('error', 'Only organization owners can delete organizations');
            $this->redirect("/organizations/manage/{$orgId}");
        }
        
        $result = $organizationModel->deleteOrganization($orgId);
        if ($result['success']) {
            $this->setFlash('success', 'Organization deleted successfully!');
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('error', $result['message']);
            $this->redirect("/organizations/manage/{$orgId}");
        }
    }
    
    // ==================== MEMBER MANAGEMENT ====================
    
    public function invite($orgId = null) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        
        if (!$orgId) {
            $orgId = $payload['org_id'] ?? null;
        }
        
        if (!$orgId) {
            echo json_encode(['success' => false, 'message' => 'Organization ID required']);
            return;
        }
        
        $email = $payload['email'] ?? '';
        $role = $payload['role'] ?? 'member';
        $message = $payload['message'] ?? '';
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Valid email address required']);
            return;
        }
        
        $organizationModel = $this->model('Organization');
        $organization = $organizationModel->getOrganizationById($orgId);
        
        if (!$organization) {
            echo json_encode(['success' => false, 'message' => 'Organization not found']);
            return;
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        if (!$memberModel->canUserManageMembers($orgId, $uid)) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to invite members']);
            return;
        }
        
        $result = $memberModel->createInvitation($orgId, $email, $role, $uid, $message);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true, 
                'message' => 'Invitation sent successfully',
                'invitation_id' => $result['invitation_id']
            ]);
        } else {
            echo json_encode($result);
        }
        
        exit;
    }
    
    public function acceptInvitation($token = null) {
        if (!$token) {
            $this->setFlash('error', 'Invalid invitation token');
            $this->redirect('/dashboard');
        }
        
        $memberModel = $this->model('OrganizationMember');
        $invitation = $memberModel->getInvitationByToken($token);
        
        if (!$invitation) {
            $this->setFlash('error', 'Invalid or expired invitation');
            $this->redirect('/dashboard');
        }
        
        $uid = $this->getCurrentUserId();
        $result = $memberModel->acceptInvitation($token, $uid);
        
        if ($result['success']) {
            $this->setFlash('success', "Welcome to {$invitation['org_name']}!");
            $this->redirect("/organizations/manage/{$result['org_id']}");
        } else {
            $this->setFlash('error', $result['message']);
            $this->redirect('/dashboard');
        }
    }
    
    public function declineInvitation($token = null) {
        if (!$token) {
            $this->setFlash('error', 'Invalid invitation token');
            $this->redirect('/dashboard');
        }
        
        $memberModel = $this->model('OrganizationMember');
        $result = $memberModel->declineInvitation($token);
        
        if ($result['success']) {
            $this->setFlash('success', 'Invitation declined');
        } else {
            $this->setFlash('error', $result['message']);
        }
        
        $this->redirect('/dashboard');
    }
    
    public function removeMember($orgId = null) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        
        if (!$orgId) {
            $orgId = $payload['org_id'] ?? null;
        }
        
        $userIdToRemove = $payload['user_id'] ?? '';
        
        if (!$orgId || !$userIdToRemove) {
            echo json_encode(['success' => false, 'message' => 'Organization ID and User ID required']);
            return;
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        if (!$memberModel->canUserManageMembers($orgId, $uid)) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to remove members']);
            return;
        }
        
        $result = $memberModel->removeMember($orgId, $userIdToRemove);
        echo json_encode($result);
        exit;
    }
    
    public function updateMemberRole($orgId = null) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        
        if (!$orgId) {
            $orgId = $payload['org_id'] ?? null;
        }
        
        $userIdToUpdate = $payload['user_id'] ?? '';
        $newRole = $payload['role'] ?? '';
        
        if (!$orgId || !$userIdToUpdate || !$newRole) {
            echo json_encode(['success' => false, 'message' => 'Organization ID, User ID, and Role required']);
            return;
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        if (!$memberModel->canUserManageMembers($orgId, $uid)) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to update member roles']);
            return;
        }
        
        $result = $memberModel->updateMemberRole($orgId, $userIdToUpdate, $newRole);
        echo json_encode($result);
        exit;
    }
    
    // ==================== FINANCIAL MANAGEMENT ====================
    
    public function finances($orgId = null) {
        if (!$orgId) {
            $this->redirect('/dashboard');
        }
        
        $organizationModel = $this->model('Organization');
        $organization = $organizationModel->getOrganizationById($orgId);
        
        if (!$organization) {
            $this->setFlash('error', 'Organization not found');
            $this->redirect('/dashboard');
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        if (!$memberModel->canUserManageFinances($orgId, $uid)) {
            $this->setFlash('error', 'You do not have permission to manage finances');
            $this->redirect("/organizations/manage/{$orgId}");
        }
        
        // Get financial accounts
        $financialAccounts = $organizationModel->getFinancialAccounts($orgId);
        
        // Get transactions with filters
        $filters = [];
        if (isset($_GET['type'])) $filters['transaction_type'] = $_GET['type'];
        if (isset($_GET['category'])) $filters['category'] = $_GET['category'];
        if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
        
        $transactions = $organizationModel->getTransactions($orgId, 50, 0, $filters);
        
        $userRole = $memberModel->getUserRole($orgId, $uid);
        
        $this->view('organizations/finances', [
            'organization' => $organization,
            'userRole' => $userRole,
            'financialAccounts' => $financialAccounts,
            'transactions' => $transactions,
            'filters' => $filters
        ]);
    }
    
    public function recordTransaction($orgId = null) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        
        if (!$orgId) {
            $orgId = $payload['org_id'] ?? null;
        }
        
        if (!$orgId) {
            echo json_encode(['success' => false, 'message' => 'Organization ID required']);
            return;
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        if (!$memberModel->canUserManageFinances($orgId, $uid)) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to record transactions']);
            return;
        }
        
        $transactionData = [
            'finance_id' => $payload['finance_id'] ?? null,
            'event_id' => $payload['event_id'] ?? null,
            'transaction_type' => $payload['transaction_type'] ?? '',
            'category' => $payload['category'] ?? '',
            'amount' => floatval($payload['amount'] ?? 0),
            'currency' => $payload['currency'] ?? 'USD',
            'description' => $payload['description'] ?? '',
            'payment_method' => $payload['payment_method'] ?? '',
            'payment_reference' => $payload['payment_reference'] ?? '',
            'processed_by' => $uid,
            'metadata' => $payload['metadata'] ?? [],
            'status' => $payload['status'] ?? 'completed'
        ];
        
        // Validation
        if (empty($transactionData['transaction_type']) || empty($transactionData['description'])) {
            echo json_encode(['success' => false, 'message' => 'Transaction type and description are required']);
            return;
        }
        
        if ($transactionData['amount'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
            return;
        }
        
        $organizationModel = $this->model('Organization');
        $transactionId = $organizationModel->recordTransaction($orgId, $transactionData);
        
        if ($transactionId) {
            echo json_encode(['success' => true, 'transaction_id' => $transactionId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record transaction']);
        }
        
        exit;
    }
    
    // ==================== API ENDPOINTS ====================
    
    public function requestJoin() {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        $organizationId = $payload['organization_id'] ?? '';
        
        if (empty($organizationId)) {
            echo json_encode(['success' => false, 'message' => 'Organization ID required']);
            exit;
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        $result = $memberModel->createJoinRequest($organizationId, $uid);
        echo json_encode($result);
        exit;
    }
    
    public function handleJoinRequest($orgSlug = null) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        $userId = $payload['user_id'] ?? '';
        $action = $payload['action'] ?? '';
        
        if (empty($userId) || empty($action)) {
            echo json_encode(['success' => false, 'message' => 'User ID and action required']);
            exit;
        }
        
        $organizationModel = $this->model('Organization');
        $organization = $organizationModel->getOrganizationBySlug($orgSlug);
        
        if (!$organization) {
            echo json_encode(['success' => false, 'message' => 'Organization not found']);
            exit;
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        if (!in_array($memberModel->getUserRole($organization['org_id'], $uid), ['owner', 'admin'])) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to handle join requests']);
            exit;
        }
        
        if ($action === 'accept') {
            $result = $memberModel->acceptJoinRequest($organization['org_id'], $userId);
        } else {
            $result = $memberModel->declineJoinRequest($organization['org_id'], $userId);
        }
        
        echo json_encode($result);
        exit;
    }
    
    public function inviteMembers($orgSlug = null) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        $emails = $payload['emails'] ?? '';
        $role = $payload['role'] ?? 'member';
        $message = $payload['message'] ?? '';
        
        if (empty($emails)) {
            echo json_encode(['success' => false, 'message' => 'Email addresses required']);
            exit;
        }
        
        $organizationModel = $this->model('Organization');
        $organization = $organizationModel->getOrganizationBySlug($orgSlug);
        
        if (!$organization) {
            echo json_encode(['success' => false, 'message' => 'Organization not found']);
            exit;
        }
        
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        if (!in_array($memberModel->getUserRole($organization['org_id'], $uid), ['owner', 'admin'])) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to invite members']);
            exit;
        }
        
        // Parse emails
        $emailList = array_map('trim', preg_split('/[,\n]/', $emails));
        $successCount = 0;
        $errors = [];
        
        foreach ($emailList as $email) {
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result = $memberModel->createInvitation($organization['org_id'], $email, $role, $uid, $message);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errors[] = "Failed to invite {$email}: {$result['message']}";
                }
            } else if (!empty($email)) {
                $errors[] = "Invalid email address: {$email}";
            }
        }
        
        echo json_encode([
            'success' => $successCount > 0,
            'message' => "{$successCount} invitations sent successfully",
            'errors' => $errors
        ]);
        exit;
    }
    
    // ==================== DASHBOARD INTEGRATION ====================
    
    public function myOrganizations() {
        $memberModel = $this->model('OrganizationMember');
        $uid = $this->getCurrentUserId();
        
        $organizations = $memberModel->getUserOrganizations($uid);
        
        $this->view('organizations/my-organizations', [
            'organizations' => $organizations
        ]);
    }
} 