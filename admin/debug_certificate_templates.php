<?php
/**
 * Debug Certificate Templates
 * 
 * This script checks the availability and readability of certificate templates
 * and attempts to diagnose issues with PDF generation.
 */

require_once '../includes/config.php';
require_admin_login();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check template directory
$template_dir = '../templates';
$templates = [];
$plans = ['BLIP', 'LPIP', 'LMIP'];

echo "<h1>Certificate Template Debugging</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .debug-section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
    .file-exists { color: green; }
    .file-missing { color: red; }
    .readable { color: green; }
    .not-readable { color: red; }
    .test-success { background-color: #d4edda; padding: 10px; border-radius: 5px; }
    .test-failure { background-color: #f8d7da; padding: 10px; border-radius: 5px; }
    pre { background-color: #f8f9fa; padding: 10px; overflow: auto; }
</style>";

// 1. Check if templates exist
echo "<div class='debug-section'>";
echo "<h2>1. Template File Check</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Plan</th><th>Template Path</th><th>Exists</th><th>Readable</th><th>Size</th><th>Last Modified</th></tr>";

foreach ($plans as $plan) {
    $template_path = $template_dir . '/Membership-Certificate-for-Basic-Life-Insurance-Plan-' . $plan . '.pdf';
    $exists = file_exists($template_path);
    $readable = is_readable($template_path);
    $size = $exists ? filesize($template_path) : 'N/A';
    $modified = $exists ? date("Y-m-d H:i:s", filemtime($template_path)) : 'N/A';
    
    $templates[$plan] = [
        'path' => $template_path,
        'exists' => $exists,
        'readable' => $readable,
        'size' => $size,
        'modified' => $modified
    ];
    
    echo "<tr>";
    echo "<td>{$plan}</td>";
    echo "<td>{$template_path}</td>";
    echo "<td class='" . ($exists ? 'file-exists' : 'file-missing') . "'>" . ($exists ? 'Yes' : 'No') . "</td>";
    echo "<td class='" . ($readable ? 'readable' : 'not-readable') . "'>" . ($readable ? 'Yes' : 'No') . "</td>";
    echo "<td>" . ($exists ? formatBytes($size) : 'N/A') . "</td>";
    echo "<td>{$modified}</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// 2. Test PDF generation for each template
echo "<div class='debug-section'>";
echo "<h2>2. PDF Generation Test</h2>";
echo "<p>Testing PDF generation for each plan using a generic application...</p>";

// Create a test directory
$test_dir = '../uploads/temp';
if (!is_dir($test_dir)) {
    mkdir($test_dir, 0755, true);
}

// Check if the directory exists and is writable
if (!is_dir($test_dir) || !is_writable($test_dir)) {
    echo "<p class='test-failure'>Error: Test directory {$test_dir} doesn't exist or is not writable.</p>";
    $test_dir = sys_get_temp_dir();
    echo "<p>Falling back to system temp directory: {$test_dir}</p>";
}

// Get a sample application ID for testing
$stmt = $pdo->query("SELECT id FROM members_information LIMIT 1");
$sample_app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sample_app) {
    echo "<p class='test-failure'>No applications found in the database for testing.</p>";
} else {
    $id = $sample_app['id'];
    
    foreach ($plans as $plan) {
        echo "<h3>Testing {$plan} Certificate Generation</h3>";
        
        // Skip if template doesn't exist
        if (!$templates[$plan]['exists']) {
            echo "<p class='test-failure'>Cannot test {$plan} - template file doesn't exist.</p>";
            continue;
        }
        
        // Test generation
        $test_file = $test_dir . '/test_certificate_' . $id . '_' . $plan . '_' . time() . '.pdf';
        
        echo "<p>Attempting to generate PDF to: {$test_file}</p>";
        
        // Enable output buffering
        ob_start();
        
        // Set up GET parameters
        $_GET['id'] = $id;
        $_GET['plan'] = $plan;
        $_GET['mode'] = 'save';
        $_GET['output_path'] = $test_file;
        
        $oldErrorLevel = error_reporting();
        error_reporting($oldErrorLevel & ~E_DEPRECATED);
        
        // Execute with error handling
        try {
            $result = require 'generate_certificate_without_exit.php';
            $success = file_exists($test_file);
            
            if ($success) {
                echo "<p class='test-success'>Successfully generated {$plan} certificate! File size: " . formatBytes(filesize($test_file)) . "</p>";
            } else {
                echo "<p class='test-failure'>Failed to generate {$plan} certificate. Return value: " . var_export($result, true) . "</p>";
                if (isset($pdf_error)) {
                    echo "<p>Error message: {$pdf_error}</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p class='test-failure'>Exception while generating {$plan} certificate: " . $e->getMessage() . "</p>";
        }
        
        // Clean up
        error_reporting($oldErrorLevel);
        ob_end_clean();
    }
}

// 3. Template content check with FPDI
echo "<div class='debug-section'>";
echo "<h2>3. PDF Template Content Check</h2>";

// Include TCPDF and FPDI
require_once '../vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

foreach ($plans as $plan) {
    echo "<h3>{$plan} Template Analysis</h3>";
    
    if (!$templates[$plan]['exists']) {
        echo "<p class='test-failure'>Cannot analyze {$plan} - template file doesn't exist.</p>";
        continue;
    }
    
    try {
        // Create new PDF instance
        $pdf = new Fpdi();
        
        // Try to load and analyze the template
        $pageCount = $pdf->setSourceFile($templates[$plan]['path']);
        
        echo "<p class='test-success'>Successfully loaded template. Page count: {$pageCount}</p>";
        
        // Try to import the first page to check for corruption
        $tplId = $pdf->importPage(1);
        echo "<p class='test-success'>Successfully imported first page from template.</p>";
        
    } catch (Exception $e) {
        echo "<p class='test-failure'>Failed to analyze template: " . $e->getMessage() . "</p>";
    }
}

echo "</div>";

// Helper function to format bytes
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
} 