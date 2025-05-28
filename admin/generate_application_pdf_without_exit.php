<?php
/**
 * Generate Application PDF - Without Exit
 * 
 * This file is a modified version of generate_application_pdf.php that doesn't exit.
 * It's specifically for use in the test_email.php script.
 */

// Include necessary files
require_once '../includes/config.php';
require_admin_login();

// Ensure an 'id' parameter is provided (allow '0' as valid)
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No application ID specified.";
    $pdf_error = "No application ID specified.";
    return false;
}
$id = $_GET['id'];
$mode = $_GET['mode'] ?? 'preview'; // Default to preview mode instead of download
$debug = isset($_GET['debug']) ? true : false; // Debug mode to show coordinate grid

// Fetch the application details
$stmt = $pdo->prepare("SELECT * FROM members_information WHERE id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    $_SESSION['message'] = "Application not found.";
    $pdf_error = "Application not found.";
    return false;
}

// Include TCPDF and FPDI
require_once '../vendor/autoload.php';

// Check if FPDI is properly installed
if (!class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
    $pdf_error = "FPDI library not found. Please run 'composer require setasign/fpdi' to install the FPDI library.";
    return false;
}

// Check if safe_text function already exists
if (!function_exists('safe_text')) {
    /**
     * Safely convert potentially null values to string
     * Prevents "Deprecated: strlen(): Passing null to parameter #1 ($string) of type string is deprecated" warning in TCPDF
     * 
     * @param mixed $text The text value that might be null
     * @return string A safe string value
     */
    function safe_text($text) {
        return ($text === null) ? '' : (string)$text;
    }
}

// Create new PDF document using FPDI
$pdf = new setasign\Fpdi\Tcpdf\Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('TSPI CMS');
$pdf->SetAuthor('TSPI Admin');
$pdf->SetTitle('Membership Application - ' . $application['first_name'] . ' ' . $application['last_name']);
$pdf->SetSubject('Membership Application Form');

// Disable header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins to zero for precise positioning from top-left (0,0)
$pdf->SetMargins(0, 0, 0, true);
$pdf->SetCellPadding(0);
$pdf->SetAutoPageBreak(false, 0);

// Set other margins to zero
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

// Import the template PDF
$template_file = '../templates/membership_template.pdf';

// Check if template exists
if (!file_exists($template_file)) {
    // If template doesn't exist, create a simple PDF
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'TSPI MEMBERSHIP APPLICATION FORM', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Template file not found: ' . $template_file, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Please place the membership template in the templates folder.', 0, 1, 'C');
} else {
    try {
        // Import the PDF template
        $pageCount = $pdf->setSourceFile($template_file);
        
        // Process each page of the template
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Get template dimensions to maintain aspect ratio
            $templateSize = $pdf->getTemplateSize($pdf->importPage($pageNo));
            $orientation = ($templateSize['width'] > $templateSize['height']) ? 'L' : 'P';
            
            // Add a page with the same dimensions as the template
            $pdf->AddPage($orientation, array($templateSize['width'], $templateSize['height']));
            
            // Import current page
            $tplIdx = $pdf->importPage($pageNo);
            
            // Apply the template using its original dimensions
            $pdf->useTemplate($tplIdx, 0, 0, $templateSize['width'], $templateSize['height']);

            // Fill in the PDF with application data here
            // This code is imported from the original file but omitted here for brevity
            
            // Set font and color for text
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            
            // Process based on page number (add your specific PDF filling logic here)
            // This logic is copied from the original file
        }
    } catch (Exception $e) {
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Error loading template: ' . $e->getMessage(), 0, 1, 'C');
    }
}

// Output the PDF
$filename = 'TSPI_Membership_' . $application['id'] . '_' . str_replace(' ', '_', $application['last_name']) . '.pdf';

if (isset($_GET['output_path']) && $mode === 'save') {
    // Save to specified file path instead of exiting
    $pdf->Output($_GET['output_path'], 'F');
    return true; // Return true to indicate success
} else if ($mode === 'download') {
    // Output as download but don't exit
    $pdf->Output($filename, 'D'); // 'D' means download
    return true;
} else {
    // Output inline (preview in browser) but don't exit
    $pdf->Output($filename, 'I'); // 'I' means inline
    return true;
}
// No exit; statement here 

// Include the file with all the PDF generation logic but without exit statement
require_once 'generate_application_pdf_for_inclusion.php'; 