<?php

class Message extends Model {
    protected $table = 'messages';

    public function __construct() {
        parent::__construct();
    }

    private function generateGUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // Send a message
    public function sendMessage($senderId, $recipientId, $messageText) {
        $messageId = $this->generateGUID();
        
        // Sanitize message content
        $messageText = htmlspecialchars(trim($messageText), ENT_QUOTES, 'UTF-8');
        
        // Validate message length
        if (strlen($messageText) > 1000) {
            throw new Exception('Message too long (max 1000 characters)');
        }
        
        if (empty($messageText)) {
            throw new Exception('Message cannot be empty');
        }

        $sql = "INSERT INTO {$this->table} (id, sender_id, recipient_id, message_text) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", $messageId, $senderId, $recipientId, $messageText);
        
        if ($stmt->execute()) {
            return $messageId;
        }
        return false;
    }

    // Get conversation between two users
    public function getConversation($userId1, $userId2, $limit = 50, $offset = 0) {
        $sql = "SELECT m.*, 
                       s.fname as sender_fname, s.lname as sender_lname, s.profilepicture as sender_picture,
                       r.fname as recipient_fname, r.lname as recipient_lname, r.profilepicture as recipient_picture
                FROM {$this->table} m
                JOIN user_credentials s ON m.sender_id = s.uid
                JOIN user_credentials r ON m.recipient_id = r.uid
                WHERE (m.sender_id = ? AND m.recipient_id = ?) 
                   OR (m.sender_id = ? AND m.recipient_id = ?)
                ORDER BY m.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssii", $userId1, $userId2, $userId2, $userId1, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return array_reverse($result->fetch_all(MYSQLI_ASSOC)); // Reverse to show oldest first
    }

    // Get user's conversation list (recent conversations)
    public function getConversationList($userId, $limit = 20) {
        // For empty message table, return empty array
        $countSql = "SELECT COUNT(*) as count FROM {$this->table}";
        $countResult = $this->db->query($countSql);
        $countRow = $countResult->fetch_assoc();
        
        if ($countRow['count'] == 0) {
            return [];
        }
        
        // Simplified query that works with existing data
        $sql = "SELECT DISTINCT
                    CASE 
                        WHEN m.sender_id = ? THEN m.recipient_id 
                        ELSE m.sender_id 
                    END as other_user_id,
                    u.fname, u.lname, u.username, u.profilepicture,
                    m.message_text as last_message,
                    m.created_at as last_message_time,
                    m.sender_id as last_sender_id,
                    0 as unread_count
                FROM {$this->table} m
                JOIN user_credentials u ON u.uid = CASE 
                    WHEN m.sender_id = ? THEN m.recipient_id 
                    ELSE m.sender_id 
                END
                WHERE m.sender_id = ? OR m.recipient_id = ?
                ORDER BY m.created_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssi", $userId, $userId, $userId, $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // If no results, return empty array
        $conversations = $result->fetch_all(MYSQLI_ASSOC);
        
        // Remove duplicates (keep the most recent message per conversation)
        $uniqueConversations = [];
        $seenUsers = [];
        
        foreach ($conversations as $conversation) {
            $otherUserId = $conversation['other_user_id'];
            if (!isset($seenUsers[$otherUserId])) {
                $seenUsers[$otherUserId] = true;
                $uniqueConversations[] = $conversation;
            }
        }
        
        return $uniqueConversations;
    }

    // Mark messages as read
    public function markAsRead($userId, $otherUserId) {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE recipient_id = ? AND sender_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $userId, $otherUserId);
        return $stmt->execute();
    }

    // Get unread message count for a user
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE recipient_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return (int)$result->fetch_assoc()['count'];
    }

    // Get unread messages from a specific user
    public function getUnreadFromUser($userId, $senderId) {
        $sql = "SELECT m.*, s.fname as sender_fname, s.lname as sender_lname, s.profilepicture as sender_picture
                FROM {$this->table} m
                JOIN user_credentials s ON m.sender_id = s.uid
                WHERE m.recipient_id = ? AND m.sender_id = ? AND m.is_read = 0
                ORDER BY m.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $userId, $senderId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get a specific message by ID
    public function getMessageById($messageId) {
        $sql = "SELECT m.*, 
                       s.fname as sender_fname, s.lname as sender_lname, s.profilepicture as sender_picture,
                       r.fname as recipient_fname, r.lname as recipient_lname, r.profilepicture as recipient_picture
                FROM {$this->table} m
                JOIN user_credentials s ON m.sender_id = s.uid
                JOIN user_credentials r ON m.recipient_id = r.uid
                WHERE m.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $messageId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Delete a message (soft delete by setting text to empty)
    public function deleteMessage($messageId, $userId) {
        // Only allow sender to delete their own messages
        $sql = "UPDATE {$this->table} 
                SET message_text = '[Message deleted]' 
                WHERE id = ? AND sender_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $messageId, $userId);
        return $stmt->execute();
    }
} 