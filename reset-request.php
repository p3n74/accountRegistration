<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'includes/db.php'; // Include the database connection
require 'includes/apikey.php'; // Include the API key

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == "reset-request") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

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
            $resetLink = "http://accounts.dcism.org/accountRegistration/reset-password.php?token=$token"; // Include token in URL
            $mail->Body = "<p>We received a request to reset your password. Please click the link below to reset your password:</p>
                           <p><a href='$resetLink'>Reset My Password</a></p>";

            $mail->send();
            echo "A password reset email has been sent to $email.";
        } catch (Exception $e) {
            echo "Error sending email: " . $mail->ErrorInfo;
        }
    } else {
        echo "This email is not registered.";
    }

    $stmt->close();
    $conn->close();
}
?>

<form method="POST">
    <input type="hidden" name="action" value="reset-request">
    <label for="email">Enter your email to reset your password:</label>
    <input type="email" name="email" id="email" required>
    <input type="submit" value="Send Reset Link">
</form>

