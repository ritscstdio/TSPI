<?php
require_once '../includes/config.php';

// Only administrators or users with setup privileges should run this script
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Only administrators can run this script.");
}

try {
    $pdo->beginTransaction();
    
    // Create users table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            name VARCHAR(100),
            role ENUM('admin', 'editor', 'user', 'comment_moderator') DEFAULT 'user',
            status ENUM('active', 'inactive', 'banned') DEFAULT 'inactive',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Create email verification table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS email_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            verification_code VARCHAR(32) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Create password reset table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            reset_token VARCHAR(32) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Check if admin user exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $admin_exists = $stmt->fetchColumn() > 0;
    
    // Create default admin user if none exists
    if (!$admin_exists) {
        // Generate a random password for the admin
        $admin_password = bin2hex(random_bytes(8)); // 16 characters
        $admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, email, name, role, status)
            VALUES ('admin', ?, 'admin@example.com', 'Administrator', 'admin', 'active')
        ");
        $stmt->execute([$admin_password_hash]);
        
        echo "<p>Created default admin user:</p>";
        echo "<ul>";
        echo "<li>Username: admin</li>";
        echo "<li>Password: " . $admin_password . "</li>";
        echo "<li>Email: admin@example.com</li>";
        echo "</ul>";
        echo "<p><strong>Important:</strong> Please change the password and email immediately!</p>";
    }
    
    $pdo->commit();
    
    echo "<p>Database tables created successfully!</p>";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<p>Error creating tables: " . $e->getMessage() . "</p>";
}
?> 