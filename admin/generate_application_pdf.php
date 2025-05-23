<?php
/**
 * Generate Application PDF
 * 
 * This file generates a PDF based on a membership application using TCPDF and FPDI.
 * It places data from the database onto specific coordinates of the PDF template.
 */

// Include necessary files
require_once '../includes/config.php';
require_admin_login();

// Ensure an 'id' parameter is provided (allow '0' as valid)
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No application ID specified.";
    redirect('/admin/applications.php');
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
    redirect('/admin/applications.php');
}

// Include TCPDF and FPDI
require_once '../vendor/autoload.php';

// Check if FPDI is properly installed
if (!class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
    die("FPDI library not found. Please run 'composer require setasign/fpdi' to install the FPDI library.");
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
            
            // Debug mode - draw coordinate grid if enabled
            if ($debug) {
                // Draw coordinate grid for debugging
                $pdf->SetDrawColor(200, 200, 200);
                $pdf->SetTextColor(200, 0, 0);
                $pdf->SetFont('helvetica', '', 6);
                
                // Draw vertical lines and labels (X-axis) every 10mm
                for ($x = 0; $x <= $templateSize['width']; $x += 10) {
                    $pdf->Line($x, 0, $x, $templateSize['height']);
                    $pdf->Text($x, 3, $x);
                }
                
                // Draw horizontal lines and labels (Y-axis) every 10mm
                for ($y = 0; $y <= $templateSize['height']; $y += 10) {
                    $pdf->Line(0, $y, $templateSize['width'], $y);
                    $pdf->Text(1, $y, $y);
                }
                
                // Reset colors
                $pdf->SetDrawColor(0, 0, 0);
            }
            
            // Set font and color for text
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            
            // Add content based on the page number
            if ($pageNo == 1) {
                // PAGE 1 - Personal Information
                
                // Decode JSON fields
                $plans = json_decode($application['plans'], true) ?: [];
                $classification = json_decode($application['classification'], true) ?: [];
                
                // Add a reference point marker in debug mode
                if ($debug) {
                    $pdf->SetDrawColor(255, 0, 0);
                    $pdf->Circle(0, 0, 3); // Mark the exact (0,0) point
                    $pdf->SetDrawColor(0, 0, 0);
                }
                
                $pdf->SetFont('helvetica', '', 10);
            
                // Branch and ID information
                $pdf->SetFont('helvetica', '', 7); // Set smaller font size using helvetica
                $pdf->SetXY(21, 24); // Branch
                $pdf->Write(0, $application['branch']);
                
                $pdf->SetXY(26.5, 28); // CID No
                $pdf->Write(0, $application['cid_no']);
                
                $pdf->SetXY(37, 31.5); // Center No
                $pdf->Write(0, $application['center_no'] ?: '');
                
                $pdf->SetFont('helvetica', '', 10); // Set smaller font size using helvetica


                // Check plans and classification checkboxes
                // Plan checkboxes
                if (in_array('BLIP', $plans)) {
                    $pdf->SetXY(131.3, 31.5); // X,Y coordinate for BLIP checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                if (in_array('LPIP', $plans)) {
                    $pdf->SetXY(131.3, 34.8); // X,Y coordinate for LPIP checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                if (in_array('LMIP', $plans)) {
                    $pdf->SetXY(131.3, 38); // X,Y coordinate for LMIP checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                if (in_array('CLIP', $plans)) {
                    $pdf->SetXY(170.5, 28.5); // X,Y coordinate for CLIP checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                if (in_array('MRI', $plans)) {
                    $pdf->SetXY(170.5, 31.7); // X,Y coordinate for MRI checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                if (in_array('GLIP', $plans)) {
                    $pdf->SetXY(170.5, 38); // X,Y coordinate for GLIP checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                
                // Classification checkboxes
                if (in_array('Borrower', $classification)) {
                    $pdf->SetXY(82.5, 26.5); // X,Y coordinate for Borrower checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                if (in_array('TKP', $classification)) {
                    $pdf->SetXY(82.5, 26.5); // X,Y coordinate for TKP Kapamilya checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                if (in_array('TMP', $classification)) {
                    $pdf->SetXY(82.5, 29.9); // X,Y coordinate for TMP checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                if (in_array('TPP', $classification)) {
                    $pdf->SetXY(82.5, 33); // X,Y coordinate for TPP checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                if (in_array('Kapamilya', $classification)) {
                    $pdf->SetXY(116.5, 26.5); // X,Y coordinate for OG checkbox (adjust as needed)
                    $pdf->Write(0, 'X');
                }
                
                // Personal Information
                // Last Name
                $pdf->SetXY(32, 55.9); 
                $pdf->Write(0, $application['last_name']);
                
                // First Name
                $pdf->SetXY(33, 64.5); 
                $pdf->Write(0, $application['first_name']);
                
                // Middle Name
                $pdf->SetXY(47, 71.5); 
                $pdf->Write(0, $application['middle_name'] ?: '');
                
                // Date of birth with proper format
                $pdf->SetXY(100, 66); 
                $pdf->Write(0, date('m/d/Y', strtotime($application['birthdate'])));
                
                // Gender checkboxes
                if ($application['gender'] == 'Male') {
                    $pdf->SetXY(85.3, 55.9);
                    $pdf->Write(0, 'X');
                } else if ($application['gender'] == 'Female') {
                    $pdf->SetXY(85.3, 59);
                    $pdf->Write(0, 'X');
                }
                
                // Civil Status checkboxes
                if ($application['civil_status'] == 'Single') {
                    $pdf->SetXY(136, 55.9);
                    $pdf->Write(0, 'X');
                } else if ($application['civil_status'] == 'Married') {
                    $pdf->SetXY(169.2, 55.9);
                    $pdf->Write(0, 'X');
                }
                else if ($application['civil_status'] == 'Widowed') {
                    $pdf->SetXY(136, 59.10);
                    $pdf->Write(0, 'X');
                } else if ($application['civil_status'] == 'Separated') {
                    $pdf->SetXY(169, 59.12);
                    $pdf->Write(0, 'X');
                }
                
                // Age
                $pdf->SetXY(150, 65); 
                $pdf->Write(0, $application['age']);
                
                // Birth Place
                $pdf->SetXY(85, 72.9); 
                $pdf->Write(0, $application['birth_place']);
                
                // Nationality
                $pdf->SetXY(152, 72.8); 
                $pdf->Write(0, $application['nationality']);
                
                // Mother's Maiden Name
                $pdf->SetXY(140, 81); 
                $pdf->Write(0, $application['mothers_maiden_last_name'] . ', ' . $application['mothers_maiden_first_name'] . ' ' . $application['mothers_maiden_middle_name']);
                
                // TIN/SSS/GSIS Number
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetXY(115.5, 77.3); 
                $pdf->Write(0, $application['id_number']);
                
                // Other ID
                $pdf->SetXY(96.5, 84.2); 
                $pdf->Write(0, $application['other_valid_ids']);
                $pdf->SetFont('helvetica', '', 10); // Set smaller font size using helvetica

                // Cell Phone
                $pdf->SetXY(20, 83); 
                $pdf->Write(0, '+63' . $application['cell_phone']);
                       
                // Telephone
                $pdf->SetXY(34, 77);
                $pdf->Write(0, ($application['contact_no'] ?: 'N/A'));
                
                // Addresses
                // Present Address
                $pdf->SetFont('helvetica', '', 7); // Set smaller font size using helvetica
                $pdf->SetXY(3, 91); 
                $pdf->Write(0, $application['present_address']);
                
                // Permanent Address
                $pdf->SetFont('helvetica', '', 6); // Set smaller font size using helvetica
                $pdf->SetXY(117, 91); 
                $pdf->Write(0, $application['permanent_address']);

                $pdf->SetFont('helvetica', '', 7); // Set smaller font size using helvetica
 
                // Present ZIP
                $pdf->SetXY(190, 91.5); 
                $pdf->Write(0, $application['present_zip_code']);
                
                // Permanent ZIP
                $pdf->SetXY(98, 91.5); 
                $pdf->Write(0, $application['permanent_zip_code']);
          
                // Present Brgy Code
                $pdf->SetXY(101, 87.7); 
                $pdf->Write(0, $application['present_brgy_code']);

                // Permanent Brgy Code
                $pdf->SetXY(192, 87.7); 
                $pdf->Write(0, $application['permanent_brgy_code']);

                $pdf->SetFont('helvetica', '', 10); // Reset to default font size

                // Home ownership checkbox
                if ($application['home_ownership'] == 'Owned') {
                    $pdf->SetXY(30, 94.5);
                    $pdf->Write(0, 'X');
                } else if ($application['home_ownership'] == 'Rented') {
                    $pdf->SetXY(44, 94.5);
                    $pdf->Write(0, 'X');
                } else if ($application['home_ownership'] == 'Living with Parents') {
                    $pdf->SetXY(59.1, 94.5);
                    $pdf->Write(0, 'X');
                }

                // Length of Stay
                $pdf->SetXY(173, 94.5); 
                $pdf->Write(0, $application['length_of_stay'] . ' year(s)');


                    // Section 2
                    // Business Information
                // Primary Business
                $pdf->SetFont('helvetica', '', 8); 
                $pdf->SetXY(78, 103.5); 
                $pdf->Write(0, $application['primary_business']);

                // Business Address
                $pdf->SetXY(131, 106.8); 
                $pdf->Write(0, $application['business_address'] ?: 'N/A');
                
                // Years in Business
                $pdf->SetXY(53.5, 107); 
                $pdf->Write(0, $application['years_in_business'] . ' year(s)');

                // Other Income Sources (if any)
                if (!empty($application["other_income_source_1"])) {
                    $pdf->SetXY(43.7, 110.5);
                    $pdf->Write(0, $application["other_income_source_1"]);
                }
                
                if (!empty($application["other_income_source_2"])) {
                    $pdf->SetXY(94.2, 110.5);
                    $pdf->Write(0, $application["other_income_source_2"]);
                }
                
                if (!empty($application["other_income_source_3"])) {
                    $pdf->SetXY(134, 110.5);
                    $pdf->Write(0, $application["other_income_source_3"]);
                }
                if (!empty($application["other_income_source_4"])) {
                    $pdf->SetXY(171, 110.5);
                    $pdf->Write(0, $application["other_income_source_4"]);
                }

                // Spouse Information (if married)
                $pdf->SetFont('helvetica', '', 10); // Reset to default font size
                if ($application['civil_status'] === 'Married' && !empty($application['spouse_name'])) {
                    // Name
                    $pdf->SetXY(5, 122);
                    $pdf->Write(0, $application['spouse_name']);
                    
                    // Occupation
                    $pdf->SetXY(90, 120.2);
                    $pdf->Write(0, $application['spouse_occupation']);
                    
                    // Birthdate
                    $pdf->SetXY(160, 120.2);
                    $pdf->Write(0, date('m/d/Y', strtotime($application['spouse_birthdate'])));
                    
                    // Spouse ID Number
                    $pdf->SetFont('helvetica', '', 7);
                    $pdf->SetXY(119, 124.8);
                    $pdf->Write(0, $application['spouse_id_number']);

                    // Spouse Age

                    $pdf->SetXY(173, 124.8);
                    $pdf->Write(0, $application['spouse_age']);
                }

     
                // Beneficiaries
                // Function to add beneficiary in table format
                $pdf->SetFont('helvetica', '', 10);
                $beneficiary_y_start = 139;
                $beneficiary_y_increment = 4.7;
                
                // First beneficiary
                if (!empty($application['beneficiary_fn_1'])) {
                    $pdf->SetXY(5, $beneficiary_y_start);
                    $pdf->Write(0, $application['beneficiary_ln_1']);
                    
                    $pdf->SetXY(50, $beneficiary_y_start);
                    $pdf->Write(0, $application['beneficiary_fn_1']);
                    
                    if (!empty($application['beneficiary_mi_1'])) {
                        $pdf->SetXY(95, $beneficiary_y_start);
                        $pdf->Write(0, $application['beneficiary_mi_1'] . '.');
                    }
                    
                    if (!empty($application['beneficiary_birthdate_1'])) {
                        $birthdate = strtotime($application['beneficiary_birthdate_1']);
                        
                        // month    
                        $pdf->SetXY(106, $beneficiary_y_start);
                        $pdf->Write(0, date('m', $birthdate));

                        // day
                        $pdf->SetXY(117, $beneficiary_y_start);
                        $pdf->Write(0, date('d', $birthdate));
                        
                        // year
                        $pdf->SetXY(125.5, $beneficiary_y_start);
                        $pdf->Write(0, date('Y', $birthdate));
                    }
                    
                    //beneficiary gender
                    $pdf->SetXY(140, $beneficiary_y_start);
                    $pdf->Write(0, $application['beneficiary_gender_1']);
                    
                    //beneficiary relationship
                    $pdf->SetXY(151, $beneficiary_y_start);
                    $pdf->Write(0, $application['beneficiary_relationship_1']); 

                    // beneficiary dependent    
                    if ($application['beneficiary_dependent_1'] == 1) {
                    $pdf->SetXY(196.9, $beneficiary_y_start);
                    $pdf->Write(0, '/'); 
                    }
                }

                // Second beneficiary
                if (!empty($application['beneficiary_fn_2'])) {
                    $pdf->SetXY(5, $beneficiary_y_start + $beneficiary_y_increment);
                    $pdf->Write(0, $application['beneficiary_ln_2']);
                    
                    $pdf->SetXY(50, $beneficiary_y_start + $beneficiary_y_increment);
                    $pdf->Write(0, $application['beneficiary_fn_2']);
                    
                    if (!empty($application['beneficiary_mi_2'])) {
                        $pdf->SetXY(95, $beneficiary_y_start + $beneficiary_y_increment);
                        $pdf->Write(0, $application['beneficiary_mi_2'] . '.');
                    }
                    
                    if (!empty($application['beneficiary_birthdate_2'])) {
                        $birthdate = strtotime($application['beneficiary_birthdate_2']);
                        
                        // month    
                        $pdf->SetXY(106, $beneficiary_y_start + $beneficiary_y_increment);
                        $pdf->Write(0, date('m', $birthdate));

                        // day
                        $pdf->SetXY(117, $beneficiary_y_start + $beneficiary_y_increment);
                        $pdf->Write(0, date('d', $birthdate));
                        
                        // year
                        $pdf->SetXY(125.5, $beneficiary_y_start + $beneficiary_y_increment);
                        $pdf->Write(0, date('Y', $birthdate));
                    }
                    
                    //beneficiary gender
                    $pdf->SetXY(140, $beneficiary_y_start + $beneficiary_y_increment);
                    $pdf->Write(0, $application['beneficiary_gender_2']);
                    
                    //beneficiary relationship
                    $pdf->SetXY(151, $beneficiary_y_start + $beneficiary_y_increment);
                    $pdf->Write(0, $application['beneficiary_relationship_2']); 

                    // beneficiary dependent    
                    if ($application['beneficiary_dependent_2'] == 1) {
                    $pdf->SetXY(196.9, $beneficiary_y_start + $beneficiary_y_increment);
                    $pdf->Write(0, '/'); 
                    }
                }

                // Third beneficiary
                if (!empty($application['beneficiary_fn_3'])) {
                    $pdf->SetXY(5, $beneficiary_y_start + ($beneficiary_y_increment * 2));
                    $pdf->Write(0, $application['beneficiary_ln_3']);
                    
                    $pdf->SetXY(50, $beneficiary_y_start + ($beneficiary_y_increment * 2));
                    $pdf->Write(0, $application['beneficiary_fn_3']);
                    
                    if (!empty($application['beneficiary_mi_3'])) {
                        $pdf->SetXY(95, $beneficiary_y_start + ($beneficiary_y_increment * 2));
                        $pdf->Write(0, $application['beneficiary_mi_3'] . '.');
                    }
                    
                    if (!empty($application['beneficiary_birthdate_3'])) {
                        $birthdate = strtotime($application['beneficiary_birthdate_3']);
                        
                        // month    
                        $pdf->SetXY(106, $beneficiary_y_start + ($beneficiary_y_increment * 2));
                        $pdf->Write(0, date('m', $birthdate));

                        // day
                        $pdf->SetXY(117, $beneficiary_y_start + ($beneficiary_y_increment * 2));
                        $pdf->Write(0, date('d', $birthdate));
                        
                        // year
                        $pdf->SetXY(125.5, $beneficiary_y_start + ($beneficiary_y_increment * 2));
                        $pdf->Write(0, date('Y', $birthdate));
                    }
                    
                    //beneficiary gender
                    $pdf->SetXY(140, $beneficiary_y_start + ($beneficiary_y_increment * 2));
                    $pdf->Write(0, $application['beneficiary_gender_3']);
                    
                    //beneficiary relationship
                    $pdf->SetXY(151, $beneficiary_y_start + ($beneficiary_y_increment * 2));
                    $pdf->Write(0, $application['beneficiary_relationship_3']); 

                    // beneficiary dependent    
                    if ($application['beneficiary_dependent_3'] == 1) {
                    $pdf->SetXY(196.9, $beneficiary_y_start + ($beneficiary_y_increment * 2));
                    $pdf->Write(0, '/'); 
                    }
                }

                // Fourth beneficiary
                if (!empty($application['beneficiary_fn_4'])) {
                    $pdf->SetXY(5, $beneficiary_y_start + ($beneficiary_y_increment * 3));
                    $pdf->Write(0, $application['beneficiary_ln_4']);
                    
                    $pdf->SetXY(50, $beneficiary_y_start + ($beneficiary_y_increment * 3));
                    $pdf->Write(0, $application['beneficiary_fn_4']);
                    
                    if (!empty($application['beneficiary_mi_4'])) {
                        $pdf->SetXY(95, $beneficiary_y_start + ($beneficiary_y_increment * 3));
                        $pdf->Write(0, $application['beneficiary_mi_4'] . '.');
                    }
                    
                    if (!empty($application['beneficiary_birthdate_4'])) {
                        $birthdate = strtotime($application['beneficiary_birthdate_4']);
                        
                        // month    
                        $pdf->SetXY(106, $beneficiary_y_start + ($beneficiary_y_increment * 3));
                        $pdf->Write(0, date('m', $birthdate));

                        // day
                        $pdf->SetXY(117, $beneficiary_y_start + ($beneficiary_y_increment * 3));
                        $pdf->Write(0, date('d', $birthdate));
                        
                        // year
                        $pdf->SetXY(125.5, $beneficiary_y_start + ($beneficiary_y_increment * 3));
                        $pdf->Write(0, date('Y', $birthdate));
                    }
                    
                    //beneficiary gender
                    $pdf->SetXY(140, $beneficiary_y_start + ($beneficiary_y_increment * 3));
                    $pdf->Write(0, $application['beneficiary_gender_4']);
                    
                    //beneficiary relationship
                    $pdf->SetXY(151, $beneficiary_y_start + ($beneficiary_y_increment * 3));
                    $pdf->Write(0, $application['beneficiary_relationship_4']); 

                    // beneficiary dependent    
                    if ($application['beneficiary_dependent_4'] == 1) {
                    $pdf->SetXY(196.9, $beneficiary_y_start + ($beneficiary_y_increment * 3));
                    $pdf->Write(0, '/'); 
                    }
                }

                // Fifth beneficiary
                if (!empty($application['beneficiary_fn_5'])) {
                    $pdf->SetXY(5, $beneficiary_y_start + ($beneficiary_y_increment * 4));
                    $pdf->Write(0, $application['beneficiary_ln_5']);
                    
                    $pdf->SetXY(50, $beneficiary_y_start + ($beneficiary_y_increment * 4));
                    $pdf->Write(0, $application['beneficiary_fn_5']);
                    
                    if (!empty($application['beneficiary_mi_5'])) {
                        $pdf->SetXY(95, $beneficiary_y_start + ($beneficiary_y_increment * 4));
                        $pdf->Write(0, $application['beneficiary_mi_5'] . '.');
                    }
                    
                    if (!empty($application['beneficiary_birthdate_5'])) {
                        $birthdate = strtotime($application['beneficiary_birthdate_5']);
                        
                        // month    
                        $pdf->SetXY(106, $beneficiary_y_start + ($beneficiary_y_increment * 4));
                        $pdf->Write(0, date('m', $birthdate));

                        // day
                        $pdf->SetXY(117, $beneficiary_y_start + ($beneficiary_y_increment * 4));
                        $pdf->Write(0, date('d', $birthdate));
                        
                        // year
                        $pdf->SetXY(125.5, $beneficiary_y_start + ($beneficiary_y_increment * 4));
                        $pdf->Write(0, date('Y', $birthdate));
                    }
                    
                    //beneficiary gender
                    $pdf->SetXY(140, $beneficiary_y_start + ($beneficiary_y_increment * 4));
                    $pdf->Write(0, $application['beneficiary_gender_5']);
                    
                    //beneficiary relationship
                    $pdf->SetXY(151, $beneficiary_y_start + ($beneficiary_y_increment * 4));
                    $pdf->Write(0, $application['beneficiary_relationship_5']); 

                    // beneficiary dependent    
                    if ($application['beneficiary_dependent_5'] == 1) {
                        $pdf->SetXY(196.9, $beneficiary_y_start + ($beneficiary_y_increment * 4));
                        $pdf->Write(0, '/'); 
                    }
                }

                // Trustee Information
                if (!empty($application['trustee_name'])) {
                    $pdf->SetXY(47, 167.2);
                    $pdf->Write(0, $application['trustee_name']);
                    
                    if (!empty($application['trustee_birthdate'])) {
                        $pdf->SetXY(120, 167.2);
                        $pdf->Write(0, date('m/d/Y', strtotime($application['trustee_birthdate'])));
                    }
                    
                    if (!empty($application['trustee_relationship'])) {
                        $pdf->SetXY(150, 167.2);
                        $pdf->Write(0, $application['trustee_relationship']);
                    }
                }

                // Member Signature Image PAGE 1
                if (!empty($application['member_signature'])) {
                    $dbPathMember = $application['member_signature'];
                
                    // Remove the leading slash from the database path
                    $cleanedDbPathMember = ltrim($dbPathMember, '/');
                
                    // Construct the relative path from the script's location (TSPI/admin)
                    // to the signatures directory (TSPI/uploads/signatures)
                    $imagePathMember = '../' . $cleanedDbPathMember;
                
                    // Resolve the relative path to an absolute file system path
                    $resolvedImagePathMember = realpath($imagePathMember);
                
                    // Check if the file exists at the resolved path before attempting to insert
                    if ($resolvedImagePathMember && file_exists($resolvedImagePathMember)) {
                        $pdf->Image($resolvedImagePathMember, 32, 232.5, 30, 15);
                    } else {
     
                    }
                }

                // Date Signed PAGE 1
                $pdf->SetY(237.5);
                $pdf->SetX(123);
                $pdf->Write(0, date('F j, Y', strtotime($application['created_at'])));

                $pdf->SetXY(172.5, 237.5); // CID No
                $pdf->Write(0, $application['cid_no']);
            

            } 
            elseif ($pageNo == 2) {
                // PAGE 2 - Business Information, Beneficiaries, Trustee
                
                // Member Signature Name
                $startX1 = 0;
                $widthOfCenteringArea = 112.5; // 170 - 40 = 130

                $pdf->SetY(253.5);
                $pdf->SetX($startX1); // Move to your desired starting X
                $pdf->Cell($widthOfCenteringArea, 10, $application['member_name'], 0, 0, 'C');
              
                // Member Signature Image
                if (!empty($application['member_signature'])) {
                    $dbPathMember = $application['member_signature'];
                
                    // Remove the leading slash from the database path
                    $cleanedDbPathMember = ltrim($dbPathMember, '/');
                
                    // Construct the relative path from the script's location (TSPI/admin)
                    // to the signatures directory (TSPI/uploads/signatures)
                    $imagePathMember = '../' . $cleanedDbPathMember;
                
                    // Resolve the relative path to an absolute file system path
                    $resolvedImagePathMember = realpath($imagePathMember);
                
                    // Check if the file exists at the resolved path before attempting to insert
                    if ($resolvedImagePathMember && file_exists($resolvedImagePathMember)) {
                        $pdf->Image($resolvedImagePathMember, 95, 247.5, 40, 20);
                    } else {
     
                    }
                }
               
                // Signed Date Member
                $pdf->SetY(257);
                $pdf->SetX(153);
                $pdf->Write(0, date('F j, Y', strtotime($application['created_at'])));
            


                
                if (!empty($application['sig_beneficiary_name'])) {
                // Beneficiary Signature Name
                $startX2 = 5;
                $widthOfCenteringArea = 112.5; // 170 - 40 = 130

                $pdf->SetY(269);
                $pdf->SetX($startX2); // Move to your desired starting X
                $pdf->Cell($widthOfCenteringArea, 10, $application['sig_beneficiary_name'], 0, 0, 'C');
              
                // Beneficiary Signature Image
       
                    $dbPathBeneficiary = $application['beneficiary_signature'];
                
                    // Remove the leading slash from the database path
                    $cleanedDbPathBeneficiary = ltrim($dbPathBeneficiary, '/');
                
                    // Construct the relative path
                    $imagePathBeneficiary = '../' . $cleanedDbPathBeneficiary;
                
                    // Resolve the relative path to an absolute file system path
                    $resolvedImagePathBeneficiary = realpath($imagePathBeneficiary);
                
                    // Check if the file exists at the resolved path before attempting to insert
                    if ($resolvedImagePathBeneficiary && file_exists($resolvedImagePathBeneficiary)) {
                        // Insert Beneficiary Signature Image (example coordinates, adjusted from member's)
                        $pdf->Image($resolvedImagePathBeneficiary, 95, 257.5, 40, 20);
                        }
              
                    
                $pdf->SetY(272);
                $pdf->SetX(153);
                $pdf->Write(0, date('F j, Y', strtotime($application['created_at'])));
            
                }
                
            }
            
            // Add status information at bottom of last page
       
        }
    } catch (Exception $e) {
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Error loading template: ' . $e->getMessage(), 0, 1, 'C');
    }
}

// Output the PDF
$filename = 'TSPI_Membership_' . $application['id'] . '_' . str_replace(' ', '_', $application['last_name']) . '.pdf';

if ($mode === 'download') {
    // Output as download
    $pdf->Output($filename, 'D'); // 'D' means download
} else {
    // Output inline (preview in browser)
    $pdf->Output($filename, 'I'); // 'I' means inline
}
exit; 