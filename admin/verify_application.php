<?php
require_once '../includes/config.php';
// Only allow admins
if (!function_exists('is_admin') || !is_admin()) {
    redirect('/');
}

$id = $_GET['id'] ?? null;
action:
$action = $_GET['action'] ?? null;

if ($id && in_array($action, ['approved', 'rejected'], true)) {
    $stmt = $pdo->prepare("UPDATE members_information SET status = ? WHERE id = ?");
    $stmt->execute([$action, $id]);
}
// Redirect back to admin list
redirect('applications.php');
?> 