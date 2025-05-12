<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (!$q) {
    echo json_encode([]);
    exit;
}

// Search in title or content
$stmt = $pdo->prepare(
    "SELECT title, slug FROM articles WHERE status = 'published' AND (title LIKE :q OR content LIKE :q) ORDER BY published_at DESC LIMIT 5"
);
$stmt->execute([':q' => "%$q%"]);
$results = $stmt->fetchAll();

echo json_encode($results); 