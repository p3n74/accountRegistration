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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f1f1;
        }
        .verification-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .verification-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="verification-container">
    <h2>Email Verification</h2>

    <div class="alert <?php echo $alert_class; ?>" role="alert">
        <?php echo $message; ?>
    </div>

    <div class="text-center">
        <a href="index.php" class="btn btn-primary">Go to Dashboard</a>
    </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>

</body>
</html>

