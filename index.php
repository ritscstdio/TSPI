<?php
// Main index file that redirects to the homepage
require_once 'includes/config.php';

// Redirect to homepage using the redirect function from config.php
// This function will properly handle the path with SITE_URL
$path = 'homepage.php';

// Check if we're getting a duplicated domain in the request
$host = $_SERVER['HTTP_HOST'] ?? '';
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

// If we detect a duplicated domain in the URL, clean it up
if (!empty($host) && strpos($request_uri, $host) !== false) {
    // Extract just the path after the domain duplication
    $path_parts = explode($host, $request_uri, 2);
    if (isset($path_parts[1])) {
        $path = ltrim($path_parts[1], '/');
        if (empty($path)) {
            $path = 'homepage.php';
        }
    }
}

redirect($path);
?> 