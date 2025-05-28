<?php
$page_title = "Edit Category";
$body_class = "admin-edit-category-page";
require_once '../includes/config.php';
require_admin_login();
require_admin_role(['admin','moderator']);

$category_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
if (!$category_id) {
    redirect('/admin/categories.php');
}
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();
if (!$category) {
    $_SESSION['message'] = "Category not found.";
    redirect('/admin/categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $errors = [];
    if (!$name) $errors[] = "Name is required.";
    if (!$slug) {
        $slug = generate_slug($name);
    }
    // ensure unique slug
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?");
    $stmt->execute([$slug, $category_id]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Slug must be unique.";
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $category_id]);
        $_SESSION['message'] = "Category updated successfully.";
        redirect('/admin/categories.php');
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
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            <div class="dashboard-container">
                <div class="page-header">
                    <h1><i class="fas fa-folder"></i> Edit Category</h1>
                    <a href="categories.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Categories</a>
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
                    <form action="" method="post" class="admin-form">
                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" id="name" name="name" value="<?php echo sanitize($category['name']); ?>" required>
                            <small>The name is how it appears on your site</small>
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug (Optional)</label>
                            <input type="text" id="slug" name="slug" value="<?php echo sanitize($category['slug']); ?>">
                            <small>The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</small>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Category</button>
                            <a href="categories.php" class="btn btn-light"><i class="fas fa-times"></i> Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 