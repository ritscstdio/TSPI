<?php
$page_title = "Categories";
$body_class = "admin-categories-page";
require_once '../includes/config.php';
require_admin_login();

// Fetch categories
$stmt = $pdo->query("SELECT c.*, COUNT(ac.content_id) AS content_count FROM categories c LEFT JOIN content_categories ac ON c.id = ac.category_id GROUP BY c.id ORDER BY c.name");
$categories = $stmt->fetchAll();
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
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            <div class="dashboard-container">
                <div class="page-header">
                    <h1>Categories</h1>
                </div>
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php
                // Group categories according to front-end navbar structure
                $groups = [
                    'Awards & Recognitions' => ['cli_awards','org_awards'],
                    'Our Impact' => ['cli_stories','ann_reports','sambayanihan','sambayanihan_client','sambayanihan_employees'],
                    'News' => ['tspi_news'],
                    'Resources & Publications' => ['ann_reports','aud_financial','newsletter'],
                    'Resources & Corporate Governance' => ['leg_documents','reg_registrations','gov_framework'],
                ];
                // Collect any uncategorized
                $all_slugs = [];
                foreach ($groups as $slugs) { $all_slugs = array_merge($all_slugs, $slugs); }
                $groups['Other'] = array_values(array_filter(array_column($categories, 'slug'), fn($s) => !in_array($s, $all_slugs)));
                ?>
                <div class="dashboard-section category-groups-container">
                    <?php if (empty($categories)): ?>
                        <p>No categories found.</p>
                    <?php else: ?>
                        <?php foreach ($groups as $label => $slugs): ?>
                            <?php
                            $categories_in_group = array_filter($categories, fn($cat) => in_array($cat['slug'], $slugs));
                            if (empty($categories_in_group) && $label !== 'Other') continue; // Skip empty predefined groups, but always show 'Other' if it exists for potential uncategorized items
                            if ($label === 'Other' && empty($categories_in_group)) continue; // Skip 'Other' if it's also empty
                            ?>
                            <div class="category-group-dropdown">
                                <button class="dropdown-toggle" type="button">
                                    <span><?php echo sanitize($label); ?></span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="dropdown-content" style="display: none;">
                                    <?php if (empty($categories_in_group)): ?>
                                        <p class="empty-group-message">No categories in this group.</p>
                                    <?php else: ?>
                                        <ul>
                                            <?php foreach ($categories_in_group as $cat): ?>
                                                <li>
                                                    <span class="category-name"><?php echo sanitize($cat['name']); ?></span>
                                                    <span class="category-details">
                                                        (<?php echo $cat['content_count']; ?> content<?php echo ($cat['content_count'] != 1) ? 's' : ''; ?>)
                                                    </span>
                                                    <span class="actions">
                                                        <a href="edit-category.php?id=<?php echo $cat['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php // 'Add Category' and delete functionality removed ?>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.category-group-dropdown .dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                this.classList.toggle('active'); // For icon rotation
                const content = this.nextElementSibling;
                if (content.style.display === 'block') {
                    content.style.display = 'none';
                } else {
                    content.style.display = 'block';
                }
            });
        });
    });
    </script>
</body>
</html> 