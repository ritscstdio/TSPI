<?php
require_once '../includes/config.php';
require_login();
require_role(['admin','editor']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['media_file'])) {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$file = $_FILES['media_file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload error code: ' . $file['error']]);
    exit;
}

$upload_dir = UPLOADS_DIR . '/media/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$filename = uniqid() . '_' . basename($file['name']);
$target_file = $upload_dir . $filename;

$mime_type = mime_content_type($file['tmp_name']);
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mime_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large']);
    exit;
}

if (!move_uploaded_file($file['tmp_name'], $target_file)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to move uploaded file']);
    exit;
}

$file_path = 'uploads/media/' . $filename;
// Insert into media table
$stmt = $pdo->prepare("INSERT INTO media (file_path, mime_type, uploaded_by) VALUES (?, ?, ?)");
$stmt->execute([$file_path, $mime_type, get_logged_in_user()['id']]);

$url = SITE_URL . '/' . $file_path;

echo json_encode(['file_path' => $file_path, 'url' => $url]);
exit; 