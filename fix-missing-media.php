<?php
require_once 'includes/config.php';
require_once 'includes/admin_auth.php';
require_admin_login(); // Only admins can run this script

// Function to check if a file exists on the server or on GitHub
function check_file_exists($filename, $type = 'media') {
    $server_path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $type . '/' . $filename;
    $github_path = 'https://raw.githubusercontent.com/ritscstdio/TSPI/main/uploads/' . $type . '/' . $filename;
    
    $server_exists = file_exists($server_path);
    $github_exists = @file_get_contents($github_path, false, stream_context_create([
        'http' => ['method' => 'HEAD']
    ])) !== false;
    
    return [
        'server' => $server_exists,
        'github' => $github_exists
    ];
}

// Get all media files from the database
$stmt = $pdo->prepare("SELECT id, file_path FROM media");
$stmt->execute();
$media_items = $stmt->fetchAll();

// Check content thumbnails as well
$stmt = $pdo->prepare("SELECT id, thumbnail FROM content WHERE thumbnail IS NOT NULL AND thumbnail != ''");
$stmt->execute();
$content_items = $stmt->fetchAll();

// Process fix if requested
$fixed_count = 0;
if (isset($_POST['fix_missing']) && $_POST['fix_missing'] === 'true') {
    $pdo->beginTransaction();
    try {
        foreach ($media_items as $item) {
            $filename = basename($item['file_path']);
            $exists = check_file_exists($filename, 'media');
            
            if (!$exists['server'] && $exists['github']) {
                // Update to use GitHub URL
                $github_url = 'https://raw.githubusercontent.com/ritscstdio/TSPI/main/uploads/media/' . $filename;
                $stmt = $pdo->prepare("UPDATE media SET file_path = ? WHERE id = ?");
                $stmt->execute([$github_url, $item['id']]);
                $fixed_count++;
            }
        }
        
        foreach ($content_items as $item) {
            $filename = basename($item['thumbnail']);
            $exists = check_file_exists($filename, 'media');
            
            if (!$exists['server'] && $exists['github']) {
                // Update to use GitHub URL
                $github_url = 'https://raw.githubusercontent.com/ritscstdio/TSPI/main/uploads/media/' . $filename;
                $stmt = $pdo->prepare("UPDATE content SET thumbnail = ? WHERE id = ?");
                $stmt->execute([$github_url, $item['id']]);
                $fixed_count++;
            }
        }
        
        $pdo->commit();
        $_SESSION['message'] = "Fixed $fixed_count media references to use GitHub URLs";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error fixing media references: " . $e->getMessage();
    }
}

// Add CSS for nicer display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Missing Media Files</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .missing { color: #e74c3c; font-weight: bold; }
        .exists { color: #2ecc71; }
        img.preview { max-width: 100px; max-height: 100px; }
        .fix-form { margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
        .btn { padding: 10px 15px; background: #3498db; color: white; border: none; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .message { padding: 10px; margin: 10px 0; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <h1>Fix Missing Media Files</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="fix-form">
        <form method="post">
            <p>This tool checks if media files exist on the server and can update references to use GitHub URLs as fallback.</p>
            <input type="hidden" name="fix_missing" value="true">
            <button type="submit" class="btn">Fix Missing Media References</button>
        </form>
    </div>
    
    <h2>Media Files Status</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Preview</th>
                <th>Filename</th>
                <th>Server Status</th>
                <th>GitHub Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($media_items as $item): 
                $filename = basename($item['file_path']);
                $exists = check_file_exists($filename, 'media');
                $full_url = SITE_URL . '/uploads/media/' . $filename;
            ?>
            <tr>
                <td><?php echo $item['id']; ?></td>
                <td><img src="<?php echo $full_url; ?>" alt="Preview" class="preview"></td>
                <td><?php echo $filename; ?></td>
                <td class="<?php echo $exists['server'] ? 'exists' : 'missing'; ?>">
                    <?php echo $exists['server'] ? 'Exists' : 'Missing'; ?>
                </td>
                <td class="<?php echo $exists['github'] ? 'exists' : 'missing'; ?>">
                    <?php echo $exists['github'] ? 'Available' : 'Not Available'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <h2>Content Thumbnails Status</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Preview</th>
                <th>Filename</th>
                <th>Server Status</th>
                <th>GitHub Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($content_items as $item): 
                if (empty($item['thumbnail'])) continue;
                $filename = basename($item['thumbnail']);
                $exists = check_file_exists($filename, 'media');
                $full_url = SITE_URL . '/uploads/media/' . $filename;
            ?>
            <tr>
                <td><?php echo $item['id']; ?></td>
                <td><img src="<?php echo $full_url; ?>" alt="Preview" class="preview"></td>
                <td><?php echo $filename; ?></td>
                <td class="<?php echo $exists['server'] ? 'exists' : 'missing'; ?>">
                    <?php echo $exists['server'] ? 'Exists' : 'Missing'; ?>
                </td>
                <td class="<?php echo $exists['github'] ? 'exists' : 'missing'; ?>">
                    <?php echo $exists['github'] ? 'Available' : 'Not Available'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p>
        <a href="admin/index.php">Back to Admin Dashboard</a>
    </p>
</body>
</html> 