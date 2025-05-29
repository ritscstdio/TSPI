<?php
$page_title = "Edit Content";
$body_class = "admin-edit-content-page";
require_once '../includes/config.php';
require_admin_login();

$current_user = get_admin_user();

// Get content ID
$content_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$content_id) {
    redirect('/admin/content.php');
}
// Fetch content data
$stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
$stmt->execute([$content_id]);
$content = $stmt->fetch();
if (!$content) {
    $_SESSION['message'] = "Content not found.";
    redirect('/admin/content.php');
}

// Fetch categories and selected categories
$all_categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$stmt = $pdo->prepare("SELECT category_id FROM content_categories WHERE content_id = ?");
$stmt->execute([$content_id]);
$selected_categories = array_column($stmt->fetchAll(), 'category_id');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $categories_input = $_POST['categories'] ?? [];
    $errors = [];
    if (!$title) $errors[] = "Title is required.";
    if (!$content) $errors[] = "Content is required.";
    if (empty($errors)) {
        // Update content
        $stmt = $pdo->prepare("UPDATE content SET title = ?, content = ?, excerpt = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $content, $excerpt, $status, $content_id]);
        
        // Update categories
        $stmt = $pdo->prepare("DELETE FROM content_categories WHERE content_id = ?");
        $stmt->execute([$content_id]);
        if (!empty($categories_input)) {
            $values = [];
            $placeholders = [];
            foreach ($categories_input as $cat_id) {
                $placeholders[] = "(?, ?)";
                $values[] = $content_id;
                $values[] = (int)$cat_id;
            }
            $query = "INSERT INTO content_categories (content_id, category_id) VALUES " . implode(', ', $placeholders);
            $stmt = $pdo->prepare($query);
            $stmt->execute($values);
        }
        
        $_SESSION['message'] = "Content updated successfully.";
        redirect('/admin/content.php');
    }
}

// Prepare thumbnail URL for preview (handle absolute or relative paths)
$thumbRaw = $content['thumbnail'] ?? '';
if ($thumbRaw) {
    if (preg_match('#^https?://#i', $thumbRaw)) {
        // For display purposes only - keep full URL
        $thumbnailUrl = htmlspecialchars($thumbRaw);
    } else {
        $thumbnailUrl = SITE_URL . '/' . htmlspecialchars($thumbRaw);
    }
} else {
    $thumbnailUrl = '';
}

// Fix for thumbnail update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thumbnail_select'])) {
    $new_thumbnail = $_POST['thumbnail_select'] ?? '';
    
    // If it's a full URL, extract just the relative path for storage
    if (preg_match('#^https?://[^/]+/(.+)$#i', $new_thumbnail, $matches)) {
        $relative_path = $matches[1];
        
        // Update the content's thumbnail with just the relative path
        $update_stmt = $pdo->prepare("UPDATE content SET thumbnail = ? WHERE id = ?");
        $update_stmt->execute([$relative_path, $content_id]);
    }
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
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            <div class="dashboard-container">
                <div class="page-header">
                    <h1>Edit Content</h1>
                    <a href="content.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Content</a>
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
                <div class="admin-form-container">
                    <form action="" method="post" enctype="multipart/form-data" class="admin-form">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" value="<?php echo sanitize($content['title']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="thumbnail_select">Thumbnail Image</label>
                            <div class="thumbnail-controls">
                                <button type="button" id="thumbnail-select-btn" class="btn btn-secondary">Choose a thumbnail</button>
                                <?php
                                // Extract the relative path for storing in the form value
                                $thumbnail_path = $thumbRaw;
                                if (preg_match('#^https?://[^/]+/(.+)$#i', $thumbnailUrl, $matches)) {
                                    $thumbnail_path = $matches[1];
                                }
                                ?>
                                <input type="hidden" id="thumbnail_select" name="thumbnail_select" value="<?php echo htmlspecialchars($thumbnail_path); ?>">
                            </div>
                            <div class="thumbnail-preview-container" style="margin-top: 1rem;">
                                <img id="thumbnail-preview" src="<?php echo $thumbnailUrl ? $thumbnailUrl : '../assets/placeholder-image.jpg'; ?>" alt="Thumbnail Preview" style="max-width: 300px; border-radius: 4px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="excerpt">Excerpt (optional)</label>
                            <textarea id="excerpt" name="excerpt" rows="3"><?php echo sanitize($content['excerpt']); ?></textarea>
                            <p class="form-hint">A short summary of the content. If left empty, an excerpt will be generated from the content.</p>
                        </div>

                        <div class="form-group">
                            <label for="content-content-editor">Content</label>
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
                            <div class="editor-content" id="content-content-editor" contenteditable="true"><?php echo htmlspecialchars($content['content']); ?></div>
                            <input type="hidden" id="content-content" name="content" value="<?php echo htmlspecialchars($content['content']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Categories</label>
                            <div class="checkbox-group-container">
                                <?php foreach ($all_categories as $cat): ?>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="category-<?php echo $cat['id']; ?>" name="categories[]" value="<?php echo $cat['id']; ?>" <?php echo in_array($cat['id'], $selected_categories) ? 'checked' : ''; ?>>
                                        <label for="category-<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group tag-input-container">
                            <label for="tag-input">Tags</label>
                            <input type="text" id="tag-input" placeholder="Add tags... (press Enter or comma after each tag)">
                            <div id="tag-container" class="tag-container"></div>
                            <input type="hidden" id="tags" name="tags" value="<?php echo isset($_POST['tags']) ? sanitize($_POST['tags']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="draft" <?php echo $content['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo $content['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="archived" <?php echo $content['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Update Content</button>
                            <a href="content.php" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <?php include 'includes/media-modal.php'; ?>

    <script src="../assets/js/admin.js"></script>
</body>
</html> 