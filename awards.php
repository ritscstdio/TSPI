<?php
$page_title = "Awards & Recognitions";
$page_description = "TSPI Awards and Recognitions";
$body_class = "awards-page";
include 'includes/header.php';
?>
<?php
// Determine type filter: organization or client
$type = isset($_GET['type']) ? $_GET['type'] : null;
if ($type === 'organization') {
    $slugFilter = "c.slug = 'awards'";
    $dynamic_page_title = 'Organization Awards';
} elseif ($type === 'client') {
    $slugFilter = "c.slug = 'cliaward'";
    $dynamic_page_title = 'Client Awards';
} else {
    $slugFilter = "c.slug IN ('awards','cliaward')";
    $dynamic_page_title = 'All Awards & Recognitions';
}

// Fetch awards articles
$sql = "SELECT a.id, a.title, a.slug, a.thumbnail, a.content, a.published_at, u.name AS author_name
        FROM articles a
        JOIN users u ON a.author_id = u.id
        JOIN article_categories ac ON a.id = ac.article_id
        JOIN categories c ON ac.category_id = c.id
        WHERE a.status = 'published' AND {$slugFilter}
        ORDER BY a.published_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$awards_articles = $stmt->fetchAll();
?>

<main>
    <section class="news-grid-section">
        <h2 class="news-page-title"><?php echo sanitize($dynamic_page_title); ?></h2>
        <div class="articles-grid">
            <?php foreach ($awards_articles as $art): ?>
                <?php
                // Determine thumbnail URL
                if ($art['thumbnail']) {
                    if (preg_match('#^https?://#i', $art['thumbnail'])) {
                        $img = $art['thumbnail'];
                    } else {
                        $img = SITE_URL . '/' . $art['thumbnail'];
                    }
                } else {
                    $img = SITE_URL . '/assets/default-thumbnail.jpg';
                }
                ?>
                <a href="<?php echo SITE_URL; ?>/article.php?slug=<?php echo $art['slug']; ?>" class="similar-post-card">
                    <div class="similar-post-thumbnail-container">
                        <img src="<?php echo $img; ?>" alt="<?php echo sanitize($art['title']); ?>" class="similar-post-thumbnail">
                    </div>
                    <div class="similar-post-content">
                        <div class="similar-post-title"><?php echo sanitize($art['title']); ?></div>
                        <div class="similar-post-meta"><?php echo sanitize($art['author_name']); ?> | <?php echo date('M j, Y', strtotime($art['published_at'])); ?></div>
                    </div>
                    <div class="similar-post-hover-content">
                        <p class="similar-post-excerpt"><?php echo sanitize(substr(strip_tags($art['content']), 0, 120)); ?>...</p>
                        <button class="cta-button read-this-btn">View Details</button>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> 