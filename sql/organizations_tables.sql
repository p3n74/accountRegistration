-- Organization System Database Schema - Tables Only
-- Tables for organization management, memberships, and financial tracking

-- Main organizations table
CREATE TABLE IF NOT EXISTS `organizations` (
  `org_id` CHAR(36) PRIMARY KEY,
  `org_name` VARCHAR(255) NOT NULL,
  `org_slug` VARCHAR(100) UNIQUE NOT NULL,
  `org_description` TEXT,
  `org_type` ENUM('student', 'club', 'department', 'external') DEFAULT 'student',
  `logo_path` VARCHAR(500),
  `banner_path` VARCHAR(500),
  `website_url` VARCHAR(500),
  `contact_email` VARCHAR(255),
  `contact_phone` VARCHAR(20),
  `status` ENUM('active', 'pending', 'suspended', 'inactive') DEFAULT 'pending',
  `verification_status` ENUM('unverified', 'pending', 'verified') DEFAULT 'unverified',
  `created_by` CHAR(36) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_org_slug` (`org_slug`),
  INDEX `idx_org_type` (`org_type`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `user_credentials`(`uid`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Organization memberships with roles
CREATE TABLE IF NOT EXISTS `organization_members` (
  `membership_id` CHAR(36) PRIMARY KEY,
  `org_id` CHAR(36) NOT NULL,
  `user_id` CHAR(36) NOT NULL,
  `role` ENUM('owner', 'admin', 'executive', 'member', 'treasurer') DEFAULT 'member',
  `permissions` JSON DEFAULT NULL, -- Custom permissions for flexibility
  `status` ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
  `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `invited_by` CHAR(36),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_org_member` (`org_id`, `user_id`),
  INDEX `idx_user_orgs` (`user_id`),
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`),
  UNIQUE KEY `unique_membership` (`org_id`, `user_id`),
  FOREIGN KEY (`org_id`) REFERENCES `organizations`(`org_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE,
  FOREIGN KEY (`invited_by`) REFERENCES `user_credentials`(`uid`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Organization invitations
CREATE TABLE IF NOT EXISTS `organization_invitations` (
  `invitation_id` CHAR(36) PRIMARY KEY,
  `org_id` CHAR(36) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `invited_by` CHAR(36) NOT NULL,
  `role` ENUM('admin', 'executive', 'member', 'treasurer') DEFAULT 'member',
  `invitation_token` VARCHAR(255) NOT NULL UNIQUE,
  `status` ENUM('pending', 'accepted', 'declined', 'expired') DEFAULT 'pending',
  `message` TEXT,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `responded_at` TIMESTAMP NULL,
  INDEX `idx_org_invitations` (`org_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_token` (`invitation_token`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`org_id`) REFERENCES `organizations`(`org_id`) ON DELETE CASCADE,
  FOREIGN KEY (`invited_by`) REFERENCES `user_credentials`(`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Organization financial accounts for payment management
CREATE TABLE IF NOT EXISTS `organization_finances` (
  `finance_id` CHAR(36) PRIMARY KEY,
  `org_id` CHAR(36) NOT NULL,
  `account_name` VARCHAR(255) NOT NULL DEFAULT 'Main Account',
  `account_type` ENUM('checking', 'savings', 'stripe', 'paypal') DEFAULT 'checking',
  `balance` DECIMAL(15,2) DEFAULT 0.00,
  `currency` CHAR(3) DEFAULT 'USD',
  `payment_processor_id` VARCHAR(255), -- Stripe account ID, PayPal ID, etc.
  `payment_processor_data` JSON, -- Store payment processor specific data
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_org_finance` (`org_id`),
  INDEX `idx_active` (`is_active`),
  FOREIGN KEY (`org_id`) REFERENCES `organizations`(`org_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Financial transactions for organization
CREATE TABLE IF NOT EXISTS `organization_transactions` (
  `transaction_id` CHAR(36) PRIMARY KEY,
  `org_id` CHAR(36) NOT NULL,
  `finance_id` CHAR(36),
  `event_id` CHAR(36), -- Link to events if transaction is event-related
  `transaction_type` ENUM('income', 'expense', 'transfer', 'fee', 'refund') NOT NULL,
  `category` VARCHAR(100), -- 'event_registration', 'merchandise', 'expense_travel', etc.
  `amount` DECIMAL(15,2) NOT NULL,
  `currency` CHAR(3) DEFAULT 'USD',
  `description` TEXT NOT NULL,
  `payment_method` VARCHAR(100), -- 'stripe', 'paypal', 'cash', 'check', etc.
  `payment_reference` VARCHAR(255), -- External payment ID/reference
  `processed_by` CHAR(36), -- User who processed the transaction
  `metadata` JSON, -- Store additional transaction data
  `status` ENUM('pending', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
  `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `processed_at` TIMESTAMP,
  INDEX `idx_org_transactions` (`org_id`),
  INDEX `idx_transaction_type` (`transaction_type`),
  INDEX `idx_category` (`category`),
  INDEX `idx_status` (`status`),
  INDEX `idx_event_transactions` (`event_id`),
  INDEX `idx_transaction_date` (`transaction_date`),
  FOREIGN KEY (`org_id`) REFERENCES `organizations`(`org_id`) ON DELETE CASCADE,
  FOREIGN KEY (`finance_id`) REFERENCES `organization_finances`(`finance_id`) ON DELETE SET NULL,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`eventid`) ON DELETE SET NULL,
  FOREIGN KEY (`processed_by`) REFERENCES `user_credentials`(`uid`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add organization link to events table
ALTER TABLE `events` 
ADD COLUMN IF NOT EXISTS `org_id` CHAR(36) NULL AFTER `eventcreator`,
ADD COLUMN IF NOT EXISTS `registration_fee` DECIMAL(10,2) DEFAULT 0.00 AFTER `org_id`,
ADD COLUMN IF NOT EXISTS `payment_required` TINYINT(1) DEFAULT 0 AFTER `registration_fee`,
ADD COLUMN IF NOT EXISTS `payment_deadline` TIMESTAMP NULL AFTER `payment_required`; 