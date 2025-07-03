-- Social Platform Database Schema
-- Tables for user search, follow system, and messaging

-- Table for follow relationships
CREATE TABLE IF NOT EXISTS `follows` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `follower_id` CHAR(36) NOT NULL,
  `followed_id` CHAR(36) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_follower` (`follower_id`),
  INDEX `idx_followed` (`followed_id`),
  UNIQUE KEY `unique_follow` (`follower_id`, `followed_id`),
  FOREIGN KEY (`follower_id`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE,
  FOREIGN KEY (`followed_id`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for messages
CREATE TABLE IF NOT EXISTS `messages` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for user online sessions (for WebSocket tracking)
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uid` CHAR(36) NOT NULL,
  `socket_id` VARCHAR(255) NOT NULL,
  `connected_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_uid` (`uid`),
  INDEX `idx_socket_id` (`socket_id`),
  FOREIGN KEY (`uid`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add username field to user_credentials if it doesn't exist
ALTER TABLE `user_credentials` 
ADD COLUMN IF NOT EXISTS `username` VARCHAR(50) UNIQUE NULL AFTER `email`;

-- Add follower/following counts for quick access
ALTER TABLE `user_credentials` 
ADD COLUMN IF NOT EXISTS `follower_count` INT DEFAULT 0 AFTER `username`,
ADD COLUMN IF NOT EXISTS `following_count` INT DEFAULT 0 AFTER `follower_count`;

-- Triggers to maintain follower/following counts
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS `update_follow_counts_insert` 
AFTER INSERT ON `follows`
FOR EACH ROW 
BEGIN
  UPDATE `user_credentials` SET `following_count` = `following_count` + 1 WHERE `uid` = NEW.follower_id;
  UPDATE `user_credentials` SET `follower_count` = `follower_count` + 1 WHERE `uid` = NEW.followed_id;
END$$

CREATE TRIGGER IF NOT EXISTS `update_follow_counts_delete` 
AFTER DELETE ON `follows`
FOR EACH ROW 
BEGIN
  UPDATE `user_credentials` SET `following_count` = `following_count` - 1 WHERE `uid` = OLD.follower_id;
  UPDATE `user_credentials` SET `follower_count` = `follower_count` - 1 WHERE `uid` = OLD.followed_id;
END$$

DELIMITER ; 