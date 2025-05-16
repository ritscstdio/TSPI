-- Check if the current foreign key exists
SET @foreign_key_exists = (
    SELECT COUNT(1) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'comments' 
    AND CONSTRAINT_NAME = 'comments_content_fk'
);

-- If it exists, drop it first
SET @drop_fk_sql = IF(@foreign_key_exists > 0, 
    'ALTER TABLE `comments` DROP FOREIGN KEY `comments_content_fk`', 
    'SELECT "Foreign key does not exist, proceeding to next step"');

PREPARE stmt FROM @drop_fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add the foreign key constraint with the correct reference
ALTER TABLE `comments` ADD CONSTRAINT `comments_content_fk` 
    FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE;

-- Check for search_content.php file
-- If search_articles.php exists and search_content.php doesn't, you may need to rename it
-- This is the search file referenced in header.php 