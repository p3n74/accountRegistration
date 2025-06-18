<?php
// Local database connection for cleanup
$servername = "127.0.0.1";
$username = "s21102134_palisade"; 
$password = "webwebwebweb";
$dbname = "s21102134_palisade";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Cleaning up migration remnants...\n";

// Check if new_uid column exists and drop it
$checkColumn = $conn->query("SHOW COLUMNS FROM user_credentials LIKE 'new_uid'");
if ($checkColumn && $checkColumn->num_rows > 0) {
    echo "Dropping leftover new_uid column...\n";
    $conn->query("ALTER TABLE user_credentials DROP COLUMN new_uid");
    echo "Cleanup complete.\n";
} else {
    echo "No cleanup needed.\n";
}

$conn->close();
?> 