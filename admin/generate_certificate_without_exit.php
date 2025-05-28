<?php
/**
 * Generate Certificate PDF - Without Exit
 * 
 * This file is a modified version of generate_certificate.php that doesn't exit.
 * It's specifically for use in the test_email.php script.
 */

// Include necessary files
require_once '../includes/config.php';
require_admin_login();

// Safely convert potentially null values to string - only define if not already defined
if (!function_exists('safe_text')) {
    function safe_text($text) {
        return ($text === null) ? '' : (string)$text;
    }
}

// Ensure an 'id' parameter is provided
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No application ID specified.";
    $pdf_error = "No application ID specified.";
    return false;
}
$id = $_GET['id'];

// Ensure a 'plan' parameter is provided
if (!isset($_GET['plan'])) {
    $_SESSION['message'] = "No plan specified.";
    $pdf_error = "No plan specified.";
    return false;
}
$plan = $_GET['plan'];

$mode = $_GET['mode'] ?? 'preview'; // Default to preview mode

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
use setasign\Fpdi\Tcpdf\Fpdi;

// Determine MC field name based on plan
$planMcField = strtolower($plan) . '_mc';

// Format full name
$fullName = strtoupper($application['first_name'] . ' ' . 
    (!empty($application['middle_name']) ? $application['middle_name'] . ' ' : '') . 
    $application['last_name']);

// Format secretary name in uppercase
$secretaryName = !empty($application['secretary_name']) 
    ? strtoupper($application['secretary_name']) 
    : '';

// Get MC number based on plan
$mcNumber = '';
switch (strtoupper($plan)) {
    case 'BLIP':
        $mcNumber = $application['blip_mc'] ?? '';
        break;
    case 'LPIP':
        $mcNumber = $application['lpip_mc'] ?? '';
        break;
    case 'LMIP':
        $mcNumber = $application['lmip_mc'] ?? '';
        break;
}

// Format date as MM/DD/YYYY
$approvalDate = !empty($application['secretary_approval_date']) 
    ? date('m/d/Y', strtotime($application['secretary_approval_date'])) 
    : date('m/d/Y'); // Default to today if not approved yet

// Branch information
$branch = !empty($application['branch']) ? $application['branch'] : 'MAIN BRANCH';

// Determine which template to use
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

// Check if template exists
if (!file_exists($templatePath)) {
    $pdf_error = "Certificate template not found. Please contact system administrator.";
    return false;
}

// President signature path
$presidentSignaturePath = '../templates/president_signature.png';

// Create new PDF instance
$pdf = new Fpdi('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('TSPI CMS');
$pdf->SetAuthor('TSPI Admin');
$pdf->SetTitle('TSPI Membership Certificate - ' . $fullName);
$pdf->SetSubject('Membership Certificate for ' . $plan);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Import the template PDF
$pageCount = $pdf->setSourceFile($templatePath);
$tplId = $pdf->importPage(1); // Import first page of template
$pdf->useTemplate($tplId, 0, 0, 297, 210); // Use template on full page (A4 landscape)

// Set font for adding content
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 0, 0);

// Add member name - Position will need adjustment based on actual template
$pdf->SetFont('helvetica', 'B', 15);
$pdf->SetXY(40, 56);
$pdf->Cell(220, 10, $fullName, 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 12);

// Add MC number if available
if (!empty($mcNumber)) {
    $pdf->SetXY(129, 28);
    $pdf->Cell(220, 10, $mcNumber, 0, 1, 'C');
}

// Add branch name
$pdf->SetXY(130, 35);
$pdf->Cell(220, 10, $branch, 0, 1, 'C');

// Add date (MM/DD/YYYY)
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetXY(88, 114);
$pdf->Cell(50, 10, $approvalDate, 0, 1, 'L');
$pdf->SetFont('helvetica', 'B', 12);

// Add president's signature if exists
if (file_exists($presidentSignaturePath)) {
    // Position will need adjustment based on actual template
    $pdf->Image($presidentSignaturePath, 185, 135, 70, '', 'PNG');
    // Add President
    $pdf->SetXY(180, 142);
    $pdf->Cell(70, 10, "RENE E. CRISTOBAL", 0, 1, 'C');
}

// Add secretary's signature if exists
if (!empty($application['secretary_signature']) && file_exists('../' . $application['secretary_signature'])) {
    // Position will need adjustment based on actual template
    $pdf->Image('../' . $application['secretary_signature'], 55, 130, 70, '', 'PNG');
}

// Add secretary name
if (!empty($secretaryName)) {
    $pdf->SetXY(50, 142);
    $pdf->Cell(70, 10, $secretaryName, 0, 1, 'C');
}

// Write Member Name
$pdf->SetFont('helvetica', 'B', 22);
$pdf->SetXY(25, 175);
$pdf->SetTextColor(0, 0, 0);
$memberName = $application['first_name'] . ' ' . 
              ($application['middle_name'] ? substr($application['middle_name'], 0, 1) . '. ' : '') . 
              $application['last_name'];
$pdf->Cell(162, 0, safe_text($memberName), 0, 0, 'C');

// Write MC Number
if (!empty($application[$planMcField])) {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetXY(75, 187);
    $pdf->Cell(60, 0, safe_text($application[$planMcField]), 0, 0, 'C');
}

// Write Certificate Date (Secretary Approval Date)
if (!empty($application['secretary_approval_date'])) {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetXY(85, 222);
    $formattedDate = date('F j, Y', strtotime(safe_text($application['secretary_approval_date'])));
    $pdf->Cell(40, 0, $formattedDate, 0, 0, 'C');
}

// Write CID Number
if (!empty($application['cid_no'])) {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetXY(129, 206);
    $pdf->Cell(40, 0, safe_text($application['cid_no']), 0, 0, 'L');
}

// Write Branch
if (!empty($application['branch'])) {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetXY(106, 228);
    $pdf->Cell(60, 0, safe_text($application['branch']), 0, 0, 'L');
}

// Secretary signature and name
if (!empty($application['secretary_signature'])) {
    $signaturePath = __DIR__ . '/../' . $application['secretary_signature'];
    if (file_exists($signaturePath)) {
        $pdf->Image($signaturePath, 128, 245, 35, 0, 'PNG');
    }
}

if (!empty($application['secretary_name'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetXY(110, 264);
    $pdf->Cell(70, 0, safe_text($application['secretary_name']), 0, 0, 'C');
}

// Output the PDF
$filename = 'TSPI_Certificate_' . $application['id'] . '_' . $plan . '_' . date('Ymd') . '.pdf';

if (isset($_GET['output_path']) && $mode === 'save') {
    // Save to specified file path without exiting
    $pdf->Output($_GET['output_path'], 'F');
    return true; // Return true to indicate success
} else if ($mode === 'download') {
    // Output as download without exiting
    $pdf->Output($filename, 'D');
    return true;
} else {
    // Output inline (preview in browser) without exiting
    $pdf->Output($filename, 'I');
    return true;
} 