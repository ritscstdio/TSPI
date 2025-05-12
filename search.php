<?php
require_once 'includes/config.php';

// Set proper headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: X-Requested-With');

// Get search query
$q = trim($_GET['q'] ?? '');

// Debug - log the search query to error log
error_log('Search query: ' . $q);

if (empty($q)) {
    echo json_encode([]);
    exit;
}

try {
    // Debug - check DB connection
    error_log('Database connection status: ' . ($pdo ? 'Connected' : 'Failed'));
    
    // Check for articles to verify database is accessible
    $check = $pdo->query("SELECT COUNT(*) as count FROM articles");
    $articleCount = $check->fetchColumn();
    error_log('Total article count in database: ' . $articleCount);
    
    // Build search query with more explicit LIKE conditions
    $sql = "SELECT DISTINCT a.id, a.title, a.slug, LEFT(a.content, 100) as excerpt, u.name as author, a.published_at 
            FROM articles a 
            LEFT JOIN users u ON a.author_id = u.id 
            LEFT JOIN article_tags at ON a.id = at.article_id 
            LEFT JOIN tags t ON at.tag_id = t.id 
            WHERE a.status = 'published' 
            AND (
                a.title LIKE :query1 OR 
                a.content LIKE :query2 OR 
                u.name LIKE :query3 OR 
                t.name LIKE :query4
            ) 
            ORDER BY a.published_at DESC 
            LIMIT 5";
            
    // Debug - log the SQL query
    error_log('Search SQL: ' . $sql);
    
    $stmt = $pdo->prepare($sql);
    
    // Bind the same parameter multiple times with different names
    $searchParam = "%$q%";
    $stmt->bindValue(':query1', $searchParam, PDO::PARAM_STR);
    $stmt->bindValue(':query2', $searchParam, PDO::PARAM_STR);
    $stmt->bindValue(':query3', $searchParam, PDO::PARAM_STR);
    $stmt->bindValue(':query4', $searchParam, PDO::PARAM_STR);
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug - log the number of results found and first result if available
    error_log('Search results count: ' . count($results));
    if (count($results) > 0) {
        error_log('First result: ' . json_encode($results[0]));
        // Explicitly verify author and date fields are present
        $first_result = $results[0];
        error_log('Author: ' . ($first_result['author'] ?? 'MISSING'));
        error_log('Date: ' . ($first_result['published_at'] ?? 'MISSING'));
        
        // Force add author and date if missing
        foreach ($results as &$item) {
            // Always include these fields to ensure consistent output
            if (!isset($item['author']) || empty($item['author'])) {
                error_log('Adding missing author field');
                $item['author'] = 'TSPI Staff';
            }
            
            if (!isset($item['published_at']) || empty($item['published_at'])) {
                error_log('Adding missing published_at field');
                $item['published_at'] = date('Y-m-d H:i:s');
            }
            
            // Ensure slug is set
            if (!isset($item['slug']) || empty($item['slug'])) {
                $item['slug'] = 'article-' . $item['id'];
                error_log('Added missing slug: ' . $item['slug']);
            }
            
            // Ensure title is set
            if (!isset($item['title']) || empty($item['title'])) {
                $item['title'] = 'Untitled Article #' . $item['id'];
                error_log('Added missing title: ' . $item['title']);
            }
        }
        
        // Let's sleep for a tiny consistent amount to stabilize responses
        usleep(50000); // 50ms delay
    }
    
    // Add cache control headers
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Return the results
    echo json_encode($results);
} catch (PDOException $e) {
    // Log error but don't expose details to frontend
    error_log('Search error: ' . $e->getMessage());
    echo json_encode(['error' => 'Search failed']);
} 