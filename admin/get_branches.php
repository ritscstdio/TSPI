<?php
/**
 * Get Branches by Region API
 * 
 * This file handles AJAX requests to fetch branches for a specified region
 */

require_once '../includes/config.php';
require_admin_login();

// Set response header to JSON
header('Content-Type: application/json');

// Validate region parameter
$region = isset($_GET['region']) ? $_GET['region'] : null;

if (!$region) {
    echo json_encode([
        'success' => false,
        'message' => 'Region parameter is required'
    ]);
    exit;
}

try {
    // Query branches by region
    $stmt = $pdo->prepare("SELECT * FROM branches WHERE region = ? ORDER BY branch ASC");
    $stmt->execute([$region]);
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return branches as JSON
    echo json_encode([
        'success' => true,
        'branches' => $branches
    ]);
} catch (Exception $e) {
    // Handle errors
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching branches: ' . $e->getMessage()
    ]);
}
exit; 