<?php
require 'includes/db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styling for the Confirmation Page */
        .container {
            max-width: 600px;
            margin-top: 100px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007BFF;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Email Confirmation</h2>

        <?php
        if (isset($_GET['token']) && !empty($_GET['token'])) {
            // Sanitize and validate the token
            $token = htmlspecialchars(trim($_GET['token']));
            
            if (strlen($token) !== 64) {
                echo "<div class='alert alert-danger'>Invalid token length. Please check your confirmation link.</div>";
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
                    echo "<div class='alert alert-info'>Token found. Confirming your email...</div>";

                    $updateStmt = $conn->prepare("UPDATE user_credentials SET emailverified = 1, currboundtoken = '0' WHERE currboundtoken = ?");
                    if (!$updateStmt) {
                        die("Prepare failed during UPDATE: " . $conn->error);
                    }

                    $updateStmt->bind_param("s", $token);
                    if ($updateStmt->execute()) {
                        echo "<div class='alert alert-success'>Your email has been successfully confirmed! You can now <a href='login.php'>log in</a>.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error during update execution: " . $updateStmt->error . "</div>";
                    }

                    $updateStmt->close();
                } else {
                    // No matching token found or already confirmed
                    echo "<div class='alert alert-danger'>Invalid or expired confirmation link.</div>";
                }

                $stmt->close();
            }
        } else {
            echo "<div class='alert alert-danger'>No confirmation token provided. Please check your link.</div>";
        }

        $conn->close();
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

