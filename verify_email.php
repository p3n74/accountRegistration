<?php
// Start the session
session_start();

// Include database connection
require_once 'includes/db.php';

// Check if the verification code is passed in the URL
if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];

    // Query to check if the verification code exists in the database
    $sql_check_code = "SELECT uid, email FROM user_credentials WHERE verification_code = ?";
    $stmt_check_code = $conn->prepare($sql_check_code);
    $stmt_check_code->bind_param("s", $verification_code);
    $stmt_check_code->execute();
    $stmt_check_code->store_result(); // Store result for checking row count

    // If a user is found with the verification code
    if ($stmt_check_code->num_rows > 0) {
        // User exists, fetch the user's UID and email
        $stmt_check_code->bind_result($uid, $email);
        $stmt_check_code->fetch();

        // Update the user's email status by removing the verification code
        $sql_update_email = "UPDATE user_credentials SET verification_code = NULL WHERE uid = ?";
        $stmt_update_email = $conn->prepare($sql_update_email);
        $stmt_update_email->bind_param("i", $uid);
        $stmt_update_email->execute();

        // Send confirmation email to the user that the email was successfully verified
        $subject = "Email Verification Successful";
        $message = "Your email address has been successfully verified and updated.";
        $headers = "From: no-reply@yourwebsite.com";

        mail($email, $subject, $message, $headers);

        // Redirect the user to the login page or their dashboard with a success message
        $_SESSION['message'] = "Email successfully verified!";
        header("Location: login.php"); // Or you can redirect to the dashboard
        exit();

    } else {
        // No user found with that verification code
        $_SESSION['message'] = "Invalid or expired verification link.";
        header("Location: login.php"); // Or an error page
        exit();
    }

    $stmt_check_code->close();
} else {
    // If no verification code is passed in the URL
    $_SESSION['message'] = "No verification code provided.";
    header("Location: login.php"); // Or an error page
    exit();
}

$conn->close();
?>

