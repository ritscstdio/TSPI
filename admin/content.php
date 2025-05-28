<?php
$page_title = "Content";
$body_class = "admin-content-page";
require_once '../includes/config.php';
require_admin_login();

// Delete content if requested
if (isset($_GET['delete'])) {
    $content_id = (int) $_GET['delete'];
    
    // Delete content and all related records
    $pdo->beginTransaction();
    
    try {
        // Delete content tags
        $stmt = $pdo->prepare("DELETE FROM content_tags WHERE content_id = ?");
        $stmt->execute([$content_id]);
        
        // Delete content categories
        $stmt = $pdo->prepare("DELETE FROM content_categories WHERE content_id = ?");
        $stmt->execute([$content_id]);
        
        // Delete comments
        $stmt = $pdo->prepare("DELETE FROM comments WHERE content_id = ?");
        $stmt->execute([$content_id]);
        
        // Delete the content
        $stmt = $pdo->prepare("DELETE FROM content WHERE id = ?");
        $stmt->execute([$content_id]);
        
        $pdo->commit();
        $_SESSION['message'] = "Content deleted successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }
    
    redirect('/admin/content.php');
}

// Pagination
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$items_per_page = ITEMS_PER_PAGE;
$offset = ($current_page - 1) * $items_per_page;

// Fetch categories for filter dropdown
$categories_list = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$filter_category = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Define status filter options
$status_filter_options = [
    '' => 'All Statuses',
    'draft' => 'Draft',
    'published' => 'Published',
    'archived' => 'Archived'
];
$filter_status = isset($_GET['status_filter']) && array_key_exists($_GET['status_filter'], $status_filter_options)
                    ? $_GET['status_filter']
                    : '';

// Define sort options
$sort_options = [
    'published_at_desc' => 'Date (Newest First)',
    'published_at_asc' => 'Date (Oldest First)',
    'title_asc' => 'Title (A-Z)',
    'title_desc' => 'Title (Z-A)',
    'votes_desc' => 'Votes (Highest First)',
    'votes_asc' => 'Votes (Lowest First)',
];
$current_sort_order = isset($_GET['sort_order']) && array_key_exists($_GET['sort_order'], $sort_options) 
                        ? $_GET['sort_order'] 
                        : 'published_at_desc'; // Default sort order

// Build ORDER BY clause
$order_by_clause = "ORDER BY ";
switch ($current_sort_order) {
    case 'published_at_asc':
        $order_by_clause .= "a.published_at ASC";
        break;
    case 'title_asc':
        $order_by_clause .= "a.title ASC";
        break;
    case 'title_desc':
        $order_by_clause .= "a.title DESC";
        break;
    case 'votes_desc':
        $order_by_clause .= "vote_count DESC";
        break;
    case 'votes_asc':
        $order_by_clause .= "vote_count ASC";
        break;
    case 'published_at_desc':
    default:
        $order_by_clause .= "a.published_at DESC";
        break;
}

// Build WHERE clauses
$where_clauses = [];
$params = [];

if ($filter_category) {
    $where_clauses[] = "ac.category_id = ?";
    $params[] = $filter_category;
}

if ($filter_status) {
    $where_clauses[] = "a.status = ?";
    $params[] = $filter_status;
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Get total contents count
$count_sql = "SELECT COUNT(DISTINCT a.id) FROM content a";
if ($filter_category) {
    $count_sql .= " JOIN content_categories ac ON a.id = ac.content_id";
}
$count_sql .= $where_sql;
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_contents = $stmt->fetchColumn();
$total_pages = ceil($total_contents / $items_per_page);

// Get contents for current page
$contents_sql = "SELECT DISTINCT a.*, u.name as author_name, u.email as author_email, u.role as author_role, 
                    (SELECT COUNT(*) FROM content_votes WHERE content_id = a.id) as vote_count 
                FROM content a 
                JOIN administrators u ON a.author_id = u.id";
if ($filter_category) {
    $contents_sql .= " JOIN content_categories ac ON a.id = ac.content_id";
}
$contents_sql .= $where_sql;
$contents_sql .= " $order_by_clause LIMIT ? OFFSET ?";

$current_page_params = array_merge($params, [$items_per_page, $offset]);

$stmt = $pdo->prepare($contents_sql);
$stmt->execute($current_page_params);
$contents = $stmt->fetchAll();

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
    <style>
        /* Fix spacing issue at the top */
        .admin-main {
            padding-top: 0 !important;
        }
        
        /* Improve filter form responsiveness */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .filter-form label {
            margin: 0;
            white-space: nowrap;
        }
        
        .filter-form select {
            flex: 1;
            min-width: 150px;
        }
        
        .filter-form button {
            margin-left: auto;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-form label {
                margin-bottom: 5px;
            }
            
            .filter-form select {
                width: 100%;
            }
            
            .filter-form button {
                width: 100%;
                margin-top: 10px;
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="dashboard-container">
                <div class="page-header">
                    <h1>Content</h1>
                    <div class="search-container" style="margin-left: auto;">
                        <input type="search" id="liveSearchcontents" class="form-control" placeholder="Search by Title...">
                    </div>
                </div>
                
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <!-- Category Filter Form -->
                <form method="get" class="filter-form">
                    <div class="filter-item">
                        <label for="category-filter">Filter by Category:</label>
                        <select id="category-filter" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories_list as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>><?php echo sanitize($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label for="status-filter">Filter by Status:</label>
                        <select id="status-filter" name="status_filter">
                            <?php foreach ($status_filter_options as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo $filter_status == $key ? 'selected' : ''; ?>><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label for="sort-order">Sort by:</label>
                        <select id="sort-order" name="sort_order">
                            <?php foreach ($sort_options as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo $current_sort_order == $key ? 'selected' : ''; ?>><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-light">Apply Filters</button>
                </form>

                <div class="dashboard-section">
                    <div class="table-responsive">
                        <table id="contentsTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Votes</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="contentsTableBody">
                                <?php if (empty($contents)): ?>
                                    <tr>
                                        <td colspan="5">No contents found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($contents as $content): ?>
                                        <tr>
                                            <td><?php echo sanitize($content['title']); ?></td>
                                            <td class="content-author-name" 
                                                data-name="<?php echo sanitize($content['author_name']); ?>" 
                                                data-email="<?php echo sanitize($content['author_email'] ?? 'N/A'); ?>" 
                                                data-role="<?php echo sanitize(ucfirst($content['author_role'] ?? 'N/A')); ?>"
                                                style="cursor: pointer; text-decoration: underline; color: var(--primary-blue);">
                                                <?php echo sanitize($content['author_name']); ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $content['status']; ?>">
                                                    <?php echo ucfirst($content['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo (int)$content['vote_count']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($content['published_at'])); ?></td>
                                            <td class="actions">
                                                <a href="edit-content.php?id=<?php echo $content['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="../content.php?slug=<?php echo $content['slug']; ?>" target="_blank" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="contents.php?delete=<?php echo $content['id']; ?>" class="btn-icon delete-btn" title="Delete" data-confirm="Are you sure you want to delete this content?"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php 
                            $base_url = "?page=";
                            $query_params = [];
                            if ($filter_category) $query_params['category'] = $filter_category;
                            if ($filter_status) $query_params['status_filter'] = $filter_status;
                            if ($current_sort_order !== 'published_at_desc') $query_params['sort_order'] = $current_sort_order;
                            $extra_params = http_build_query($query_params);
                            ?>

                            <?php if ($current_page > 1): ?>
                                <a href="<?php echo $base_url . ($current_page - 1) . ($extra_params ? '&' . $extra_params : ''); ?>" class="pagination-item"><i class="fas fa-chevron-left"></i></a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="<?php echo $base_url . $i . ($extra_params ? '&' . $extra_params : ''); ?>" class="pagination-item <?php echo $i === $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="<?php echo $base_url . ($current_page + 1) . ($extra_params ? '&' . $extra_params : ''); ?>" class="pagination-item"><i class="fas fa-chevron-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <a href="add-content.php" class="fab-add-button">
                <i class="fas fa-plus"></i> Add Content
            </a>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
