-- Update administrators table schema to use new role definitions
-- This adds new roles and removes old ones

-- Check if table exists
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'administrators');

-- Create or modify table
SET @sql = IF(@table_exists > 0,
    'ALTER TABLE administrators MODIFY COLUMN role ENUM("admin", "moderator", "insurance_officer", "loan_officer", "secretary") DEFAULT "moderator"',
    'CREATE TABLE administrators (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM("admin", "moderator", "insurance_officer", "loan_officer", "secretary") DEFAULT "moderator",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

-- Execute the statement
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if system_logs table exists before logging
SET @logs_exist = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'system_logs');
SET @log_sql = IF(@logs_exist > 0, 
    'INSERT INTO system_logs (action, description, created_at) VALUES (\'schema_update\', \'Updated administrators table schema to use new role definitions\', NOW())',
    'SELECT \'System logs table not found, skipping log entry\' as message'
);

PREPARE log_stmt FROM @log_sql;
EXECUTE log_stmt;
DEALLOCATE PREPARE log_stmt;

-- Display success message
SELECT 'Schema update completed successfully.' as message; 