<?php

class DashboardController extends Controller {
    
    public function __construct() {
        $this->requireAuth();
    }
    
    public function index() {
        $userModel = $this->model('User');
        $eventModel = $this->model('Event');
        
        $uid = $this->getCurrentUserId();
        $user = $userModel->getUserById($uid);
        
        // If user not found, clear session and redirect to login
        if (!$user) {
            session_destroy();
            $this->redirect('/auth/login');
            return;
        }
        
        $createdEvents = $eventModel->getEventsByCreator($uid);
        $attendedEvents = $userModel->getAttendedEvents($uid);
        
        // DEBUG: Add logging
        error_log("DEBUG Dashboard: User UID = $uid");
        error_log("DEBUG Dashboard: User program_id = " . ($user['program_id'] ?? 'NULL'));
        
        // Determine user's school_id via program
        $schoolId = null;
        if (!empty($user['program_id'])) {
            $db = new Database();
            $stmt = $db->prepare("SELECT s.school_id FROM program_list pl JOIN department d ON pl.department_id = d.department_id JOIN school s ON d.school_id = s.school_id WHERE pl.program_id = ? LIMIT 1");
            $stmt->bind_param("i", $user['program_id']);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $schoolId = $res['school_id'] ?? null;
            error_log("DEBUG Dashboard: Derived school_id = " . ($schoolId ?? 'NULL'));
        }

        $promotedEvents = [];
        $departmentId = null;
        if (!empty($user['program_id'])) {
            $db = new Database();
            $stmtDep = $db->prepare("SELECT department_id FROM program_list WHERE program_id = ? LIMIT 1");
            $stmtDep->bind_param("i", $user['program_id']);
            $stmtDep->execute();
            $rowDep = $stmtDep->get_result()->fetch_assoc();
            $departmentId = $rowDep['department_id'] ?? null;
            error_log("DEBUG Dashboard: Derived department_id = " . ($departmentId ?? 'NULL'));
        }

        if ($departmentId) {
            $promotedEvents = $eventModel->getPromotedEventsByDepartment($departmentId, 5);
            error_log("DEBUG Dashboard: Department events count = " . count($promotedEvents));
        }
        // Fallback to school-wide events if department list is empty
        if (empty($promotedEvents) && $schoolId) {
            $promotedEvents = $eventModel->getPromotedEventsBySchool($schoolId, 5);
            error_log("DEBUG Dashboard: School events count = " . count($promotedEvents));
        }
        
        // fallback via organization memberships
        if (!$departmentId || !$schoolId) {
            require_once __DIR__ . '/../models/OrganizationMember.php';
            $memModel = new OrganizationMember();
            $memberships = $memModel->getUserOrganizations($uid);
            error_log("DEBUG Dashboard: User memberships count = " . count($memberships));
            if (!empty($memberships)) {
                // take first membership that has school_id/department_id
                foreach ($memberships as $m) {
                    if (!empty($m['department_id']) && !$departmentId) {
                        $departmentId = $m['department_id'];
                        error_log("DEBUG Dashboard: From membership - department_id = $departmentId");
                    }
                    if (!empty($m['school_id']) && !$schoolId) {
                        $schoolId = $m['school_id'];
                        error_log("DEBUG Dashboard: From membership - school_id = $schoolId");
                    }
                    if ($departmentId && $schoolId) break;
                }
            }
        }
        
        // Re-attempt promoted events fetch if still empty after membership-derived IDs
        if (empty($promotedEvents) && $departmentId) {
            $promotedEvents = $eventModel->getPromotedEventsByDepartment($departmentId, 5);
            error_log("DEBUG Dashboard: Re-attempt department events count = " . count($promotedEvents));
        }

        if (empty($promotedEvents) && $schoolId) {
            $promotedEvents = $eventModel->getPromotedEventsBySchool($schoolId, 5);
            error_log("DEBUG Dashboard: Re-attempt school events count = " . count($promotedEvents));
        }
        
        error_log("DEBUG Dashboard: Final promoted events count = " . count($promotedEvents));
        
        $data = [
            'user' => $user,
            'createdEvents' => $createdEvents,
            'attendedEvents' => $attendedEvents,
            'promotedEvents' => $promotedEvents
        ];
        
        $this->view('dashboard/index', $data);
    }
    
    public function profile() {
        $userModel = $this->model('User');
        $uid = $this->getCurrentUserId();
        $user = $userModel->getUserById($uid);
        
        // If user not found, clear session and redirect to login
        if (!$user) {
            session_destroy();
            $this->redirect('/auth/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if user is a student
            $isStudent = (int)($user['is_student'] ?? 0) === 1;
            
            if ($isStudent) {
                // Students cannot change their names
                $this->setFlash('error', 'Students cannot modify their name information as it is managed by the university system.');
                $this->view('dashboard/profile', ['user' => $user]);
                return;
            }
            
            $fname = trim($_POST['fname'] ?? '');
            $mname = trim($_POST['mname'] ?? '');
            $lname = trim($_POST['lname'] ?? '');
            
            if (empty($fname) || empty($lname)) {
                $this->setFlash('error', 'First name and last name are required');
                $this->view('dashboard/profile', ['user' => $user]);
                return;
            }
            
            $userData = [
                'fname' => $fname,
                'mname' => $mname,
                'lname' => $lname
            ];
            
            if ($userModel->updateUser($uid, $userData)) {
                $this->setFlash('success', 'Profile updated successfully');
                $user = $userModel->getUserById($uid); // Refresh user data
            } else {
                $this->setFlash('error', 'Failed to update profile');
            }
        }
        
        $this->view('dashboard/profile', ['user' => $user]);
    }
    
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $this->setFlash('error', 'Please fill in all fields');
                $this->redirect('/dashboard/profile');
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                $this->setFlash('error', 'New passwords do not match');
                $this->redirect('/dashboard/profile');
                return;
            }
            
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                $this->setFlash('error', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long');
                $this->redirect('/dashboard/profile');
                return;
            }
            
            $userModel = $this->model('User');
            $uid = $this->getCurrentUserId();
            $user = $userModel->getUserById($uid);
            
            if (!password_verify($currentPassword, $user['password'])) {
                $this->setFlash('error', 'Current password is incorrect');
                $this->redirect('/dashboard/profile');
                return;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            if ($userModel->updatePassword($uid, $hashedPassword)) {
                $this->setFlash('success', 'Password changed successfully');
            } else {
                $this->setFlash('error', 'Failed to change password');
            }
        }
        
        $this->redirect('/dashboard/profile');
    }
    
    public function uploadProfilePicture() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
            $file = $_FILES['profile_picture'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->setFlash('error', 'File upload failed');
                $this->redirect('/dashboard/profile');
                return;
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                $this->setFlash('error', 'Only JPEG, PNG, and GIF files are allowed');
                $this->redirect('/dashboard/profile');
                return;
            }
            
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                $this->setFlash('error', 'File size must be less than 5MB');
                $this->redirect('/dashboard/profile');
                return;
            }
            
            $uploadDir = PROFILE_PICTURES_PATH;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uid = $this->getCurrentUserId();
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $uid . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $userModel = $this->model('User');
                if ($userModel->updateProfilePicture($uid, $filename)) {
                    $this->setFlash('success', 'Profile picture updated successfully');
                } else {
                    $this->setFlash('error', 'Failed to update profile picture');
                }
            } else {
                $this->setFlash('error', 'Failed to upload file');
            }
        }
        
        $this->redirect('/dashboard/profile');
    }
    
    public function badges() {
        $userModel = $this->model('User');
        $uid = $this->getCurrentUserId();
        $attendedEvents = $userModel->getAttendedEvents($uid);
        
        $this->view('dashboard/badges', ['attendedEvents' => $attendedEvents]);
    }
} 