<?php
// Script to fix URLs in the database that reference localhost
require_once 'includes/config.php';
require_once 'includes/admin_auth.php';
require_admin_login(); // Only admins can run this script

// Function to fix a URL to remove localhost reference if needed
function fix_url($url) {
    if (strpos($url, 'localhost/TSPI') !== false) {
        // Extract the path part after localhost/TSPI/
        $relative_path = preg_replace('#^.*?/TSPI/#', '', $url);
        return $relative_path; // Just store the relative path
    }
    return $url; // Return as is if it's not a localhost URL
}

// Start transaction for safety
$pdo->beginTransaction();

try {
    // Fix media table paths
    $stmt = $pdo->prepare("SELECT id, file_path FROM media WHERE file_path LIKE '%localhost%'");
    $stmt->execute();
    $media_items = $stmt->fetchAll();
    
    echo "<h2>Fixing Media Paths</h2>";
    
    foreach ($media_items as $item) {
        $fixed_path = fix_url($item['file_path']);
        $update_stmt = $pdo->prepare("UPDATE media SET file_path = ? WHERE id = ?");
        $update_stmt->execute([$fixed_path, $item['id']]);
        
        echo "Fixed: {$item['file_path']} → {$fixed_path}<br>";
    }
    
    // Fix content thumbnails
    $stmt = $pdo->prepare("SELECT id, thumbnail FROM content WHERE thumbnail LIKE '%localhost%'");
    $stmt->execute();
    $content_items = $stmt->fetchAll();
    
    echo "<h2>Fixing Content Thumbnails</h2>";
    
    foreach ($content_items as $item) {
        $fixed_path = fix_url($item['thumbnail']);
        $update_stmt = $pdo->prepare("UPDATE content SET thumbnail = ? WHERE id = ?");
        $update_stmt->execute([$fixed_path, $item['id']]);
        
        echo "Fixed: {$item['thumbnail']} → {$fixed_path}<br>";
    }
    
    // Commit the changes if all went well
    $pdo->commit();
    
    echo "<h2>Done!</h2>";
    echo "<p>All localhost URLs have been fixed. You can now <a href='index.php'>return to the homepage</a>.</p>";
    
} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    echo "<h2>Error</h2>";
    echo "<p>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 