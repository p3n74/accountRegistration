<?php

class Controller {
    // Load model
    public function model($model) {
        require_once 'app/models/' . $model . '.php';
        return new $model();
    }

    // Load view
    public function view($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Get flash message for layout
        $flash = $this->getFlash();
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        require_once 'app/views/' . $view . '.php';
        
        // Get the content
        $content = ob_get_clean();
        
        // Include the layout
        require_once 'app/views/shared/layout.php';
    }

    // Load view without layout (for AJAX responses, etc.)
    public function viewPartial($view, $data = []) {
        extract($data);
        require_once 'app/views/' . $view . '.php';
    }

    // Redirect to another page
    public function redirect($url) {
        // If URL doesn't start with http or is already relative, prepend base path
        if (!preg_match('/^https?:\/\//', $url) && strpos($url, $this->getBasePath()) !== 0) {
            $url = $this->getBasePath() . $url;
        }
        header('Location: ' . $url);
        exit();
    }

    // Get base path for the application
    public function getBasePath() {
        // Detect if we're in a subdirectory
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

    // Check if user is logged in
    public function requireAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['uid'])) {
            $this->redirect('/auth/login');
        }
    }

    // Get current user ID
    public function getCurrentUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['uid'] ?? null;
    }

    // Get current user data
    public function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['uid'])) {
            return null;
        }
        
        $userModel = $this->model('User');
        return $userModel->getUserById($_SESSION['uid']);
    }

    // Set flash message
    public function setFlash($type, $message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    // Get and clear flash message
    public function getFlash() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
} 