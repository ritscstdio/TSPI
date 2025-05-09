<?php
$page_title = "Categories";
$body_class = "admin-categories-page";
require_once '../includes/config.php';
require_login();

// Delete category if requested
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $_SESSION['message'] = "Category deleted successfully.";
    redirect('/admin/categories.php');
}

// Fetch categories
$stmt = $pdo->query("SELECT c.*, COUNT(ac.article_id) AS article_count FROM categories c LEFT JOIN article_categories ac ON c.id = ac.category_id GROUP BY c.id ORDER BY c.name");
$categories = $stmt->fetchAll();
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
                    <h1>Categories</h1>
                    <a href="add-category.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</a>
                </div>
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                <div class="dashboard-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Articles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr><td colspan="4">No categories found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td><?php echo sanitize($cat['name']); ?></td>
                                            <td><?php echo sanitize($cat['slug']); ?></td>
                                            <td><?php echo $cat['article_count']; ?></td>
                                            <td class="actions">
                                                <a href="edit-category.php?id=<?php echo $cat['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="btn-icon delete-btn" title="Delete" data-confirm="Are you sure you want to delete this category? This will unassign it from all articles."><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 