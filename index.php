<?php
// Main index file that redirects to the homepage
require_once 'includes/config.php';

// Redirect to homepage using the redirect function from config.php
// This function will properly handle the path with SITE_URL
redirect('homepage.php');
?> 