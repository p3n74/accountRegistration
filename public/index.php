<?php
// Start session
session_start();

// Load configuration
require_once '../app/config/config.php';

// Load core classes
require_once '../app/core/Database.php';
require_once '../app/core/Model.php';
require_once '../app/core/Controller.php';
require_once '../app/core/App.php';

// Check for authentication via currboundtoken cookie
if (isset($_COOKIE['currboundtoken']) && !isset($_SESSION['uid'])) {
    try {
        $db = new Database();
        $stmt = $db->prepare("SELECT uid, fname, lname, email, currboundtoken FROM user_credentials WHERE currboundtoken = ?");
        $stmt->bind_param("s", $_COOKIE['currboundtoken']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Set session variables
            $_SESSION['uid'] = $user['uid'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['lname'] = $user['lname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['currboundtoken'] = $user['currboundtoken'];
        }
    } catch (Exception $e) {
        // Log error but don't break the application
        error_log("Authentication check error: " . $e->getMessage());
    }
}

// Handle routing for PHP's built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Serve the file directly
}

// Start the application
$app = new App();
