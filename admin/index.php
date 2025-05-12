<?php
$page_title = "Admin Dashboard";
$body_class = "admin-dashboard-page";
require_once '../includes/config.php';
require_login();
require_role(['admin', 'editor', 'comment_moderator']);

// Get dashboard stats
$stats = [];

// Total articles
$stmt = $pdo->query("SELECT COUNT(*) FROM articles");
$stats['total_articles'] = $stmt->fetchColumn();

// Published articles
$stmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'");
$stats['published_articles'] = $stmt->fetchColumn();

// Draft articles
$stmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'draft'");
$stats['draft_articles'] = $stmt->fetchColumn();

// Archived articles
$stmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'archived'");
$stats['archived_articles'] = $stmt->fetchColumn();

// Total comments
$stmt = $pdo->query("SELECT COUNT(*) FROM comments");
$stats['total_comments'] = $stmt->fetchColumn();

// Pending comments
$stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'");
$stats['pending_comments'] = $stmt->fetchColumn();

// Recent articles
$stmt = $pdo->query("SELECT a.*, u.name as author_name, u.email as author_email, u.role as author_role 
                      FROM articles a 
                      JOIN users u ON a.author_id = u.id 
                      ORDER BY a.published_at DESC
                      LIMIT 5");
$recent_articles = $stmt->fetchAll();

// Recent comments
$stmt = $pdo->query("SELECT c.*, a.title as article_title, a.slug as article_slug 
                      FROM comments c 
                      JOIN articles a ON c.article_id = a.id 
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
                    <div class="stat-box" data-link="articles.php">
                        <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
                        <div class="stat-info">
                            <h3>Total Articles</h3>
                            <p class="stat-number"><?php echo $stats['total_articles']; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-box" data-link="articles.php?status_filter=published">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-info">
                            <h3>Published Articles</h3>
                            <p class="stat-number"><?php echo $stats['published_articles']; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-box" data-link="articles.php?status_filter=draft">
                        <div class="stat-icon"><i class="fas fa-edit"></i></div>
                        <div class="stat-info">
                            <h3>Draft Articles</h3>
                            <p class="stat-number"><?php echo $stats['draft_articles']; ?></p>
                        </div>
                    </div>

                    <div class="stat-box" data-link="articles.php?status_filter=archived">
                        <div class="stat-icon"><i class="fas fa-archive"></i></div>
                        <div class="stat-info">
                            <h3>Archived Articles</h3>
                            <p class="stat-number"><?php echo $stats['archived_articles']; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-box" data-link="comments.php?status_filter=pending">
                        <div class="stat-icon"><i class="fas fa-comment-dots"></i></div>
                        <div class="stat-info">
                            <h3>Pending Comments</h3>
                            <p class="stat-number"><?php echo $stats['pending_comments']; ?></p>
                        </div>
                    </div>

                    <div class="stat-box" data-link="comments.php">
                        <div class="stat-icon"><i class="fas fa-comments"></i></div>
                        <div class="stat-info">
                            <h3>Total Comments</h3>
                            <p class="stat-number"><?php echo $stats['total_comments']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-sections">
                    <div class="dashboard-section recent-articles">
                        <div class="section-header">
                            <h2>Recent Articles</h2>
                            <a href="articles.php" class="view-all">View All</a>
                        </div>
                        
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
                                    <?php if (empty($recent_articles)): ?>
                                        <tr>
                                            <td colspan="5">No articles found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_articles as $article): ?>
                                            <tr>
                                                <td><?php echo sanitize($article['title']); ?></td>
                                                <td class="article-author-name" 
                                                    data-name="<?php echo sanitize($article['author_name']); ?>" 
                                                    data-email="<?php echo sanitize($article['author_email'] ?? 'N/A'); ?>" 
                                                    data-role="<?php echo sanitize(ucfirst($article['author_role'] ?? 'N/A')); ?>"
                                                    style="cursor: pointer; text-decoration: underline; color: var(--primary-blue);">
                                                    <?php echo sanitize($article['author_name']); ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $article['status']; ?>">
                                                        <?php echo ucfirst($article['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($article['published_at'])); ?></td>
                                                <td class="actions">
                                                    <a href="edit-article.php?id=<?php echo $article['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                    <a href="../article.php?slug=<?php echo $article['slug']; ?>" target="_blank" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
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
                                        <th>Article</th>
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
                                                <td><?php echo sanitize($comment['article_title']); ?></td>
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
                                                    <a href="../article.php?slug=<?php echo sanitize($comment['article_slug']); ?>" target="_blank" class="btn-icon" title="View Article"><i class="fas fa-external-link-alt"></i></a>
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
