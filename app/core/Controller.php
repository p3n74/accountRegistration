<?php

class Controller {
    // Load model
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    // Load view
    public function view($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        require_once '../app/views/' . $view . '.php';
        
        // Get the content
        $content = ob_get_clean();
        
        // Include the layout
        require_once '../app/views/shared/layout.php';
    }

    // Load view without layout (for AJAX responses, etc.)
    public function viewPartial($view, $data = []) {
        extract($data);
        require_once '../app/views/' . $view . '.php';
    }

    // Redirect to another page
    public function redirect($url) {
        header('Location: ' . $url);
        exit();
    }

    // Check if user is logged in
    public function requireAuth() {
        session_start();
        if (!isset($_SESSION['uid'])) {
            $this->redirect('/auth/login');
        }
    }

    // Get current user ID
    public function getCurrentUserId() {
        session_start();
        return $_SESSION['uid'] ?? null;
    }

    // Get current user data
    public function getCurrentUser() {
        session_start();
        if (!isset($_SESSION['uid'])) {
            return null;
        }
        
        $userModel = $this->model('User');
        return $userModel->getUserById($_SESSION['uid']);
    }

    // Set flash message
    public function setFlash($type, $message) {
        session_start();
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    // Get and clear flash message
    public function getFlash() {
        session_start();
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
} 