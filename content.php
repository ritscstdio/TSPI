<?php
$body_class = "content-page";
require_once 'includes/config.php';

// Handle AJAX vote requests for comments and contents
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote_action'])) {
    header('Content-Type: application/json');
    if (!is_logged_in()) {
        echo json_encode(['error' => 'Login required', 'message' => 'You must be logged in to vote.']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $action = $_POST['vote_action']; // e.g., 'upvote', 'downvote', 'content_upvote', 'content_downvote'
    $item_id = null;
    $table_name = '';
    $votes_table_name = '';
    $item_id_column = '';

    if (isset($_POST['comment_id'])) {
        $item_id = (int)$_POST['comment_id'];
        $table_name = 'comments';
        $votes_table_name = 'comment_votes';
        $item_id_column = 'comment_id';
    } elseif (isset($_POST['content_id'])) {
        $item_id = (int)$_POST['content_id'];
        $table_name = 'content';
        $votes_table_name = 'content_votes';
        $item_id_column = 'content_id';
    } else {
        echo json_encode(['error' => 'Invalid request', 'message' => 'Missing item ID.']);
        exit;
    }

    $requested_vote = 0;
    if ($action === 'upvote' || $action === 'content_upvote') {
        $requested_vote = 1;
    } elseif ($action === 'downvote' || $action === 'content_downvote') {
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

// Get content slug
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    // Redirect to home page if no slug provided
    redirect('/');
}

// Get content details
$stmt = $pdo->prepare("SELECT a.*, u.name as author_name 
                      FROM content a 
                      JOIN users u ON a.author_id = u.id 
                      WHERE a.slug = ?");
$stmt->execute([$slug]);
$content = $stmt->fetch();

if (!$content) {
    // content not found at all
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
} elseif ($content['status'] === 'archived') {
    // content is archived
    $page_title = "content Not Available";
    include 'includes/header.php';
    echo "<main><div class='container' style='padding: 2rem; text-align: center;'>";
    echo "<h1>content Not Available</h1>";
    echo "<p>This content has been archived and is no longer available.</p>";
    echo "<a href='" . SITE_URL . "/' class='btn'>Go to Homepage</a>";
    echo "</div></main>";
    include 'includes/footer.php';
    exit;
} elseif ($content['status'] !== 'published') {
    // content found but not published (e.g., draft)
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}

// Get user votes if logged in
$user_votes = [];
$user_content_vote = null;
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    
    // Get user's votes on comments
    $stmt = $pdo->prepare("SELECT comment_id, vote FROM comment_votes WHERE user_id = ? AND comment_id IN (
                          SELECT id FROM comments WHERE content_id = ?)");
    $stmt->execute([$user_id, $content['id']]);
    while ($row = $stmt->fetch()) {
        $user_votes[$row['comment_id']] = $row['vote'];
    }
    
    // Get user's vote on this content
    $stmt = $pdo->prepare("SELECT vote FROM content_votes WHERE user_id = ? AND content_id = ?");
    $stmt->execute([$user_id, $content['id']]);
    $user_content_vote = $stmt->fetchColumn();
}

// Get content categories
$stmt = $pdo->prepare("SELECT c.* 
                      FROM categories c 
                      JOIN content_categories ac ON c.id = ac.category_id 
                      WHERE ac.content_id = ?");
$stmt->execute([$content['id']]);
$categories = $stmt->fetchAll();

// Get content tags
$stmt = $pdo->prepare("SELECT t.* 
                      FROM tags t 
                      JOIN content_tags at ON t.id = at.tag_id 
                      WHERE at.content_id = ?");
$stmt->execute([$content['id']]);
$tags = $stmt->fetchAll();

// Get previous content
$stmt = $pdo->prepare("SELECT id, title, slug 
                      FROM content 
                      WHERE published_at < ? AND status = 'published' 
                      ORDER BY published_at DESC 
                      LIMIT 1");
$stmt->execute([$content['published_at']]);
$prev_content = $stmt->fetch();

// Get next content
$stmt = $pdo->prepare("SELECT id, title, slug 
                      FROM content 
                      WHERE published_at > ? AND status = 'published' 
                      ORDER BY published_at ASC 
                      LIMIT 1");
$stmt->execute([$content['published_at']]);
$next_content = $stmt->fetch();

// Get similar contents based on categories
$stmt = $pdo->prepare("SELECT a.*, u.name as author_name
                      FROM content a 
                      JOIN users u ON a.author_id = u.id
                      JOIN content_categories ac1 ON a.id = ac1.content_id 
                      JOIN content_categories ac2 ON ac2.category_id = ac1.category_id 
                      WHERE ac2.content_id = ? AND a.id != ? AND a.status = 'published' 
                      GROUP BY a.id, u.name 
                      ORDER BY COUNT(a.id) DESC, a.published_at DESC 
                      LIMIT 4");
$stmt->execute([$content['id'], $content['id']]);
$similar_contents = $stmt->fetchAll();

// Get comments for the content
$stmt = $pdo->prepare("SELECT c.*, u.profile_picture 
                      FROM comments c 
                      LEFT JOIN users u ON c.user_id = u.id 
                      WHERE c.content_id = ? AND c.status = 'approved' AND c.parent_id IS NULL
                      ORDER BY c.pinned DESC, c.vote_score DESC, c.posted_at DESC");
$stmt->execute([$content['id']]);
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
    $comment_text = $_POST['comment'] ?? '';
    $parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
    
    $errors = [];
    if (!$comment_text) {
        $errors[] = "Comment is required.";
    }
    if (empty($errors)) {
        // Get content ID to avoid variable collision
        $content_id = $content['id'];
        
        // Insert comment with user_id
        $stmt = $pdo->prepare("INSERT INTO comments (content_id, parent_id, author_name, author_email, author_website, content, user_id, ip) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $content_id,
            $parent_id,
            $author_name,
            $author_email,
            $author_website,
            $comment_text,
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
        redirect('/content.php?slug=' . $slug);
    }
}

$page_title = $content['title'];
$page_description = $content['excerpt'] ?: substr(strip_tags($content['content']), 0, 160);
$page_image = $content['thumbnail'] ? SITE_URL . '/' . $content['thumbnail'] : null;

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
    margin-right: 0.5rem; /* Reduced from 1rem */
    /* float: left; */ /* Removed */
}
.comment-avatar-icon {
    width: 40px;
    height: 40px;
    font-size: 40px;
    color: #ccc;
    margin-right: 0.5rem; /* Reduced from 1rem */
    /* float: left; */ /* Removed */
    display: flex; /* Added for better icon centering */
    align-items: center; /* Added */
    justify-content: center; /* Added */
}
.comment {
    overflow: auto; 
    margin-bottom: 1.5rem; 
}
.comment > .comment-content {
    margin-left: 0; /* Changed from user's 1rem to 0 as avatar is now inside header */
}
.comment-reply { 
    margin-top: 1rem; 
    margin-left: 1rem; /* User's existing value for reply block indent */
}

/* Added for avatar within header */
.comment-header {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem; 
}

/* Ensure no float or bottom margin for avatars within the header */
.comment-header .comment-avatar,
.comment-header .comment-avatar-icon {
    float: none;
    margin-bottom: 0;
}

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

/* Enhanced Login Required Box Styling */
.login-required-box {
    /* background-color: #f8f9fa; */
    border-radius: 8px;
    padding: 2rem;
    margin: 2rem 0;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #e0e0e0;
    /* max-width: 500px; */
    margin-left: auto;
    margin-right: auto;
}

.login-required-box i {
    font-size: 3rem;
    color: var(--primary-blue);
    margin-bottom: 1rem;
    display: block;
}

.login-required-box p {
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
    color: #555;
}

.login-required-box .btn-primary {
    display: inline-block;
    background-color: var(--primary-blue);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.login-required-box .btn-primary:hover {
    background-color: var(--dark-navy);
}

.login-required-box .signup-link {
    margin-top: 1rem;
    font-size: 0.9rem;
}

.login-required-box .signup-link a {
    color: var(--primary-blue);
    text-decoration: underline;
}

.login-required-box .signup-link a:hover {
    color: var(--dark-navy);
}

/* Vote Button Styling */
.comment-votes,
.content-votes {
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
.vote-btn.active.content-upvote-btn {
    color: var(--primary-blue);
}

.vote-btn.active.downvote-btn,
.vote-btn.active.content-downvote-btn {
    color: var(--secondary-gold);
}

.vote-score,
.content-vote-score {
    font-weight: bold;
    font-size: 0.9rem;
    color: #333;
    min-width: 22px;
    text-align: center;
    transition: transform 0.3s ease, color 0.3s ease;
    padding: 0 0.2rem;
}

.vote-score.score-up,
.content-vote-score.score-up {
    transform: translateY(-2px) scale(1.1);
    color: var(--primary-blue);
}

.vote-score.score-down,
.content-vote-score.score-down {
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

/* content vote container styling */
.content-votes-container {
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    flex-direction: column; /* Stack heading and buttons vertically */
    align-items: flex-start; /* Align items to the start */
}

.content-votes-container h4 {
    margin-bottom: 0.5rem; /* Add some space below the heading */
}

/* Similar Posts Carousel Styling */
.similar-posts-carousel-container {
    position: relative;
    width: 100%;
    overflow: hidden; /* Can be here or on .similar-posts-carousel */
}

.similar-posts-carousel {
    /* display: flex; */ /* Removed if .carousel-slides is the flex container being transformed */
    overflow: hidden; /* Changed from auto */
    position: relative; /* Added for potential arrow positioning */
    /* scroll-snap-type: x mandatory; */ /* Removed or set to none */
    -webkit-overflow-scrolling: touch; 
    scrollbar-width: none; 
}
.similar-posts-carousel::-webkit-scrollbar { 
    display: none;
}

.carousel-slides {
    display: flex;
    transition: transform 0.5s ease-in-out;
}

.carousel-slide {
    /* flex: 0 0 calc(100% / 3 - 20px); /* Original */
    flex: 0 0 calc((100% - 40px) / 3); /* Default: 3 slides, 2 gaps of 20px */
    margin-right: 20px;
    /* scroll-snap-align: start; */ /* Keep if native scroll is ever re-enabled */
    box-sizing: border-box; 
}

.carousel-slide:last-child {
    margin-right: 0;
}

/* Responsive adjustments for carousel slides */
@media (max-width: 992px) { /* Tablets and wider phones, show 2 slides */
    .carousel-slide {
        flex: 0 0 calc((100% - 20px) / 2); /* 2 slides, 1 gap of 20px */
        margin-right: 20px;
    }
    .carousel-slide:nth-child(2n) { /* If two slides are shown, the second one might need its margin adjusted depending on container */
      /* Covered by last-child if it's the end of the whole list, or if JS correctly pages. */
    }
}

@media (max-width: 767px) { /* Narrower tablets and phones, show 1 slide */
    .carousel-slide {
        flex: 0 0 100%; /* 1 slide, 0 gap within the slide's own definition */
        margin-right: 0;
    }
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

/* Toast notification */
.toast-container {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
}
.toast {
    background-color: rgba(0,0,0,0.8);
    color: #fff;
    padding: 12px 20px;
    border-radius: 4px;
    margin-top: 10px;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}
.toast.show {
    opacity: 1;
}

/* Video embed container for responsive iframes */
.video-embed-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    height: 0;
    overflow: hidden;
    max-width: 100%;
    margin-bottom: 1.5rem;
}

.video-embed-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

</style>

<main>
    <article class="content-container">
        <!-- content Header -->
        <header class="content-header">
            <div class="content-thumbnail">
                <?php if ($content['thumbnail']): ?>
                    <img src="<?php echo $content['thumbnail']; ?>" alt="<?php echo sanitize($content['title']); ?>">
                <?php else: ?>
                    <img src="assets/default-thumbnail.jpg" alt="<?php echo sanitize($content['title']); ?>">
                <?php endif; ?>
            </div>
            
            <h1 class="content-title"><?php echo sanitize($content['title']); ?></h1>
            
            <div class="content-meta">
                <span class="content-date"><?php echo date('F j, Y', strtotime($content['published_at'])); ?></span>
                <span class="content-author">By <?php echo sanitize($content['author_name']); ?></span>
                
            </div>

            <?php if (!empty($categories)): ?>
                <div class="content-categories">
                    <?php foreach ($categories as $index => $category): ?>
                        <a href="category.php?slug=<?php echo $category['slug']; ?>"><?php echo sanitize($category['name']); ?></a>
                        <?php if ($index < count($categories) - 1): ?>|<?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </header>

        <!-- content Body -->
        <div class="content-body">
            <?php
                // Wrap iframes in a responsive container
                $bodyContent = $content['content'];
                $bodyContent = preg_replace('/<iframe(.*?)>(.*?)<\/iframe>/is', '<div class="video-embed-container"><iframe$1>$2</iframe></div>', $bodyContent);
                echo $bodyContent;
            ?>
        </div>

        <!-- content Votes -->
        <div class="content-votes-container">
            <h4>Had a good read? </h4> 
            <div class="content-votes">
                <button class="vote-btn content-upvote-btn <?php echo (isset($user_content_vote) && $user_content_vote == 1) ? 'active' : ''; ?>" 
                        data-content-id="<?php echo $content['id']; ?>"
                        aria-pressed="<?php echo (isset($user_content_vote) && $user_content_vote == 1) ? 'true' : 'false'; ?>">
                    <i class="fas fa-thumbs-up"></i>
                    <span class="sr-only">Like</span>
                </button>
                <span class="content-vote-score" data-content-id="<?php echo $content['id']; ?>"><?php echo $content['vote_score']; ?></span>
                <button class="vote-btn content-downvote-btn <?php echo (isset($user_content_vote) && $user_content_vote == -1) ? 'active' : ''; ?>" 
                        data-content-id="<?php echo $content['id']; ?>"
                        aria-pressed="<?php echo (isset($user_content_vote) && $user_content_vote == -1) ? 'true' : 'false'; ?>">
                    <i class="fas fa-thumbs-down"></i>
                    <span class="sr-only">Dislike</span>
                </button>
            </div>
        </div>

        <!-- content Tags -->
        <?php if (!empty($tags)): ?>
            <div class="content-tags">
                <?php foreach ($tags as $tag): ?>
                    <span class="tag">#<?php echo sanitize($tag['name']); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Navigation -->
        <nav class="content-navigation">
            <div class="prev-post">
                <?php if ($prev_content): ?>
                    <span class="nav-label">Previous Post</span>
                    <a href="content.php?slug=<?php echo $prev_content['slug']; ?>" class="nav-link"><?php echo sanitize($prev_content['title']); ?></a>
                <?php endif; ?>
            </div>
            <div class="next-post">
                <?php if ($next_content): ?>
                    <span class="nav-label">Next Post</span>
                    <a href="content.php?slug=<?php echo $next_content['slug']; ?>" class="nav-link"><?php echo sanitize($next_content['title']); ?></a>
                <?php endif; ?>
            </div>
        </nav>
    </article>

    <!-- Similar Posts -->
    <?php if (!empty($similar_contents)): ?>
        <section class="similar-posts">
            <h2>Similar Posts</h2>
            <div class="similar-posts-carousel-container">
                <div class="similar-posts-carousel">
                    <div class="carousel-slides">
                        <?php foreach ($similar_contents as $similar): ?>
                            <div class="carousel-slide">
                                <div class="similar-post-card">
                                    <?php if ($similar['thumbnail']): ?>
                                        <img src="<?php echo $similar['thumbnail']; ?>" alt="<?php echo sanitize($similar['title']); ?>" class="similar-post-thumbnail">
                                    <?php else: ?>
                                        <img src="assets/default-thumbnail.jpg" alt="<?php echo sanitize($similar['title']); ?>" class="similar-post-thumbnail">
                                    <?php endif; ?>
                                    <div class="similar-post-content">
                                        <h3 class="similar-post-title">
                                            <a href="content.php?slug=<?php echo $similar['slug']; ?>"><?php echo sanitize($similar['title']); ?></a>
                                        </h3>
                                        <p class="similar-post-meta">
                                            Published by <?php echo sanitize($similar['author_name']); ?> | <?php echo date('F j, Y', strtotime($similar['published_at'])); ?>
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

    <article class="content-container">
        <!-- Comments Section -->
        <section class="comments-section">
            <h2>Comments</h2>
            
            <?php if (!empty($comments)): ?>
                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-content">
                                <div class="comment-header">
                                    <?php if ($comment['profile_picture']): ?>
                                        <img src="<?php echo SITE_URL . '/uploads/profile_pics/' . sanitize($comment['profile_picture']); ?>" alt="Avatar" class="comment-avatar">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle comment-avatar-icon"></i>
                                    <?php endif; ?>
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

                                <!-- Comment replies will be moved here -->
                                <?php 
                                $replies = get_comment_replies($comment['id'], $pdo);
                                if (!empty($replies)) { // Check if there are replies before starting the container
                                    echo '<div class="comment-replies-container">'; // Optional: a container for all replies if needed for specific styling
                                    foreach ($replies as $reply): 
                                ?>
                                    <div class="comment comment-reply">
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <?php if ($reply['profile_picture']): ?>
                                                    <img src="<?php echo SITE_URL . '/uploads/profile_pics/' . sanitize($reply['profile_picture']); ?>" alt="Avatar" class="comment-avatar">
                                                <?php else: ?>
                                                    <i class="fas fa-user-circle comment-avatar-icon"></i>
                                                <?php endif; ?>
                                                <h4 class="comment-author"><?php echo sanitize($reply['author_name']); ?></h4>
                                                <span class="comment-date"><?php echo date('F j, Y \a\t g:i a', strtotime($reply['posted_at'])); ?></span>
                                            </div>
                                            <div class="comment-body">
                                                <p><?php echo nl2br(sanitize($reply['content'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    endforeach; 
                                    echo '</div>'; // Close optional container
                                }
                                ?>
                            </div>
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
            const slides = Array.from(carouselContainer.querySelectorAll('.carousel-slide')); // Ensure it's an array
            const prevButton = carouselContainer.querySelector('.prev-arrow');
            const nextButton = carouselContainer.querySelector('.next-arrow');
            const paginationContainer = carouselContainer.querySelector('.carousel-pagination');
            
            let slidesPerView = 3; // Will be updated
            let currentPageIndex = 0;
            const totalSlides = slides.length;
            let totalPages = 0; // Will be calculated

            function getSlidesPerView() {
                if (window.innerWidth <= 767) {
                    return 1;
                } else if (window.innerWidth <= 992) {
                    return 2;
                }
                return 3;
            }

            function updateCarouselConfig() {
                slidesPerView = getSlidesPerView();
                totalPages = Math.ceil(totalSlides / slidesPerView);
                // Ensure currentPageIndex is valid after config change (e.g. resize)
                currentPageIndex = Math.max(0, Math.min(currentPageIndex, totalPages - 1));
            }

            function updateCarouselDisplay() {
                if (!carousel) return;
                // Transform based on currentPageIndex (0-indexed page number)
                const pageOffset = -currentPageIndex * 100;
                carousel.style.transform = `translateX(${pageOffset}%)`;

                // Update pagination dots
                if (paginationContainer) {
                    const dots = paginationContainer.querySelectorAll('span');
                    dots.forEach((dot, index) => {
                        if (index === currentPageIndex && totalPages > 1) {
                            dot.classList.add('active');
                        } else {
                            dot.classList.remove('active');
                        }
                    });
                }

                // Update arrow visibility
                if (prevButton) prevButton.style.display = (currentPageIndex === 0 || totalPages <= 1) ? 'none' : 'block';
                if (nextButton) nextButton.style.display = (currentPageIndex >= totalPages - 1 || totalPages <= 1) ? 'none' : 'block';
            }

            function createPaginationDots() {
                if (!paginationContainer || totalSlides === 0) return;
                paginationContainer.innerHTML = ''; // Clear existing dots
                
                if (totalPages <= 1) return; // No dots if only one page or no slides

                for (let i = 0; i < totalPages; i++) {
                    const dot = document.createElement('span');
                    dot.addEventListener('click', () => {
                        currentPageIndex = i;
                        updateCarouselDisplay();
                    });
                    paginationContainer.appendChild(dot);
                }
            }
            
            function initializeCarousel() {
                if (totalSlides === 0) {
                    if(prevButton) prevButton.style.display = 'none';
                    if(nextButton) nextButton.style.display = 'none';
                    if(paginationContainer) paginationContainer.innerHTML = '';
                    return;
                }
                updateCarouselConfig();
                createPaginationDots();
                updateCarouselDisplay();
            }

            if (nextButton) {
                nextButton.addEventListener('click', () => {
                    if (currentPageIndex < totalPages - 1) {
                        currentPageIndex++;
                        updateCarouselDisplay();
                    }
                });
            }

            if (prevButton) {
                prevButton.addEventListener('click', () => {
                    if (currentPageIndex > 0) {
                        currentPageIndex--;
                        updateCarouselDisplay();
                    }
                });
            }
            
            // Initial setup
            initializeCarousel();

            // Re-initialize on window resize
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    const oldTotalPages = totalPages;
                    initializeCarousel(); // This will update config, dots, and display
                }, 250); // Debounce resize event
            });
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

// Add toast notification function
function showToast(message) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    container.appendChild(toast);
    // Force reflow so animation triggers
    void toast.offsetWidth;
    toast.classList.add('show');
    // Remove toast after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (container.contains(toast)) {
                container.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.comment-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Prepare form data and include submit_comment so server detects it
            const formData = new FormData(form);
            formData.append('submit_comment', '1');
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message);
                refreshComments();
            })
            .catch(err => console.error('Comment submission failed:', err));
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function handleVote(button, iscontentVote) {
        const itemId = button.getAttribute(iscontentVote ? 'data-content-id' : 'data-comment-id');
        const action = button.classList.contains(iscontentVote ? 'content-upvote-btn' : 'upvote-btn') 
                       ? (iscontentVote ? 'content_upvote' : 'upvote') 
                       : (iscontentVote ? 'content_downvote' : 'downvote');

        const formData = new URLSearchParams();
        formData.append('vote_action', action);
        if (iscontentVote) {
            formData.append('content_id', itemId);
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

            const scoreSelector = iscontentVote 
                ? `.content-vote-score[data-content-id="${itemId}"]` 
                : `.vote-score[data-comment-id="${itemId}"]`;
            const scoreDisplay = document.querySelector(scoreSelector);
            
            const upvoteBtnSelector = iscontentVote
                ? `.vote-btn.content-upvote-btn[data-content-id="${itemId}"]`
                : `.vote-btn.upvote-btn[data-comment-id="${itemId}"]`;
            const upvoteBtn = document.querySelector(upvoteBtnSelector);

            const downvoteBtnSelector = iscontentVote
                ? `.vote-btn.content-downvote-btn[data-content-id="${itemId}"]`
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

    document.querySelectorAll('.vote-btn.content-upvote-btn, .vote-btn.content-downvote-btn').forEach(btn => {
        btn.addEventListener('click', function() { handleVote(this, true); });
    });
});
</script>

<?php
include 'includes/footer.php';
?>
