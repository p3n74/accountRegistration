<?php

class ApiController extends Controller {
    private $userModel;
    private $followModel;
    private $messageModel;

    public function __construct() {
        // Set content type for API responses
        header('Content-Type: application/json; charset=utf-8');
        $this->userModel = $this->model('User');
        $this->followModel = $this->model('Follow');
        $this->messageModel = $this->model('Message');
    }

    // Generate JWT token for authenticated user
    public function generateToken() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $currentUserId = $this->getCurrentUserId();
        if (!$currentUserId) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        try {
            $user = $this->userModel->getUserById($currentUserId);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                return;
            }

            // Generate JWT token
            $token = $this->generateJWT($user);

            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'uid' => $user['uid'],
                    'fname' => $user['fname'],
                    'lname' => $user['lname'],
                    'username' => $user['username'] ?? null
                ]
            ]);
        } catch (Exception $e) {
            error_log("Token generation error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    private function generateJWT($user) {
        require_once '../app/helpers/JWT.php';
        
        $payload = [
            'uid' => $user['uid'],
            'email' => $user['email'],
            'fname' => $user['fname'],
            'lname' => $user['lname'],
            'username' => $user['username'] ?? null
        ];
        
        return JWT::generate($payload);
    }

    // Main API router - handles /api/* routes
    public function index($resource = null, $action = null, $id = null) {
        try {
            // Parse the API route
            if ($resource === 'auth') {
                if ($action === 'token') {
                    return $this->generateToken();
                } elseif ($action === 'test-token') {
                    return $this->testToken();
                }
            } elseif ($resource === 'users') {
                if ($action === 'search') {
                    return $this->searchUsers();
                } elseif (is_numeric($action)) {
                    // Handle /api/users/{id} routes
                    $userId = $action;
                    $userAction = $id;
                    
                    if ($userAction === 'follow') {
                        return $this->followUser($userId);
                    } elseif ($userAction === 'unfollow') {
                        return $this->unfollowUser($userId);
                    } else {
                        return $this->getUserProfile($userId);
                    }
                } else {
                    // Handle general user API
                    return $this->getUserProfile($action);
                }
            }
            
            // Unknown API endpoint
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    // Test endpoint to get a token (for development only)
    public function testToken() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password required']);
            return;
        }

        try {
            $user = $this->userModel->getUserByEmail($email);
            if (!$user || !password_verify($password, $user['password'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid credentials']);
                return;
            }

            if ((int)($user['emailverified'] ?? 0) !== 1) {
                http_response_code(401);
                echo json_encode(['error' => 'Email not verified']);
                return;
            }

            // Generate JWT token
            $token = $this->generateJWT($user);

            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'uid' => $user['uid'],
                    'fname' => $user['fname'],
                    'lname' => $user['lname'],
                    'username' => $user['username'] ?? null
                ]
            ]);
        } catch (Exception $e) {
            error_log("Test token generation error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    // User Search API - GET /api/users/search?q={query}
    public function searchUsers() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $query = $_GET['q'] ?? '';
        
        // Validation
        if (strlen($query) < 2) {
            http_response_code(400);
            echo json_encode(['error' => 'Query must be at least 2 characters']);
            return;
        }

        if (strlen($query) > 50) {
            http_response_code(400);
            echo json_encode(['error' => 'Query too long']);
            return;
        }

        try {
            $results = $this->userModel->searchUsers($query, 20);
            echo json_encode([
                'success' => true,
                'users' => $results,
                'count' => count($results)
            ]);
        } catch (Exception $e) {
            error_log("User search error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    // Follow User API - POST /api/users/{userId}/follow
    public function followUser($userId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Check authentication
        $currentUserId = $this->getCurrentUserId();
        if (!$currentUserId) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID required']);
            return;
        }

        // Validate target user exists
        $targetUser = $this->userModel->getUserById($userId);
        if (!$targetUser) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }

        // Prevent self-follow
        if ($currentUserId === $userId) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot follow yourself']);
            return;
        }

        try {
            // Check if already following
            if ($this->followModel->isFollowing($currentUserId, $userId)) {
                http_response_code(409);
                echo json_encode(['error' => 'Already following this user']);
                return;
            }

            // Create follow relationship
            $followResult = $this->followModel->followUser($currentUserId, $userId);
            
            if ($followResult) {
                // Publish Redis event for real-time notification
                $this->publishFollowEvent($userId, $currentUserId, 'followed');
                
                // Get updated follower count
                $updatedUser = $this->userModel->getUserById($userId);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Successfully followed user',
                    'follower_count' => $updatedUser['follower_count'] ?? 0
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to follow user']);
            }
        } catch (Exception $e) {
            error_log("Follow user error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    // Unfollow User API - DELETE /api/users/{userId}/unfollow
    public function unfollowUser($userId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $currentUserId = $this->getCurrentUserId();
        if (!$currentUserId) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID required']);
            return;
        }

        try {
            if (!$this->followModel->isFollowing($currentUserId, $userId)) {
                http_response_code(404);
                echo json_encode(['error' => 'Not following this user']);
                return;
            }

            $unfollowResult = $this->followModel->unfollowUser($currentUserId, $userId);
            
            if ($unfollowResult) {
                // Publish Redis event for real-time notification
                $this->publishFollowEvent($userId, $currentUserId, 'unfollowed');
                
                // Get updated follower count
                $updatedUser = $this->userModel->getUserById($userId);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Successfully unfollowed user',
                    'follower_count' => $updatedUser['follower_count'] ?? 0
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to unfollow user']);
            }
        } catch (Exception $e) {
            error_log("Unfollow user error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    // Get User Profile with Follow Status - GET /api/users/{userId}
    public function getUserProfile($userId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $currentUserId = $this->getCurrentUserId();
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID required']);
            return;
        }

        try {
            $user = $this->userModel->getUserById($userId);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                return;
            }

            // Remove sensitive data
            unset($user['password']);
            unset($user['currboundtoken']);
            unset($user['password_reset_token']);

            // Add follow status if authenticated
            if ($currentUserId) {
                $user['is_following'] = $this->followModel->isFollowing($currentUserId, $userId);
                $user['follows_you'] = $this->followModel->isFollowing($userId, $currentUserId);
            }

            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } catch (Exception $e) {
            error_log("Get user profile error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    private function publishFollowEvent($targetUserId, $followerUserId, $action) {
        // This will be used by the Node.js server for real-time notifications
        // For now, we'll use a simple file-based approach, but this should be Redis in production
        $eventData = [
            'type' => 'user_' . $action,
            'target_user_id' => $targetUserId,
            'follower_user_id' => $followerUserId,
            'timestamp' => time()
        ];
        
        // Write to a temp file that Node.js can watch
        $eventFile = '../storage/events/follow_events.json';
        $events = [];
        
        if (file_exists($eventFile)) {
            $events = json_decode(file_get_contents($eventFile), true) ?? [];
        }
        
        $events[] = $eventData;
        
        // Keep only last 100 events
        if (count($events) > 100) {
            $events = array_slice($events, -100);
        }
        
        file_put_contents($eventFile, json_encode($events));
    }
} 