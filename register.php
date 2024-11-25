<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'includes/db.php'; // Include the database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if (strlen($password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Generate a unique token for email confirmation
    $token = bin2hex(random_bytes(32));

    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, token, is_confirmed) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $token);

    if ($stmt->execute()) {
        // Send confirmation email
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '21102134@usc.edu.ph'; // Replace with your Gmail
            $mail->Password = 'rufm xhjs ntyk ofkc';   // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('211021342@usc.edu.ph', 'Nikolai');
            $mail->addAddress($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Confirm Your Account';
            $confirmationLink = "http://yourwebsite.com/confirm.php?token=$token";
            $mail->Body = "<p>Hi $name,</p>
                           <p>Thank you for registering. Please click the link below to confirm your email:</p>
                           <p><a href='$confirmationLink'>Confirm My Account</a></p>";

            $mail->send();
            echo "Registration successful. A confirmation email has been sent to $email.";
        } catch (Exception $e) {
            echo "Error sending email: " . $mail->ErrorInfo;
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

