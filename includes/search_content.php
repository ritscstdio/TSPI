<?php
require_once 'config.php';

// Set the header to indicate this is an AJAX response
header('Content-Type: application/json');

// Check if this is an AJAX request
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Direct access not allowed'
    ]);
    exit;
}

// Get search query
$query = $_GET['q'] ?? '';

// Validate search query
if (empty($query) || strlen($query) < 2) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Search query too short'
    ]);
    exit;
}

try {
    // Prepare search terms
    $search_term = '%' . $query . '%';
    
    // Perform search
    $sql = "SELECT a.id, a.title, a.slug, a.published_at, u.name as author
            FROM content a
            JOIN users u ON a.author_id = u.id
            WHERE (a.title LIKE ? OR a.content LIKE ?)
            AND a.status = 'published'
            ORDER BY a.published_at DESC
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$search_term, $search_term]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'count' => count($results),
        'results' => $results
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log('Search error: ' . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while searching'
    ]);
} 