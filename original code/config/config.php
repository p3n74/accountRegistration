<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 's21102134_palisade');
define('DB_PASS', 'webwebwebweb');
define('DB_NAME', 's21102134_palisade');

// Application configuration
define('APP_NAME', 'Event Management System');
define('APP_URL', 'http://localhost:8000');
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