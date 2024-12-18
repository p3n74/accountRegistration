<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'includes/db.php'; // Include the database connection
require 'includes/apikey.php'; // Include the API key

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == "register") {
    // Sanitize and validate inputs
    $fname = htmlspecialchars($_POST['fname']);
    $mname = htmlspecialchars($_POST['mname']);
    $lname = htmlspecialchars($_POST['lname']);

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if (strlen($password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    // Create the fullname variable by concatenating first, middle, and last names
    $fullname = trim("$fname $mname $lname");

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if the email already exists in the database
    $checkEmailSql = "SELECT * FROM user_credentials WHERE email = ?";
    $stmt = $conn->prepare($checkEmailSql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Error: This email address is already registered.");
    }

    // Generate a unique token for email confirmation
    $token = bin2hex(random_bytes(32));

    // Insert user into the database
    $insertSql = "INSERT INTO user_credentials (fname, mname, lname, fullname, email, password, currboundtoken, emailverified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("sssssss", $fname, $mname, $lname, $fullname, $email, $hashedPassword, $token);

    if ($stmt->execute()) {
        // Send confirmation email
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '21102134@usc.edu.ph'; // Gmail used to send email 
            $mail->Password = $apikey; // API KEY 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('211021342@usc.edu.ph', 'Nikolai');
            $mail->addAddress($email, $fname);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Confirm Your DCISM Account';
            $confirmationLink = "http://accounts.dcism.org/accountRegistration/confirm.php?token=$token";
            $mail->Body = "<p>Hi $fname,</p>
                           <p>Thank you for registering. Please click the link below to confirm your email:</p>
                           <p><a href='$confirmationLink'>Confirm My Account</a></p>";

            $mail->send();

            // Redirect to avoid form resubmission
            header("Location: regpage.php?status=success");
            exit; // Make sure to exit after the redirect
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

