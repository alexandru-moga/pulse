SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `applications` (
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

INSERT INTO `pages` (`id`, `name`, `title`, `description`, `table_name`, `module_config`, `menu_enabled`, `created_at`, `parent_id`, `visibility`) VALUES
(1, 'index', 'Home', 'Welcome to PULSE - Programming University Learning & Software Engineering', 'page_index', NULL, 1, '2025-05-10 17:47:05', NULL, NULL),
(2, 'members', 'Members', 'Our PULSE community members', 'page_members', NULL, 1, '2025-05-10 17:47:05', NULL, NULL),
(3, 'apply', 'Apply', 'Apply for a PULSE project', 'page_apply', NULL, 1, '2025-05-10 17:47:05', NULL, NULL),
(4, 'contact', 'Contact', 'Get in touch with PULSE', 'page_contact', NULL, 1, '2025-05-10 17:47:05', NULL, NULL),
(5, 'dashboard', 'Dashboard', 'Member dashboard', NULL, NULL, 1, '2025-05-10 17:47:05', NULL, NULL),
(12, 'dashboard/login', 'Login', 'User login page', NULL, NULL, 1, '2025-05-28 09:21:51', 5, 'guest'),
(13, 'dashboard/forgot', 'Forgot Password', 'Reset your password', NULL, NULL, 1, '2025-05-28 09:21:51', 5, 'guest'),
(14, 'dashboard/index', 'Profile', 'User profile page', NULL, NULL, 1, '2025-05-28 09:27:57', 5, 'Member, Co-leader, Leader'),
(15, 'dashboard/change-password', 'Change Password', 'Change your password', NULL, NULL, 1, '2025-05-28 09:27:57', 5, 'Member, Co-leader, Leader'),
(16, 'dashboard/projects', 'My Projects', 'Your assigned projects', NULL, NULL, 1, '2025-05-28 09:27:57', 5, 'Member, Co-leader, Leader'),
(17, 'dashboard/users', 'User Management', 'Manage users', NULL, NULL, 1, '2025-05-28 09:27:57', 5, 'Co-leader, Leader'),
(19, 'dashboard/applications', 'Applications', 'Applications', NULL, NULL, 1, '2025-06-08 05:17:14', 5, 'Co-leader, Leader'),
(20, 'dashboard/contact_messages', 'Contact Messages', 'Contact Messages', NULL, NULL, 1, '2025-06-09 20:32:26', 5, 'Co-leader, Leader'),
(21, 'dashboard/settings', 'Settings', 'Settings', NULL, NULL, 1, '2025-06-10 07:43:09', 5, 'Co-leader, Leader'),
(30, 'dashboard/logout', 'Logout', 'Logout', NULL, NULL, 1, '2025-05-31 18:40:41', 5, 'Member, Co-leader, Leader');

CREATE TABLE `page_apply` (
  `id` int(11) NOT NULL,
  `block_name` varchar(100) NOT NULL,
  `block_type` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `page_apply` (`id`, `block_name`, `block_type`, `content`, `order_num`, `is_active`) VALUES
(11, 'apply_title', 'title-3', '{\"text\": \"Join Phoenix Club\"}', 1, 1),
(12, 'apply_subtitle', 'heading-3', '{\"text\": \"Ready to start your coding journey with us?.\"}', 2, 1),
(13, 'apply_form', 'apply-form', '{\n  \"sections\": [\n    {\n      \"title\": \"Personal Information\",\n      \"fields\": [\n        {\n          \"name\": \"first_name\",\n          \"label\": \"First Name\",\n          \"type\": \"text\",\n          \"placeholder\": \"Your first name\",\n          \"required\": true\n        },\n        {\n          \"name\": \"last_name\",\n          \"label\": \"Last Name\",\n          \"type\": \"text\",\n          \"placeholder\": \"Your last name\",\n          \"required\": true\n        }\n      ]\n    },\n    {\n      \"title\": \"Academic Information\",\n      \"fields\": [\n        {\n          \"name\": \"school\",\n          \"label\": \"School\",\n          \"type\": \"text\",\n          \"placeholder\": \"Your school name\",\n          \"required\": true\n        }\n      ]\n    }\n  ]\n}', 3, 1);

CREATE TABLE `page_contact` (
  `id` int(11) NOT NULL,
  `block_name` varchar(100) NOT NULL,
  `block_type` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `page_contact` (`id`, `block_name`, `block_type`, `content`, `order_num`, `is_active`) VALUES
(1, 'apply_title', 'title-3', '{\"text\": \"Contact us\"}', 1, 1),
(2, 'subtitle', 'heading-3', '{\"text\": \"Feel free to ask anything.\"}', 2, 1),
(3, 'contact_form', 'contact-form', '{\n    \"title\": \"Get in Touch\",\n    \"subtitle\": \"We\'ll respond within 24 hours\",\n    \"description\": \"Have a question or want to join our team? Fill out the form below and we\'ll get back to you.\",\n    \"fields\": [\n      {\"name\": \"name\", \"label\": \"Full Name\", \"required\": true, \"placeholder\": \"Your name\"},\n      {\"name\": \"email\", \"label\": \"Email Address\", \"required\": true, \"placeholder\": \"your.email@example.com\"},\n      {\"name\": \"message\", \"label\": \"Message\", \"type\": \"textarea\", \"required\": true, \"placeholder\": \"Write your message here...\"}\n    ],\n    \"button_text\": \"Send Message\"\n}\n', 3, 1);

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
(2, 'scroll-arrow', 'scroll-arrow', '[]', 2, 1),
(4, 'title-2', 'title-2', '{\r\n  \"first\": \"OUR\",\r\n  \"second\": \"ACTIVITY\"\r\n}', 3, 1),
(8, 'active_members', 'stats', '[\n  { \"value\": \"150\", \"label\": \"Active Members\" },\n  { \"value\": \"25\", \"label\": \"Projects Active\" },\n  { \"value\": \"50\", \"label\": \"Projects Completed\" }\n]\n', 8, 1),
(9, 'title-2', 'title-2', '{\r\n  \"first\": \"OUR\",\r\n  \"second\": \"IMPACT\"\r\n}', 9, 1),
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
(1, 'title', 'title-3', '{\"text\": \"Our members\"}', 1, 1),
(2, 'members_description', 'heading-3', '{\"text\": \"Meet the talented programmers who make up PULSE\"}', 2, 1),
(4, 'members_grid', 'members-grid', '{\"title\": \"Our Team\", \"subtitle\": \"Meet the PULSE community\"}', 3, 1);

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','completed') DEFAULT 'active',
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
(8, 'site_url', 'https://example.com'),
(9, 'maintenance_mode', '0');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `discord_id` varchar(255) DEFAULT NULL,
  `school` varchar(255) DEFAULT NULL,
  `ysws_projects` text DEFAULT NULL,
  `hcb_member` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `class` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `join_date` timestamp NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `slack_id` text DEFAULT NULL,
  `github_username` text DEFAULT NULL,
  `active_member` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL DEFAULT 'changeme'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_projects` (
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `discord_id`, `school`, `ysws_projects`, `hcb_member`, `birthdate`, `class`, `phone`, `role`, `join_date`, `description`, `slack_id`, `github_username`, `active_member`, `password`) VALUES
(1, 'Admin', 'User', 'admin@example.com', '', '', NULL, '0', '2025-01-31', '', '', 'Leader', '2025-06-13 21:54:39', '', '', '', 1, '$2y$10$hipuKxGAeivbuRBymienKOcmjqehGnOL5LEKG9eRtr9yJsbu5yZVW');