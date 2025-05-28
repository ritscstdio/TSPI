<?php
require_once '../includes/config.php';
require_admin_login();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get application ID and plan
$id = 34; // Hardcoded for debugging
$plan = 'LPIP'; // Specifically test LPIP

// Fetch application
$stmt = $pdo->prepare("SELECT * FROM members_information WHERE id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    die("Application not found");
}

echo "<h2>Debugging Certificate Generation for Plan: {$plan}</h2>";

// Check MC number for this plan
$planMcField = strtolower($plan) . '_mc';
echo "<p>MC Number for {$plan}: " . ($application[$planMcField] ?? 'NOT FOUND') . "</p>";

// Check if template exists
$templatePath = '';
switch (strtoupper($plan)) {
    case 'BLIP':
        $templatePath = '../templates/Membership-Certificate-for-Basic-Life-Insurance-Plan-BLIP.pdf';
        break;
    case 'LPIP':
        $templatePath = '../templates/Membership-Certificate-for-Basic-Life-Insurance-Plan-LPIP.pdf';
        break;
    case 'LMIP':
        $templatePath = '../templates/Membership-Certificate-for-Basic-Life-Insurance-Plan-LMIP.pdf';
        break;
    default:
        $templatePath = '../templates/membership_template.pdf';
}

echo "<p>Template Path: {$templatePath}</p>";
echo "<p>Template Exists: " . (file_exists($templatePath) ? 'YES' : 'NO') . "</p>";

// Check temp directory
$temp_dir = '../uploads/temp';
echo "<p>Temp Directory: {$temp_dir}</p>";
echo "<p>Temp Directory Exists: " . (is_dir($temp_dir) ? 'YES' : 'NO') . "</p>";
echo "<p>Temp Directory Writable: " . (is_writable($temp_dir) ? 'YES' : 'NO') . "</p>";

// Try to create a test file in the temp directory
$test_file = $temp_dir . '/test_write.txt';
$write_success = file_put_contents($test_file, 'Test write');
echo "<p>Test Write to Temp Directory: " . ($write_success !== false ? 'SUCCESS' : 'FAILED') . "</p>";

// Check tcpdf/fpdi installed
echo "<p>TCPDF/FPDI Installed: " . (class_exists('setasign\Fpdi\Tcpdf\Fpdi') ? 'YES' : 'NO') . "</p>";

// Now try to generate the certificate directly
$cert_pdf_path = $temp_dir . '/debug_certificate_' . $id . '_' . $plan . '.pdf';
echo "<h3>Attempting to Generate Certificate...</h3>";
echo "<p>Output Path: {$cert_pdf_path}</p>";

try {
    // Set up variables for certificate generation
    $_GET['id'] = $id;
    $_GET['plan'] = $plan;
    $_GET['mode'] = 'save';
    $_GET['output_path'] = $cert_pdf_path;
    
    // Temporarily suppress deprecation warnings for TCPDF
    $oldErrorLevel = error_reporting();
    error_reporting($oldErrorLevel & ~E_DEPRECATED);
    
    // Include the certificate generator with output buffering
    ob_start();
    require_once 'generate_certificate_without_exit.php';
    $output = ob_get_clean();
    
    // Restore error reporting
    error_reporting($oldErrorLevel);
    
    // Check result
    echo "<p>Generation Result: " . (file_exists($cert_pdf_path) ? 'SUCCESS' : 'FAILED') . "</p>";
    if (!empty($output)) {
        echo "<p>Output from generator:</p>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Output the last few errors
echo "<h3>Last PHP Errors:</h3>";
$error_log = ini_get('error_log');
if (file_exists($error_log)) {
    $errors = file_get_contents($error_log);
    echo "<pre>" . htmlspecialchars(substr($errors, -5000)) . "</pre>";
} else {
    echo "<p>Error log file not found or not accessible.</p>";
}
?> 