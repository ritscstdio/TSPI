<?php
// Admin authentication functions

/**
 * Check if the current user is logged in as an administrator
 * 
 * @return bool True if logged in as admin, false otherwise
 */
function is_admin_logged_in() {
    global $pdo;
    
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    // Verify the admin exists in the administrators table
    $stmt = $pdo->prepare("SELECT id FROM administrators WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ? true : false;
}

/**
 * Check if the current user is an admin
 * 
 * @return bool True if user is an admin, false otherwise
 */
function is_admin() {
    return is_admin_logged_in();
}

/**
 * Require admin login to access a page
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        $_SESSION['message'] = "You must be logged in as an administrator to access that page.";
        redirect('/admin/login.php');
    }
}

/**
 * Get the currently logged in admin user
 * 
 * @return array|null The admin user data or null if not logged in
 */
function get_admin_user() {
    global $pdo;
    if (isset($_SESSION['admin_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM administrators WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    }
    return null;
} 