-- Migration: Convert old page builder structure to new DragDropBuilder structure
-- Run this migration to convert existing pages to work with the new drag-and-drop builder

-- Update existing page tables to match new DragDropBuilder structure
-- This script will modify all page_* tables to use the new column structure

-- First, let's create a temporary script to handle all page tables dynamically
-- You'll need to run these commands for each page table (page_index, page_members, page_apply, page_contact, etc.)

-- For page_index table:
-- 1. Add new columns
ALTER TABLE `page_index` 
ADD COLUMN `component_type` varchar(50) DEFAULT NULL AFTER `block_type`,
ADD COLUMN `settings` text DEFAULT NULL AFTER `content`,
ADD COLUMN `position` int(11) DEFAULT 0 AFTER `order_num`;

-- 2. Copy data from old columns to new columns
UPDATE `page_index` SET 
`component_type` = `block_type`,
`settings` = `content`,
`position` = `order_num`;

-- 3. Drop old columns
ALTER TABLE `page_index`
DROP COLUMN `block_name`,
DROP COLUMN `block_type`, 
DROP COLUMN `content`,
DROP COLUMN `order_num`;

-- For page_members table:
-- 1. Add new columns
ALTER TABLE `page_members` 
ADD COLUMN `component_type` varchar(50) DEFAULT NULL AFTER `block_type`,
ADD COLUMN `settings` text DEFAULT NULL AFTER `content`,
ADD COLUMN `position` int(11) DEFAULT 0 AFTER `order_num`;

-- 2. Copy data from old columns to new columns
UPDATE `page_members` SET 
`component_type` = `block_type`,
`settings` = `content`,
`position` = `order_num`;

-- 3. Drop old columns
ALTER TABLE `page_members`
DROP COLUMN `block_name`,
DROP COLUMN `block_type`, 
DROP COLUMN `content`,
DROP COLUMN `order_num`;

-- For page_apply table:
-- 1. Add new columns
ALTER TABLE `page_apply` 
ADD COLUMN `component_type` varchar(50) DEFAULT NULL AFTER `block_type`,
ADD COLUMN `settings` text DEFAULT NULL AFTER `content`,
ADD COLUMN `position` int(11) DEFAULT 0 AFTER `order_num`;

-- 2. Copy data from old columns to new columns
UPDATE `page_apply` SET 
`component_type` = `block_type`,
`settings` = `content`,
`position` = `order_num`;

-- 3. Drop old columns
ALTER TABLE `page_apply`
DROP COLUMN `block_name`,
DROP COLUMN `block_type`, 
DROP COLUMN `content`,
DROP COLUMN `order_num`;

-- For page_contact table:
-- 1. Add new columns
ALTER TABLE `page_contact` 
ADD COLUMN `component_type` varchar(50) DEFAULT NULL AFTER `block_type`,
ADD COLUMN `settings` text DEFAULT NULL AFTER `content`,
ADD COLUMN `position` int(11) DEFAULT 0 AFTER `order_num`;

-- 2. Copy data from old columns to new columns
UPDATE `page_contact` SET 
`component_type` = `block_type`,
`settings` = `content`,
`position` = `order_num`;

-- 3. Drop old columns
ALTER TABLE `page_contact`
DROP COLUMN `block_name`,
DROP COLUMN `block_type`, 
DROP COLUMN `content`,
DROP COLUMN `order_num`;

-- Note: If you have other page_* tables, repeat the same pattern above for each table.
-- You can find all page tables with: SHOW TABLES LIKE 'page_%';

-- Component type mapping for old types to new types:
-- The old component types may need to be mapped to new types if they're different
-- Common mappings might include:
-- 'title-3' -> 'heading'
-- 'heading-3' -> 'heading'  
-- 'text-block' -> 'text'
-- 'contact-form' -> 'contact_form' (if you create this component)
-- 'members-grid' -> 'members_grid' (if you create this component)

-- Update component types if needed (example):
-- UPDATE `page_index` SET `component_type` = 'heading' WHERE `component_type` = 'title-3';
-- UPDATE `page_index` SET `component_type` = 'heading' WHERE `component_type` = 'heading-3';

-- You might also need to update the settings JSON format if it differs from the old content format
