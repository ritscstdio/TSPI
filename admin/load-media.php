<?php
require_once '../includes/config.php';
require_admin_login();

// Get all media images
$stmt = $pdo->query("SELECT * FROM media WHERE mime_type LIKE 'image/%' ORDER BY uploaded_at DESC");
$media_images = $stmt->fetchAll();

$response = [
    'success' => true,
    'media' => []
];

foreach ($media_images as $img) {
    $url = SITE_URL . '/' . $img['file_path'];
    $response['media'][] = [
        'id' => $img['id'],
        'url' => $url,
        'mime_type' => $img['mime_type'],
        'uploaded_at' => $img['uploaded_at']
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit; 