<?php

class SocialController extends Controller {
    private $userModel;
    private $followModel;
    private $messageModel;

    public function __construct() {
        $this->requireAuth();
        $this->userModel = $this->model('User');
        $this->followModel = $this->model('Follow');
        $this->messageModel = $this->model('Message');
    }

    public function index() {
        $currentUserId = $this->getCurrentUserId();
        $user = $this->userModel->getUserById($currentUserId);
        
        // Get conversation list
        $conversations = $this->messageModel->getConversationList($currentUserId, 20);
        
        // Get follow suggestions
        $suggestions = $this->followModel->getFollowSuggestions($currentUserId, 6);
        
        // Get unread message count
        $unreadCount = $this->messageModel->getUnreadCount($currentUserId);
        
        // Get following and follower counts
        $followCounts = $this->followModel->getFollowCounts($currentUserId);
        
        $data = [
            'user' => $user,
            'conversations' => $conversations,
            'suggestions' => $suggestions,
            'unreadCount' => $unreadCount,
            'followCounts' => $followCounts
        ];
        
        $this->view('social/index', $data);
    }

    public function messages($otherUserId = null) {
        $currentUserId = $this->getCurrentUserId();
        
        if (!$otherUserId) {
            // Redirect to social index if no user specified
            $this->redirect('/social');
        }
        
        // Get the other user's info
        $otherUser = $this->userModel->getUserById($otherUserId);
        if (!$otherUser) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/social');
        }
        
        // Get conversation history
        $messages = $this->messageModel->getConversation($currentUserId, $otherUserId, 50);
        
        // Mark messages as read
        $this->messageModel->markAsRead($currentUserId, $otherUserId);
        
        $data = [
            'otherUser' => $otherUser,
            'messages' => $messages,
            'currentUserId' => $currentUserId
        ];
        
        $this->view('social/chat', $data);
    }

    public function following() {
        $currentUserId = $this->getCurrentUserId();
        
        // Get users that current user is following
        $following = $this->followModel->getFollowing($currentUserId, 50);
        
        // Get followers
        $followers = $this->followModel->getFollowers($currentUserId, 50);
        
        // Get mutual follows (friends)
        $mutualFollows = $this->followModel->getMutualFollows($currentUserId, 50);
        
        $data = [
            'following' => $following,
            'followers' => $followers,
            'mutualFollows' => $mutualFollows
        ];
        
        $this->view('social/following', $data);
    }

    public function search() {
        $query = $_GET['q'] ?? '';
        $currentUserId = $this->getCurrentUserId();
        
        $searchResults = [];
        if (strlen($query) >= 2) {
            $searchResults = $this->userModel->searchUsers($query, 20);
            
            // Add follow status for each user
            foreach ($searchResults as &$user) {
                if ($user['uid'] !== $currentUserId) {
                    $user['is_following'] = $this->followModel->isFollowing($currentUserId, $user['uid']);
                    $user['follows_you'] = $this->followModel->isFollowing($user['uid'], $currentUserId);
                }
            }
        }
        
        $data = [
            'query' => $query,
            'searchResults' => $searchResults
        ];
        
        $this->view('social/search', $data);
    }
} 