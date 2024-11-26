<?php
session_start(); // Start the session

require 'includes/db.php'; // Include database connection

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'login') {
    // Sanitize and validate inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Prepare SQL to fetch user details
    $stmt = $conn->prepare("SELECT uid, fname, lname, password, emailverified FROM user_credentials WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify if email is verified
        if ($user['emailverified'] != 1) {
            die("Email not verified. Please check your email for the confirmation link.");
        }

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Successful login
            // Store user details in the session
            $_SESSION['uid'] = $user['uid'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['lname'] = $user['lname'];
            $_SESSION['email'] = $email;

            // Redirect to a dashboard or home page
            header("Location: dashboard.php");
            exit;
        } else {
            // Incorrect password
            die("Incorrect password.");
        }
    } else {
        // Email not found
        die("Email not registered.");
    }

    $stmt->close();
}

$conn->close();
?>

