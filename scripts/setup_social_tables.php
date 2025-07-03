<?php
require_once dirname(__DIR__) . '/app/config/config.php';

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Setting up social platform tables...\n";

try {
    // Create follows table
    $sql = "CREATE TABLE IF NOT EXISTS `follows` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `follower_id` CHAR(36) NOT NULL,
        `followed_id` CHAR(36) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_follower` (`follower_id`),
        INDEX `idx_followed` (`followed_id`),
        UNIQUE KEY `unique_follow` (`follower_id`, `followed_id`),
        FOREIGN KEY (`follower_id`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE,
        FOREIGN KEY (`followed_id`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Follows table created successfully\n";
    } else {
        echo "Error creating follows table: " . $conn->error . "\n";
    }

    // Create messages table
    $sql = "CREATE TABLE IF NOT EXISTS `messages` (
        `id` CHAR(36) PRIMARY KEY,
        `sender_id` CHAR(36) NOT NULL,
        `recipient_id` CHAR(36) NOT NULL,
        `message_text` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `read_at` TIMESTAMP NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        INDEX `idx_sender` (`sender_id`),
        INDEX `idx_recipient` (`recipient_id`),
        INDEX `idx_conversation` (`sender_id`, `recipient_id`, `created_at`),
        INDEX `idx_created_at` (`created_at`),
        FOREIGN KEY (`sender_id`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE,
        FOREIGN KEY (`recipient_id`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Messages table created successfully\n";
    } else {
        echo "Error creating messages table: " . $conn->error . "\n";
    }

    // Create user_sessions table
    $sql = "CREATE TABLE IF NOT EXISTS `user_sessions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `uid` CHAR(36) NOT NULL,
        `socket_id` VARCHAR(255) NOT NULL,
        `connected_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_uid` (`uid`),
        INDEX `idx_socket_id` (`socket_id`),
        FOREIGN KEY (`uid`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ User sessions table created successfully\n";
    } else {
        echo "Error creating user_sessions table: " . $conn->error . "\n";
    }

    // Add username field to user_credentials
    $sql = "ALTER TABLE `user_credentials` 
            ADD COLUMN IF NOT EXISTS `username` VARCHAR(50) UNIQUE NULL AFTER `email`";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Username field added to user_credentials\n";
    } else {
        echo "Error adding username field: " . $conn->error . "\n";
    }

    // Add follower/following counts
    $sql = "ALTER TABLE `user_credentials` 
            ADD COLUMN IF NOT EXISTS `follower_count` INT DEFAULT 0 AFTER `username`,
            ADD COLUMN IF NOT EXISTS `following_count` INT DEFAULT 0 AFTER `follower_count`";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Follow count fields added to user_credentials\n";
    } else {
        echo "Error adding follow count fields: " . $conn->error . "\n";
    }

    // Create triggers for follow counts
    $sql = "CREATE TRIGGER IF NOT EXISTS `update_follow_counts_insert` 
            AFTER INSERT ON `follows`
            FOR EACH ROW 
            BEGIN
                UPDATE `user_credentials` SET `following_count` = `following_count` + 1 WHERE `uid` = NEW.follower_id;
                UPDATE `user_credentials` SET `follower_count` = `follower_count` + 1 WHERE `uid` = NEW.followed_id;
            END";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Follow count insert trigger created\n";
    } else {
        echo "Error creating insert trigger: " . $conn->error . "\n";
    }

    $sql = "CREATE TRIGGER IF NOT EXISTS `update_follow_counts_delete` 
            AFTER DELETE ON `follows`
            FOR EACH ROW 
            BEGIN
                UPDATE `user_credentials` SET `following_count` = `following_count` - 1 WHERE `uid` = OLD.follower_id;
                UPDATE `user_credentials` SET `follower_count` = `follower_count` - 1 WHERE `uid` = OLD.followed_id;
            END";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Follow count delete trigger created\n";
    } else {
        echo "Error creating delete trigger: " . $conn->error . "\n";
    }

    echo "\n✅ Social platform setup completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
} finally {
    $conn->close();
}
?> 