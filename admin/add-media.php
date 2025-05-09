<?php
$page_title = "Upload Media";
$body_class = "admin-upload-media-page";
require_once '../includes/config.php';
require_login();
require_role(['admin','editor']);

$current_user = get_logged_in_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload failed with error code: " . ($_FILES['media_file']['error'] ?? 'no file');
    } else {
        $file = $_FILES['media_file'];
        $upload_dir = UPLOADS_DIR . '/media/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = uniqid() . '_' . basename($file['name']);
        $target_file = $upload_dir . $filename;
        $mime_type = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg','image/png','image/gif'];
        if (!in_array($mime_type,$allowed)) {
            $errors[] = "Invalid file type.";
        } elseif ($file['size'] > 5*1024*1024) {
            $errors[] = "File too large.";
        } elseif (!move_uploaded_file($file['tmp_name'],$target_file)) {
            $errors[] = "Failed to move uploaded file.";
        } else {
            $path = 'uploads/media/'.$filename;
            $stmt = $pdo->prepare("INSERT INTO media (file_path, mime_type, uploaded_by) VALUES (?,?,?)");
            $stmt->execute([$path,$mime_type,$current_user['id']]);
            $_SESSION['message'] = "Media uploaded.";
            redirect('/admin/media.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - TSPI CMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            <div class="dashboard-container">
                <h1>Upload Media</h1>
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <ul><?php foreach($errors as $e): ?><li><?php echo sanitize($e); ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select File</label>
                        <input type="file" name="media_file" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 