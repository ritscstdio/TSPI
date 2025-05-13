<?php
require_once 'config.php';

// Prevent direct access
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header('HTTP/1.0 403 Forbidden');
    echo 'Access forbidden.';
    exit;
}

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode(['status' => 'error', 'message' => 'No search query provided']);
    exit;
}

// Search in articles
try {
    // Prepare search query - search in title and content
    $stmt = $pdo->prepare("
        SELECT id, title, content, author_id, published_at, slug 
        FROM articles 
        WHERE status = 'published' 
        AND (title LIKE ? OR content LIKE ?)
        ORDER BY published_at DESC
        LIMIT 10
    ");
    
    $searchParam = "%{$query}%";
    $stmt->execute([$searchParam, $searchParam]);
    $results = $stmt->fetchAll();
    
    // Get author information for each article
    $articles = [];
    foreach ($results as $article) {
        // Get author name
        $authorStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $authorStmt->execute([$article['author_id']]);
        $author = $authorStmt->fetch();
        
        $articles[] = [
            'id' => $article['id'],
            'title' => $article['title'],
            'author' => $author ? $author['name'] : 'Unknown',
            'published_at' => $article['published_at'],
            'slug' => $article['slug']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'count' => count($articles),
        'results' => $articles
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} 