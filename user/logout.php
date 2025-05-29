<?php
require_once '../includes/config.php';

// Only start a session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy session
$_SESSION = array();
session_destroy();

// Redirect to homepage
redirect('/homepage.php');
?> 