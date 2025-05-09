<?php
require_once 'includes/config.php';

// Get page slug from query
$slug = $_GET['slug'] ?? '';
if (!$slug) {
    // Redirect to home page if no slug provided
    redirect('/');
}

// Fetch page details
$stmt = $pdo->prepare("SELECT p.*, u.name as author_name FROM pages p JOIN users u ON p.author_id = u.id WHERE p.slug = ? AND p.status = 'published'");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    // Page not found
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

// Page meta for head
$page_title = $page['title'];
$page_description = substr(strip_tags($page['content']), 0, 160);
$page_image = null;
$body_class = 'page';

// Render header
include 'includes/header.php';
?>

<main class="page-container">
    <article>
        <h1 class="page-title"><?php echo sanitize($page['title']); ?></h1>
        <div class="page-content">
            <?php echo $page['content']; ?>
        </div>
    </article>
</main>

<?php
// Render footer
include 'includes/footer.php';
?> 