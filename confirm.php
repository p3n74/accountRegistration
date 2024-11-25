<?php
require 'includes/db.php'; // Include the database connection

if (isset($_GET['token'])) {
    $token = htmlspecialchars($_GET['token']); // Sanitize the token from the URL

    $stmt = $conn->prepare("SELECT id FROM users WHERE token = ? AND is_confirmed = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE users SET is_confirmed = 1, token = NULL WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            echo "<p>Your email has been successfully confirmed! You can now log in.</p>";
        } else {
            echo "<p>There was an error confirming your email. Please try again later.</p>";
        }
    } else {
        echo "<p>Invalid or expired confirmation link. Please contact support if you believe this is an error.</p>";
    }

    $stmt->close();
} else {
    echo "<p>No confirmation token provided. Please check the link and try again.</p>";
}

$conn->close();
?>

