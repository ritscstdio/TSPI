<?php
$body_class = "article-page";
require_once 'includes/config.php';

// Get article slug
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    // Redirect to home page if no slug provided
    redirect('/');
}

// Get article details
$stmt = $pdo->prepare("SELECT a.*, u.name as author_name 
                      FROM articles a 
                      JOIN users u ON a.author_id = u.id 
                      WHERE a.slug = ?");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    // Article not found at all
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
} elseif ($article['status'] === 'archived') {
    // Article is archived
    $page_title = "Article Not Available";
    include 'includes/header.php';
    echo "<main><div class='container' style='padding: 2rem; text-align: center;'>";
    echo "<h1>Article Not Available</h1>";
    echo "<p>This article has been archived and is no longer available.</p>";
    echo "<a href='" . SITE_URL . "/' class='btn'>Go to Homepage</a>";
    echo "</div></main>";
    include 'includes/footer.php';
    exit;
} elseif ($article['status'] !== 'published') {
    // Article found but not published (e.g., draft)
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}

// Get article categories
$stmt = $pdo->prepare("SELECT c.* 
                      FROM categories c 
                      JOIN article_categories ac ON c.id = ac.category_id 
                      WHERE ac.article_id = ?");
$stmt->execute([$article['id']]);
$categories = $stmt->fetchAll();

// Get article tags
$stmt = $pdo->prepare("SELECT t.* 
                      FROM tags t 
                      JOIN article_tags at ON t.id = at.tag_id 
                      WHERE at.article_id = ?");
$stmt->execute([$article['id']]);
$tags = $stmt->fetchAll();

// Get previous article
$stmt = $pdo->prepare("SELECT id, title, slug 
                      FROM articles 
                      WHERE published_at < ? AND status = 'published' 
                      ORDER BY published_at DESC 
                      LIMIT 1");
$stmt->execute([$article['published_at']]);
$prev_article = $stmt->fetch();

// Get next article
$stmt = $pdo->prepare("SELECT id, title, slug 
                      FROM articles 
                      WHERE published_at > ? AND status = 'published' 
                      ORDER BY published_at ASC 
                      LIMIT 1");
$stmt->execute([$article['published_at']]);
$next_article = $stmt->fetch();

// Get similar articles based on categories
$stmt = $pdo->prepare("SELECT a.* 
                      FROM articles a 
                      JOIN article_categories ac1 ON a.id = ac1.article_id 
                      JOIN article_categories ac2 ON ac2.category_id = ac1.category_id 
                      WHERE ac2.article_id = ? AND a.id != ? AND a.status = 'published' 
                      GROUP BY a.id 
                      ORDER BY COUNT(a.id) DESC, a.published_at DESC 
                      LIMIT 4");
$stmt->execute([$article['id'], $article['id']]);
$similar_articles = $stmt->fetchAll();

// Get comments for the article
$stmt = $pdo->prepare("SELECT * 
                      FROM comments 
                      WHERE article_id = ? AND status = 'approved' AND parent_id IS NULL
                      ORDER BY posted_at DESC");
$stmt->execute([$article['id']]);
$comments = $stmt->fetchAll();

// Get replies for each comment
function get_comment_replies($comment_id, $pdo) {
    $stmt = $pdo->prepare("SELECT * 
                          FROM comments 
                          WHERE parent_id = ? AND status = 'approved'
                          ORDER BY posted_at ASC");
    $stmt->execute([$comment_id]);
    return $stmt->fetchAll();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $author_name = $_POST['name'] ?? '';
    $author_email = $_POST['email'] ?? '';
    $author_website = $_POST['website'] ?? '';
    $content = $_POST['comment'] ?? '';
    $parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
    
    $errors = [];
    
    // Validate required fields
    if (!$author_name) $errors[] = "Name is required.";
    if (!$author_email) $errors[] = "Email is required.";
    if (!$content) $errors[] = "Comment is required.";
    
    // Validate email
    if ($author_email && !filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Validate website if provided
    if ($author_website && !filter_var($author_website, FILTER_VALIDATE_URL)) {
        $errors[] = "Invalid website URL.";
    }
    
    if (empty($errors)) {
        // Insert comment
        $stmt = $pdo->prepare("INSERT INTO comments (article_id, parent_id, author_name, author_email, author_website, content, ip) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $article['id'], 
            $parent_id, 
            $author_name, 
            $author_email, 
            $author_website, 
            $content, 
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $_SESSION['message'] = "Comment submitted successfully! It will be visible after approval.";
        redirect('/article.php?slug=' . $slug);
    }
}

$page_title = $article['title'];
$page_description = $article['excerpt'] ?: substr(strip_tags($article['content']), 0, 160);
$page_image = $article['thumbnail'] ? SITE_URL . '/' . $article['thumbnail'] : null;

include 'includes/header.php';
?>

<main>
    <article class="article-container">
        <!-- Article Header -->
        <header class="article-header">
            <div class="article-thumbnail">
                <?php if ($article['thumbnail']): ?>
                    <img src="<?php echo $article['thumbnail']; ?>" alt="<?php echo sanitize($article['title']); ?>">
                <?php else: ?>
                    <img src="assets/default-thumbnail.jpg" alt="<?php echo sanitize($article['title']); ?>">
                <?php endif; ?>
            </div>
            
            <h1 class="article-title"><?php echo sanitize($article['title']); ?></h1>
            
            <div class="article-meta">
                <span class="article-date"><?php echo date('F j, Y', strtotime($article['published_at'])); ?></span>
                <span class="article-author">By <?php echo sanitize($article['author_name']); ?></span>
            </div>

            <?php if (!empty($categories)): ?>
                <div class="article-categories">
                    <?php foreach ($categories as $index => $category): ?>
                        <a href="category.php?slug=<?php echo $category['slug']; ?>"><?php echo sanitize($category['name']); ?></a>
                        <?php if ($index < count($categories) - 1): ?>|<?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </header>

        <!-- Article Body -->
        <div class="article-body">
            <?php
                // Wrap iframes in a responsive container
                $content = $article['content'];
                $content = preg_replace('/<iframe.*?>.*?<\/iframe>/is', '<div class="video-embed-container">$0</div>', $content);
                echo $content;
            ?>
        </div>

        <!-- Article Tags -->
        <?php if (!empty($tags)): ?>
            <div class="article-tags">
                <?php foreach ($tags as $tag): ?>
                    <a href="tag.php?slug=<?php echo $tag['slug']; ?>" class="tag">#<?php echo sanitize($tag['name']); ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Navigation -->
        <nav class="article-navigation">
            <div class="prev-post">
                <?php if ($prev_article): ?>
                    <span class="nav-label">Previous Post</span>
                    <a href="article.php?slug=<?php echo $prev_article['slug']; ?>" class="nav-link"><?php echo sanitize($prev_article['title']); ?></a>
                <?php endif; ?>
            </div>
            <div class="next-post">
                <?php if ($next_article): ?>
                    <span class="nav-label">Next Post</span>
                    <a href="article.php?slug=<?php echo $next_article['slug']; ?>" class="nav-link"><?php echo sanitize($next_article['title']); ?></a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Similar Posts -->
        <?php if (!empty($similar_articles)): ?>
            <section class="similar-posts">
                <h2>Similar Posts</h2>
                <div class="similar-posts-carousel-container">
                    <div class="similar-posts-carousel">
                        <div class="carousel-slides">
                            <?php foreach ($similar_articles as $similar): ?>
                                <div class="carousel-slide">
                                    <div class="similar-post-card">
                                        <?php if ($similar['thumbnail']): ?>
                                            <img src="<?php echo $similar['thumbnail']; ?>" alt="<?php echo sanitize($similar['title']); ?>" class="similar-post-thumbnail">
                                        <?php else: ?>
                                            <img src="assets/default-thumbnail.jpg" alt="<?php echo sanitize($similar['title']); ?>" class="similar-post-thumbnail">
                                        <?php endif; ?>
                                        <div class="similar-post-content">
                                            <h3 class="similar-post-title">
                                                <a href="article.php?slug=<?php echo $similar['slug']; ?>"><?php echo sanitize($similar['title']); ?></a>
                                            </h3>
                                            <p class="similar-post-meta">
                                                <?php echo date('F j, Y', strtotime($similar['published_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button class="carousel-arrow prev-arrow" aria-label="Previous">&#10094;</button>
                    <button class="carousel-arrow next-arrow" aria-label="Next">&#10095;</button>
                    <div class="carousel-pagination">
                        <!-- Pagination dots will be generated by JavaScript -->
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Comments Section -->
        <section class="comments-section">
            <h2>Comments</h2>
            
            <?php if (!empty($comments)): ?>
                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-content">
                                <div class="comment-header">
                                    <h4 class="comment-author"><?php echo sanitize($comment['author_name']); ?></h4>
                                    <span class="comment-date"><?php echo date('F j, Y \a\t g:i a', strtotime($comment['posted_at'])); ?></span>
                                </div>
                                <div class="comment-body">
                                    <p><?php echo nl2br(sanitize($comment['content'])); ?></p>
                                </div>
                                <div class="comment-actions">
                                    <button class="comment-reply-btn" data-comment-id="<?php echo $comment['id']; ?>">Reply</button>
                                </div>
                            </div>
                            
                            <!-- Comment replies -->
                            <?php 
                            $replies = get_comment_replies($comment['id'], $pdo);
                            foreach ($replies as $reply): 
                            ?>
                                <div class="comment comment-reply">
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <h4 class="comment-author"><?php echo sanitize($reply['author_name']); ?></h4>
                                            <span class="comment-date"><?php echo date('F j, Y \a\t g:i a', strtotime($reply['posted_at'])); ?></span>
                                        </div>
                                        <div class="comment-body">
                                            <p><?php echo nl2br(sanitize($reply['content'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Reply form (hidden by default) -->
                            <div class="reply-form-container" id="reply-form-<?php echo $comment['id']; ?>" style="display: none; margin-top: 1rem; padding-left: 2rem;">
                                <h4>Leave a Reply</h4>
                                <form action="" method="post" class="comment-form">
                                    <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                    <div class="form-group">
                                        <label for="reply-comment-<?php echo $comment['id']; ?>">Comment</label>
                                        <textarea id="reply-comment-<?php echo $comment['id']; ?>" name="comment" rows="4" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="reply-name-<?php echo $comment['id']; ?>">Name</label>
                                        <input type="text" id="reply-name-<?php echo $comment['id']; ?>" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="reply-email-<?php echo $comment['id']; ?>">Email</label>
                                        <input type="email" id="reply-email-<?php echo $comment['id']; ?>" name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="reply-website-<?php echo $comment['id']; ?>">Website</label>
                                        <input type="url" id="reply-website-<?php echo $comment['id']; ?>" name="website">
                                    </div>
                                    <div class="form-group checkbox">
                                        <input type="checkbox" id="reply-save-info-<?php echo $comment['id']; ?>" name="save-info">
                                        <label for="reply-save-info-<?php echo $comment['id']; ?>">Save my name, email, and website in this browser for the next time I comment.</label>
                                    </div>
                                    <button type="submit" name="submit_comment" class="submit-comment">Post Reply</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>

            <!-- Comment Form -->
            <div class="comment-form-container">
                <h3>Leave a Reply</h3>
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form action="" method="post" class="comment-form">
                    <div class="form-group">
                        <label for="comment">Comment</label>
                        <textarea id="comment" name="comment" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website">
                    </div>
                    <div class="form-group checkbox">
                        <input type="checkbox" id="save-info" name="save-info">
                        <label for="save-info">Save my name, email, and website in this browser for the next time I comment.</label>
                    </div>
                    <button type="submit" name="submit_comment" class="submit-comment">Post Comment</button>
                </form>
            </div>
        </section>
    </article>
</main>

<script>
    // Toggle reply forms
    document.addEventListener('DOMContentLoaded', function() {
        const replyButtons = document.querySelectorAll('.comment-reply-btn');
        
        replyButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const commentId = this.getAttribute('data-comment-id');
                const replyForm = document.getElementById('reply-form-' + commentId);
                
                // Hide all other reply forms
                document.querySelectorAll('.reply-form-container').forEach(function(form) {
                    if (form.id !== 'reply-form-' + commentId) {
                        form.style.display = 'none';
                    }
                });
                
                // Toggle this reply form
                replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
            });
        });
    });
</script>

<?php
include 'includes/footer.php';
?>
