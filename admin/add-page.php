<?php
$page_title = "Add Page";
$body_class = "admin-add-page-page";
require_once '../includes/config.php';
require_login();

$current_user = get_logged_in_user();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $errors = [];
    if (!$title) $errors[] = "Title is required.";
    if (!$content) $errors[] = "Content is required.";
    if (empty($errors)) {
        $slug = generate_slug($title);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pages WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() > 0) {
            $slug .= '-' . uniqid();
        }
        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, status, author_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $status, $current_user['id']]);
        $_SESSION['message'] = "Page created successfully.";
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
                    <h1>Add Page</h1>
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
                        <input type="text" id="title" name="title" value="<?php echo sanitize($title ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo (isset($status) && $status=='draft')?'selected':''; ?>>Draft</option>
                            <option value="published" <?php echo (isset($status) && $status=='published')?'selected':''; ?>>Published</option>
                            <option value="archived" <?php echo (isset($status) && $status=='archived')?'selected':''; ?>>Archived</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Page</button>
                </form>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 