<?php
require 'includes/db.php'; // Include the database connection

// Check if the token is provided in the URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    // Sanitize and trim the token
    $token = htmlspecialchars(trim($_GET['token']));

    // Debugging output to verify token retrieval
    echo "Token from URL: " . $token . "<br>";

    // Validate token format (assuming tokens are 64 characters long, adjust if necessary)
    if (strlen($token) !== 64) {
        die("<p>Invalid token format.</p>");
    }

    // Check if the token exists and the user is not yet confirmed
    $stmt = $conn->prepare("SELECT id FROM users WHERE token = ? AND is_confirmed = 0");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token exists, proceed to confirm the user
        $stmt = $conn->prepare("UPDATE users SET is_confirmed = 1, token = NULL WHERE token = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            echo "<p>Your email has been successfully confirmed! You can now log in.</p>";
        } else {
            echo "<p>Error updating confirmation. Please try again later.</p>";
        }
    } else {
        // Token is invalid or the user is already confirmed
        echo "<p>Invalid or expired confirmation link.</p>";
    }

    $stmt->close();
} else {
    // No token provided
    echo "<p>No confirmation token provided. Please check the link and try again.</p>";
}

// Close the database connection
$conn->close();
?>



