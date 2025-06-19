<?php

class AuthController extends Controller {
    private $userModel;
    private $existingStudentModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->existingStudentModel = $this->model('ExistingStudent');
    }
    
    public function index() {
        // Default method - redirect to login
        $this->redirect('/auth/login');
    }
    
    public function login() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['uid'])) {
            header('Location: /dashboard');
            exit;
        }

        // Handle POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $this->view('auth/login', [
                    'error' => 'Please fill in all fields'
                ]);
                return;
            }
            
            try {
                $user = $this->userModel->getUserByEmail($email);
                
                if (!$user) {
                    // Email not found
                    $this->view('auth/login', [
                        'error' => 'Email not registered'
                    ]);
                    return;
                }

                if ((int)($user['emailverified'] ?? 0) !== 1) {
                    // Email not verified
                    $this->view('auth/login', [
                        'error' => 'Email not verified. Please check your inbox.'
                    ]);
                    return;
                }

                if (!password_verify($password, $user['password'])) {
                    // Password incorrect
                    $this->view('auth/login', [
                        'error' => 'Incorrect password'
                    ]);
                    return;
                }

                // Successful login
                $_SESSION['uid'] = $user['uid'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['fname'] = $user['fname'] ?? '';
                $_SESSION['lname'] = $user['lname'] ?? '';
                $_SESSION['last_activity'] = time();

                session_regenerate_id(true);

                header('Location: /dashboard');
                exit;
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $this->view('auth/login', [
                    'error' => 'An error occurred during login. Please try again.'
                ]);
            }
        } else {
            // Display login form
            $this->view('auth/login');
        }
    }
    
    public function register() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['uid'])) {
            header('Location: /dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fname = $_POST['fname'] ?? '';
            $mname = $_POST['mname'] ?? '';
            $lname = $_POST['lname'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Check if this is an existing student and override names if necessary
            $existingStudent = $this->existingStudentModel->getStudentByEmail($email);
            if ($existingStudent) {
                $fname = $existingStudent['fname'];
                $mname = $existingStudent['mname'] ?? '';
                $lname = $existingStudent['lname'];
            }
            
            if (empty($fname) || empty($lname) || empty($email) || empty($password) || empty($confirm_password)) {
                $this->view('auth/register', [
                    'error' => 'Please fill in all fields',
                    'fname' => $fname,
                    'mname' => $mname,
                    'lname' => $lname,
                    'email' => $email
                ]);
                return;
            }
            
            if ($password !== $confirm_password) {
                $this->view('auth/register', [
                    'error' => 'Passwords do not match',
                    'fname' => $fname,
                    'mname' => $mname,
                    'lname' => $lname,
                    'email' => $email
                ]);
                return;
            }
            
            try {
                if ($this->userModel->getUserByEmail($email)) {
                    $this->view('auth/register', [
                        'error' => 'Email already exists',
                        'fname' => $fname,
                        'mname' => $mname,
                        'lname' => $lname,
                        'email' => $email
                    ]);
                    return;
                }
                
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));

                $fullname = trim($fname . ' ' . ($mname ? $mname . ' ' : '') . $lname);

                $isCreated = $this->userModel->createUser([
                    'fname' => $fname,
                    'mname' => $mname,
                    'lname' => $lname,
                    'fullname' => $fullname,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'token' => $token
                ]);
                
                if ($isCreated) {
                    // createUser now returns the GUID
                    $uid = $isCreated;
                    
                    // Send email verification instead of direct login
                    $this->sendVerificationEmail($email, $token);
                    
                    $this->view('auth/register', [
                        'success' => 'Registration successful! Please check your email for a verification link before logging in.'
                    ]);
                } else {
                    $this->view('auth/register', [
                        'error' => 'Registration failed',
                        'fname' => $fname,
                        'mname' => $mname,
                        'lname' => $lname,
                        'email' => $email
                    ]);
                }
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $this->view('auth/register', [
                    'error' => 'An error occurred during registration. Please try again.',
                    'fname' => $fname,
                    'mname' => $mname,
                    'lname' => $lname,
                    'email' => $email
                ]);
            }
        } else {
            $this->view('auth/register');
        }
    }
    
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        header('Location: /auth/login');
        exit;
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
                
                if ($userModel->setPasswordResetToken($email, $token, TOKEN_EXPIRY)) {
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
        $token = trim((string)$token);
        if ($token === '') {
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
    
    public function verify($token = null) {
        if (!$token || strlen($token) !== 64) {
            $this->setFlash('error', 'Invalid token provided.');
            $this->redirect('/auth/login');
            return;
        }
        
        // Use legacy-compatible verification logic
        require_once '../includes/db.php';
        
        $stmt = $conn->prepare("SELECT uid FROM user_credentials WHERE currboundtoken = ? AND emailverified = 0");
        if (!$stmt) {
            $this->setFlash('error', 'Database error occurred.');
            $this->redirect('/auth/login');
            return;
        }
        
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $updateStmt = $conn->prepare("UPDATE user_credentials SET emailverified = 1, currboundtoken = '0' WHERE currboundtoken = ?");
            if (!$updateStmt) {
                $this->setFlash('error', 'Database error occurred.');
                $this->redirect('/auth/login');
                return;
            }
            
            $updateStmt->bind_param("s", $token);
            if ($updateStmt->execute()) {
                $this->setFlash('success', 'Your email has been successfully verified! You can now log in.');
            } else {
                $this->setFlash('error', 'Error during verification. Please try again.');
            }
            $updateStmt->close();
        } else {
            $this->setFlash('error', 'Invalid or expired verification token.');
        }
        
        $stmt->close();
        $this->redirect('/auth/login');
    }
    
    private function sendVerificationEmail($email, $token) {
        require_once '../phpmailer/Exception.php';
        require_once '../phpmailer/PHPMailer.php';
        require_once '../phpmailer/SMTP.php';
        require_once '../includes/apikey.php';
        require_once '../includes/config.php';

        $verificationLink = BASE_URL . 'auth/verify/' . $token;

        $signature = "<br><br>--
                      <br><strong>Nikolai Tristan E. Pazon</strong>
                      <br>Vice-President for Finance | Computer and Information Sciences Council
                      <br><a href='http://dcism.org'>Department of Computer, Information Sciences, and Mathematics</a>
                      <br><span style='color: green;'>UNIVERSITY OF SAN CARLOS</span>
                      <br><em style='color: green;'>The content of this email is confidential and is intended for the recipient specified in message only. It is strictly forbidden to share any part of this message with any third party without the express consent of the sender. If you received this message by mistake, please reply to this message and follow with its deletion, so that we can ensure such a mistake does not occur in the future.
    </em>";

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '21102134@usc.edu.ph';
            $mail->Password   = $apikey;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('21102134@usc.edu.ph', 'DCISM Accounts');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Email Verification - DCISM Accounts';
            $mail->Body    = "<p>Welcome to DCISM Accounts!</p>
                               <p>Please click the link below to verify your email address:</p>
                               <p><a href='$verificationLink'>Verify My Email</a></p>
                               <p>If you didn't create an account, you can safely ignore this email.</p>" . $signature;

            $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('PHPMailer error: ' . $e->getMessage());
        }
    }
    
    private function sendPasswordResetEmail($email, $token) {
        require_once '../phpmailer/Exception.php';
        require_once '../phpmailer/PHPMailer.php';
        require_once '../phpmailer/SMTP.php';
        require_once '../includes/apikey.php';
        require_once '../includes/config.php';

        $resetLink = BASE_URL . 'auth/reset/' . $token;

        $signature = "<br><br>--
                      <br><strong>Nikolai Tristan E. Pazon</strong>
                      <br>Vice-President for Finance | Computer and Information Sciences Council
                      <br><a href='http://dcism.org'>Department of Computer, Information Sciences, and Mathematics</a>
                      <br><span style='color: green;'>UNIVERSITY OF SAN CARLOS</span>
                      <br><em style='color: green;'>The content of this email is confidential and is intended for the recipient specified in message only. It is strictly forbidden to share any part of this message with any third party without the express consent of the sender. If you received this message by mistake, please reply to this message and follow with its deletion, so that we can ensure such a mistake does not occur in the future.
    </em>";

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '21102134@usc.edu.ph';
            $mail->Password   = $apikey;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('21102134@usc.edu.ph', 'DCISM Accounts');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "<p>You requested a password reset. Click the link below to reset your password:</p>
                               <p><a href='$resetLink'>Reset My Password</a></p>
                               <p>This link will expire in 1 hour.</p>" . $signature;

            $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('PHPMailer error: ' . $e->getMessage());
        }
    }
    
    private function sendEmail($to, $subject, $message) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . APP_NAME . " <" . SMTP_USER . ">" . "\r\n";
        
        mail($to, $subject, $message, $headers);
    }

    public function reset($token = null) {
        return $this->resetPassword($token);
    }

    public function checkExistingStudent() {
        // Set content type to JSON first
        header('Content-Type: application/json');
        
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Get email from request
        $email = $_POST['email'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Invalid email address', 'debug' => 'Email validation failed']);
            return;
        }

        try {
            $existingStudent = $this->existingStudentModel->getStudentByEmail($email);
            
            if ($existingStudent) {
                echo json_encode([
                    'exists' => true,
                    'data' => [
                        'fname' => $existingStudent['fname'],
                        'mname' => $existingStudent['mname'] ?? '',
                        'lname' => $existingStudent['lname']
                    ],
                    'debug' => 'Student found'
                ]);
            } else {
                echo json_encode(['exists' => false, 'debug' => 'Student not found']);
            }
        } catch (Exception $e) {
            error_log("Error checking existing student: " . $e->getMessage());
            echo json_encode(['error' => 'Server error', 'debug' => $e->getMessage()]);
        }
        exit; // Make sure we don't output anything else
    }
} 