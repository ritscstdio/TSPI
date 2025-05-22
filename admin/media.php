<?php
$page_title = "Media Library";
$body_class = "admin-media-library-page";
require_once '../includes/config.php';
require_admin_login();

$current_user = get_admin_user();

$stmt = $pdo->query("SELECT m.*, u.name as uploader_name FROM media m JOIN administrators u ON m.uploaded_by = u.id ORDER BY m.uploaded_at DESC");
$media_items = $stmt->fetchAll();
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
                    <h1>Media Library</h1>
                    <div class="search-container">
                        <input type="search" id="liveSearchMedia" class="form-control" placeholder="Search by Filename in current view...">
                    </div>
                </div>
                <?php if ($msg = get_flash_message()): ?>
                    <div class="message"><?php echo $msg; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="mediaTable">
                        <thead>
                            <tr><th>ID</th><th>Preview</th><th>File</th><th>Type</th><th>Uploaded By</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody id="mediaTableBody">
                            <?php if (empty($media_items)): ?>
                                <tr><td colspan="7">No media found.</td></tr>
                            <?php else: foreach ($media_items as $m): ?>
                                <tr>
                                    <td><?php echo $m['id']; ?></td>
                                    <td><?php if (strpos($m['mime_type'],'image/')===0): ?><img src="<?php echo SITE_URL.'/'.$m['file_path']; ?>" alt="Preview" class="media-preview-thumb" /><?php endif; ?></td>
                                    <td><a href="<?php echo SITE_URL.'/'.$m['file_path']; ?>" target="_blank"><?php echo basename($m['file_path']); ?></a></td>
                                    <td><?php echo $m['mime_type']; ?></td>
                                    <td><?php echo sanitize($m['uploader_name']); ?></td>
                                    <td><?php echo date('M j, Y',strtotime($m['uploaded_at'])); ?></td>
                                    <td class="actions">
                                        <a href="edit-media.php?id=<?php echo $m['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="edit-media.php?id=<?php echo $m['id']; ?>&action=delete" class="btn-icon delete-btn" data-confirm="Delete this media?"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <a href="add-media.php" class="fab-add-button">
                <i class="fas fa-upload"></i> Upload Media
            </a>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html> 