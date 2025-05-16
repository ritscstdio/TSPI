<?php
$page_title = "Client Stories";
$page_description = "Client Stories and Success from TSPI";
$body_class = "stories-page";
include 'includes/header.php';
?>
<?php
// Target the client stories category
$category_slug = 'cli_stories';

// Get category info
$category_query = "SELECT id, name FROM categories WHERE slug = ?";
$category_stmt = $pdo->prepare($category_query);
$category_stmt->execute([$category_slug]);
$category = $category_stmt->fetch();

// Available sort options
$sort_options = [
    'published_at_desc' => 'Date (Newest First)',
    'published_at_asc'  => 'Date (Oldest First)',
    'title_asc'         => 'Title (A-Z)',
    'title_desc'        => 'Title (Z-A)',
];
$current_sort = isset($_GET['sort']) && array_key_exists($_GET['sort'], $sort_options)
    ? $_GET['sort']
    : 'published_at_desc';

// Build query
$sql = "SELECT a.id, a.title, a.slug, a.thumbnail, a.content, a.published_at, a.author_id, u.name as author_name 
        FROM articles a 
        JOIN users u ON a.author_id = u.id
        JOIN article_categories ac ON a.id = ac.article_id
        JOIN categories c ON ac.category_id = c.id
        WHERE a.status = 'published' AND c.slug = ?";

// Add sorting
switch ($current_sort) {
    case 'published_at_asc':
        $sql .= " ORDER BY a.published_at ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY a.title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY a.title DESC";
        break;
    case 'published_at_desc':
    default:
        $sql .= " ORDER BY a.published_at DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$category_slug]);
$stories = $stmt->fetchAll();

?>

<main>
    <section class="news-grid-section">
        <h2 class="news-page-title">Client Stories</h2>
        <!-- Sort Control -->
        <form method="get" class="news-filters">
            <label for="sort-order">Sort By:</label>
            <select id="sort-order" name="sort" onchange="this.form.submit()">
                <?php foreach ($sort_options as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $current_sort == $key ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Articles Grid -->
        <div class="articles-grid">
            <?php if (count($stories) > 0): ?>
                <?php foreach ($stories as $story): ?>
                    <?php
                    if ($story['thumbnail']) {
                        if (preg_match('#^https?://#i', $story['thumbnail'])) {
                            $img = $story['thumbnail'];
                        } else {
                            $img = SITE_URL . '/' . $story['thumbnail'];
                        }
                    } else {
                        $img = SITE_URL . '/assets/default-thumbnail.jpg';
                    }
                    ?>
                    <a href="<?php echo SITE_URL; ?>/article.php?slug=<?php echo $story['slug']; ?>" class="similar-post-card">
                        <div class="similar-post-thumbnail-container">
                            <img src="<?php echo $img; ?>" alt="<?php echo sanitize($story['title']); ?>" class="similar-post-thumbnail">
                        </div>
                        <div class="similar-post-content">
                            <div class="similar-post-title"><?php echo sanitize($story['title']); ?></div>
                            <div class="similar-post-meta">
                                <?php echo sanitize($story['author_name']); ?> | <?php echo date('M j, Y', strtotime($story['published_at'])); ?>
                            </div>
                        </div>
                        <div class="similar-post-hover-content">
                            <p class="similar-post-excerpt">
                                <?php echo sanitize(substr(strip_tags($story['content']), 0, 120)); ?>...
                            </p>
                            <button class="cta-button read-this-btn">Read this!</button>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-articles-message">
                    <p>No client stories found.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the form elements
    const filterForm = document.querySelector('.news-filters');
    const sortSelect = document.getElementById('sort-order');
    
    // Add event listener to form control
    sortSelect.addEventListener('change', function() {
        filterForm.submit();
    });
});
</script>

<?php include 'includes/footer.php'; ?> 