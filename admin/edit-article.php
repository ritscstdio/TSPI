<?php
$page_title = "Edit Article";
$body_class = "admin-edit-article-page";
require_once '../includes/config.php';
require_login();

$current_user = get_logged_in_user();

// Get article ID
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$article_id) {
    redirect('/admin/articles.php');
}
// Fetch article data
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();
if (!$article) {
    $_SESSION['message'] = "Article not found.";
    redirect('/admin/articles.php');
}

// Fetch categories and selected categories
$all_categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$stmt = $pdo->prepare("SELECT category_id FROM article_categories WHERE article_id = ?");
$stmt->execute([$article_id]);
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
        // Update article
        $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, excerpt = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $content, $excerpt, $status, $article_id]);
        
        // Update categories
        $stmt = $pdo->prepare("DELETE FROM article_categories WHERE article_id = ?");
        $stmt->execute([$article_id]);
        if (!empty($categories_input)) {
            $values = [];
            $placeholders = [];
            foreach ($categories_input as $cat_id) {
                $placeholders[] = "(?, ?)";
                $values[] = $article_id;
                $values[] = (int)$cat_id;
            }
            $query = "INSERT INTO article_categories (article_id, category_id) VALUES " . implode(', ', $placeholders);
            $stmt = $pdo->prepare($query);
            $stmt->execute($values);
        }
        
        $_SESSION['message'] = "Article updated successfully.";
        redirect('/admin/articles.php');
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
                    <h1>Edit Article</h1>
                    <a href="articles.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Articles</a>
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
                        <input type="text" id="title" name="title" value="<?php echo sanitize($article['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
                        <textarea id="excerpt" name="excerpt" rows="3"><?php echo sanitize($article['excerpt']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($article['content']); ?></textarea>
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
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo $article['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $article['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $article['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Article</button>
                </form>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 