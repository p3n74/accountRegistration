<?php
require 'includes/db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $sql = "SELECT id FROM users WHERE token = ? AND is_confirmed = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $sql = "UPDATE users SET is_confirmed = 1 WHERE token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        echo "Your account has been verified!";
    } else {
        echo "Invalid or expired token.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "No token provided.";
}
?>

