<?php
$page_title = "News";
$page_description = "Latest TSPI contents and Updates";
$body_class = "news-page"; // Add specific body class for news page styling
include 'includes/header.php';
?>
<?php
// Fetch categories and set up filters/sorting
$categories_list = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$filter_category = isset($_GET['category']) ? (int)
    $_GET['category'] : null;
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
        JOIN users u ON a.author_id = u.id";
$params = [];
if ($filter_category) {
    $sql .= " JOIN content_categories ac ON a.id = ac.content_id";
}
$sql .= " WHERE a.status = 'published'";
if ($filter_category) {
    $sql .= " AND ac.category_id = ?";
    $params[] = $filter_category;
}
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
$stmt->execute($params);
$news_contents = $stmt->fetchAll();

// Determine the page title based on the filter
$dynamic_page_title = "TSPI News"; // Default title
if ($filter_category) {
    foreach ($categories_list as $cat) {
        if ($cat['id'] == $filter_category) {
            $dynamic_page_title = sanitize($cat['name']); // Use category name as title
            break;
        }
    }
}

?>

<main>
    <section class="news-grid-section">
        <h2 class="news-page-title"><?php echo $dynamic_page_title; ?></h2>
        <!-- Filter and Sort Controls -->
        <form method="get" class="news-filters">
            <label for="filter-category">Filter by Category:</label>
            <select id="filter-category" name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories_list as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo sanitize($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
            <?php foreach ($news_contents as $art): ?>
                <?php
                // Improved thumbnail handling
                $img = '';
                if ($art['thumbnail']) {
                    if (preg_match('#^https?://#i', $art['thumbnail'])) {
                        $img = $art['thumbnail'];
                    } else if (strpos($art['thumbnail'], 'uploads/media/') !== false) {
                        $filename = basename($art['thumbnail']);
                        $img = SITE_URL . '/uploads/media/' . $filename;
                    } else if (strpos($art['thumbnail'], 'src/assets/') !== false) {
                        $filename = basename($art['thumbnail']);
                        $img = SITE_URL . '/src/assets/' . $filename;
                    } else {
                        $img = resolve_asset_path($art['thumbnail']);
                    }
                } else {
                    $img = SITE_URL . '/src/assets/default-thumbnail.jpg';
                }
                ?>
                <a href="<?php echo SITE_URL; ?>/content.php?slug=<?php echo $art['slug']; ?>" class="similar-post-card">
                    <div class="similar-post-thumbnail-container">
                        <img src="<?php echo $img; ?>" alt="<?php echo sanitize($art['title']); ?>" class="similar-post-thumbnail">
                    </div>
                    <div class="similar-post-content">
                        <div class="similar-post-title"><?php echo sanitize($art['title']); ?></div>
                        <div class="similar-post-meta">
                            <?php echo sanitize($art['author_name']); ?> | <?php echo date('M j, Y', strtotime($art['published_at'])); ?>
                        </div>
                    </div>
                    <div class="similar-post-hover-content">
                        <p class="similar-post-excerpt">
                            <?php echo sanitize(substr(strip_tags($art['content']), 0, 120)); ?>...
                        </p>
                        <button class="cta-button read-this-btn">Read this!</button>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> 