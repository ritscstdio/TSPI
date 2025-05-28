<?php
$page_title = "Add Category";
$body_class = "admin-add-category-page";
require_once '../includes/config.php';
require_admin_login();
require_admin_role(['admin','moderator']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $errors = [];
    if (!$name) $errors[] = "Name is required.";
    if (!$slug) {
        // auto-generate slug
        $slug = generate_slug($name);
    }
    // ensure unique slug
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Slug must be unique.";
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        $_SESSION['message'] = "Category added successfully.";
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
                    <h1><i class="fas fa-folder-plus"></i> Add Category</h1>
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
                            <input type="text" id="name" name="name" value="<?php echo sanitize($name ?? ''); ?>" required>
                            <small>The name is how it appears on your site</small>
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug (Optional)</label>
                            <input type="text" id="slug" name="slug" value="<?php echo sanitize($slug ?? ''); ?>">
                            <small>The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens. Leave blank for automatic generation.</small>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Category</button>
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