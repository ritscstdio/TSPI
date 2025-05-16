-- Check if there's an article_id column in the comments table
SET @column_exists = (
    SELECT COUNT(1) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'comments' 
    AND COLUMN_NAME = 'article_id'
);

-- If article_id column exists, rename it to content_id
SET @rename_column_sql = IF(@column_exists > 0, 
    'ALTER TABLE `comments` CHANGE `article_id` `content_id` INT(11)',
    'SELECT "Column article_id does not exist, no rename needed"');

PREPARE stmt FROM @rename_column_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if there's an existing foreign key constraint
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

-- Add the foreign key constraint to the correct table
ALTER TABLE `comments` ADD CONSTRAINT `comments_content_fk` 
    FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE; 