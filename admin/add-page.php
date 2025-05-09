<?php
$page_title = "Add Page";
$body_class = "admin-add-page-page";
require_once '../includes/config.php';
require_login();
require_role(['admin','editor']);

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
                        <div class="editor-toolbar">
                            <button type="button" class="toolbar-btn" data-command="bold" title="Bold"><i class="fas fa-bold"></i></button>
                            <button type="button" class="toolbar-btn" data-command="italic" title="Italic"><i class="fas fa-italic"></i></button>
                            <button type="button" class="toolbar-btn" data-command="underline" title="Underline"><i class="fas fa-underline"></i></button>
                            <button type="button" class="toolbar-btn" data-command="strikeThrough" title="Strike Through"><i class="fas fa-strikethrough"></i></button>
                            <button type="button" class="toolbar-btn" data-command="createLink" title="Insert Link"><i class="fas fa-link"></i></button>
                            <button type="button" class="toolbar-btn" data-command="unlink" title="Remove Link"><i class="fas fa-unlink"></i></button>
                            <button type="button" class="toolbar-btn" data-command="insertImage" title="Insert Image"><i class="fas fa-image"></i></button>
                            <button type="button" class="toolbar-btn" data-command="resizeImage" title="Resize Image"><i class="fas fa-expand-alt"></i></button>
                            <button type="button" class="toolbar-btn" data-command="insertVideo" title="Insert Video"><i class="fas fa-video"></i></button>
                            <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="H1" title="Heading 1">H1</button>
                            <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="H2" title="Heading 2">H2</button>
                            <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="H3" title="Heading 3">H3</button>
                            <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="P" title="Paragraph">P</button>
                            <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List"><i class="fas fa-list-ul"></i></button>
                            <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List"><i class="fas fa-list-ol"></i></button>
                            <button type="button" class="toolbar-btn" data-command="justifyLeft" title="Align Left"><i class="fas fa-align-left"></i></button>
                            <button type="button" class="toolbar-btn" data-command="justifyCenter" title="Align Center"><i class="fas fa-align-center"></i></button>
                            <button type="button" class="toolbar-btn" data-command="justifyRight" title="Align Right"><i class="fas fa-align-right"></i></button>
                        </div>
                        <div class="editor-content" id="article-content-editor" contenteditable="true"><?php echo isset($_POST['content']) ? $_POST['content'] : ''; ?></div>
                        <input type="hidden" id="article-content" name="content" value="<?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?>">
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

    <?php include 'includes/media-modal.php'; ?>

    <script src="../assets/js/admin.js"></script>
</body>
</html> 