<?php
require_once '../includes/config.php';

// Only clear admin-specific session variables 
unset($_SESSION['admin_id']);
unset($_SESSION['admin_role']);

// Save any client-related session variables
$client_id = $_SESSION['user_id'] ?? null;
$client_name = $_SESSION['user_name'] ?? null;
$client_email = $_SESSION['user_email'] ?? null;
$client_role = $_SESSION['user_role'] ?? null;
$client_token = $_SESSION['token'] ?? null;

// Only regenerate session ID instead of destroying the entire session
session_regenerate_id(true);

// Restore client-related session variables if they existed
if ($client_id) $_SESSION['user_id'] = $client_id;
if ($client_name) $_SESSION['user_name'] = $client_name;
if ($client_email) $_SESSION['user_email'] = $client_email;
if ($client_role) $_SESSION['user_role'] = $client_role;
if ($client_token) $_SESSION['token'] = $client_token;

// Redirect to login page
redirect('/admin/login.php');
?>
