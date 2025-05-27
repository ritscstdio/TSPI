<?php
/**
 * Generate Membership Certificate
 * 
 * This file generates a PDF certificate for approved members
 */

require_once '../includes/config.php';
require_admin_login();

// Ensure an application ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No application ID specified.";
    redirect('/admin/applications.php');
}

$id = $_GET['id'];
$mode = $_GET['mode'] ?? 'preview'; // Default to preview

// Fetch the application details
$stmt = $pdo->prepare("SELECT * FROM members_information WHERE id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    $_SESSION['message'] = "Application not found.";
    redirect('/admin/applications.php');
}

// Check if both IO and LO have approved
if ($application['io_approved'] !== 'approved' || $application['lo_approved'] !== 'approved') {
    $_SESSION['message'] = "Cannot generate certificate. Both Insurance Officer and Loan Officer must approve first.";
    redirect('/admin/view_application.php?id=' . $id);
}

// Include TCPDF
require_once '../vendor/autoload.php';

// Create new PDF document
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('TSPI CMS');
$pdf->SetAuthor('TSPI Admin');
$pdf->SetTitle('TSPI Membership Certificate');
$pdf->SetSubject('Membership Certificate');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(true, 0);

// Add a page
$pdf->AddPage();

// Set background image if exists (placeholder for now)
if (file_exists('../assets/images/certificate_bg.jpg')) {
    $pdf->Image('../assets/images/certificate_bg.jpg', 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);
} else {
    // If no background image exists, create a decorative border
    $pdf->SetLineWidth(1.5);
    $pdf->SetDrawColor(0, 86, 179); // TSPI blue color
    $pdf->Rect(10, 10, 277, 190);
    
    // Inner border
    $pdf->SetLineWidth(0.5);
    $pdf->SetDrawColor(0, 56, 119); // Darker blue
    $pdf->Rect(15, 15, 267, 180);
}

// Check if a specific plan is requested
$plan = $_GET['plan'] ?? null;

// Get all plans if not passed as a parameter
if (!$plan && !empty($application['plans'])) {
    $plans = json_decode($application['plans'], true);
    if (is_array($plans) && !empty($plans)) {
        $plan = $plans[0]; // Default to first plan
    }
}

// Get plans for certificate type
$plans = json_decode($application['plans'], true) ?: [];
$certificateType = $plan ?: (!empty($plans) ? implode(' & ', $plans) : 'Membership');

// Set the certificate background and content based on the plan
if ($plan) {
    // Load different certificate templates based on plan
    switch (strtolower($plan)) {
        case 'msbl':
            $pdf->Image('../assets/images/certificates/msbl_template.png', 0, 0, 297, 210);
            break;
        case 'damayan':
            $pdf->Image('../assets/images/certificates/damayan_template.png', 0, 0, 297, 210);
            break;
        case 'insurance':
            $pdf->Image('../assets/images/certificates/insurance_template.png', 0, 0, 297, 210);
            break;
        case 'kabuhayan':
            $pdf->Image('../assets/images/certificates/kabuhayan_template.png', 0, 0, 297, 210);
            break;
        default:
            // Default certificate template
            $pdf->Image('../assets/images/certificates/default_template.png', 0, 0, 297, 210);
    }
} else {
    // Use default template if no specific plan is selected
    $pdf->Image('../assets/images/certificates/default_template.png', 0, 0, 297, 210);
}

// Set font
$pdf->SetFont('helvetica', 'B', 30);
$pdf->SetTextColor(0, 56, 119); // Dark blue

// Title
$pdf->SetY(40);
$pdf->Cell(0, 0, 'CERTIFICATE OF MEMBERSHIP', 0, 1, 'C');

// TSPI Program/Plan name
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetY(55);
$pdf->Cell(0, 0, $certificateType . ' PROGRAM', 0, 1, 'C');

// Content
$pdf->SetY(80);
$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 0, 'This certifies that', 0, 1, 'C');

// Member name
$pdf->SetY(95);
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetTextColor(0, 56, 119); // Dark blue
$pdf->Cell(0, 0, strtoupper($application['first_name'] . ' ' . $application['middle_name'] . ' ' . $application['last_name']), 0, 1, 'C');

// Content continued
$pdf->SetY(110);
$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 0, 'has been accepted as a member of', 0, 1, 'C');

// Organization name
$pdf->SetY(120);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 0, 'TSPI MEMBERSHIP PROGRAM', 0, 1, 'C');

// Current date
$pdf->SetY(135);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 0, 'Starting ' . date('F j, Y'), 0, 1, 'C');

// Signature section
$pdf->SetY(160);

// Left signature - Secretary
$pdf->SetX(60);
$pdf->Line(30, 160, 90, 160);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(60, 0, 'Secretary', 0, 0, 'C');

// Right signature - Branch Manager (placeholder)
$pdf->SetX(180);
$pdf->Line(150, 160, 210, 160);
$pdf->Cell(60, 0, 'Branch Manager', 0, 1, 'C');

// Certificate ID and details at bottom
$pdf->SetY(180);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 0, 'Certificate No: TSPI-' . sprintf('%06d', $application['id']) . ' | CID No: ' . $application['cid_no'], 0, 1, 'C');
$pdf->SetY(185);
$pdf->Cell(0, 0, 'Issue Date: ' . date('m/d/Y'), 0, 1, 'C');

// Output the PDF
$filename = 'TSPI_Certificate_' . $application['id'] . '_' . date('Ymd') . '.pdf';

if ($mode === 'download') {
    // Output as download
    $pdf->Output($filename, 'D');
} else {
    // Output inline (preview in browser)
    $pdf->Output($filename, 'I');
}
exit; 