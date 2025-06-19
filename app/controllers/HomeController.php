<?php
class HomeController extends Controller {
    public function index() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $basePath = $this->getBasePath();
        
        if (isset($_SESSION['uid'])) {
            header('Location: ' . $basePath . '/dashboard');
            exit;
        }
        header('Location: ' . $basePath . '/auth/login');
        exit;
    }
} 