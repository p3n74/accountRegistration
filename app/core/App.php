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
            'auth/verifyemail',
            'auth/checkexistingstudent'
        ];

        // Define API routes that require authentication
        $apiRoutes = [
            'api/users/search',
            'api/users'
        ];

        // Parse the URL
        $url = $this->parseUrl();

        // Get the current route
        $currentRoute = '';
        if (isset($url[0])) {
            $currentRoute .= strtolower($url[0]);
            if (isset($url[1])) {
                $currentRoute .= '/' . strtolower($url[1]);
                if (isset($url[2])) {
                    $currentRoute .= '/' . strtolower($url[2]);
                }
            }
        }

        // Check if the current route is public or API
        $isPublicRoute = in_array($currentRoute, $publicRoutes);
        $isApiRoute = strpos($currentRoute, 'api/') === 0;

        // Handle authentication redirects (skip for API routes as they handle their own auth)
        if (!$isApiRoute) {
            if (!$isLoggedIn && !$isPublicRoute) {
                // Not logged in and trying to access protected route
                header('Location: /auth/login');
                exit;
            } else if ($isLoggedIn && $isPublicRoute) {
                // Logged in and trying to access public route
                header('Location: /dashboard');
                exit;
            }
        }

        // Load the controller
        if (isset($url[0])) {
            if (file_exists('../app/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
                $this->controller = ucfirst($url[0]) . 'Controller';
                unset($url[0]);
            }
        }

        require_once '../app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Load the method
        if (isset($url[1])) {
            // Special handling for API routes - always use index method for API controller
            if (get_class($this->controller) === 'ApiController') {
                $this->method = 'index';
            } elseif (method_exists($this->controller, $url[1])) {
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
        $requestUri = trim($requestUri, '/');

        if ($requestUri === '') {
            return [];
        }

        return explode('/', filter_var($requestUri, FILTER_SANITIZE_URL));
    }

    protected function checkSession() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            // Session has expired
            session_unset();
            session_destroy();
            header('Location: /auth/login');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
} 