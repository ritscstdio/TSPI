<?php
// Database configuration - Use environment variables for Railway deployment
define('DB_HOST', getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', getenv('DB_USER') ?: $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?? '');
define('DB_NAME', getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? 'railway');
define('DB_PORT', getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?? '3306');

// Site configuration
// Dynamic site URL for Railway - will work with Railway's auto-assigned domain
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
$railwayDomain = getenv('RAILWAY_STATIC_URL') ?: $_ENV['RAILWAY_STATIC_URL'] ?? '';

if (!empty($railwayDomain)) {
    define('SITE_URL', rtrim($railwayDomain, '/')); // Ensure no trailing slash
} else {
    // Detect if we're running on Railway (they set PORT env variable)
    if (getenv('PORT') || isset($_ENV['PORT'])) {
        // Running on Railway without custom domain
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        define('SITE_URL', rtrim($protocol . $host, '/')); // Ensure no trailing slash
    } else {
        // Local development
        define('SITE_URL', 'http://localhost');
    }
}

// Define BASE_PATH constant for subdirectory installations
// For Railway and production, this should be empty
// For local XAMPP installation, this might be '/TSPI'
define('BASE_PATH', getenv('BASE_PATH') ?: $_ENV['BASE_PATH'] ?? '');

// Site constants
define('SITE_NAME', 'TSPI Site');
define('ADMIN_EMAIL', 'no-reply@tspi.site');
define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('ITEMS_PER_PAGE', 10);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connect to database using PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Add mysqli connection for compatibility
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if (!$conn) {
    die("mysqli connection failed: " . mysqli_connect_error());
}

// Helper functions
function redirect($path) {
    // Check if path is already a full URL
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        header("Location: " . $path);
        exit;
    }
    
    // Normalize path to ensure it starts with a slash
    $path = '/' . ltrim($path, '/');
    
    // Create full URL without duplication
    $url = SITE_URL . $path;
    
    header("Location: " . $url);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['message'] = "You must be logged in to access that page.";
        redirect('/admin/login.php');
    }
}

function sanitize($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function get_logged_in_user() {  // RENAMED FUNCTION from get_current_user to get_logged_in_user
    global $pdo;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}


function get_logged_in_admin() {  // RENAMED FUNCTION from get_current_user to get_logged_in_user
    global $pdo;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM administrators WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}


function get_flash_message() {
    $message = $_SESSION['message'] ?? '';
    unset($_SESSION['message']);
    return $message;
}

function generate_slug($text) {
    // Remove special characters
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // Lowercase
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

function require_role($allowed_roles) {
    // Ensure user is logged in first
    if (!is_logged_in()) {
        require_login();
    }
    $user = get_logged_in_user();
    $roles = (array) $allowed_roles;
    if (!$user || !in_array($user['role'], $roles)) {
        $_SESSION['message'] = "You do not have permission to access that page.";
        redirect('/admin/index.php');
    }
}

/**
 * Require specific admin role to access a page
 * 
 * @param string|array $allowed_roles One or more roles that are allowed to access
 */
function require_admin_role($allowed_roles) {
    // Ensure admin is logged in first
    if (!is_admin_logged_in()) {
        require_admin_login();
    }
    $admin = get_admin_user();
    $roles = (array) $allowed_roles;
    if (!$admin || !in_array($admin['role'], $roles)) {
        $_SESSION['message'] = "You do not have permission to access that page.";
        redirect('/admin/index.php');
    }
}

// Change redirect after login/logout
function redirect_after_auth() {
    redirect('/homepage.php');
}

// After logout, also change to homepage.php

// edit_1: enable a dedicated profile-pics folder
define('PROFILE_PICS_DIR', __DIR__ . '/../uploads/profile_pics');
if (!is_dir(PROFILE_PICS_DIR)) {
    mkdir(PROFILE_PICS_DIR, 0755, true);
}

// Include administrator authentication functions
require_once __DIR__ . '/admin_auth.php';