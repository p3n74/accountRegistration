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
            $message = "Your email is already verified.";
            $alert_class = "alert-success";
        } else {
            // Mark the email as verified and update the user's email address
            $sql_update = "UPDATE user_credentials SET email = ?, verification_code = NULL, emailverified = 1 WHERE uid = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $new_email, $uid);
            $stmt_update->execute();

            if ($stmt_update->affected_rows > 0) {
                $message = "Your new email has been successfully verified and updated.";
                $alert_class = "alert-success";
            } else {
                $message = "There was an error verifying your email. Please try again later.";
                $alert_class = "alert-danger";
            }
        }
    } else {
        // Invalid verification code
        $message = "The verification code is invalid or has expired.";
        $alert_class = "alert-danger";
    }

    $stmt->close();
} else {
    // If no verification code is provided
    $message = "No verification code provided.";
    $alert_class = "alert-danger";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <!-- Favicon -->
    <link rel="icon" href="icon.png" type="image/png">
    <!-- Tailwind CSS -->
    <link href="dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-center mb-6 text-primary-700">Email Verification</h2>
            
            <?php if (isset($message)): ?>
                <div class="alert <?php echo $alert_class; ?> mb-6"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="text-center">
                <a href="index.php" class="btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>

