<?php
require_once '../includes/config.php';

// Clear admin session variables 
unset($_SESSION['admin_id']);
unset($_SESSION['admin_role']);

// Destroy session
session_destroy();

// Redirect to login page
redirect('/admin/login.php');
?>
