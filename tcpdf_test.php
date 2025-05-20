<?php
// Test file to check if TCPDF is working correctly

// Display PHP extension information
echo "<h1>PHP Extensions Information</h1>";
echo "<p>GD Extension: " . (extension_loaded('gd') ? "Enabled ✅" : "Not enabled ❌") . "</p>";
echo "<p>Imagick Extension: " . (extension_loaded('imagick') ? "Enabled ✅" : "Not enabled ❌") . "</p>";
echo "<hr>";

// Check if TCPDF is available
echo "<h1>TCPDF Test</h1>";
if (!file_exists('vendor/autoload.php')) {
    echo "<p style='color:red'>Composer dependencies not installed. Please run the installation script first.</p>";
    exit;
}

// Include the TCPDF library
require_once 'vendor/autoload.php';

try {
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('TSPI CMS');
    $pdf->SetAuthor('TSPI Admin');
    $pdf->SetTitle('TCPDF Test');
    
    // Remove header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 12);
    
    // Add content
    $pdf->Cell(0, 10, 'TCPDF is working correctly!', 0, 1, 'C');
    
    // Output the PDF as a string
    $pdfData = $pdf->Output('test.pdf', 'S');
    
    echo "<p style='color:green'>TCPDF is working correctly! ✅</p>";
    echo "<p>A sample PDF was generated successfully.</p>";
    echo "<p><a href='#' onclick='window.location.href=\"tcpdf_test.php?download=1\";'>Download test PDF</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . " ❌</p>";
    echo "<p>Please make sure you have installed all the required dependencies by running the installation script.</p>";
}

// Handle download request
if (isset($_GET['download'])) {
    // Create PDF for download
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('TSPI CMS');
    $pdf->SetAuthor('TSPI Admin');
    $pdf->SetTitle('TCPDF Test');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'TCPDF is working correctly!', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
    $pdf->Output('tcpdf_test.pdf', 'D');
    exit;
}
?> 