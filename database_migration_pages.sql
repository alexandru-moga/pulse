-- Updated Phoenix database with new drag-and-drop builder structure
-- This replaces the old phoenix.sql with migrated page tables

-- Drop and recreate page tables with new structure
DROP TABLE IF EXISTS `page_apply`;
CREATE TABLE `page_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `component_type` varchar(50) NOT NULL,
  `settings` text DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrate data to new structure
INSERT INTO `page_apply` (`id`, `component_type`, `settings`, `position`, `is_active`) VALUES
(11, 'heading', '{\"text\": \"Join Phoenix Club\", \"level\": \"h1\", \"align\": \"center\"}', 1, 1),
(12, 'heading', '{\"text\": \"Ready to start your coding journey with us?\", \"level\": \"h3\", \"align\": \"center\"}', 2, 1),
(13, 'apply_form', '{\n  \"sections\": [\n    {\n      \"title\": \"Personal Information\",\n      \"fields\": [\n        {\n          \"name\": \"first_name\",\n          \"label\": \"First Name\",\n          \"type\": \"text\",\n          \"placeholder\": \"Your first name\",\n          \"required\": true\n        },\n        {\n          \"name\": \"last_name\",\n          \"label\": \"Last Name\",\n          \"type\": \"text\",\n          \"placeholder\": \"Your last name\",\n          \"required\": true\n        }\n      ]\n    },\n    {\n      \"title\": \"Academic Information\",\n      \"fields\": [\n        {\n          \"name\": \"school\",\n          \"label\": \"School\",\n          \"type\": \"text\",\n          \"placeholder\": \"Your school name\",\n          \"required\": true\n        }\n      ]\n    }\n  ]\n}', 3, 1);

DROP TABLE IF EXISTS `page_contact`;
CREATE TABLE `page_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `component_type` varchar(50) NOT NULL,
  `settings` text DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrate data to new structure
INSERT INTO `page_contact` (`id`, `component_type`, `settings`, `position`, `is_active`) VALUES
(1, 'heading', '{\"text\": \"Contact us\", \"level\": \"h1\", \"align\": \"center\"}', 1, 1),
(2, 'heading', '{\"text\": \"Feel free to ask anything.\", \"level\": \"h3\", \"align\": \"center\"}', 2, 1),
(3, 'contact_form', '{\n    \"title\": \"Get in Touch\",\n    \"subtitle\": \"We\'ll respond within 24 hours\",\n    \"description\": \"Have a question or want to join our team? Fill out the form below and we\'ll get back to you.\",\n    \"fields\": [\n      {\"name\": \"name\", \"label\": \"Full Name\", \"required\": true, \"placeholder\": \"Your name\"},\n      {\"name\": \"email\", \"label\": \"Email Address\", \"required\": true, \"placeholder\": \"your.email@example.com\"},\n      {\"name\": \"message\", \"label\": \"Message\", \"type\": \"textarea\", \"required\": true, \"placeholder\": \"Write your message here...\"}\n    ],\n    \"button_text\": \"Send Message\"\n}\n', 3, 1);

DROP TABLE IF EXISTS `page_index`;
CREATE TABLE `page_index` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `component_type` varchar(50) NOT NULL,
  `settings` text DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add sample homepage content
INSERT INTO `page_index` (`id`, `component_type`, `settings`, `position`, `is_active`) VALUES
(1, 'hero', '{\"title\": \"Welcome to Daydream Timisoara\", \"subtitle\": \"Building the future through code\", \"background\": \"#1f2937\", \"align\": \"center\"}', 1, 1),
(2, 'text', '{\"content\": \"We are a community of passionate developers and innovators creating amazing projects together.\", \"align\": \"center\"}', 2, 1),
(3, 'button', '{\"text\": \"Join Us Today\", \"link\": \"/apply.php\", \"style\": \"primary\", \"size\": \"lg\", \"align\": \"center\"}', 3, 1);

DROP TABLE IF EXISTS `page_members`;
CREATE TABLE `page_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `component_type` varchar(50) NOT NULL,
  `settings` text DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrate data to new structure
INSERT INTO `page_members` (`id`, `component_type`, `settings`, `position`, `is_active`) VALUES
(1, 'heading', '{\"text\": \"Our members\", \"level\": \"h1\", \"align\": \"center\"}', 1, 1),
(2, 'heading', '{\"text\": \"Meet the talented programmers who make up PULSE\", \"level\": \"h3\", \"align\": \"center\"}', 2, 1),
(4, 'members_grid', '{\"title\": \"Our Team\", \"subtitle\": \"Meet the PULSE community\"}', 3, 1);
