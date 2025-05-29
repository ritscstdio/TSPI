<?php
require_once 'includes/config.php';

// Set a proper content type
header('Content-Type: text/plain');

// Output server environment info
echo "Server Environment Information\n";
echo "----------------------------\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";
echo "SITE_URL: " . SITE_URL . "\n";
echo "BASE_PATH: " . BASE_PATH . "\n\n";

echo "Asset Path Resolution Tests\n";
echo "-----------------------\n";
$test_paths = [
    '/src/assets/logo.jpg',
    'src/assets/logo.jpg',
    '/assets/css/styles.css',
    'assets/css/styles.css',
    '/src/css/mainstyles.css',
    'src/css/mainstyles.css'
];

foreach ($test_paths as $path) {
    echo "Original Path: $path\n";
    echo "Resolved with resolve_asset_path(): " . resolve_asset_path($path) . "\n";
    echo "Resolved with resolve_css_path(): " . resolve_css_path($path) . "\n";
    
    // Check if file exists on server
    $server_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($path, '/');
    echo "Server path: $server_path\n";
    echo "File exists on server: " . (file_exists($server_path) ? 'Yes' : 'No') . "\n";
    
    // Check case-insensitive variations
    $dir = dirname($server_path);
    $base = basename($server_path);
    
    echo "Directory exists: " . (is_dir($dir) ? 'Yes' : 'No') . "\n";
    if (is_dir($dir)) {
        $files = scandir($dir);
        echo "Files in directory " . $dir . ":\n";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "- $file" . (strtolower($file) == strtolower($base) ? ' (case-insensitive match)' : '') . "\n";
            }
        }
    }
    
    echo "\n";
}

// List all directories to find assets
function list_directories($dir, $prefix = '') {
    if (!is_dir($dir)) {
        echo "$dir is not a directory\n";
        return;
    }
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            echo $prefix . "[DIR] $item\n";
            if ($item == 'src' || $item == 'assets') {
                list_directories($path, $prefix . '  ');
            }
        }
    }
}

echo "Directory Structure\n";
echo "-----------------\n";
list_directories($_SERVER['DOCUMENT_ROOT']); 