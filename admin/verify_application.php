<?php
require_once '../includes/config.php';
require_admin_login();

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($id && in_array($action, ['approved', 'rejected'], true)) {
    $stmt = $pdo->prepare("UPDATE members_information SET status = ? WHERE id = ?");
    $stmt->execute([$action, $id]);
    $_SESSION['message'] = "Application " . ucfirst($action) . " successfully.";
}
// Redirect back to admin list
redirect('applications.php');
?> 