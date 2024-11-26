<?php
// Ensure the session is active
session_start();

// Check if the user is logged in by verifying session variables
if (isset($_SESSION['uid'], $_SESSION['email'], $_SESSION['fname'])) {
    // Display session variables
    echo "User ID: " . $_SESSION['uid'] . "<br>";
    echo "Email: " . $_SESSION['email'] . "<br>";
    echo "First Name: " . $_SESSION['fname'] . "<br>";
} else {
    // Redirect to login page or show an error if not logged in
    echo "You are not logged in. Please log in to access this page.";
    // Optionally redirect:
    // header("Location: login.php");
    // exit;
}
?>

