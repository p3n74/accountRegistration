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
        $createdEvents = $eventModel->getEventsByCreator($uid);
        $attendedEvents = $userModel->getAttendedEvents($uid);
        
        $data = [
            'user' => $user,
            'createdEvents' => $createdEvents,
            'attendedEvents' => $attendedEvents
        ];
        
        $this->view('dashboard/index', $data);
    }
    
    public function profile() {
        $userModel = $this->model('User');
        $uid = $this->getCurrentUserId();
        $user = $userModel->getUserById($uid);
        
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