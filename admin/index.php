<?php
$page_title = "Admin Dashboard";
$body_class = "admin-dashboard-page";
require_once '../includes/config.php';
require_admin_login();

// Get stats
$stmt = $pdo->query("SELECT 
                    (SELECT COUNT(*) FROM content) as total_contents,
                    (SELECT COUNT(*) FROM content WHERE status = 'published') as published_contents,
                    (SELECT COUNT(*) FROM content WHERE status = 'draft') as draft_contents,
                    (SELECT COUNT(*) FROM content WHERE status = 'archived') as archived_contents,
                    (SELECT COUNT(*) FROM comments) as total_comments,
                    (SELECT COUNT(*) FROM comments WHERE status = 'pending') as pending_comments,
                    (SELECT COUNT(*) FROM administrators) as total_users");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Recent contents
$stmt = $pdo->query("SELECT a.*, u.name as author_name, u.email as author_email, u.role as author_role 
                      FROM content a 
                      JOIN administrators u ON a.author_id = u.id 
                      ORDER BY a.published_at DESC
                      LIMIT 5");
$recent_contents = $stmt->fetchAll();

// Recent comments
$stmt = $pdo->query("SELECT c.*, a.title as content_title, a.slug as content_slug 
                      FROM comments c 
                      JOIN content a ON c.content_id = a.id 
                      ORDER BY c.posted_at DESC 
                      LIMIT 5");
$recent_comments = $stmt->fetchAll();
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
                <h1>Dashboard</h1>
                
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="stat-boxes">
                    <div class="stat-box" data-link="content.php">
                        <div class="stat-content">
                            <i class="fas fa-file-alt stat-icon"></i>
                            <h3>Total Content</h3>
                            <p class="stat-number"><?php echo $stats['total_contents']; ?></p>
                        </div>
                    </div>
                    <div class="stat-box" data-link="content.php?status_filter=published">
                        <div class="stat-content">
                            <i class="fas fa-check-circle stat-icon"></i>
                            <h3>Published Content</h3>
                            <p class="stat-number"><?php echo $stats['published_contents']; ?></p>
                        </div>
                    </div>
                    <div class="stat-box" data-link="content.php?status_filter=draft">
                        <div class="stat-content">
                            <i class="fas fa-pencil-alt stat-icon"></i>
                            <h3>Draft Content</h3>
                            <p class="stat-number"><?php echo $stats['draft_contents']; ?></p>
                        </div>
                    </div>
                    <div class="stat-box" data-link="content.php?status_filter=archived">
                        <div class="stat-content">
                            <i class="fas fa-archive stat-icon"></i>
                            <h3>Archived Content</h3>
                            <p class="stat-number"><?php echo $stats['archived_contents']; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-box" data-link="comments.php?status_filter=pending">
                        <div class="stat-content">
                            <i class="fas fa-comment-dots stat-icon"></i>
                            <h3>Pending Comments</h3>
                            <p class="stat-number"><?php echo $stats['pending_comments']; ?></p>
                        </div>
                    </div>

                    <div class="stat-box" data-link="comments.php">
                        <div class="stat-content">
                            <i class="fas fa-comments stat-icon"></i>
                            <h3>Total Comments</h3>
                            <p class="stat-number"><?php echo $stats['total_comments']; ?></p>
                        </div>
                    </div>
                </div>
                
                                <div class="dashboard-sections">                    <div class="dashboard-section recent-contents">                        <div class="section-header">                            <h2>Recent Content</h2>                            <a href="content.php" class="view-all">View All</a>                        </div>
                        
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
                                    <?php if (empty($recent_contents)): ?>
                                        <tr>
                                            <td colspan="5">No contents found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_contents as $content): ?>
                                            <tr>
                                                <td><?php echo sanitize($content['title']); ?></td>
                                                <td class="content-author-name" 
                                                    data-name="<?php echo sanitize($content['author_name']); ?>" 
                                                    data-email="<?php echo sanitize($content['author_email'] ?? 'N/A'); ?>" 
                                                    data-role="<?php echo sanitize(ucfirst($content['author_role'] ?? 'N/A')); ?>"
                                                    style="cursor: pointer; text-decoration: underline; color: var(--primary-blue);">
                                                    <?php echo sanitize($content['author_name']); ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $content['status']; ?>">
                                                        <?php echo ucfirst($content['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($content['published_at'])); ?></td>
                                                <td class="actions">
                                                    <a href="edit-content.php?id=<?php echo $content['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                    <a href="../content.php?slug=<?php echo $content['slug']; ?>" target="_blank" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="dashboard-section recent-comments">
                        <div class="section-header">
                            <h2>Recent Comments</h2>
                            <a href="comments.php" class="view-all">View All</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Author</th>
                                        <th>Comment</th>
                                        <th>content</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_comments)): ?>
                                        <tr>
                                            <td colspan="6">No comments found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_comments as $comment): ?>
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
                                                <td><?php echo sanitize($comment['content_title']); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $comment['status']; ?>">
                                                        <?php echo ucfirst($comment['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($comment['posted_at'])); ?></td>
                                                <td class="actions">
                                                    <a href="edit-comment.php?id=<?php echo $comment['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                    <?php if ($comment['status'] === 'pending'): ?>
                                                        <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=approve" class="btn-icon" title="Approve"><i class="fas fa-check"></i></a>
                                                        <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=deny" class="btn-icon" title="Deny"><i class="fas fa-ban"></i></a>
                                                    <?php elseif ($comment['status'] === 'approved'): ?>
                                                        <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=hide" class="btn-icon" title="Hide"><i class="fas fa-eye-slash"></i></a>
                                                    <?php elseif ($comment['status'] === 'spam'): ?>
                                                        <a href="comment-action.php?id=<?php echo $comment['id']; ?>&action=approve" class="btn-icon" title="Approve"><i class="fas fa-check"></i></a>
                                                    <?php endif; ?>
                                                    <a href="../content.php?slug=<?php echo sanitize($comment['content_slug']); ?>" target="_blank" class="btn-icon" title="View content"><i class="fas fa-external-link-alt"></i></a>
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
            </div>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
