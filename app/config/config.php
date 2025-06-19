<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 's21102134_palisade');
define('DB_PASS', 'webwebwebweb');
define('DB_NAME', 's21102134_palisade');

// Application configuration
define('APP_NAME', 'Event Management System');

// Auto-detect base URL and path for production
function getBasePath() {
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        
        // If we're in the public directory, go up one level
        if (basename($basePath) === 'public') {
            $basePath = dirname($basePath);
        }
        
        // Special case: if we're running from /accounts/ subdirectory
        // and the script name includes /accounts/, return /accounts
        if (strpos($scriptName, '/accounts/') !== false) {
            return '/accounts';
        }
        
        // Check if document root already points to our app directory
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        if (!empty($docRoot) && strpos($docRoot, 'accounts') !== false && $basePath === '/accounts') {
            // If document root contains 'accounts' and we're in /accounts path, return /accounts
            return '/accounts';
        }
        
        // Normalize path for root installations
        if ($basePath === '/' || $basePath === '\\') {
            return '';
        }
        
        return $basePath;
    }
    return '';
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = getBasePath();
    return $protocol . '://' . $host . $basePath;
}

// URL helper function for views
function url($path = '') {
    $basePath = getBasePath();
    if (empty($path)) {
        return $basePath ?: '/';
    }
    
    // Ensure path starts with /
    if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }
    
    return $basePath . $path;
}

define('APP_URL', getBaseUrl());
define('APP_ROOT', dirname(dirname(__FILE__)));

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '21102134@usc.edu.ph');
define('SMTP_PASS', ''); // Your API key here

// File upload paths (relative to public directory)
define('UPLOAD_PATH', 'uploads/');
define('PROFILE_PICTURES_PATH', 'profilePictures/');
define('EVENT_BADGES_PATH', 'eventbadges/');

// Security
define('TOKEN_EXPIRY', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8); 