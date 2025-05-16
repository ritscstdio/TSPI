<?php
$page_title = "SAMBAYANIHAN";
$page_description = "TSPI SAMBAYANIHAN Activities";
$body_class = "sambayanihan-page";
include 'includes/header.php';
?>
<?php
// Fetch only the sambayanihan categories
$categories_sql = "SELECT * FROM categories WHERE slug IN ('sambayanihan', 'sambayanihan_client', 'sambayanihan_employees') ORDER BY name";
$categories_stmt = $pdo->query($categories_sql);
$categories_list = $categories_stmt ? $categories_stmt->fetchAll() : [];

// If the categories don't exist yet, create a default structure for display
if (empty($categories_list)) {
    $categories_list = [
        ['id' => 'sambayanihan', 'name' => 'All SAMBAYANIHAN', 'slug' => 'sambayanihan'],
        ['id' => 'sambayanihan_client', 'name' => 'With Clients', 'slug' => 'sambayanihan_client'],
        ['id' => 'sambayanihan_employees', 'name' => 'With Employees', 'slug' => 'sambayanihan_employees']
    ];
}

// Get category ID from URL if provided
$filter_category = isset($_GET['category']) ? $_GET['category'] : null;

// Initialize category_slug variable
$category_slug = null;

// If filter is passed as slug or id, handle it appropriately
if ($filter_category) {
    // Check if it's a slug
    if (!is_numeric($filter_category)) {
        $category_slug = $filter_category;
        
        // Find the corresponding category in our list
        foreach ($categories_list as $cat) {
            if ($cat['slug'] == $category_slug) {
                $filter_category = $cat['id'];
                break;
            }
        }
    } else {
        // It's an ID, get the slug
        foreach ($categories_list as $cat) {
            if ($cat['id'] == $filter_category) {
                $category_slug = $cat['slug'];
                break;
            }
        }
    }
}

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
        JOIN categories c ON ac.category_id = c.id";

$sql .= " WHERE a.status = 'published'";
$params = [];

// Filter by specified category if provided, otherwise filter by all sambayanihan categories
if ($filter_category && is_numeric($filter_category)) {
    $sql .= " AND ac.category_id = ?";
    $params[] = $filter_category;
} else if ($category_slug) {
    $sql .= " AND c.slug = ?";
    $params[] = $category_slug;
} else {
    $sql .= " AND c.slug IN ('sambayanihan', 'sambayanihan_client', 'sambayanihan_employees')";
}

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
$stmt->execute($params);
$sambayanihan_articles = $stmt->fetchAll();

// Determine the page title based on the filter
$dynamic_page_title = "SAMBAYANIHAN"; // Default title
if ($filter_category) {
    foreach ($categories_list as $cat) {
        if ($cat['id'] == $filter_category) {
            $dynamic_page_title = "SAMBAYANIHAN " . sanitize($cat['name']);
            break;
        }
    }
}

?>

<main>
    <section class="news-grid-section">
        <h2 class="news-page-title"><?php echo $dynamic_page_title; ?></h2>
        <!-- Filter Control -->
        <form method="get" class="news-filters">
            <label for="filter-category">Filter by:</label>
            <select id="filter-category" name="category" onchange="this.form.submit()">
                <option value="">All SAMBAYANIHAN</option>
                <?php foreach ($categories_list as $cat): ?>
                    <?php if ($cat['slug'] != 'sambayanihan'): ?>
                    <option value="<?php echo $cat['slug']; ?>" <?php echo $category_slug == $cat['slug'] ? 'selected' : ''; ?>>
                        <?php echo sanitize($cat['name']); ?>
                    </option>
                    <?php endif; ?>
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

        <!-- Articles Grid -->
        <div class="articles-grid">
            <?php if (count($sambayanihan_articles) > 0): ?>
                <?php foreach ($sambayanihan_articles as $art): ?>
                    <?php
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
            <?php else: ?>
                <div class="no-articles-message">
                    <p>No SAMBAYANIHAN activities found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the form elements
    const filterForm = document.querySelector('.news-filters');
    const categorySelect = document.getElementById('filter-category');
    const sortSelect = document.getElementById('sort-order');
    
    // Add event listeners to form controls
    categorySelect.addEventListener('change', function() {
        // Reset the sort to default when changing category
        sortSelect.value = 'published_at_desc';
        filterForm.submit();
    });
    
    sortSelect.addEventListener('change', function() {
        // Keep the current category when changing sort
        filterForm.submit();
    });
});
</script>

<?php include 'includes/footer.php'; ?> 