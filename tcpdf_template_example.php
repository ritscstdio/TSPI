<?php
/**
 * TCPDF Template Example
 * 
 * This example demonstrates how to use TCPDF to place text at specific coordinates
 * on an existing PDF template file.
 */

// Include autoloader for TCPDF and FPDI
require_once 'vendor/autoload.php';

// Check if TCPDF is properly installed
if (!class_exists('TCPDF')) {
    die("TCPDF not found. Please run 'composer install' first.");
}

// We need the FPDI class for PDF importing functionality
if (!class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
    // If you're seeing this error, you need to install FPDI
    die("FPDI library not found. Please run 'composer require setasign/fpdi' to install the FPDI library.");
}

// Create a sample database array (in real app, this would come from your database)
$application = [
    'id' => '12345',
    'first_name' => 'Juan',
    'middle_name' => 'Dela',
    'last_name' => 'Cruz',
    'birthdate' => '1985-06-15',
    'gender' => 'Male',
    'email' => 'juan.delacruz@example.com',
    'cell_phone' => '9171234567',
    'address' => '123 Main St., Barangay Poblacion',
    'city' => 'Makati City',
    'province' => 'Metro Manila',
    'membership_date' => date('Y-m-d')
];

/**
 * Method 1: Using existing PDF as template with FPDI
 */
function generatePDFWithTemplateImport($application) {
    // Create new PDF document using FPDI which extends TCPDF
    $pdf = new setasign\Fpdi\Tcpdf\Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('TSPI CMS');
    $pdf->SetAuthor('TSPI Admin');
    $pdf->SetTitle('Membership Form - ' . $application['first_name'] . ' ' . $application['last_name']);
    
    // Disable header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(0, 0, 0);
    
    // Import the template PDF
    // Note: You need to have a template PDF file in your project
    // For demonstration purposes, we'll assume there's a file named "membership_template.pdf"
    $template_file = 'templates/membership_template.pdf';
    
    // Check if template exists
    if (!file_exists($template_file)) {
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 14);
        $pdf->Cell(0, 10, 'Template file not found: ' . $template_file, 0, 1, 'C');
        $pdf->Cell(0, 10, 'This is a demonstration of how to use a template.', 0, 1, 'C');
    } else {
        try {
            // Import the PDF template
            $pageCount = $pdf->setSourceFile($template_file);
            
            // Loop through all pages of the template
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // Add a page to the output PDF - use the same size and orientation as the template
                $templateSize = $pdf->getTemplateSize($pdf->importPage($pageNo));
                $orientation = ($templateSize['width'] > $templateSize['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, array($templateSize['width'], $templateSize['height']));
                
                // Import current page
                $tplIdx = $pdf->importPage($pageNo);
                
                // Apply the template using its original dimensions to prevent distortion
                $pdf->useTemplate($tplIdx, 0, 0, $templateSize['width'], $templateSize['height']);
                
                // Add content to the template based on the page number
                $pdf->SetFont('helvetica', '', 12);
                $pdf->SetTextColor(0, 0, 0);
                
                // First page specific content
                if ($pageNo == 1) {
                    // ID - positioned at top right
                    $pdf->SetXY(21, 27);
                    $pdf->Write(0, 'ID: ' . $application['id']);
                    
                    // Name - using Y positions based on the form layout
                    $pdf->SetXY(50, 40);
                    $pdf->Write(0, $application['first_name'] . ' ' . $application['middle_name'] . ' ' . $application['last_name']);
                    
                    // Birthdate
                    $pdf->SetXY(50, 50);
                    $pdf->Write(0, date('m/d/Y', strtotime($application['birthdate'])));
                    
                    // Gender
                    $pdf->SetXY(50, 60);
                    $pdf->Write(0, $application['gender']);
                    
                    // Email
                    $pdf->SetXY(50, 70);
                    $pdf->Write(0, $application['email']);
                    
                    // Phone
                    $pdf->SetXY(50, 80);
                    $pdf->Write(0, '+63' . $application['cell_phone']);
                    
                    // Address
                    $pdf->SetXY(50, 90);
                    $pdf->Write(0, $application['address'] . ', ' . $application['city'] . ', ' . $application['province']);
                    
                    // Current date
                    $pdf->SetXY(50, 100);
                    $pdf->Write(0, date('m/d/Y'));
                }
                
                // Second page specific content (if any)
                else if ($pageNo == 2) {
                    // Add page 2 specific content here
                    $pdf->SetXY(50, 40);
                    $pdf->Write(0, 'Page 2 data: Membership date - ' . date('m/d/Y', strtotime($application['membership_date'])));
                    
                    // Other page 2 content...
                }
                
                // Additional pages...
                else {
                    // Add content for page 3 and beyond
                    $pdf->SetXY(50, 40);
                    $pdf->Write(0, 'Additional page ' . $pageNo . ' content');
                }
            }
        } catch (Exception $e) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Error loading template: ' . $e->getMessage(), 0, 1, 'C');
        }
    }
    
    // Output the PDF
    $pdf->Output('filled_template.pdf', 'I'); // 'I' means inline (display in browser)
}

/**
 * Method 2: Using SetXY and Write for precise positioning
 */
function generatePDFWithPrecisePositioning($application) {
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('TSPI CMS');
    $pdf->SetAuthor('TSPI Admin');
    $pdf->SetTitle('Membership Form - ' . $application['first_name'] . ' ' . $application['last_name']);
    
    // Disable header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(0, 0, 0);
    
    // Add a page
    $pdf->AddPage();
    
    // Load and add background image (template)
    // Note: This could be a scanned form or a PDF converted to image
    $template_img = 'templates/membership_form_bg.jpg';
    
    if (file_exists($template_img)) {
        $pdf->Image($template_img, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
    } else {
        // If template image doesn't exist, create a mock form 
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'TSPI MEMBERSHIP FORM (MOCK TEMPLATE)', 0, 1, 'C');
        $pdf->Line(10, 25, 200, 25);
        $pdf->SetFont('helvetica', '', 10);
        
        // Mock form fields
        $fields = [
            'Full Name:', 'Birthdate:', 'Gender:', 'Email:',
            'Phone:', 'Address:', 'Membership Date:', 'Application ID:'
        ];
        
        $y = 40;
        foreach ($fields as $field) {
            $pdf->SetXY(20, $y);
            $pdf->Cell(40, 8, $field, 1, 0, 'L');
            $pdf->Cell(130, 8, '', 1, 1, 'L');
            $y += 12;
        }
    }
    
    // Set font
    $pdf->SetFont('helvetica', '', 12);
    
    // Set text color
    $pdf->SetTextColor(0, 0, 0);
    
    // Add content at specific positions
    
    // Full Name
    $pdf->SetXY(60, 40);
    $pdf->Write(0, $application['first_name'] . ' ' . $application['middle_name'] . ' ' . $application['last_name']);
    
    // Birthdate
    $pdf->SetXY(60, 52);
    $pdf->Write(0, date('m/d/Y', strtotime($application['birthdate'])));
    
    // Gender
    $pdf->SetXY(60, 64);
    $pdf->Write(0, $application['gender']);
    
    // Email
    $pdf->SetXY(60, 76);
    $pdf->Write(0, $application['email']);
    
    // Phone
    $pdf->SetXY(60, 88);
    $pdf->Write(0, '+63' . $application['cell_phone']);
    
    // Address
    $pdf->SetXY(60, 100);
    $pdf->Write(0, $application['address'] . ', ' . $application['city'] . ', ' . $application['province']);
    
    // Membership Date
    $pdf->SetXY(60, 112);
    $pdf->Write(0, date('m/d/Y'));
    
    // Application ID
    $pdf->SetXY(60, 124);
    $pdf->Write(0, $application['id']);
    
    // Output the PDF
    $pdf->Output('precise_positioning.pdf', 'I');
}

/**
 * Method 3: Using form fields with TCPDF
 */
function generatePDFWithFormFields($application) {
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('TSPI CMS');
    $pdf->SetAuthor('TSPI Admin');
    $pdf->SetTitle('Membership Form - ' . $application['first_name'] . ' ' . $application['last_name']);
    
    // Disable header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 10, 'TSPI MEMBERSHIP FORM', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 12);
    
    // Add content as form fields
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(60, 10, 'Application Details', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    // Create form fields
    // See TCPDF documentation for more details on TextField parameters
    
    // Application ID
    $pdf->TextField('application_id', 60, 10, ['value' => $application['id']]);
    
    $pdf->Ln(5);
    
    // Personal Information section
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(60, 10, 'Personal Information', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    // Name Fields
    $pdf->Cell(40, 7, 'First Name:', 0, 0);
    $pdf->TextField('first_name', 60, 7, ['value' => $application['first_name']]);
    $pdf->Ln();
    
    $pdf->Cell(40, 7, 'Middle Name:', 0, 0);
    $pdf->TextField('middle_name', 60, 7, ['value' => $application['middle_name']]);
    $pdf->Ln();
    
    $pdf->Cell(40, 7, 'Last Name:', 0, 0);
    $pdf->TextField('last_name', 60, 7, ['value' => $application['last_name']]);
    $pdf->Ln();
    
    // Other fields
    $pdf->Cell(40, 7, 'Birthdate:', 0, 0);
    $pdf->TextField('birthdate', 60, 7, ['value' => date('m/d/Y', strtotime($application['birthdate']))]);
    $pdf->Ln();
    
    $pdf->Cell(40, 7, 'Gender:', 0, 0);
    $pdf->TextField('gender', 60, 7, ['value' => $application['gender']]);
    $pdf->Ln();
    
    $pdf->Cell(40, 7, 'Email:', 0, 0);
    $pdf->TextField('email', 60, 7, ['value' => $application['email']]);
    $pdf->Ln();
    
    $pdf->Cell(40, 7, 'Phone:', 0, 0);
    $pdf->TextField('phone', 60, 7, ['value' => '+63' . $application['cell_phone']]);
    $pdf->Ln();
    
    // Address
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(60, 10, 'Address Information', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    $pdf->Cell(40, 7, 'Address:', 0, 0);
    $pdf->TextField('address', 140, 7, ['value' => $application['address']]);
    $pdf->Ln();
    
    $pdf->Cell(40, 7, 'City:', 0, 0);
    $pdf->TextField('city', 60, 7, ['value' => $application['city']]);
    $pdf->Ln();
    
    $pdf->Cell(40, 7, 'Province:', 0, 0);
    $pdf->TextField('province', 60, 7, ['value' => $application['province']]);
    $pdf->Ln();
    
    // Date fields
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(60, 10, 'Membership Information', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    $pdf->Cell(40, 7, 'Membership Date:', 0, 0);
    $pdf->TextField('membership_date', 60, 7, ['value' => date('m/d/Y')]);
    $pdf->Ln();
    
    // Set some field as read-only to prevent editing
    $pdf->Cell(40, 7, 'System Date:', 0, 0);
    $pdf->TextField('system_date', 60, 7, ['value' => date('m/d/Y H:i:s'), 'readonly' => true]);
    $pdf->Ln();
    
    // Output the PDF
    $pdf->Output('form_fields.pdf', 'I');
}

// Determine which example to run (you can show all on a real web page)
$method = $_GET['method'] ?? 'template';

switch ($method) {
    case 'template':
        generatePDFWithTemplateImport($application);
        break;
    case 'positioning':
        generatePDFWithPrecisePositioning($application);
        break;
    case 'form':
        generatePDFWithFormFields($application);
        break;
    default:
        generatePDFWithTemplateImport($application);
}
?> 