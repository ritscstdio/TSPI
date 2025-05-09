<?php
$page_title = "Comments";
$body_class = "admin-comments-page";
require_once '../includes/config.php';
require_login();
require_role(['admin','editor','comment_moderator']);

// Fetch all comments with article titles
$stmt = $pdo->query(
    "SELECT c.*, a.title AS article_title, a.slug AS article_slug FROM comments c JOIN articles a ON c.article_id = a.id ORDER BY posted_at DESC"
);
$comments = $stmt->fetchAll();
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
                    <h1>Comments</h1>
                </div>
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                <div class="dashboard-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Author</th>
                                    <th>Comment</th>
                                    <th>Article</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($comments)): ?>
                                    <tr><td colspan="6">No comments found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <tr>
                                            <td><?php echo sanitize($comment['author_name']); ?></td>
                                            <td title="<?php echo sanitize($comment['content']); ?>"><?php echo nl2br(sanitize(substr($comment['content'], 0, 50))) . '...'; ?></td>
                                            <td><?php echo sanitize($comment['article_title']); ?></td>
                                            <td><span class="status-badge status-<?php echo $comment['status']; ?>"><?php echo ucfirst($comment['status']); ?></span></td>
                                            <td><?php echo date('M j, Y', strtotime($comment['posted_at'])); ?></td>
                                            <td class="actions">
                                                <?php if ($comment['status'] === 'pending'): ?>
                                                    <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=approve" class="btn-icon" title="Approve"><i class="fas fa-check"></i></a>
                                                    <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=deny" class="btn-icon" title="Deny"><i class="fas fa-ban"></i></a>
                                                <?php elseif ($comment['status'] === 'approved'): ?>
                                                    <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=hide" class="btn-icon" title="Hide"><i class="fas fa-eye-slash"></i></a>
                                                <?php elseif ($comment['status'] === 'spam'): ?>
                                                    <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=approve" class="btn-icon" title="Approve"><i class="fas fa-check"></i></a>
                                                <?php endif; ?>
                                                <a href="../article.php?slug=<?php echo $comment['article_slug']; ?>" target="_blank" class="btn-icon" title="View Article"><i class="fas fa-external-link-alt"></i></a>
                                                <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=delete" class="btn-icon delete-btn" title="Delete" data-confirm="Are you sure you want to delete this comment?"><i class="fas fa-trash"></i></a>
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