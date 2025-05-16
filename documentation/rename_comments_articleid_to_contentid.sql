-- Rename content_id to content_id in comments table
-- WARNING: Before running, check if the foreign key constraints exist using:
-- SHOW CREATE TABLE comments;
-- If the constraints have different names, adjust the statements below accordingly

-- Drop existing foreign key constraint on content_id
ALTER TABLE `comments` DROP FOREIGN KEY `comments_content_fk`;
-- Drop index on content_id
ALTER TABLE `comments` DROP INDEX `content_id`;

-- Rename column
ALTER TABLE `comments` CHANGE `content_id` `content_id` INT(11) NOT NULL;

-- Recreate index on content_id
ALTER TABLE `comments` ADD KEY `content_id` (`content_id`);

-- Add foreign key constraint referencing content table
ALTER TABLE `comments` ADD CONSTRAINT `comments_content_fk` FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE; 