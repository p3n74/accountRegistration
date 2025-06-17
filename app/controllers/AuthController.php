<?php

class AuthController extends Controller {
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $this->setFlash('error', 'Please fill in all fields');
                $this->view('auth/login');
                return;
            }
            
            $userModel = $this->model('User');
            $user = $userModel->getUserByEmail($email);
            
            if (!$user || !password_verify($password, $user['password'])) {
                $this->setFlash('error', 'Invalid email or password');
                $this->view('auth/login');
                return;
            }
            
            // Start session and set user data
            session_start();
            $_SESSION['uid'] = $user['uid'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['fname'] = $user['fname'];
            
            $this->redirect('/dashboard');
        } else {
            $this->view('auth/login');
        }
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fname = trim($_POST['fname'] ?? '');
            $mname = trim($_POST['mname'] ?? '');
            $lname = trim($_POST['lname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (empty($fname) || empty($lname) || empty($email) || empty($password)) {
                $this->setFlash('error', 'Please fill in all required fields');
                $this->view('auth/register', ['fname' => $fname, 'mname' => $mname, 'lname' => $lname, 'email' => $email]);
                return;
            }
            
            if ($password !== $confirmPassword) {
                $this->setFlash('error', 'Passwords do not match');
                $this->view('auth/register', ['fname' => $fname, 'mname' => $mname, 'lname' => $lname, 'email' => $email]);
                return;
            }
            
            if (strlen($password) < PASSWORD_MIN_LENGTH) {
                $this->setFlash('error', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long');
                $this->view('auth/register', ['fname' => $fname, 'mname' => $mname, 'lname' => $lname, 'email' => $email]);
                return;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Please enter a valid email address');
                $this->view('auth/register', ['fname' => $fname, 'mname' => $mname, 'lname' => $lname, 'email' => $email]);
                return;
            }
            
            $userModel = $this->model('User');
            
            // Check if email already exists
            if ($userModel->getUserByEmail($email)) {
                $this->setFlash('error', 'Email already registered');
                $this->view('auth/register', ['fname' => $fname, 'mname' => $mname, 'lname' => $lname, 'email' => $email]);
                return;
            }
            
            // Create user
            $fullname = $fname . ' ' . $mname . ' ' . $lname;
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));
            
            $userData = [
                'fname' => $fname,
                'mname' => $mname,
                'lname' => $lname,
                'fullname' => $fullname,
                'email' => $email,
                'password' => $hashedPassword,
                'token' => $token
            ];
            
            if ($userModel->createUser($userData)) {
                // Send verification email
                $this->sendVerificationEmail($email, $token);
                $this->setFlash('success', 'Registration successful! Please check your email to verify your account.');
                $this->redirect('/auth/login');
            } else {
                $this->setFlash('error', 'Registration failed. Please try again.');
                $this->view('auth/register', ['fname' => $fname, 'mname' => $mname, 'lname' => $lname, 'email' => $email]);
            }
        } else {
            $this->view('auth/register');
        }
    }
    
    public function logout() {
        session_start();
        session_destroy();
        $this->redirect('/auth/login');
    }
    
    public function resetRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Please enter a valid email address');
                $this->view('auth/reset-request');
                return;
            }
            
            $userModel = $this->model('User');
            $user = $userModel->getUserByEmail($email);
            
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY);
                
                if ($userModel->setPasswordResetToken($email, $token, $expiry)) {
                    $this->sendPasswordResetEmail($email, $token);
                }
            }
            
            // Always show success message for security
            $this->setFlash('success', 'If an account with that email exists, a password reset link has been sent.');
            $this->redirect('/auth/login');
        } else {
            $this->view('auth/reset-request');
        }
    }
    
    public function resetPassword($token = null) {
        if (!$token) {
            $this->redirect('/auth/login');
        }
        
        $userModel = $this->model('User');
        $user = $userModel->getUserByResetToken($token);
        
        if (!$user) {
            $this->setFlash('error', 'Invalid or expired reset token');
            $this->redirect('/auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($password) || $password !== $confirmPassword) {
                $this->setFlash('error', 'Passwords do not match');
                $this->view('auth/reset-password', ['token' => $token]);
                return;
            }
            
            if (strlen($password) < PASSWORD_MIN_LENGTH) {
                $this->setFlash('error', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long');
                $this->view('auth/reset-password', ['token' => $token]);
                return;
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            if ($userModel->updatePassword($user['uid'], $hashedPassword)) {
                $userModel->clearResetToken($user['email']);
                $this->setFlash('success', 'Password reset successful! You can now login with your new password.');
                $this->redirect('/auth/login');
            } else {
                $this->setFlash('error', 'Password reset failed. Please try again.');
                $this->view('auth/reset-password', ['token' => $token]);
            }
        } else {
            $this->view('auth/reset-password', ['token' => $token]);
        }
    }
    
    public function verifyEmail($token = null) {
        if (!$token) {
            $this->redirect('/auth/login');
        }
        
        $userModel = $this->model('User');
        
        if ($userModel->verifyEmail($token)) {
            $this->setFlash('success', 'Email verified successfully! You can now login.');
        } else {
            $this->setFlash('error', 'Invalid verification token');
        }
        
        $this->redirect('/auth/login');
    }
    
    private function sendVerificationEmail($email, $token) {
        $subject = "Email Verification - " . APP_NAME;
        $verificationLink = APP_URL . "/auth/verify/" . $token;
        
        $message = "
        <html>
        <body>
            <h2>Welcome to " . APP_NAME . "!</h2>
            <p>Please click the link below to verify your email address:</p>
            <p><a href='{$verificationLink}'>{$verificationLink}</a></p>
            <p>If you didn't create an account, you can safely ignore this email.</p>
        </body>
        </html>";
        
        $this->sendEmail($email, $subject, $message);
    }
    
    private function sendPasswordResetEmail($email, $token) {
        $subject = "Password Reset - " . APP_NAME;
        $resetLink = APP_URL . "/auth/reset/" . $token;
        
        $message = "
        <html>
        <body>
            <h2>Password Reset Request</h2>
            <p>You requested a password reset. Click the link below to reset your password:</p>
            <p><a href='{$resetLink}'>{$resetLink}</a></p>
            <p>This link will expire in 10 minutes.</p>
            <p>If you didn't request a password reset, you can safely ignore this email.</p>
        </body>
        </html>";
        
        $this->sendEmail($email, $subject, $message);
    }
    
    private function sendEmail($to, $subject, $message) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . APP_NAME . " <" . SMTP_USER . ">" . "\r\n";
        
        mail($to, $subject, $message, $headers);
    }
} 