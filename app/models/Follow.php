<?php

class Follow extends Model {
    protected $table = 'follows';

    public function __construct() {
        parent::__construct();
    }

    // Check if user A follows user B
    public function isFollowing($followerId, $followedId) {
        $sql = "SELECT 1 FROM {$this->table} WHERE follower_id = ? AND followed_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $followerId, $followedId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Follow a user
    public function followUser($followerId, $followedId) {
        // Double-check not already following
        if ($this->isFollowing($followerId, $followedId)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table} (follower_id, followed_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $followerId, $followedId);
        return $stmt->execute();
    }

    // Unfollow a user
    public function unfollowUser($followerId, $followedId) {
        $sql = "DELETE FROM {$this->table} WHERE follower_id = ? AND followed_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $followerId, $followedId);
        return $stmt->execute();
    }

    // Get followers of a user
    public function getFollowers($userId, $limit = 50, $offset = 0) {
        $sql = "SELECT u.uid, u.fname, u.lname, u.username, u.profilepicture, f.created_at as followed_at
                FROM {$this->table} f
                JOIN user_credentials u ON f.follower_id = u.uid
                WHERE f.followed_id = ?
                ORDER BY f.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get users that a user is following
    public function getFollowing($userId, $limit = 50, $offset = 0) {
        $sql = "SELECT u.uid, u.fname, u.lname, u.username, u.profilepicture, f.created_at as followed_at
                FROM {$this->table} f
                JOIN user_credentials u ON f.followed_id = u.uid
                WHERE f.follower_id = ?
                ORDER BY f.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get follow counts for a user
    public function getFollowCounts($userId) {
        $followersSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE followed_id = ?";
        $followingSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE follower_id = ?";
        
        $stmt = $this->db->prepare($followersSql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $followersResult = $stmt->get_result();
        $followersCount = $followersResult->fetch_assoc()['count'];
        
        $stmt = $this->db->prepare($followingSql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $followingResult = $stmt->get_result();
        $followingCount = $followingResult->fetch_assoc()['count'];
        
        return [
            'followers' => (int)$followersCount,
            'following' => (int)$followingCount
        ];
    }

    // Get mutual follows (friends)
    public function getMutualFollows($userId, $limit = 50, $offset = 0) {
        $sql = "SELECT u.uid, u.fname, u.lname, u.username, u.profilepicture
                FROM {$this->table} f1
                JOIN {$this->table} f2 ON f1.followed_id = f2.follower_id AND f1.follower_id = f2.followed_id
                JOIN user_credentials u ON f1.followed_id = u.uid
                WHERE f1.follower_id = ?
                ORDER BY u.fname, u.lname
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get follow suggestions (users followed by people you follow)
    public function getFollowSuggestions($userId, $limit = 10) {
        $sql = "SELECT DISTINCT u.uid, u.fname, u.lname, u.username, u.profilepicture,
                       COUNT(*) as mutual_connections
                FROM {$this->table} f1
                JOIN {$this->table} f2 ON f1.followed_id = f2.follower_id
                JOIN user_credentials u ON f2.followed_id = u.uid
                WHERE f1.follower_id = ?
                  AND f2.followed_id != ?
                  AND f2.followed_id NOT IN (
                    SELECT followed_id FROM {$this->table} WHERE follower_id = ?
                  )
                GROUP BY u.uid
                ORDER BY mutual_connections DESC, u.fname, u.lname
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssi", $userId, $userId, $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
} 