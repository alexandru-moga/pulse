-- Migration: Update Phoenix database to match new drag-and-drop component system
-- Date: September 24, 2025
-- Description: Migrates all page component data to use new component types consistent with ComponentManager.php

-- =====================================================
-- BACKUP ORIGINAL DATA (Run before applying changes)
-- =====================================================

-- Create backup tables
CREATE TABLE IF NOT EXISTS `page_apply_backup_20250924` AS SELECT * FROM `page_apply`;
CREATE TABLE IF NOT EXISTS `page_contact_backup_20250924` AS SELECT * FROM `page_contact`;  
CREATE TABLE IF NOT EXISTS `page_index_backup_20250924` AS SELECT * FROM `page_index`;
CREATE TABLE IF NOT EXISTS `page_members_backup_20250924` AS SELECT * FROM `page_members`;

-- =====================================================
-- UPDATE PAGE_APPLY TABLE
-- =====================================================

-- Clear existing data
DELETE FROM `page_apply`;

-- Insert updated component data with new component types
INSERT INTO `page_apply` (`id`, `component_type`, `settings`, `position`, `is_active`) VALUES
(11, 'title_3', '{"text": "Join Phoenix Club"}', 1, 1),
(12, 'title', '{"text": "Ready to start your coding journey with us?"}', 2, 1),
(13, 'apply_form', '{
  "sections": [
    {
      "title": "Personal Information",
      "fields": [
        {
          "name": "first_name",
          "label": "First Name",
          "type": "text",
          "placeholder": "Your first name",
          "required": true
        },
        {
          "name": "last_name",
          "label": "Last Name",
          "type": "text",
          "placeholder": "Your last name",
          "required": true
        }
      ]
    },
    {
      "title": "Academic Information",
      "fields": [
        {
          "name": "school",
          "label": "School",
          "type": "text",
          "placeholder": "Your school name",
          "required": true
        }
      ]
    }
  ]
}', 3, 1);

-- =====================================================
-- UPDATE PAGE_CONTACT TABLE
-- =====================================================

-- Clear existing data
DELETE FROM `page_contact`;

-- Insert updated component data with new component types
INSERT INTO `page_contact` (`id`, `component_type`, `settings`, `position`, `is_active`) VALUES
(1, 'title_3', '{"text": "Contact us"}', 1, 1),
(2, 'title', '{"text": "Feel free to ask anything."}', 2, 1),
(3, 'contact_form', '{
    "title": "Get in Touch",
    "subtitle": "We\'ll respond within 24 hours",
    "description": "Have a question or want to join our team? Fill out the form below and we\'ll get back to you.",
    "fields": [
      {"name": "name", "label": "Full Name", "required": true, "placeholder": "Your name"},
      {"name": "email", "label": "Email Address", "required": true, "placeholder": "your.email@example.com"},
      {"name": "message", "label": "Message", "type": "textarea", "required": true, "placeholder": "Write your message here..."}
    ],
    "button_text": "Send Message"
}', 3, 1);

-- =====================================================
-- UPDATE PAGE_INDEX TABLE
-- =====================================================

-- Clear existing data
DELETE FROM `page_index`;

-- Insert updated component data with new component types
INSERT INTO `page_index` (`id`, `component_type`, `settings`, `position`, `is_active`) VALUES
(1, 'welcome', '{
  "title": "Welcome to <span class=\"text-red-500\">PHOENIX</span>",
  "subtitle": "STUDENT-LED TECH COMMUNITY",
  "description": "Join a vibrant community of students passionate about technology and innovation. We build, learn, and grow together through hackathons, workshops, and collaborative projects.",
  "primaryButton": {
    "text": "Get Involved",
    "url": "/apply.php"
  },
  "secondaryButton": {
    "text": "Contact us",
    "url": "/contact.php"
  }
}', 1, 1),
(2, 'scroll_arrow', '[]', 2, 1),
(4, 'title_2', '{
  "first": "OUR",
  "second": "ACTIVITY"
}', 3, 1),
(8, 'stats', '[
  { "value": "150", "label": "Active Members" },
  { "value": "25", "label": "Projects Active" },
  { "value": "50", "label": "Projects Completed" }
]', 4, 1),
(9, 'title_2', '{
  "first": "OUR",
  "second": "IMPACT"
}', 5, 1),
(10, 'core_values', '{
    "values": [
      {
        "title": "Excellence",
        "description": "We strive for excellence in everything we do, pushing boundaries and challenging the status quo.",
        "icon": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" fill=\"currentColor\" viewBox=\"0 0 24 24\"><path d=\"M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z\"/></svg>"
      },
      {
        "title": "Innovation",
        "description": "We embrace innovation, encouraging creative thinking and novel approaches to problem-solving.",
        "icon": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" fill=\"currentColor\" viewBox=\"0 0 24 24\"><path d=\"M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.5 5h3v7.5h-3v-7.5zm1.5 12.75c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z\"/></svg>"
      },
      {
        "title": "Integrity",
        "description": "We act with integrity in all our dealings, maintaining the highest ethical standards and transparency.",
        "icon": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" fill=\"currentColor\" viewBox=\"0 0 24 24\"><path d=\"M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.31 7.526c-.099-.807.528-1.526 1.348-1.526.771 0 1.377.676 1.28 1.451l-.757 6.053c-.035.283-.276.496-.561.496s-.526-.213-.562-.496l-.748-5.978zm1.31 10.724c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z\"/></svg>"
      }
    ]
  }', 6, 1);

-- =====================================================
-- UPDATE PAGE_MEMBERS TABLE
-- =====================================================

-- Clear existing data
DELETE FROM `page_members`;

-- Insert updated component data with new component types
INSERT INTO `page_members` (`id`, `component_type`, `settings`, `position`, `is_active`) VALUES
(1, 'title_3', '{"text": "Our members"}', 1, 1),
(2, 'title', '{"text": "Meet the talented programmers who make up PULSE"}', 2, 1),
(4, 'members_grid', '{"title": "Our Team", "subtitle": "Meet the PULSE community"}', 3, 1);

-- =====================================================
-- COMPONENT TYPE MAPPINGS APPLIED
-- =====================================================
/*
Old Component Type -> New Component Type
'heading' (h1) -> 'title_3'
'heading' (h3) -> 'title' 
'hero' -> 'welcome'
'text' -> Content moved to other components
'button' -> Integrated into other components
'image' -> Removed/integrated
'apply_form' -> 'apply_form' (no change)
'contact_form' -> 'contact_form' (no change)
'members_grid' -> 'members_grid' (no change)

New components added:
- 'welcome': Hero-style welcome section with title, subtitle, and buttons
- 'scroll_arrow': Navigation arrow component
- 'title_2': Dual-text title component
- 'stats': Statistics display component
- 'core_values': Values showcase component
- 'title_3': Large heading component
- 'title': General heading component
*/

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Check component counts by type
SELECT component_type, COUNT(*) as count 
FROM page_apply 
GROUP BY component_type
UNION ALL
SELECT component_type, COUNT(*) as count 
FROM page_contact 
GROUP BY component_type
UNION ALL
SELECT component_type, COUNT(*) as count 
FROM page_index 
GROUP BY component_type
UNION ALL
SELECT component_type, COUNT(*) as count 
FROM page_members 
GROUP BY component_type;

-- List all unique component types across all page tables
SELECT DISTINCT component_type FROM page_apply
UNION
SELECT DISTINCT component_type FROM page_contact
UNION  
SELECT DISTINCT component_type FROM page_index
UNION
SELECT DISTINCT component_type FROM page_members
ORDER BY component_type;

-- =====================================================
-- ROLLBACK INSTRUCTIONS (if needed)
-- =====================================================
/*
To rollback this migration, run:

DROP TABLE `page_apply`;
CREATE TABLE `page_apply` AS SELECT * FROM `page_apply_backup_20250924`;

DROP TABLE `page_contact`;  
CREATE TABLE `page_contact` AS SELECT * FROM `page_contact_backup_20250924`;

DROP TABLE `page_index`;
CREATE TABLE `page_index` AS SELECT * FROM `page_index_backup_20250924`;

DROP TABLE `page_members`;
CREATE TABLE `page_members` AS SELECT * FROM `page_members_backup_20250924`;

-- Restore table structure
ALTER TABLE `page_apply` ADD PRIMARY KEY (`id`);
ALTER TABLE `page_contact` ADD PRIMARY KEY (`id`);
ALTER TABLE `page_index` ADD PRIMARY KEY (`id`);
ALTER TABLE `page_members` ADD PRIMARY KEY (`id`);

-- Then drop backup tables
DROP TABLE `page_apply_backup_20250924`;
DROP TABLE `page_contact_backup_20250924`;
DROP TABLE `page_index_backup_20250924`;  
DROP TABLE `page_members_backup_20250924`;
*/
