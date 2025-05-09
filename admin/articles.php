<?php
$page_title = "Articles";
$body_class = "admin-articles-page";
require_once '../includes/config.php';
require_login();
require_role(['admin','editor']);

// Delete article if requested
if (isset($_GET['delete'])) {
    $article_id = (int) $_GET['delete'];
    
    // Delete article and all related records
    $pdo->beginTransaction();
    
    try {
        // Delete article tags
        $stmt = $pdo->prepare("DELETE FROM article_tags WHERE article_id = ?");
        $stmt->execute([$article_id]);
        
        // Delete article categories
        $stmt = $pdo->prepare("DELETE FROM article_categories WHERE article_id = ?");
        $stmt->execute([$article_id]);
        
        // Delete comments
        $stmt = $pdo->prepare("DELETE FROM comments WHERE article_id = ?");
        $stmt->execute([$article_id]);
        
        // Delete the article
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);
        
        $pdo->commit();
        $_SESSION['message'] = "Article deleted successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }
    
    redirect('/admin/articles.php');
}

// Pagination
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$items_per_page = ITEMS_PER_PAGE;
$offset = ($current_page - 1) * $items_per_page;

// Fetch categories for filter dropdown
$categories_list = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$filter_category = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get total articles count
if ($filter_category) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles a JOIN article_categories ac ON a.id = ac.article_id WHERE ac.category_id = ?");
    $stmt->execute([$filter_category]);
} else {
    $stmt = $pdo->query("SELECT COUNT(*) FROM articles");
}
$total_articles = $stmt->fetchColumn();
$total_pages = ceil($total_articles / $items_per_page);

// Get articles for current page
if ($filter_category) {
    $stmt = $pdo->prepare("SELECT a.*, u.name as author_name FROM articles a JOIN users u ON a.author_id = u.id JOIN article_categories ac ON a.id = ac.article_id WHERE ac.category_id = ? ORDER BY a.published_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$filter_category, $items_per_page, $offset]);
} else {
    $stmt = $pdo->prepare("SELECT a.*, u.name as author_name FROM articles a JOIN users u ON a.author_id = u.id ORDER BY a.published_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$items_per_page, $offset]);
}
$articles = $stmt->fetchAll();

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
                    <h1>Articles</h1>
                    <a href="add-article.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Article</a>
                </div>
                
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <!-- Category Filter Form -->
                <form method="get" class="filter-form" style="margin-bottom: 1rem;">
                    <label for="category-filter">Filter by Category:</label>
                    <select id="category-filter" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories_list as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>><?php echo sanitize($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-light">Filter</button>
                </form>
                
                <div class="dashboard-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($articles)): ?>
                                    <tr>
                                        <td colspan="5">No articles found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($articles as $article): ?>
                                        <tr>
                                            <td><?php echo sanitize($article['title']); ?></td>
                                            <td><?php echo sanitize($article['author_name']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $article['status']; ?>">
                                                    <?php echo ucfirst($article['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($article['published_at'])); ?></td>
                                            <td class="actions">
                                                <a href="edit-article.php?id=<?php echo $article['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="../article.php?slug=<?php echo $article['slug']; ?>" target="_blank" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="articles.php?delete=<?php echo $article['id']; ?>" class="btn-icon delete-btn" title="Delete" data-confirm="Are you sure you want to delete this article?"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>" class="pagination-item"><i class="fas fa-chevron-left"></i></a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="pagination-item <?php echo $i === $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>" class="pagination-item"><i class="fas fa-chevron-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>
