<?php
/**
 * Generate Final Approval Report PDF
 * 
 * This file generates an official PDF report for approved applications
 * Only available when both IO and LO have approved and Secretary approves
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
    $_SESSION['message'] = "Cannot generate final report. Both Insurance Officer and Loan Officer must approve first.";
    redirect('/admin/view_application.php?id=' . $id);
}

// Include TCPDF
require_once '../vendor/autoload.php';

class MYPDF extends TCPDF {
    // Page header
    public function Header() {
        // Logo
        if (file_exists('../assets/images/logo.png')) {
            $this->Image('../assets/images/logo.png', 10, 10, 30, '', 'PNG');
        }
        
        // Set font
        $this->SetFont('helvetica', 'B', 15);
        
        // Title
        $this->Cell(0, 15, 'TSPI MEMBERSHIP APPLICATION', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Line break
        $this->Ln(20);
        
        // Subtitle - Official Approval Report
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'OFFICIAL APPROVAL REPORT', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(15);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        
        // Add timestamp
        $this->SetX(15);
        $this->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF document
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('TSPI CMS');
$pdf->SetAuthor('TSPI Admin');
$pdf->SetTitle('Final Approval Report - ' . $application['first_name'] . ' ' . $application['last_name']);
$pdf->SetSubject('Membership Application Final Approval');

// Set margins
$pdf->SetMargins(15, 40, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Application Status Box
$pdf->SetFillColor(240, 240, 240);
$pdf->Rect(15, $pdf->GetY(), 180, 30, 'F');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(180, 10, 'APPLICATION STATUS', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);

// Status details
$pdf->Cell(60, 6, 'Application ID:', 0, 0);
$pdf->Cell(120, 6, $application['id'], 0, 1);

$pdf->Cell(60, 6, 'Status:', 0, 0);
$pdf->Cell(120, 6, ucfirst($application['status']), 0, 1);

$pdf->Cell(60, 6, 'Insurance Officer Approval:', 0, 0);
$pdf->Cell(120, 6, 'APPROVED by ' . $application['io_name'] . ' on ' . date('m/d/Y', strtotime($application['io_approval_date'])), 0, 1);

$pdf->Cell(60, 6, 'Loan Officer Approval:', 0, 0);
$pdf->Cell(120, 6, 'APPROVED by ' . $application['lo_name'] . ' on ' . date('m/d/Y', strtotime($application['lo_approval_date'])), 0, 1);

$pdf->Ln(10);

// Personal Information Section
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(180, 10, 'PERSONAL INFORMATION', 0, 1, 'L');
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);

// Name information
$pdf->Cell(60, 6, 'Name:', 0, 0);
$pdf->Cell(120, 6, $application['first_name'] . ' ' . $application['middle_name'] . ' ' . $application['last_name'], 0, 1);

$pdf->Cell(60, 6, 'CID Number:', 0, 0);
$pdf->Cell(120, 6, $application['cid_no'], 0, 1);

$pdf->Cell(60, 6, 'Branch:', 0, 0);
$pdf->Cell(120, 6, $application['branch'], 0, 1);

$pdf->Cell(60, 6, 'Center Number:', 0, 0);
$pdf->Cell(120, 6, $application['center_no'] ?: 'N/A', 0, 1);

$pdf->Cell(60, 6, 'Gender:', 0, 0);
$pdf->Cell(120, 6, $application['gender'], 0, 1);

$pdf->Cell(60, 6, 'Civil Status:', 0, 0);
$pdf->Cell(120, 6, $application['civil_status'], 0, 1);

$pdf->Cell(60, 6, 'Birth Date:', 0, 0);
$pdf->Cell(120, 6, date('F j, Y', strtotime($application['birthdate'])), 0, 1);

$pdf->Cell(60, 6, 'Age:', 0, 0);
$pdf->Cell(120, 6, $application['age'] . ' years old', 0, 1);

$pdf->Cell(60, 6, 'Contact Information:', 0, 0);
$pdf->Cell(120, 6, 'Email: ' . $application['email'] . ' | Phone: +63' . $application['cell_phone'], 0, 1);

$pdf->Ln(5);

// Plans and Classification
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(180, 10, 'PLANS AND CLASSIFICATION', 0, 1, 'L');
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);

// Plans
$plans = json_decode($application['plans'], true) ?: [];
$pdf->Cell(60, 6, 'Plans:', 0, 0);
$pdf->Cell(120, 6, implode(', ', $plans), 0, 1);

// Classification
$classification = json_decode($application['classification'], true) ?: [];
$pdf->Cell(60, 6, 'Classification:', 0, 0);
$pdf->Cell(120, 6, implode(', ', $classification), 0, 1);

$pdf->Ln(5);

// Beneficiaries Section
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(180, 10, 'BENEFICIARIES', 0, 1, 'L');
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);

// Table header
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 7, 'Name', 1, 0, 'C');
$pdf->Cell(30, 7, 'Relationship', 1, 0, 'C');
$pdf->Cell(30, 7, 'Birthdate', 1, 0, 'C');
$pdf->Cell(30, 7, 'Gender', 1, 0, 'C');
$pdf->Cell(30, 7, 'Dependent', 1, 1, 'C');
$pdf->SetFont('helvetica', '', 9);

// Beneficiaries
for ($i = 1; $i <= 5; $i++) {
    if (!empty($application["beneficiary_fn_{$i}"])) {
        $fullName = $application["beneficiary_fn_{$i}"] . ' ' . 
                  ($application["beneficiary_mi_{$i}"] ? $application["beneficiary_mi_{$i}"] . '. ' : '') . 
                  $application["beneficiary_ln_{$i}"];
        
        $birthdate = !empty($application["beneficiary_birthdate_{$i}"]) ? 
                    date('m/d/Y', strtotime($application["beneficiary_birthdate_{$i}"])) : 'N/A';
        
        $pdf->Cell(60, 7, $fullName, 1, 0);
        $pdf->Cell(30, 7, $application["beneficiary_relationship_{$i}"] ?: 'N/A', 1, 0);
        $pdf->Cell(30, 7, $birthdate, 1, 0);
        $pdf->Cell(30, 7, $application["beneficiary_gender_{$i}"] ?: 'N/A', 1, 0);
        $pdf->Cell(30, 7, $application["beneficiary_dependent_{$i}"] ? 'Yes' : 'No', 1, 1);
    }
}

// Add a new page for signatures
$pdf->AddPage();

// Approval Section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(180, 10, 'OFFICIAL APPROVAL', 0, 1, 'C');
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(10);

// Insurance Officer Signature Section
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(180, 10, 'Insurance Officer Approval', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// IO Signature Box
$pdf->Cell(60, 6, 'Name:', 0, 0);
$pdf->Cell(120, 6, $application['io_name'], 0, 1);
$pdf->Cell(60, 6, 'Approval Date:', 0, 0);
$pdf->Cell(120, 6, date('F j, Y', strtotime($application['io_approval_date'])), 0, 1);

// Signature placeholder
$pdf->Cell(60, 6, 'Signature:', 0, 1);
$pdf->Rect(15, $pdf->GetY(), 80, 20);
$pdf->Ln(25);

// Loan Officer Signature Section
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(180, 10, 'Loan Officer Approval', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// LO Signature Box
$pdf->Cell(60, 6, 'Name:', 0, 0);
$pdf->Cell(120, 6, $application['lo_name'], 0, 1);
$pdf->Cell(60, 6, 'Approval Date:', 0, 0);
$pdf->Cell(120, 6, date('F j, Y', strtotime($application['lo_approval_date'])), 0, 1);

// Signature placeholder
$pdf->Cell(60, 6, 'Signature:', 0, 1);
$pdf->Rect(15, $pdf->GetY(), 80, 20);
$pdf->Ln(25);

// Secretary Approval Section
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(180, 10, 'Secretary Final Approval', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// Secretary Signature Box - This will be filled when the actual signature is captured
$pdf->Cell(60, 6, 'Name:', 0, 0);
$pdf->Cell(120, 6, '______________________', 0, 1);
$pdf->Cell(60, 6, 'Approval Date:', 0, 0);
$pdf->Cell(120, 6, date('F j, Y'), 0, 1);

// Signature placeholder
$pdf->Cell(60, 6, 'Signature:', 0, 1);
$pdf->Rect(15, $pdf->GetY(), 80, 20);
$pdf->Ln(25);

// Final Note
$pdf->SetFont('helvetica', 'I', 10);
$pdf->MultiCell(180, 10, 'This document serves as the official record of approval for the membership application. It has been verified and approved by all required officers.', 0, 'C');

// Output the PDF
$filename = 'TSPI_Final_Approval_' . $application['id'] . '_' . date('Ymd') . '.pdf';

if ($mode === 'download') {
    // Output as download
    $pdf->Output($filename, 'D');
} else {
    // Output inline (preview in browser)
    $pdf->Output($filename, 'I');
}
exit; 