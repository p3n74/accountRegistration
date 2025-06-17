<?php
require '../includes/db.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == "reset-password") {
    // Get the token and new password from the form
    $token = $_POST['token'];
    $newPassword = $_POST['password'];

    // Check if the token is valid and not expired
    $stmt = $conn->prepare("SELECT * FROM user_credentials WHERE password_reset_token = ? AND password_reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token is valid, reset the password
        $row = $result->fetch_assoc();
        $email = $row['email'];

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the password in the database
        $update_sql = "UPDATE user_credentials SET password = ?, password_reset_token = NULL, password_reset_expiry = NULL WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashedPassword, $email);
        $update_stmt->execute();

        // Redirect to the login page after successful password reset to avoid resubmission
        header("Location: login.php");
        exit; // Ensure no further code is executed
    } else {
        // Handle invalid or expired token (if needed)
        // You can set an error message or log the attempt
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Favicon -->
    <link rel="icon" href="icon.png" type="image/png">
    <!-- Tailwind CSS -->
    <link href="dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-center mb-6 text-primary-700">Reset Password</h2>
            
            <?php if (isset($message)): ?>
                <div class="alert <?php echo $alert_class; ?> mb-6"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" action="reset-password.php">
                <input type="hidden" name="action" value="reset-password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="mb-6">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-primary w-full">Reset Password</button>
            </form>

            <a href="login.php" class="text-primary-600 hover:text-primary-700 hover:underline mt-4 block text-center">Remembered your password? Login here</a>
        </div>
    </div>
</body>
</html>

