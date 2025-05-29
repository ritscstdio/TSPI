<?php
// Database configuration with environment variable support for Docker/Railway deployment
$db_host = getenv('DB_HOST') ?: 'crossover.proxy.rlwy.net';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: 'mQXhlFdbZwNPUnyQBGWSBKPHOMajvArt';
$db_name = getenv('DB_NAME') ?: 'railway';
$db_port = getenv('DB_PORT') ?: '50379';

// Create the database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Site URL configuration
$site_url = getenv('RAILWAY_STATIC_URL') ?: 'http://localhost';

// Other configuration settings
$upload_dir = __DIR__ . '/uploads/';
$temp_dir = __DIR__ . '/temp/';
$logs_dir = __DIR__ . '/logs/';

// Create directories if they don't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}
if (!file_exists($temp_dir)) {
    mkdir($temp_dir, 0775, true);
}
if (!file_exists($logs_dir)) {
    mkdir($logs_dir, 0775, true);
}
?> 