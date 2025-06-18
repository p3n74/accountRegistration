<?php
class HomeController extends Controller {
    public function index() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['uid'])) {
            header('Location: /dashboard');
            exit;
        }
        header('Location: /auth/login');
        exit;
    }
} 