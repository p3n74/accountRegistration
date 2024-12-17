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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styling for the Reset Password Page */
        .container {
            max-width: 500px;
            margin-top: 100px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007BFF;
            border: none;
        }
        h2 {
            color: #007BFF;
        }
        label {
            font-weight: bold;
        }
        .btn-link {
            color: #007BFF;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Reset Your Password</h2>
        <form method="POST">
            <input type="hidden" name="action" value="reset-password">
            <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>"> <!-- Pass the token from the URL -->
            <div class="mb-3">
                <label for="password" class="form-label">Enter a new password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>

        <!-- Link to the Login page -->
        <div class="mt-3 text-center">
            <a href="login.php" class="btn btn-link">Remembered your password? Login here</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

