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

// If MC number is still empty, use the CID number as a fallback
if (empty($mcNumber) && !empty($application['cid_no'])) {
    $mcNumber = $application['cid_no'] . '-' . strtoupper($plan);
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
        // If LPIP template doesn't exist, use BLIP as a fallback
        if (!file_exists($templatePath)) {
            // Log that we're using a fallback
            error_log("LPIP template not found at {$templatePath}, falling back to BLIP template");
            $templatePath = '../templates/Membership-Certificate-for-Basic-Life-Insurance-Plan-BLIP.pdf';
        }
        break;
    case 'LMIP':
        $templatePath = '../templates/Membership-Certificate-for-Basic-Life-Insurance-Plan-LMIP.pdf';
        // If LMIP template doesn't exist, use BLIP as a fallback
        if (!file_exists($templatePath)) {
            error_log("LMIP template not found at {$templatePath}, falling back to BLIP template");
            $templatePath = '../templates/Membership-Certificate-for-Basic-Life-Insurance-Plan-BLIP.pdf';
        }
        break;
    default:
        $templatePath = '../templates/membership_template.pdf';
}

// Check if template exists - if not, use a default template
if (!file_exists($templatePath)) {
    // Try to use BLIP template as fallback
    $fallbackPath = '../templates/Membership-Certificate-for-Basic-Life-Insurance-Plan-BLIP.pdf';
    error_log("Template not found at {$templatePath}, trying fallback: {$fallbackPath}");
    $templatePath = $fallbackPath;
    
    // If still not found, use generic template
    if (!file_exists($templatePath)) {
        $genericPath = '../templates/membership_template.pdf';
        error_log("Fallback template not found, trying generic template: {$genericPath}");
        $templatePath = $genericPath;
    }
    
    // If still not found, we can't proceed
    if (!file_exists($templatePath)) {
        $error_message = "Certificate template not found for plan {$plan}. Please check template paths.";
        error_log($error_message);
        $pdf_error = $error_message;
        return false;
    }
}

// For debugging purposes, log which template we're actually using
error_log("Using certificate template for {$plan}: {$templatePath}");

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

// Add the second page of the template if it exists
if ($pageCount >= 2) {
    $pdf->AddPage();
    $tplId2 = $pdf->importPage(2); // Import second page of template
    $pdf->useTemplate($tplId2, 0, 0, 297, 210); // Use template on full page (A4 landscape)
    
}

// Output the PDF
$filename = 'TSPI_Certificate_' . $application['id'] . '_' . $plan . '_' . date('Ymd') . '.pdf';

// Add more detailed error handling for PDF output
if (isset($_GET['output_path']) && $mode === 'save') {
    try {
        // Save to specified file path without exiting
        $output_path = $_GET['output_path'];
        $result = $pdf->Output($output_path, 'F');
        
        // Verify file was created
        if (file_exists($output_path)) {
            error_log("Successfully created certificate PDF at: {$output_path}");
            return true; // Return true to indicate success
        } else {
            error_log("Failed to create certificate PDF at: {$output_path} despite no exceptions");
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception while generating certificate for {$plan}: " . $e->getMessage());
        return false;
    }
} else if ($mode === 'download') {
    // Output as download without exiting
    $pdf->Output($filename, 'D');
    return true;
} else {
    // Output inline (preview in browser) without exiting
    $pdf->Output($filename, 'I');
    return true;
} 