<?php
require_once 'includes/config.php';
require_once 'includes/admin_auth.php';
require_admin_login(); // Only admins can run this script

// Function to check if a file exists on GitHub
function file_exists_on_github($filename, $type = 'media') {
    $github_path = 'https://raw.githubusercontent.com/ritscstdio/TSPI/main/uploads/' . $type . '/' . $filename;
    return @file_get_contents($github_path, false, stream_context_create([
        'http' => ['method' => 'HEAD']
    ])) !== false;
}

// Function to download a file from GitHub
function download_file_from_github($filename, $type = 'media') {
    $github_path = 'https://raw.githubusercontent.com/ritscstdio/TSPI/main/uploads/' . $type . '/' . $filename;
    $local_path = __DIR__ . '/uploads/' . $type . '/' . $filename;
    
    // Create directory if it doesn't exist
    if (!is_dir(dirname($local_path))) {
        mkdir(dirname($local_path), 0775, true);
    }
    
    $content = @file_get_contents($github_path);
    if ($content === false) {
        return false;
    }
    
    return file_put_contents($local_path, $content) !== false;
}

// Get all media files from the database
$stmt = $pdo->prepare("SELECT id, file_path FROM media");
$stmt->execute();
$media_items = $stmt->fetchAll();

// Check content thumbnails as well
$stmt = $pdo->prepare("SELECT id, thumbnail FROM content WHERE thumbnail IS NOT NULL AND thumbnail != ''");
$stmt->execute();
$content_items = $stmt->fetchAll();

// Process file download if requested
$downloaded_count = 0;
$failed_count = 0;
if (isset($_POST['download_files']) && $_POST['download_files'] === 'true') {
    // Process media files
    foreach ($media_items as $item) {
        $filename = basename($item['file_path']);
        $server_path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/media/' . $filename;
        
        // Skip if file already exists on server
        if (file_exists($server_path)) {
            continue;
        }
        
        // Download from GitHub
        if (download_file_from_github($filename, 'media')) {
            $downloaded_count++;
        } else {
            $failed_count++;
        }
    }
    
    // Process content thumbnails
    foreach ($content_items as $item) {
        if (empty($item['thumbnail'])) continue;
        $filename = basename($item['thumbnail']);
        $server_path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/media/' . $filename;
        
        // Skip if file already exists on server or was already processed
        if (file_exists($server_path)) {
            continue;
        }
        
        // Download from GitHub
        if (download_file_from_github($filename, 'media')) {
            $downloaded_count++;
        } else {
            $failed_count++;
        }
    }
    
    $_SESSION['message'] = "Downloaded $downloaded_count files successfully. $failed_count files failed to download.";
}

// Add CSS for nicer display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Missing Media</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .missing { color: #e74c3c; font-weight: bold; }
        .exists { color: #2ecc71; }
        img.preview { max-width: 100px; max-height: 100px; }
        .action-form { margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
        .btn { padding: 10px 15px; background: #3498db; color: white; border: none; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .message { padding: 10px; margin: 10px 0; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .tool-description { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Upload Missing Media</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    
    <div class="tool-description">
        <p>This tool checks for media files that exist in your GitHub repository but are missing from this server, 
        and downloads them to your server's uploads folder.</p>
    </div>
    
    <div class="action-form">
        <form method="post">
            <input type="hidden" name="download_files" value="true">
            <button type="submit" class="btn">Download Missing Files from GitHub</button>
        </form>
    </div>
    
    <h2>Missing Media Files</h2>
    <table>
        <thead>
            <tr>
                <th>Filename</th>
                <th>Server Status</th>
                <th>GitHub Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $processed_files = [];
            foreach ($media_items as $item): 
                $filename = basename($item['file_path']);
                // Skip if already processed
                if (in_array($filename, $processed_files)) continue;
                $processed_files[] = $filename;
                
                $server_path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/media/' . $filename;
                $server_exists = file_exists($server_path);
                $github_exists = file_exists_on_github($filename, 'media');
                
                // Only show missing files
                if ($server_exists) continue;
            ?>
            <tr>
                <td><?php echo $filename; ?></td>
                <td class="<?php echo $server_exists ? 'exists' : 'missing'; ?>">
                    <?php echo $server_exists ? 'Exists' : 'Missing'; ?>
                </td>
                <td class="<?php echo $github_exists ? 'exists' : 'missing'; ?>">
                    <?php echo $github_exists ? 'Available' : 'Not Available'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php foreach ($content_items as $item): 
                if (empty($item['thumbnail'])) continue;
                $filename = basename($item['thumbnail']);
                // Skip if already processed
                if (in_array($filename, $processed_files)) continue;
                $processed_files[] = $filename;
                
                $server_path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/media/' . $filename;
                $server_exists = file_exists($server_path);
                $github_exists = file_exists_on_github($filename, 'media');
                
                // Only show missing files
                if ($server_exists) continue;
            ?>
            <tr>
                <td><?php echo $filename; ?></td>
                <td class="<?php echo $server_exists ? 'exists' : 'missing'; ?>">
                    <?php echo $server_exists ? 'Exists' : 'Missing'; ?>
                </td>
                <td class="<?php echo $github_exists ? 'exists' : 'missing'; ?>">
                    <?php echo $github_exists ? 'Available' : 'Not Available'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p>
        <a href="fix-missing-media.php">Check Media Status</a> | 
        <a href="admin/index.php">Back to Admin Dashboard</a>
    </p>
</body>
</html> 