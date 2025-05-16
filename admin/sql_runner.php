<?php
// Simple utility script to run SQL files
require_once '../includes/config.php';

// Check if a file was passed
if ($argc < 2) {
    echo "Usage: php sql_runner.php [path_to_sql_file]\n";
    exit(1);
}

$file_path = $argv[1];

// Check if file exists
if (!file_exists($file_path)) {
    echo "Error: File not found: $file_path\n";
    exit(1);
}

// Read the SQL file
$sql = file_get_contents($file_path);

// Execute SQL commands
try {
    $result = $pdo->exec($sql);
    echo "SQL executed successfully from file: $file_path\n";
    echo "Affected rows: $result\n";
} catch (PDOException $e) {
    echo "Error executing SQL: " . $e->getMessage() . "\n";
    exit(1);
} 