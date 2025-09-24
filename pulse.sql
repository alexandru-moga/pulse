SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `(4, 'title-2', 'title_2', '{\r\n  \"first\": \"OUR\",\r\n  \"second\": \"ACTIVITY\"\r\n}', 3, 1),pplications` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `school` varchar(255) NOT NULL,
  `class` varchar(20) NOT NULL,
  `birthdate` date NOT NULL,
  `phone` varchar(20) NOT NULL,
  `superpowers` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_id` varchar(255) DEFAULT NULL,
  `discord_username` varchar(255) DEFAULT NULL,
  `status` enum('waiting','accepted','rejected') NOT NULL DEFAULT 'waiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `status` enum('waiting','solved') NOT NULL DEFAULT 'waiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `footer` (
  `id` int(11) NOT NULL,
  `section_type` enum('logo','links','cta','credits') NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`content`)),
  `order_num` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `footer` (`id`, `section_type`, `content`, `order_num`, `is_active`, `created_at`) VALUES
(1, 'logo', '{\"path\": \"images/logo.svg\"}', 1, 1, '2025-05-10 22:11:57'),
(2, 'links', '{\n  \"title\": \"Explore\",\n  \"items\": [\n    {\"text\": \"Members\", \"url\": \"/members.php\"},\n    {\"text\": \"Apply\", \"url\": \"/apply.php\"},\n    {\"text\": \"Contact\", \"url\": \"/contact.php\"}\n  ]\n}\n', 2, 1, '2025-05-10 22:11:57'),
(3, 'cta', '{\n  \"title\": \"Get Involved\",\n  \"text\": \"Join Us Today!\",\n  \"url\": \"/apply.php\"\n}\n', 3, 1, '2025-05-10 22:11:57'),
(4, 'credits', '{ \"show_attribution\": true }', 4, 1, '2025-06-10 10:57:53');

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `module_config` text DEFAULT NULL,
  `menu_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  `visibility` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pages` (`id`, `name`, `title`, `description`, `table_name`, `module_config`, `menu_enabled`, `created_at`, `parent_id`, `visibility`, `effects`) VALUES
(1, 'index', 'Home', 'Welcome to PULSE - Programming University Learning & Software Engineering', 'page_index', NULL, 1, '2025-05-10 17:47:05', NULL, NULL, '["mouse","globe","grid"]'),
(2, 'members', 'Members', 'Our PULSE community members', 'page_members', NULL, 1, '2025-05-10 17:47:05', NULL, NULL, '["mouse","grid"]'),
(3, 'apply', 'Apply', 'Apply for a PULSE project', 'page_apply', NULL, 1, '2025-05-10 17:47:05', NULL, NULL, '["mouse","net","grid"]'),
(4, 'contact', 'Contact', 'Get in touch with PULSE', 'page_contact', NULL, 1, '2025-05-10 17:47:05', NULL, NULL, '["mouse","grid","birds"]'),
(5, 'dashboard/index', 'Dashboard', 'Member dashboard', NULL, NULL, 1, '2025-05-10 17:47:05', NULL, NULL, '[]');

CREATE TABLE `page_apply` (
  `id` int(11) NOT NULL,
  `block_name` varchar(100) NOT NULL,
  `block_type` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `page_apply` (`id`, `block_name`, `block_type`, `content`, `order_num`, `is_active`) VALUES
(11, 'apply_title', 'title_3', '{\"text\": \"Join Phoenix Club\"}', 1, 1),
(12, 'apply_subtitle', 'title', '{\"text\": \"Ready to start your coding journey with us?.\"}', 2, 1),
(13, 'apply_form', 'apply_form', '{\n  \"sections\": [\n    {\n      \"title\": \"Personal Information\",\n      \"fields\": [\n        {\n          \"name\": \"first_name\",\n          \"label\": \"First Name\",\n          \"type\": \"text\",\n          \"placeholder\": \"Your first name\",\n          \"required\": true\n        },\n        {\n          \"name\": \"last_name\",\n          \"label\": \"Last Name\",\n          \"type\": \"text\",\n          \"placeholder\": \"Your last name\",\n          \"required\": true\n        }\n      ]\n    },\n    {\n      \"title\": \"Academic Information\",\n      \"fields\": [\n        {\n          \"name\": \"school\",\n          \"label\": \"School\",\n          \"type\": \"text\",\n          \"placeholder\": \"Your school name\",\n          \"required\": true\n        }\n      ]\n    }\n  ]\n}', 3, 1);

CREATE TABLE `page_contact` (
  `id` int(11) NOT NULL,
  `block_name` varchar(100) NOT NULL,
  `block_type` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `page_contact` (`id`, `block_name`, `block_type`, `content`, `order_num`, `is_active`) VALUES
(1, 'apply_title', 'title_3', '{\"text\": \"Contact us\"}', 1, 1),
(2, 'subtitle', 'title', '{\"text\": \"Feel free to ask anything.\"}', 2, 1),
(3, 'contact_form', 'contact_form', '{\n    \"title\": \"Get in Touch\",\n    \"subtitle\": \"We\'ll respond within 24 hours\",\n    \"description\": \"Have a question or want to join our team? Fill out the form below and we\'ll get back to you.\",\n    \"fields\": [\n      {\"name\": \"name\", \"label\": \"Full Name\", \"required\": true, \"placeholder\": \"Your name\"},\n      {\"name\": \"email\", \"label\": \"Email Address\", \"required\": true, \"placeholder\": \"your.email@example.com\"},\n      {\"name\": \"message\", \"label\": \"Message\", \"type\": \"textarea\", \"required\": true, \"placeholder\": \"Write your message here...\"}\n    ],\n    \"button_text\": \"Send Message\"\n}\n', 3, 1);

CREATE TABLE `page_index` (
  `id` int(11) NOT NULL,
  `block_name` varchar(100) NOT NULL,
  `block_type` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `page_index` (`id`, `block_name`, `block_type`, `content`, `order_num`, `is_active`) VALUES
(1, 'welcome', 'welcome', '{\n  \"title\": \"Welcome to <span class=\\\"text-red-500\\\">PHOENIX</span>\",\n  \"subtitle\": \"STUDENT-LED TECH COMMUNITY\",\n  \"description\": \"Join a vibrant community of students passionate about technology and innovation. We build, learn, and grow together through hackathons, workshops, and collaborative projects.\",\n  \"primaryButton\": {\n    \"text\": \"Get Involved\",\n    \"url\": \"/apply.php\"\n  },\n  \"secondaryButton\": {\n    \"text\": \"Contact us\",\n    \"url\": \"/contact.php\"\n  }\n}', 1, 1),
(2, 'scroll-arrow', 'scroll_arrow', '[]', 2, 1),
(4, 'title-2', 'title-2', '{\r\n  \"first\": \"OUR\",\r\n  \"second\": \"ACTIVITY\"\r\n}', 3, 1),
(8, 'active_members', 'stats', '[\n  { \"value\": \"150\", \"label\": \"Active Members\" },\n  { \"value\": \"25\", \"label\": \"Projects Active\" },\n  { \"value\": \"50\", \"label\": \"Projects Completed\" }\n]\n', 8, 1),
(9, 'title-2', 'title_2', '{\r\n  \"first\": \"OUR\",\r\n  \"second\": \"IMPACT\"\r\n}', 9, 1),
(10, 'core_values', 'core_values', '{\n    \"values\": [\n      {\n        \"title\": \"Excellence\",\n        \"description\": \"We strive for excellence in everything we do, pushing boundaries and challenging the status quo.\",\n        \"icon\": \"<svg xmlns=\\\"http://www.w3.org/2000/svg\\\" width=\\\"24\\\" height=\\\"24\\\" fill=\\\"currentColor\\\" viewBox=\\\"0 0 24 24\\\"><path d=\\\"M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z\\\"/></svg>\"\n      },\n      {\n        \"title\": \"Innovation\",\n        \"description\": \"We embrace innovation, encouraging creative thinking and novel approaches to problem-solving.\",\n        \"icon\": \"<svg xmlns=\\\"http://www.w3.org/2000/svg\\\" width=\\\"24\\\" height=\\\"24\\\" fill=\\\"currentColor\\\" viewBox=\\\"0 0 24 24\\\"><path d=\\\"M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.5 5h3v7.5h-3v-7.5zm1.5 12.75c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z\\\"/></svg>\"\n      },\n      {\n        \"title\": \"Integrity\",\n        \"description\": \"We act with integrity in all our dealings, maintaining the highest ethical standards and transparency.\",\n        \"icon\": \"<svg xmlns=\\\"http://www.w3.org/2000/svg\\\" width=\\\"24\\\" height=\\\"24\\\" fill=\\\"currentColor\\\" viewBox=\\\"0 0 24 24\\\"><path d=\\\"M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.31 7.526c-.099-.807.528-1.526 1.348-1.526.771 0 1.377.676 1.28 1.451l-.757 6.053c-.035.283-.276.496-.561.496s-.526-.213-.562-.496l-.748-5.978zm1.31 10.724c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z\\\"/></svg>\"\n      }\n    ]\n  }', 10, 1);

CREATE TABLE `page_members` (
  `id` int(11) NOT NULL,
  `block_name` varchar(100) NOT NULL,
  `block_type` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `page_members` (`id`, `block_name`, `block_type`, `content`, `order_num`, `is_active`) VALUES
(1, 'title', 'title_3', '{\"text\": \"Our members\"}', 1, 1),
(2, 'members_description', 'title', '{\"text\": \"Meet the talented programmers who make up PULSE\"}', 2, 1),
(4, 'members_grid', 'members_grid', '{\"title\": \"Our Team\", \"subtitle\": \"Meet the PULSE community\"}', 3, 1);

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1, 'smtp_host', 'smtp.gmail.com'),
(2, 'smtp_port', '587'),
(3, 'smtp_user', 'example@gmail.com'),
(4, 'smtp_pass', 'app-password'),
(5, 'smtp_from', 'example@gmail.com'),
(6, 'smtp_from_name', 'PULSE Team'),
(7, 'site_title', 'Pulse'),
(8, 'site_url', 'http://localhost/pulse'),
(9, 'maintenance_mode', '0'),
(10, 'github_client_id', ''),
(11, 'github_client_secret', ''),
(12, 'github_redirect_uri', ''),
(13, 'slack_client_id', ''),
(14, 'slack_client_secret', ''),
(15, 'slack_redirect_uri', ''),
(16, 'slack_bot_token', ''),
(17, 'slack_webhook_url', ''),
(18, 'google_client_id', ''),
(19, 'google_client_secret', ''),
(20, 'google_redirect_uri', ''),
(21, 'discord_client_id', ''),
(22, 'discord_client_secret', ''),
(23, 'discord_redirect_uri', ''),
(24, 'discord_bot_token', ''),
(25, 'discord_guild_id', ''),
(26, 'discord_member_role_id', ''),
(27, 'discord_co_leader_role_id', ''),
(28, 'discord_leader_role_id', ''),
(29, 'automatic_certificates', '1');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `discord_id` varchar(255) DEFAULT NULL,
  `school` varchar(255) DEFAULT NULL,
  `hcb_member` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `class` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('Leader','Co-leader','Member','Guest') DEFAULT 'Member',
  `join_date` timestamp NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `slack_id` text DEFAULT NULL,
  `github_username` text DEFAULT NULL,
  `active_member` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL DEFAULT 'changeme'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reward_amount DECIMAL(10,2) DEFAULT NULL,
    reward_description TEXT DEFAULT NULL,
    requirements TEXT DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE project_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('not_participating','waiting','rejected','accepted','completed') DEFAULT 'waiting',
    pizza_grant ENUM('none','applied','received') DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    start_datetime DATETIME,
    end_datetime DATETIME,
    reminders TEXT,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE event_ysws (
    event_id INT NOT NULL,
    ysws_link VARCHAR(255) NOT NULL,
    PRIMARY KEY (event_id, ysws_link),
    FOREIGN KEY (event_id) REFERENCES events(id)
);


INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `discord_id`, `school`, `ysws_projects`, `hcb_member`, `birthdate`, `class`, `phone`, `role`, `join_date`, `description`, `slack_id`, `github_username`, `active_member`, `password`) VALUES
(1, 'Admin', 'User', 'admin@example.com', '', '', NULL, '0', '2025-01-31', '', '', 'Leader', '2025-06-13 21:54:39', '', '', '', 1, '$2y$10$hipuKxGAeivbuRBymienKOcmjqehGnOL5LEKG9eRtr9yJsbu5yZVW');

CREATE TABLE IF NOT EXISTS discord_login_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state_token VARCHAR(255) NOT NULL UNIQUE,
    csrf_token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    INDEX idx_state_token (state_token),
    INDEX idx_expires_at (expires_at)
);

CREATE TABLE IF NOT EXISTS `slack_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `slack_user_id` varchar(50) NOT NULL,
  `slack_username` varchar(100) DEFAULT NULL,
  `slack_email` varchar(255) DEFAULT NULL,
  `team_id` varchar(50) DEFAULT NULL,
  `team_name` varchar(255) DEFAULT NULL,
  `linked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `slack_user_id` (`slack_user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `github_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `github_id` varchar(50) NOT NULL,
  `github_username` varchar(100) NOT NULL,
  `github_name` varchar(255) DEFAULT NULL,
  `github_email` varchar(255) DEFAULT NULL,
  `github_avatar_url` text DEFAULT NULL,
  `linked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `github_id` (`github_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `google_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `google_id` varchar(50) NOT NULL,
  `google_email` varchar(255) NOT NULL,
  `google_name` varchar(255) DEFAULT NULL,
  `google_picture` text DEFAULT NULL,
  `linked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `google_id` (`google_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add certificate downloads tracking table
CREATE TABLE IF NOT EXISTS certificate_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    certificate_type ENUM('project_accepted', 'project_completed') DEFAULT 'project_accepted',
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    download_count INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_certificate (user_id, project_id, certificate_type)
);

-- Add certificate settings to settings table
INSERT INTO settings (name, value) VALUES 
('certificate_enabled', '1'),
('certificate_title', 'Certificate of Achievement'),
('certificate_org_name', 'PULSE'),
('certificate_signature_name', 'Leadership Team'),
('certificate_signature_title', 'Director')
ON DUPLICATE KEY UPDATE value = value;