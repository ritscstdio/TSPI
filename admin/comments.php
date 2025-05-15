<?php
$page_title = "Comments";
$body_class = "admin-comments-page";
require_once '../includes/config.php';
require_login();
require_role(['admin','editor','comment_moderator']);

// Define status filter options
$status_filter_options = [
    '' => 'All Statuses',
    'approved' => 'Approved',
    'pending' => 'Pending',
    'spam' => 'Spam'
];
$filter_status = isset($_GET['status_filter']) && array_key_exists($_GET['status_filter'], $status_filter_options)
                    ? $_GET['status_filter']
                    : '';

// Build WHERE clause for status
$where_sql = "";
$params = [];
if ($filter_status) {
    $where_sql = " WHERE c.status = ?";
    $params[] = $filter_status;
}

// Fetch all comments with article titles
$sql = "SELECT c.*, a.title AS article_title, a.slug AS article_slug 
        FROM comments c 
        JOIN articles a ON c.article_id = a.id 
        $where_sql 
        ORDER BY c.pinned DESC, c.vote_score DESC, c.posted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comments = $stmt->fetchAll();
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
                    <h1>Comments</h1>
                </div>
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="get" class="filter-form">
                    <label for="status-filter">Filter by Status:</label>
                    <select id="status-filter" name="status_filter" onchange="this.form.submit()">
                        <?php foreach ($status_filter_options as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $filter_status == $key ? 'selected' : ''; ?>><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button type="submit" class="btn btn-light">Filter</button></noscript>
                </form>

                <div class="dashboard-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Author</th>
                                    <th>Comment</th>
                                    <th>Article</th>
                                    <th>Votes</th>
                                    <th>Pinned</th>
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
                                            <td class="comment-author-name" 
                                                data-name="<?php echo sanitize($comment['author_name']); ?>" 
                                                data-email="<?php echo sanitize($comment['author_email'] ?? 'N/A'); ?>" 
                                                data-website="<?php echo sanitize($comment['author_website'] ?? 'N/A'); ?>"
                                                style="cursor: pointer; text-decoration: underline; color: var(--primary-blue);">
                                                <?php echo sanitize($comment['author_name']); ?>
                                            </td>
                                            <td class="comment-text-preview" data-full-comment="<?php echo nl2br(sanitize($comment['content'])); ?>">
                                                <?php echo nl2br(sanitize(substr($comment['content'], 0, 50))) . (strlen($comment['content']) > 50 ? '...' : ''); ?>
                                            </td>
                                            <td><?php echo sanitize($comment['article_title']); ?></td>
                                            <td><?php echo $comment['vote_score']; ?></td>
                                            <td><?php echo $comment['pinned'] ? 'Yes' : 'No'; ?></td>
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
                                                <?php if ($comment['pinned']): ?>
                                                    <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=unpin" class="btn-icon" title="Unpin"><i class="fas fa-thumbtack fa-rotate-90"></i></a>
                                                <?php else: ?>
                                                    <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=pin" class="btn-icon" title="Pin"><i class="fas fa-thumbtack"></i></a>
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
    <?php include 'includes/footer.php'; ?>
</body>
</html> 