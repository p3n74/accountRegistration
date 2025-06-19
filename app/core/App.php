<?php

class App {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];
    protected $middleware = [];

    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        $isLoggedIn = isset($_SESSION['uid']);

        // Define public routes that don't require authentication
        $publicRoutes = [
            'auth/login',
            'auth/register',
            'auth/reset-password',
            'auth/forgot-password',
            'auth/resetrequest',
            'auth/reset',
            'auth/resetpassword',
            'auth/verify',
            'auth/verifyemail'
        ];

        // Parse the URL
        $url = $this->parseUrl();

        // Get the current route
        $currentRoute = '';
        if (isset($url[0])) {
            $currentRoute .= strtolower($url[0]);
            if (isset($url[1])) {
                $currentRoute .= '/' . strtolower($url[1]);
            }
        }

        // Check if the current route is public
        $isPublicRoute = in_array($currentRoute, $publicRoutes);

        // Handle authentication redirects
        if (!$isLoggedIn && !$isPublicRoute) {
            // Not logged in and trying to access protected route
            header('Location: ' . $this->getBasePath() . '/auth/login');
            exit;
        } else if ($isLoggedIn && $isPublicRoute) {
            // Logged in and trying to access public route
            header('Location: ' . $this->getBasePath() . '/dashboard');
            exit;
        }

        // Load the controller
        if (isset($url[0])) {
            if (file_exists('app/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
                $this->controller = ucfirst($url[0]) . 'Controller';
                unset($url[0]);
            }
        }

        require_once 'app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Load the method
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // Get the parameters
        $this->params = $url ? array_values($url) : [];

        // Call the controller method with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    protected function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }

        // Fallback for environments (like PHP built-in server) where URL is not passed as a query string
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove the base path if it exists (for subdirectory installations)
        $basePath = $this->getBasePath();
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        $requestUri = trim($requestUri, '/');

        if ($requestUri === '') {
            return [];
        }

        return explode('/', filter_var($requestUri, FILTER_SANITIZE_URL));
    }

    protected function getBasePath() {
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

    protected function checkSession() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            // Session has expired
            session_unset();
            session_destroy();
            header('Location: ' . $this->getBasePath() . '/auth/login');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
} 