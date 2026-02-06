-- ============================================================================
-- Hack Club OAuth Integration - Database Migration
-- Date: 2026-02-06
-- ============================================================================

-- Create table for Hack Club OAuth login sessions
CREATE TABLE IF NOT EXISTS `hackclub_login_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state_token` varchar(255) NOT NULL,
  `csrf_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `state_token` (`state_token`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create table for Hack Club account links
CREATE TABLE IF NOT EXISTS `hackclub_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hackclub_id` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `slack_id` varchar(255) DEFAULT NULL,
  `verification_status` varchar(50) DEFAULT NULL,
  `ysws_eligible` tinyint(1) DEFAULT 0,
  `access_token` text,
  `refresh_token` text,
  `token_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `hackclub_id` (`hackclub_id`),
  KEY `slack_id` (`slack_id`),
  CONSTRAINT `hackclub_links_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Hack Club OAuth settings (with placeholder values)
INSERT INTO `settings` (`name`, `value`) VALUES 
('hackclub_client_id', '') ON DUPLICATE KEY UPDATE `value` = `value`;

INSERT INTO `settings` (`name`, `value`) VALUES 
('hackclub_client_secret', '') ON DUPLICATE KEY UPDATE `value` = `value`;

INSERT INTO `settings` (`name`, `value`) VALUES 
('hackclub_redirect_uri', '') ON DUPLICATE KEY UPDATE `value` = `value`;

-- ============================================================================
-- Migration Complete!
-- ============================================================================
-- 
-- Next Steps:
-- 1. Go to https://auth.hackclub.com/developer/apps to create an app
-- 2. Configure the app with your redirect URI
-- 3. Update the settings in /dashboard/hackclub-settings.php
-- 
-- ============================================================================
