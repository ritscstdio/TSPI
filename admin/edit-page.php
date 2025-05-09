<?php
$page_title = "Edit Page";
$body_class = "admin-edit-page-page";
require_once '../includes/config.php';
require_login();

$current_user = get_logged_in_user();

// Get page by ID
$page_id = $_GET['id'] ?? null;
if (!$page_id) {
    redirect('/admin/pages.php');
}
$stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$page_id]);
$page_item = $stmt->fetch();
if (!$page_item) {
    $_SESSION['message'] = "Page not found.";
    redirect('/admin/pages.php');
}

// Prefill values
$title = $page_item['title'];
$content = $page_item['content'];
$status = $page_item['status'];
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    if (!$title) $errors[] = "Title is required.";
    if (!$content) $errors[] = "Content is required.";
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE pages SET title = ?, content = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $content, $status, $page_id]);
        $_SESSION['message'] = "Page updated successfully.";
        redirect('/admin/pages.php');
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
                <div class="page-header">
                    <h1>Edit Page</h1>
                    <a href="pages.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Pages</a>
                </div>
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo $e; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="" method="post" class="form">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo sanitize($title); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($content); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Page</button>
                </form>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 