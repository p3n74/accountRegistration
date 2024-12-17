<?php
require 'includes/db.php'; // Include the database connection

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

        echo "Your password has been successfully updated.";
    } else {
        echo "Invalid or expired token.";
    }

    $stmt->close();
    $conn->close();
}
?>

<form method="POST">
    <input type="hidden" name="action" value="reset-password">
    <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>"> <!-- Pass the token from the URL -->
    <label for="password">Enter a new password:</label>
    <input type="password" name="password" id="password" required>
    <input type="submit" value="Reset Password">
</form>

