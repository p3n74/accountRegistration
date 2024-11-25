<?php
require 'includes/db.php';
require 'phpmailer/PHPMailerAutoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $token = bin2hex(random_bytes(32));

    // Insert user data into the database
    $sql = "INSERT INTO users (name, email, password, token, is_confirmed) VALUES (?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $password, $token);

    if ($stmt->execute()) {
        // Send confirmation email
        $link = "http://yourdomain.com/registration/verify.php?token=$token";
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@gmail.com'; // Replace with your email
        $mail->Password = 'your_email_password'; // Replace with your email password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'Registration System');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Account Confirmation';
        $mail->Body = "<p>Hello $name,</p><p>Click <a href='$link'>here</a> to confirm your email.</p>";

        if ($mail->send()) {
            echo "Registration successful. Please check your email to confirm your account.";
        } else {
            echo "Error: Unable to send confirmation email.";
        }
    } else {
        echo "Error: Unable to register. Please try again.";
    }

    $stmt->close();
    $conn->close();
}
?>

