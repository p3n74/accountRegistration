<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include Composer's autoload if installed via Composer

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = '21102134@usc.edu.ph'; // Your Gmail address
    $mail->Password = 'ygfl uysf pthz rcxn';   // App password created in step 1
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('your_email@gmail.com', 'Your Name');
    $mail->addAddress($email, $name); // User's email and name

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Account Confirmation';
    $mail->Body = "<p>Hello $name,</p>
                   <p>Click <a href='$link'>here</a> to confirm your email.</p>";

    $mail->send();
    echo "Registration successful. Please check your email to confirm your account.";
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}

