<?php
$body_class = "article-page";
require_once 'includes/config.php';

// Handle AJAX vote requests for comments and articles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote_action'])) {
    header('Content-Type: application/json');
    if (!is_logged_in()) {
        echo json_encode(['error' => 'Login required', 'message' => 'You must be logged in to vote.']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $action = $_POST['vote_action']; // e.g., 'upvote', 'downvote', 'article_upvote', 'article_downvote'
    $item_id = null;
    $table_name = '';
    $votes_table_name = '';
    $item_id_column = '';

    if (isset($_POST['comment_id'])) {
        $item_id = (int)$_POST['comment_id'];
        $table_name = 'comments';
        $votes_table_name = 'comment_votes';
        $item_id_column = 'comment_id';
    } elseif (isset($_POST['article_id'])) {
        $item_id = (int)$_POST['article_id'];
        $table_name = 'articles';
        $votes_table_name = 'article_votes';
        $item_id_column = 'article_id';
    } else {
        echo json_encode(['error' => 'Invalid request', 'message' => 'Missing item ID.']);
        exit;
    }

    $requested_vote = 0;
    if ($action === 'upvote' || $action === 'article_upvote') {
        $requested_vote = 1;
    } elseif ($action === 'downvote' || $action === 'article_downvote') {
        $requested_vote = -1;
    }

    if ($requested_vote === 0) {
        echo json_encode(['error' => 'Invalid action', 'message' => 'Invalid vote action specified.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check for existing vote by the user on this item
        $stmt = $pdo->prepare("SELECT vote FROM {$votes_table_name} WHERE {$item_id_column} = ? AND user_id = ?");
        $stmt->execute([$item_id, $user_id]);
        $existing_vote = $stmt->fetchColumn();

        $current_score_change = 0;

        if ($existing_vote === false) { // No existing vote, new vote
            $stmt = $pdo->prepare("INSERT INTO {$votes_table_name} ({$item_id_column}, user_id, vote) VALUES (?, ?, ?)");
            $stmt->execute([$item_id, $user_id, $requested_vote]);
            $current_score_change = $requested_vote;
        } elseif ((int)$existing_vote === $requested_vote) { // User clicked the same button again (revoke vote)
            $stmt = $pdo->prepare("DELETE FROM {$votes_table_name} WHERE {$item_id_column} = ? AND user_id = ?");
            $stmt->execute([$item_id, $user_id]);
            $current_score_change = -$requested_vote; // Reverse the original vote
        } else { // User changed their vote (e.g., from upvote to downvote)
            $stmt = $pdo->prepare("UPDATE {$votes_table_name} SET vote = ? WHERE {$item_id_column} = ? AND user_id = ?");
            $stmt->execute([$requested_vote, $item_id, $user_id]);
            $current_score_change = $requested_vote - (int)$existing_vote; // Difference between new and old vote
        }

        // Update the main item's vote_score
        if ($current_score_change !== 0) {
            $stmt = $pdo->prepare("UPDATE {$table_name} SET vote_score = vote_score + ? WHERE id = ?");
            $stmt->execute([$current_score_change, $item_id]);
        }

        $pdo->commit();

        // Get the new total vote_score for the item and the user's current vote
        $stmt = $pdo->prepare("SELECT vote_score FROM {$table_name} WHERE id = ?");
        $stmt->execute([$item_id]);
        $new_total_score = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT vote FROM {$votes_table_name} WHERE {$item_id_column} = ? AND user_id = ?");
        $stmt->execute([$item_id, $user_id]);
        $user_current_vote = $stmt->fetchColumn();
        if ($user_current_vote === false) $user_current_vote = 0; // No vote means 0

        echo json_encode(['success' => true, 'vote_score' => $new_total_score, 'user_vote' => (int)$user_current_vote]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Vote processing error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error', 'message' => 'Could not process your vote at this time.']);
    }
    exit;
}

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

// Get user votes if logged in
$user_votes = [];
$user_article_vote = null;
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    
    // Get user's votes on comments
    $stmt = $pdo->prepare("SELECT comment_id, vote FROM comment_votes WHERE user_id = ? AND comment_id IN (
                          SELECT id FROM comments WHERE article_id = ?)");
    $stmt->execute([$user_id, $article['id']]);
    while ($row = $stmt->fetch()) {
        $user_votes[$row['comment_id']] = $row['vote'];
    }
    
    // Get user's vote on this article
    $stmt = $pdo->prepare("SELECT vote FROM article_votes WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$user_id, $article['id']]);
    $user_article_vote = $stmt->fetchColumn();
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
$stmt = $pdo->prepare("SELECT c.*, u.profile_picture 
                      FROM comments c 
                      LEFT JOIN users u ON c.user_id = u.id 
                      WHERE c.article_id = ? AND c.status = 'approved' AND c.parent_id IS NULL
                      ORDER BY c.pinned DESC, c.vote_score DESC, c.posted_at DESC");
$stmt->execute([$article['id']]);
$comments = $stmt->fetchAll();

// Get replies for each comment
function get_comment_replies($comment_id, $pdo) {
    $stmt = $pdo->prepare("SELECT c.*, u.profile_picture 
                          FROM comments c 
                          LEFT JOIN users u ON c.user_id = u.id 
                          WHERE c.parent_id = ? AND c.status = 'approved'
                          ORDER BY c.posted_at ASC");
    $stmt->execute([$comment_id]);
    return $stmt->fetchAll();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!is_logged_in()) {
        $_SESSION['message'] = "You must be logged in to comment.";
        redirect('/user/login.php');
    }
    $user = get_logged_in_user();
    $author_name = $user['name'];
    $author_email = $user['email'];
    $author_website = null;
    $content = $_POST['comment'] ?? '';
    $parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
    
    $errors = [];
    if (!$content) {
        $errors[] = "Comment is required.";
    }
    if (empty($errors)) {
        // Insert comment with user_id
        $stmt = $pdo->prepare("INSERT INTO comments (article_id, parent_id, author_name, author_email, author_website, content, user_id, ip) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $article['id'],
            $parent_id,
            $author_name,
            $author_email,
            $author_website,
            $content,
            $user['id'],
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $successMessage = "Comment submitted successfully! It will be visible after approval.";
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['message' => $successMessage]);
            exit;
        }
        $_SESSION['message'] = $successMessage;
        redirect('/article.php?slug=' . $slug);
    }
}

$page_title = $article['title'];
$page_description = $article['excerpt'] ?: substr(strip_tags($article['content']), 0, 160);
$page_image = $article['thumbnail'] ? SITE_URL . '/' . $article['thumbnail'] : null;

include 'includes/header.php';
?>

<style>
/* Disable textarea resizing */
.comment-form textarea,
.reply-form textarea {
    resize: none;
}
/* Comment avatar styling */
.comment-avatar {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
    margin-right: 1rem;
    float: left;
}
.comment-avatar-icon {
    width: 40px;
    height: 40px;
    font-size: 40px;
    color: #ccc;
    margin-right: 1rem;
    float: left;
}
.comment {
    overflow: auto;
}
.comment .comment-content {
    margin-left: 5px;
}
.comment.comment-reply .comment-content {
    margin-left: 0;
}
.reply-form-container {
    margin-left: 0;
    margin-top: 1rem;
    padding-left: 0;
    width: 100%;
}
.login-required-box {
    border: 1px solid #eee;
    background: #fafbfc;
    padding: 2rem 1.5rem;
    border-radius: 8px;
    text-align: center;
    margin: 2rem 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.login-required-box i {
    font-size: 2.5rem;
    color: #888;
    margin-bottom: 0.5rem;
    display: block;
}
.login-required-box .btn {
    margin: 1rem 0 0.5rem 0;
    display: inline-block;
}
.login-required-box .signup-link {
    margin-top: 0.5rem;
    font-size: 1rem;
}

/* Vote Button Styling */
.comment-votes,
.article-votes {
    display: inline-flex;
    align-items: center;
    background-color: #f0f0f0;
    border-radius: 20px;
    padding: 0.2rem 0.3rem;
    gap: 0.3rem;
    vertical-align: middle;
}

.comment-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.vote-btn {
    background-color: transparent;
    border: none;
    cursor: pointer;
    padding: 0.3rem 0.4rem;
    font-size: 1.1rem;
    color: #555;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s ease-in-out, transform 0.1s ease;
    line-height: 1;
}

.vote-btn:hover {
    color: #000;
}

.vote-btn:active {
    transform: scale(0.9);
}

.vote-btn.active.upvote-btn,
.vote-btn.active.article-upvote-btn {
    color: var(--primary-blue);
}

.vote-btn.active.downvote-btn,
.vote-btn.active.article-downvote-btn {
    color: var(--secondary-gold);
}

.vote-score,
.article-vote-score {
    font-weight: bold;
    font-size: 0.9rem;
    color: #333;
    min-width: 22px;
    text-align: center;
    transition: transform 0.3s ease, color 0.3s ease;
    padding: 0 0.2rem;
}

.vote-score.score-up,
.article-vote-score.score-up {
    transform: translateY(-2px) scale(1.1);
    color: var(--primary-blue);
}

.vote-score.score-down,
.article-vote-score.score-down {
    transform: translateY(2px) scale(1.1);
    color: var(--secondary-gold);
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}

/* Article vote container styling */
.article-votes-container {
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    flex-direction: column; /* Stack heading and buttons vertically */
    align-items: flex-start; /* Align items to the start */
}

.article-votes-container h4 {
    margin-bottom: 0.5rem; /* Add some space below the heading */
}

/* Similar Posts Carousel Styling */
.similar-posts-carousel-container {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.similar-posts-carousel {
    display: flex;
    overflow-x: auto; /* Allows horizontal scrolling if needed, good for responsiveness */
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    scrollbar-width: none; /* Hide scrollbar for Firefox */
}
.similar-posts-carousel::-webkit-scrollbar { /* Hide scrollbar for Chrome, Safari, Edge */
    display: none;
}

.carousel-slides {
    display: flex;
    transition: transform 0.5s ease-in-out;
}

.carousel-slide {
    flex: 0 0 calc(100% / 3 - 20px); /* Show 3 cards, account for margin */
    margin-right: 20px;
    scroll-snap-align: start;
    box-sizing: border-box; /* Include padding and border in the element's total width and height */
}

.carousel-slide:last-child {
    margin-right: 0;
}

.similar-post-card {
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
    background-color: #fff;
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Animation for hover */
    height: 100%; /* Ensure cards have same height */
    display: flex;
    flex-direction: column;
}

.similar-post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.similar-post-thumbnail {
    width: 100%;
    height: 180px; /* Fixed height for thumbnails */
    object-fit: cover;
}

.similar-post-content {
    padding: 1rem;
    flex-grow: 1; /* Allows content to fill available space */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.similar-post-title a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
}
.similar-post-title a:hover {
    color: var(--primary-blue);
}

.carousel-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0,0,0,0.5);
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
    z-index: 10;
    border-radius: 50%;
}

.prev-arrow {
    left: 10px;
}

.next-arrow {
    right: 10px;
}

.carousel-pagination {
    text-align: center;
    margin-top: 1rem;
}

.carousel-pagination span {
    display: inline-block;
    width: 10px;
    height: 10px;
    margin: 0 5px;
    background-color: #ccc;
    border-radius: 50%;
    cursor: pointer;
}

.carousel-pagination span.active {
    background-color: #555;
}

/* Reply form animation */
.reply-form-container {
    margin-left: 0;
    margin-top: 1rem;
    padding-left: 0;
    width: 100%;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease-out, opacity 0.5s ease-out; /* Smooth transition */
    opacity: 0;
}

.reply-form-container.visible {
    max-height: 500px; /* Adjust as needed, should be larger than the form's content */
    opacity: 1;
}

</style>

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

        <!-- Article Votes -->
        <div class="article-votes-container">
            <h4>Did you like this article? </h4> 
            <div class="article-votes">
                <button class="vote-btn article-upvote-btn <?php echo (isset($user_article_vote) && $user_article_vote == 1) ? 'active' : ''; ?>" 
                        data-article-id="<?php echo $article['id']; ?>"
                        aria-pressed="<?php echo (isset($user_article_vote) && $user_article_vote == 1) ? 'true' : 'false'; ?>">
                    <i class="fas fa-thumbs-up"></i>
                    <span class="sr-only">Like</span>
                </button>
                <span class="article-vote-score" data-article-id="<?php echo $article['id']; ?>"><?php echo $article['vote_score']; ?></span>
                <button class="vote-btn article-downvote-btn <?php echo (isset($user_article_vote) && $user_article_vote == -1) ? 'active' : ''; ?>" 
                        data-article-id="<?php echo $article['id']; ?>"
                        aria-pressed="<?php echo (isset($user_article_vote) && $user_article_vote == -1) ? 'true' : 'false'; ?>">
                    <i class="fas fa-thumbs-down"></i>
                    <span class="sr-only">Dislike</span>
                </button>
            </div>
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
    </article>

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

    <article class="article-container">
        <!-- Comments Section -->
        <section class="comments-section">
            <h2>Comments</h2>
            
            <?php if (!empty($comments)): ?>
                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <?php if ($comment['profile_picture']): ?>
                                <img src="<?php echo SITE_URL . '/uploads/profile_pics/' . sanitize($comment['profile_picture']); ?>" alt="Avatar" class="comment-avatar">
                            <?php else: ?>
                                <i class="fas fa-user-circle comment-avatar-icon"></i>
                            <?php endif; ?>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <h4 class="comment-author"><?php echo sanitize($comment['author_name']); ?></h4>
                                    <span class="comment-date"><?php echo date('F j, Y \a\t g:i a', strtotime($comment['posted_at'])); ?></span>
                                </div>
                                <div class="comment-body">
                                    <p><?php echo nl2br(sanitize($comment['content'])); ?></p>
                                </div>
                                <div class="comment-actions">
                                    <?php if (is_logged_in()): ?>
                                        <div class="comment-votes">
                                            <button class="vote-btn upvote-btn <?php echo (isset($user_votes[$comment['id']]) && $user_votes[$comment['id']] == 1) ? 'active' : ''; ?>" 
                                                    data-comment-id="<?php echo $comment['id']; ?>" 
                                                    aria-pressed="<?php echo (isset($user_votes[$comment['id']]) && $user_votes[$comment['id']] == 1) ? 'true' : 'false'; ?>">
                                                <i class="fas fa-thumbs-up"></i>
                                                <span class="sr-only">Like</span>
                                            </button>
                                            <span class="vote-score" data-comment-id="<?php echo $comment['id']; ?>"><?php echo $comment['vote_score']; ?></span>
                                            <button class="vote-btn downvote-btn <?php echo (isset($user_votes[$comment['id']]) && $user_votes[$comment['id']] == -1) ? 'active' : ''; ?>" 
                                                    data-comment-id="<?php echo $comment['id']; ?>"
                                                    aria-pressed="<?php echo (isset($user_votes[$comment['id']]) && $user_votes[$comment['id']] == -1) ? 'true' : 'false'; ?>">
                                                <i class="fas fa-thumbs-down"></i>
                                                <span class="sr-only">Dislike</span>
                                            </button>
                                        </div>
                                        <button class="comment-reply-btn" data-comment-id="<?php echo $comment['id']; ?>">Reply</button>
                                    <?php endif; ?>
                                </div>
                                <?php if (is_logged_in()): ?>
                                    <div class="reply-form-container" id="reply-form-<?php echo $comment['id']; ?>">
                                        <h4>Leave a Reply</h4>
                                        <form action="" method="post" class="comment-form">
                                            <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                            <div class="form-group">
                                                <label for="reply-comment-<?php echo $comment['id']; ?>">Comment</label>
                                                <textarea id="reply-comment-<?php echo $comment['id']; ?>" name="comment" rows="4" required style="font-family: inherit;"></textarea>
                                            </div>
                                            <button type="submit" name="submit_comment" class="submit-comment">Post Reply</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Comment replies -->
                            <?php 
                            $replies = get_comment_replies($comment['id'], $pdo);
                            foreach ($replies as $reply): 
                            ?>
                                <div class="comment comment-reply">
                                    <?php if ($reply['profile_picture']): ?>
                                        <img src="<?php echo SITE_URL . '/uploads/profile_pics/' . sanitize($reply['profile_picture']); ?>" alt="Avatar" class="comment-avatar">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle comment-avatar-icon"></i>
                                    <?php endif; ?>
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
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>

            <?php if (is_logged_in()): ?>
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
                        <textarea id="comment" name="comment" rows="6" required style="font-family: inherit;"></textarea>
                    </div>
                    <button type="submit" name="submit_comment" class="submit-comment">Post Comment</button>
                </form>
            </div>
            <?php else: ?>
            <div class="login-required-box">
                <i class="fas fa-sign-in-alt"></i>
                <p>You must be logged in to leave a reply.</p>
                <a href="<?php echo SITE_URL; ?>/user/login.php" class="btn btn-primary">Login</a>
                <p class="signup-link">Don't have an account? <a href="<?php echo SITE_URL; ?>/user/signup.php">Sign up here</a>.</p>
            </div>
            <?php endif; ?>
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
                
                // Hide all other reply forms by removing 'visible' class
                document.querySelectorAll('.reply-form-container.visible').forEach(function(form) {
                    if (form.id !== 'reply-form-' + commentId) {
                        form.classList.remove('visible');
                    }
                });
                
                // Toggle this reply form's visibility
                replyForm.classList.toggle('visible');
            });
        });

        // Similar Posts Carousel Logic
        const carouselContainer = document.querySelector('.similar-posts-carousel-container');
        if (carouselContainer) {
            const carousel = carouselContainer.querySelector('.carousel-slides');
            const slides = carouselContainer.querySelectorAll('.carousel-slide');
            const prevButton = carouselContainer.querySelector('.prev-arrow');
            const nextButton = carouselContainer.querySelector('.next-arrow');
            const paginationContainer = carouselContainer.querySelector('.carousel-pagination');
            
            const slidesPerView = 3;
            let currentIndex = 0;
            const totalSlides = slides.length;
            const totalPages = Math.ceil(totalSlides / slidesPerView);

            function updateCarousel() {
                const offset = -currentIndex * (100 / slidesPerView) * slidesPerView; // This logic might need adjustment based on exact layout
                // Correcting the transform logic for a 3-slide view
                // Each slide is (100/3)% width. We want to move by one slide's width at a time.
                // The offset needs to consider the margin-right on slides if not using gap.
                // The .carousel-slide CSS has: flex: 0 0 calc(100% / 3 - 20px); margin-right: 20px;
                // So, each "group" of 3 slides effectively takes up 100% of the carousel-slides container width.
                // We need to calculate the width of a single slide including its margin for accurate translation.
                
                // Let's simplify by moving one full "page" (slidesPerView) at a time.
                const pageOffset = -currentIndex * 100; // Move by 100% of the container width for each page
                carousel.style.transform = `translateX(${pageOffset}%)`;

                // Update pagination
                if (paginationContainer) {
                    document.querySelectorAll('.carousel-pagination span').forEach((dot, index) => {
                        if (index === Math.floor(currentIndex / slidesPerView) && totalPages > 1) {
                            dot.classList.add('active');
                        } else {
                            dot.classList.remove('active');
                        }
                    });
                }

                // Update arrow visibility
                if (prevButton) prevButton.style.display = currentIndex === 0 ? 'none' : 'block';
                if (nextButton) nextButton.style.display = (currentIndex + slidesPerView >= totalSlides) ? 'none' : 'block';
                if (totalPages <= 1) {
                  if (prevButton) prevButton.style.display = 'none';
                  if (nextButton) nextButton.style.display = 'none';
                }
            }

            function createPagination() {
                if (!paginationContainer || totalPages <= 1) return;
                paginationContainer.innerHTML = ''; // Clear existing dots
                for (let i = 0; i < totalPages; i++) {
                    const dot = document.createElement('span');
                    dot.addEventListener('click', () => {
                        currentIndex = i * slidesPerView;
                         // Ensure currentIndex doesn't exceed bounds when clicking pagination for the last page
                        if (currentIndex + slidesPerView > totalSlides) {
                            currentIndex = totalSlides - slidesPerView;
                            if (currentIndex < 0) currentIndex = 0; // Handle case with less than 3 slides
                        }
                        updateCarousel();
                    });
                    paginationContainer.appendChild(dot);
                }
            }

            if (totalSlides > 0) {
                createPagination();
                updateCarousel(); // Initial setup
            }

            if (nextButton) {
                nextButton.addEventListener('click', () => {
                    if (currentIndex + slidesPerView < totalSlides) {
                        currentIndex += slidesPerView;
                        // Clamp to ensure we don't go past the last possible full view
                        if (currentIndex + slidesPerView > totalSlides) {
                             currentIndex = totalSlides - slidesPerView;
                        }
                         if (currentIndex < 0) currentIndex = 0; // Ensure it doesn't go negative
                    } else {
                        // If on the last set of items, and it's not a full set, don't advance further
                        // Or, optionally, loop back to the start
                        // currentIndex = 0; // Loop to start
                    }
                    updateCarousel();
                });
            }

            if (prevButton) {
                prevButton.addEventListener('click', () => {
                    if (currentIndex - slidesPerView >= 0) {
                        currentIndex -= slidesPerView;
                    } else {
                         currentIndex = 0; // Go to start if trying to go before start
                    }
                    updateCarousel();
                });
            }
            
            // Adjust slides to be 1/3rd of the .similar-posts-carousel (the flex container for .carousel-slides)
            // The .carousel-slides will be 300% width if it has 3x the number of slides it can show.
            // The slides themselves are already styled with flex: 0 0 calc(100% / 3 - 20px);
            // We need to make sure the carousel.style.transform moves correctly.
            // The slidesPerView logic is key.
            if (carousel && slides.length > slidesPerView) {
                 // No direct width style needed on carousel (the flex container for slides)
                 // Its width is determined by its parent and the flex properties of its children
            } else if (carousel) {
                // If not enough slides to scroll, hide arrows and pagination
                if(prevButton) prevButton.style.display = 'none';
                if(nextButton) nextButton.style.display = 'none';
                if(paginationContainer) paginationContainer.innerHTML = '';
            }
        }
    });
</script>

<script>
// AJAX comment/reply submission
function refreshComments() {
    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newCommentsSection = doc.querySelector('.comments-section');
            if (newCommentsSection) {
                document.querySelector('.comments-section').innerHTML = newCommentsSection.innerHTML;
            }
        });
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.comment-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch(window.location.href, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message';
                messageDiv.textContent = data.message;
                messageDiv.style.opacity = 0;
                form.parentNode.insertBefore(messageDiv, form);
                setTimeout(() => {
                    messageDiv.style.transition = 'opacity 0.5s';
                    messageDiv.style.opacity = 1;
                }, 10);
                refreshComments();
            })
            .catch(err => console.error('Comment submission failed:', err));
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function handleVote(button, isArticleVote) {
        const itemId = button.getAttribute(isArticleVote ? 'data-article-id' : 'data-comment-id');
        const action = button.classList.contains(isArticleVote ? 'article-upvote-btn' : 'upvote-btn') 
                       ? (isArticleVote ? 'article_upvote' : 'upvote') 
                       : (isArticleVote ? 'article_downvote' : 'downvote');

        const formData = new URLSearchParams();
        formData.append('vote_action', action);
        if (isArticleVote) {
            formData.append('article_id', itemId);
        } else {
            formData.append('comment_id', itemId);
        }

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.message || 'An error occurred.');
                return;
            }

            const scoreSelector = isArticleVote 
                ? `.article-vote-score[data-article-id="${itemId}"]` 
                : `.vote-score[data-comment-id="${itemId}"]`;
            const scoreDisplay = document.querySelector(scoreSelector);
            
            const upvoteBtnSelector = isArticleVote
                ? `.vote-btn.article-upvote-btn[data-article-id="${itemId}"]`
                : `.vote-btn.upvote-btn[data-comment-id="${itemId}"]`;
            const upvoteBtn = document.querySelector(upvoteBtnSelector);

            const downvoteBtnSelector = isArticleVote
                ? `.vote-btn.article-downvote-btn[data-article-id="${itemId}"]`
                : `.vote-btn.downvote-btn[data-comment-id="${itemId}"]`;
            const downvoteBtn = document.querySelector(downvoteBtnSelector);

            if (scoreDisplay) {
                const oldScore = parseInt(scoreDisplay.textContent);
                scoreDisplay.textContent = data.vote_score;
                scoreDisplay.classList.remove('score-up', 'score-down');
                if (data.vote_score > oldScore) {
                    scoreDisplay.classList.add('score-up');
                } else if (data.vote_score < oldScore) {
                    scoreDisplay.classList.add('score-down');
                }
                setTimeout(() => scoreDisplay.classList.remove('score-up', 'score-down'), 500); // Remove animation class
            }

            if (upvoteBtn) {
                if (data.user_vote === 1) {
                    upvoteBtn.classList.add('active');
                    upvoteBtn.setAttribute('aria-pressed', 'true');
                } else {
                    upvoteBtn.classList.remove('active');
                    upvoteBtn.setAttribute('aria-pressed', 'false');
                }
            }

            if (downvoteBtn) {
                if (data.user_vote === -1) {
                    downvoteBtn.classList.add('active');
                    downvoteBtn.setAttribute('aria-pressed', 'true');
                } else {
                    downvoteBtn.classList.remove('active');
                    downvoteBtn.setAttribute('aria-pressed', 'false');
                }
            }
        })
        .catch(err => console.error('Vote failed:', err));
    }

    document.querySelectorAll('.vote-btn.upvote-btn, .vote-btn.downvote-btn').forEach(btn => {
        btn.addEventListener('click', function() { handleVote(this, false); });
    });

    document.querySelectorAll('.vote-btn.article-upvote-btn, .vote-btn.article-downvote-btn').forEach(btn => {
        btn.addEventListener('click', function() { handleVote(this, true); });
    });
});
</script>

<?php
include 'includes/footer.php';
?>
