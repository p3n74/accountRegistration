<?php
// Start the session
session_start();

// Include database connection
require_once 'includes/db.php';

// Check if the verification code is provided
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $verification_code = $_GET['code'];

    // SQL to find the user with the provided verification code
    $sql = "SELECT uid, email, new_email, verification_code, emailverified FROM user_credentials WHERE verification_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $stmt->store_result();

    // Check if the verification code exists in the database
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($uid, $email, $new_email, $verification_code, $emailverified);
        $stmt->fetch();

        // If the user is already verified, inform them
        if ($emailverified == 1) {
            echo "<p>Your email is already verified.</p>";
        } else {
            // Mark the email as verified and update the user's email address
            $sql_update = "UPDATE user_credentials SET email = ?, verification_code = NULL, emailverified = 1 WHERE uid = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $new_email, $uid);
            $stmt_update->execute();

            if ($stmt_update->affected_rows > 0) {
                echo "<p>Your new email has been successfully verified and updated.</p>";
            } else {
                echo "<p>There was an error verifying your email. Please try again later.</p>";
            }
        }
    } else {
        // Invalid verification code
        echo "<p>The verification code is invalid or has expired.</p>";
    }

    $stmt->close();
} else {
    // If no verification code is provided
    echo "<p>No verification code provided.</p>";
}

$conn->close();
?>
