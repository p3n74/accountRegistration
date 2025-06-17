<?php
require 'includes/db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Email Confirmation</title>
	 <!-- Favicon -->
 <link rel="icon" href="icon.png" type="image/png">
    <!-- Tailwind CSS -->
    <link href="dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-center mb-6 text-primary-700">Email Confirmation</h2>

            <?php
            if (isset($_GET['token']) && !empty($_GET['token'])) {
                // Sanitize and validate the token
                $token = htmlspecialchars(trim($_GET['token']));
                
                if (strlen($token) !== 64) {
                    echo "<div class='alert alert-danger mb-6'>Invalid token length. Please check your confirmation link.</div>";
                } else {
                    // Verify the token exists in the database
                    $stmt = $conn->prepare("SELECT uid FROM user_credentials WHERE currboundtoken = ? AND emailverified = 0");
                    if (!$stmt) {
                        die("Prepare failed during SELECT: " . $conn->error);
                    }

                    $stmt->bind_param("s", $token);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        // Token is valid and user is not confirmed
                        echo "<div class='alert alert-info mb-6'>Token found. Confirming your email...</div>";

                        $updateStmt = $conn->prepare("UPDATE user_credentials SET emailverified = 1, currboundtoken = '0' WHERE currboundtoken = ?");
                        if (!$updateStmt) {
                            die("Prepare failed during UPDATE: " . $conn->error);
                        }

                        $updateStmt->bind_param("s", $token);
                        if ($updateStmt->execute()) {
                            echo "<div class='alert alert-success mb-6'>Your email has been successfully confirmed! You can now <a href='login.php' class='text-primary-600 hover:underline'>log in</a>.</div>";
                        } else {
                            echo "<div class='alert alert-danger mb-6'>Error during update execution: " . $updateStmt->error . "</div>";
                        }

                        $updateStmt->close();
                    } else {
                        echo "<div class='alert alert-danger mb-6'>Invalid or expired token. Please check your confirmation link or request a new one.</div>";
                    }

                    $stmt->close();
                }
            } else {
                echo "<div class='alert alert-danger mb-6'>No token provided. Please check your confirmation link.</div>";
            }
            ?>

            <div class="text-center">
                <a href="login.php" class="btn-primary">Go to Login</a>
            </div>
        </div>
    </div>
</body>
</html>

