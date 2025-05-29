<?php
$page_title = "Fix Image URLs";
$body_class = "admin-fix-urls";
require_once '../includes/config.php';
require_admin_login();

$message = '';
$fixed_count = 0;

// This function will extract just the path part of a URL
function extract_relative_path($url) {
    if (preg_match('#^https?://[^/]+/(.+)$#i', $url, $matches)) {
        return $matches[1]; 
    }
    return $url;
}

// Fix URLs if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    
    try {
        // Fix content thumbnails
        $stmt = $pdo->prepare("SELECT id, thumbnail FROM content WHERE thumbnail LIKE 'http%'");
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        foreach ($items as $item) {
            $original = $item['thumbnail'];
            $fixed = extract_relative_path($original);
            
            $update = $pdo->prepare("UPDATE content SET thumbnail = ? WHERE id = ?");
            $update->execute([$fixed, $item['id']]);
            
            $fixed_count++;
        }
        
        // Fix media paths
        $stmt = $pdo->prepare("SELECT id, file_path FROM media WHERE file_path LIKE 'http%'");
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        foreach ($items as $item) {
            $original = $item['file_path'];
            $fixed = extract_relative_path($original);
            
            $update = $pdo->prepare("UPDATE media SET file_path = ? WHERE id = ?");
            $update->execute([$fixed, $item['id']]);
            
            $fixed_count++;
        }
        
        $pdo->commit();
        $message = "Fixed $fixed_count URLs in the database.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

// Count how many URLs need fixing
$stmt = $pdo->prepare("SELECT COUNT(*) FROM content WHERE thumbnail LIKE 'http%'");
$stmt->execute();
$content_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM media WHERE file_path LIKE 'http%'");
$stmt->execute();
$media_count = $stmt->fetchColumn();

$total_count = $content_count + $media_count;
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
                    <h1>Fix Image URLs</h1>
                    <a href="dashboard.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="message success">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>URL Fixer Utility</h2>
                    </div>
                    <div class="admin-card-body">
                        <p>This tool will find and fix all image URLs in the database that contain full URLs (like http://localhost) and convert them to relative paths.</p>
                        
                        <div class="url-stats">
                            <p><strong>URLs that need fixing:</strong> <?php echo $total_count; ?></p>
                            <ul>
                                <li>Content thumbnails: <?php echo $content_count; ?></li>
                                <li>Media library: <?php echo $media_count; ?></li>
                            </ul>
                        </div>
                        
                        <?php if ($total_count > 0): ?>
                            <form method="post" action="" class="admin-form">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Fix URLs Now</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p>All URLs in the database are already in the correct format.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 