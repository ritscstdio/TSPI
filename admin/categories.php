<?php
$page_title = "Categories";
$body_class = "admin-categories-page";
require_once '../includes/config.php';
require_login();
require_role(['admin','editor']);

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
                <div class="dashboard-section">
                    <?php foreach ($groups as $label => $slugs): ?>
                        <h2><?php echo $label; ?></h2>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr><th>Name</th><th>contents</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($categories as $cat): if (in_array($cat['slug'], $slugs)): ?>
                                    <tr>
                                        <td><?php echo sanitize($cat['name']); ?></td>
                                        <td><?php echo $cat['content_count']; ?></td>
                                        <td class="actions">
                                            <a href="edit-category.php?id=<?php echo $cat['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php // 'Add Category' and delete functionality removed ?>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html> 