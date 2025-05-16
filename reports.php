<?php
$page_title = "Annual Reports";
$page_description = "TSPI Annual Reports";
$body_class = "reports-page";
include 'includes/header.php';
?>
<?php
// Target the annual reports category
$category_slug = 'ann_reports';

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
        FROM content a 
        JOIN users u ON a.author_id = u.id
        JOIN content_categories ac ON a.id = ac.content_id
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
$reports = $stmt->fetchAll();

?>

<main>
    <section class="news-grid-section">
        <h2 class="news-page-title">Annual Reports</h2>
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

        <!-- contents Grid -->
        <div class="contents-grid">
            <?php if (count($reports) > 0): ?>
                <?php foreach ($reports as $report): ?>
                    <?php
                    if ($report['thumbnail']) {
                        if (preg_match('#^https?://#i', $report['thumbnail'])) {
                            $img = $report['thumbnail'];
                        } else {
                            $img = SITE_URL . '/' . $report['thumbnail'];
                        }
                    } else {
                        $img = SITE_URL . '/assets/default-thumbnail.jpg';
                    }
                    ?>
                    <a href="<?php echo SITE_URL; ?>/content.php?slug=<?php echo $report['slug']; ?>" class="similar-post-card">
                        <div class="similar-post-thumbnail-container">
                            <img src="<?php echo $img; ?>" alt="<?php echo sanitize($report['title']); ?>" class="similar-post-thumbnail">
                        </div>
                        <div class="similar-post-content">
                            <div class="similar-post-title"><?php echo sanitize($report['title']); ?></div>
                            <div class="similar-post-meta">
                                <?php echo sanitize($report['author_name']); ?> | <?php echo date('M j, Y', strtotime($report['published_at'])); ?>
                            </div>
                        </div>
                        <div class="similar-post-hover-content">
                            <p class="similar-post-excerpt">
                                <?php echo sanitize(substr(strip_tags($report['content']), 0, 120)); ?>...
                            </p>
                            <button class="cta-button read-this-btn">Read this!</button>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-contents-message">
                    <p>No annual reports found.</p>
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