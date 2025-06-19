<?php
// Start session
session_start();

// Load configuration
require_once 'app/config/config.php';

// Load core classes
require_once 'app/core/Database.php';
require_once 'app/core/Model.php';
require_once 'app/core/Controller.php';
require_once 'app/core/App.php';

// Handle routing for PHP's built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Serve the file directly
}

// Start the application
$app = new App();
