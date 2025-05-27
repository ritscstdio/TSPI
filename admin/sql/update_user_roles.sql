-- SQL script to update user roles
-- This script converts old roles (editor, comment_moderator) to the new role structure

-- Update comment_moderator to moderator
UPDATE administrators
SET role = 'moderator'
WHERE role = 'comment_moderator';

-- Update editor to moderator
UPDATE administrators
SET role = 'moderator'
WHERE role = 'editor';

-- Only log the change if system_logs table exists
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'system_logs');
SET @sql = IF(@table_exists > 0, 
    'INSERT INTO system_logs (action, description, created_at) VALUES (\'role_update\', \'Updated user roles from old structure to new structure\', NOW())',
    'SELECT \'System logs table not found, skipping log entry\' as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Display success message
SELECT 'Role updates completed successfully.' as message; 