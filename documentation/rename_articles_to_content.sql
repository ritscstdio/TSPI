-- Rename primary content tables
RENAME TABLE `contents` TO `content`;
RENAME TABLE `content_categories` TO `content_categories`;
RENAME TABLE `content_tags` TO `content_tags`;
RENAME TABLE `content_votes` TO `content_votes`;

-- Drop existing foreign keys referencing old contents table
ALTER TABLE `comments` DROP FOREIGN KEY `comments_ibfk_1`;

-- Recreate foreign key to content table
ALTER TABLE `comments` ADD CONSTRAINT `comments_content_fk` FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE;

-- Update additional FK constraints for categories, tags, votes tables
ALTER TABLE `content_categories` DROP FOREIGN KEY `content_categories_ibfk_1`;
ALTER TABLE `content_categories` ADD CONSTRAINT `content_categories_content_fk` FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE;
ALTER TABLE `content_tags` DROP FOREIGN KEY `content_tags_ibfk_1`;
ALTER TABLE `content_tags` ADD CONSTRAINT `content_tags_content_fk` FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE;
ALTER TABLE `content_votes` DROP FOREIGN KEY `content_votes_ibfk_1`;
ALTER TABLE `content_votes` ADD CONSTRAINT `content_votes_content_fk` FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE;

COMMIT; 