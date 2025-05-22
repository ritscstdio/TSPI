<?php
$page_title = "Edit Media";
$body_class = "admin-edit-media-page";
require_once '../includes/config.php';
require_admin_login();

$id = $_GET['id'] ?? null;
if (!$id) redirect('/admin/media.php');

$stmt = $pdo->prepare("SELECT * FROM media WHERE id = ?");
$stmt->execute([$id]);
$media = $stmt->fetch();
if (!$media) redirect('/admin/media.php');

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $file_path = UPLOADS_DIR . '/media/' . basename($media['file_path']);
    if (file_exists($file_path)) unlink($file_path);
    $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['message'] = "Media deleted.";
    redirect('/admin/media.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - TSPI CMS</title>
    <link rel="icon" type="image/png" href="../src/assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            <div class="dashboard-container">
                <h1>Edit Media</h1>
                <div class="media-preview">
                    <?php if (strpos($media['mime_type'], 'image/') === 0): ?>
                        <img src="<?php echo SITE_URL . '/' . $media['file_path']; ?>" alt="" style="max-width:200px; margin-bottom:1rem;">
                    <?php endif; ?>
                    <p>File: <?php echo basename($media['file_path']); ?></p>
                    <p>Type: <?php echo $media['mime_type']; ?></p>
                    <p>Uploaded: <?php echo date('M j, Y', strtotime($media['uploaded_at'])); ?></p>
                </div>
                <a href="edit-media.php?id=<?php echo $media['id']; ?>&action=delete" class="btn btn-danger delete-btn" data-confirm="Delete this media?">Delete Media</a>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 