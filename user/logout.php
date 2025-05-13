<?php
require_once '../includes/config.php';

// Destroy session
session_start();
$_SESSION = array();
session_destroy();

// Redirect to homepage
redirect('/homepage.php');
?> 