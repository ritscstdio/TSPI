<?php
$page_title = "Pages";
$body_class = "admin-pages-page";
require_once '../includes/config.php';
require_login();
require_role(['admin','editor']);

// Delete page if requested
if (isset($_GET['delete'])) {
    $page_id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$page_id]);
    $_SESSION['message'] = "Page deleted successfully.";
    redirect('/admin/pages.php');
}

// Pagination
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$items_per_page = ITEMS_PER_PAGE;
$offset = ($current_page - 1) * $items_per_page;

// Get total pages count
$stmt = $pdo->query("SELECT COUNT(*) FROM pages");
$total_count = $stmt->fetchColumn();
$total_pages = ceil($total_count / $items_per_page);

// Fetch pages for current page
$stmt = $pdo->prepare("SELECT p.*, u.name as author_name FROM pages p JOIN users u ON p.author_id = u.id ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$items_per_page, $offset]);
$pages = $stmt->fetchAll();
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
                    <h1>Pages</h1>
                    <a href="add-page.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Page</a>
                </div>
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
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
                                <?php if (empty($pages)): ?>
                                    <tr><td colspan="5">No pages found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($pages as $page_item): ?>
                                        <tr>
                                            <td><?php echo sanitize($page_item['title']); ?></td>
                                            <td><?php echo sanitize($page_item['author_name']); ?></td>
                                            <td><span class="status-badge status-<?php echo $page_item['status']; ?>"><?php echo ucfirst($page_item['status']); ?></span></td>
                                            <td><?php echo date('M j, Y', strtotime($page_item['created_at'])); ?></td>
                                            <td class="actions">
                                                <a href="edit-page.php?id=<?php echo $page_item['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="../page.php?slug=<?php echo $page_item['slug']; ?>" target="_blank" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="pages.php?delete=<?php echo $page_item['id']; ?>" class="btn-icon delete-btn" title="Delete" data-confirm="Are you sure you want to delete this page?"><i class="fas fa-trash"></i></a>
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