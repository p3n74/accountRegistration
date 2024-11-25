<?php
require 'includes/db.php';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    // Sanitize and validate the token
    $token = htmlspecialchars(trim($_GET['token']));
    echo "Token from URL: " . $token . "<br>";

    if (strlen($token) !== 64) {
        die("<p>Invalid token length. Please check your confirmation link.</p>");
    }

    // Verify the token exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE token = ? AND is_confirmed = 0");
    if (!$stmt) {
        die("Prepare failed during SELECT: " . $conn->error);
    }

    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token is valid and user is not confirmed
        echo "<p>Token found in the database. Confirming the user...</p>";
        
        $updateStmt = $conn->prepare("UPDATE users SET is_confirmed = 1, token = '0' WHERE token = ?");
        if (!$updateStmt) {
            die("Prepare failed during UPDATE: " . $conn->error);
        }

        $updateStmt->bind_param("s", $token);
        if ($updateStmt->execute()) {
            echo "<p>Your email has been successfully confirmed! You can now log in.</p>";
        } else {
            echo "<p>Error during update execution: " . $updateStmt->error . "</p>";
        }

        $updateStmt->close();
    } else {
        // No matching token found or already confirmed
        echo "<p>Invalid or expired confirmation link.</p>";
    }

    $stmt->close();
} else {
    echo "<p>No confirmation token provided. Please check your link.</p>";
}

$conn->close();
?>

