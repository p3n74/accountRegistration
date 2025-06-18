<?php
require '../phpmailer/Exception.php';
require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../includes/db.php'; // Include the database connection
require '../includes/apikey.php'; // Include the API key
require_once '../includes/config.php'; // Include configuration (BASE_URL)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$emailError = ''; // Initialize error variable

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == "reset-request") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email format."; // Set error message if email is invalid
    } else {
        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT * FROM user_credentials WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email exists, generate reset token
            $token = bin2hex(random_bytes(32)); // Generate a unique token
            $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expiry time (1 hour)

            // Store the token and expiry in the database
            $update_sql = "UPDATE user_credentials SET password_reset_token = ?, password_reset_expiry = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sss", $token, $expiry_time, $email);
            $update_stmt->execute();

            // Send reset email with token
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '21102134@usc.edu.ph'; // Gmail used to send email
                $mail->Password = $apikey; // API KEY
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('211021342@usc.edu.ph', 'Nikolai');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $resetLink = BASE_URL . "reset-password.php?token=$token"; // Include token in URL
                $mail->Body = "<p>We received a request to reset your password. Please click the link below to reset your password:</p>
                               <p><a href='$resetLink'>Reset My Password</a></p>";

                $mail->send();
                // Password reset email sent successfully.
            } catch (Exception $e) {
                // Error sending email.
            }
        } else {
            $emailError = "This email is not registered."; // Set error message if email doesn't exist
        }

        $stmt->close();
        $conn->close();
        
        // Redirect to a success status page to prevent form resubmission
        header("Location: reset-request.php?status=success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Request</title>
    <!-- Favicon -->
    <link rel="icon" href="icon.png" type="image/png">
    <!-- Tailwind CSS -->
    <link href="dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-center mb-6 text-primary-700">Reset Password Request</h2>
            
            <?php if (!empty($emailError)): ?>
                <div class="alert alert-danger mb-6"><?php echo htmlspecialchars($emailError); ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="alert alert-success mb-6">If an account with that email exists, a password reset link has been sent. Please check your inbox.</div>
            <?php endif; ?>

            <form method="POST" action="reset-request.php">
                <input type="hidden" name="action" value="reset-request">
                
                <div class="mb-6">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-primary w-full">Send Reset Link</button>
            </form>

            <a href="login.php" class="text-primary-600 hover:text-primary-700 hover:underline mt-4 block text-center">Back to Login</a>
        </div>
    </div>
</body>
</html>

