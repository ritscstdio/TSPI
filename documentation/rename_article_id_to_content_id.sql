-- Rename article_id to content_id in comments table

-- Check if there's an index on article_id
-- SHOW INDEX FROM comments WHERE Column_name = 'article_id';
-- Check if there are foreign keys on article_id
-- SHOW CREATE TABLE comments;

-- If the foreign key is present, drop it first (modify constraint name as needed)
-- ALTER TABLE `comments` DROP FOREIGN KEY `comments_content_fk`;

-- Rename the column
ALTER TABLE `comments` CHANGE `article_id` `content_id` INT(11) NOT NULL;

-- Re-add the foreign key constraint
ALTER TABLE `comments` ADD CONSTRAINT `comments_content_fk` FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE; 