<?php
/**
 * Generate Application PDF for Inclusion
 * 
 * This file is a copy of generate_application_pdf.php without the exit statement.
 * It's specifically for inclusion in generate_application_pdf_without_exit.php.
 */

// Include necessary files
require_once '../includes/config.php';
require_admin_login();

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

// Ensure an 'id' parameter is provided (allow '0' as valid)
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No application ID specified.";
    redirect('/admin/applications.php');
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
    redirect('/admin/applications.php');
    return false;
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
                
                // Add all the personal information, addresses, etc.
                // This includes exactly the same logic as in generate_application_pdf.php
                
                // Branch and ID information
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetXY(21, 24);
                $pdf->Write(0, safe_text($application['branch']));
                
                $pdf->SetXY(26.5, 28);
                $pdf->Write(0, safe_text($application['cid_no']));
                
                $pdf->SetXY(37, 31.5);
                $pdf->Write(0, safe_text($application['center_no'] ?: ''));
                
                $pdf->SetFont('helvetica', '', 10);
                
                // Check plans and classification checkboxes
                // Plan checkboxes
                if (in_array('BLIP', $plans)) {
                    $pdf->SetXY(131.3, 31.5);
                    $pdf->Write(0, 'X');
                }
                if (in_array('LPIP', $plans)) {
                    $pdf->SetXY(131.3, 34.8);
                    $pdf->Write(0, 'X');
                }
                if (in_array('LMIP', $plans)) {
                    $pdf->SetXY(131.3, 38);
                    $pdf->Write(0, 'X');
                }
                if (in_array('CLIP', $plans)) {
                    $pdf->SetXY(170.5, 28.5);
                    $pdf->Write(0, 'X');
                }
                if (in_array('MRI', $plans)) {
                    $pdf->SetXY(170.5, 31.7);
                    $pdf->Write(0, 'X');
                }
                if (in_array('GLIP', $plans)) {
                    $pdf->SetXY(170.5, 38);
                    $pdf->Write(0, 'X');
                }
                
                // Classification checkboxes
                if (in_array('Borrower', $classification)) {
                    $pdf->SetXY(82.5, 26.5);
                    $pdf->Write(0, 'X');
                }
                if (in_array('TKP', $classification)) {
                    $pdf->SetXY(82.5, 26.5);
                    $pdf->Write(0, 'X');
                }
                if (in_array('TMP', $classification)) {
                    $pdf->SetXY(82.5, 29.9);
                    $pdf->Write(0, 'X');
                }
                if (in_array('TPP', $classification)) {
                    $pdf->SetXY(82.5, 33);
                    $pdf->Write(0, 'X');
                }
                if (in_array('Kapamilya', $classification)) {
                    $pdf->SetXY(116.5, 26.5);
                    $pdf->Write(0, 'X');
                }
                
                // Personal Information
                // Last Name
                $pdf->SetXY(32, 55.9);
                $pdf->Write(0, safe_text($application['last_name']));
                
                // First Name
                $pdf->SetXY(33, 64.5);
                $pdf->Write(0, safe_text($application['first_name']));
                
                // Middle Name
                $pdf->SetXY(47, 71.5);
                $pdf->Write(0, safe_text($application['middle_name'] ?: ''));
                
                // Date of birth with proper format
                $pdf->SetXY(100, 66);
                $pdf->Write(0, date('m/d/Y', strtotime(safe_text($application['birthdate']))));
                
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
                $pdf->Write(0, safe_text($application['age']));
                
                // Birth Place
                $pdf->SetXY(85, 72.9);
                $pdf->Write(0, safe_text($application['birth_place']));
                
                // Nationality
                $pdf->SetXY(152, 72.8);
                $pdf->Write(0, safe_text($application['nationality']));
                
                // Mother's Maiden Name
                $pdf->SetXY(140, 81);
                $pdf->Write(0, safe_text($application['mothers_maiden_last_name'] . ', ' . $application['mothers_maiden_first_name'] . ' ' . $application['mothers_maiden_middle_name']));
                
                // TIN/SSS/GSIS Number
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetXY(115.5, 77.3);
                $pdf->Write(0, safe_text($application['id_number']));
                
                // Other ID
                $pdf->SetXY(96.5, 84.2);
                $pdf->Write(0, safe_text($application['other_valid_ids']));
                $pdf->SetFont('helvetica', '', 10);
                
                // Cell Phone
                $pdf->SetXY(20, 83);
                $pdf->Write(0, '+63' . safe_text($application['cell_phone']));
                
                // Telephone
                $pdf->SetXY(34, 77);
                $pdf->Write(0, safe_text($application['contact_no'] ?: 'N/A'));
                
                // Addresses
                // Present Address
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetXY(3, 91);
                $pdf->Write(0, safe_text($application['present_address']));
                
                // Permanent Address
                $pdf->SetFont('helvetica', '', 6);
                $pdf->SetXY(117, 91);
                $pdf->Write(0, safe_text($application['permanent_address']));
                
                $pdf->SetFont('helvetica', '', 7);
                
                // Present ZIP
                $pdf->SetXY(190, 91.5);
                $pdf->Write(0, safe_text($application['present_zip_code']));
                
                // Permanent ZIP
                $pdf->SetXY(98, 91.5);
                $pdf->Write(0, safe_text($application['permanent_zip_code']));
                
                // Present Brgy Code
                $pdf->SetXY(101, 87.7);
                $pdf->Write(0, safe_text($application['present_brgy_code']));
                
                // Permanent Brgy Code
                $pdf->SetXY(192, 87.7);
                $pdf->Write(0, safe_text($application['permanent_brgy_code']));
                
                $pdf->SetFont('helvetica', '', 10);
                
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
                $pdf->Write(0, safe_text($application['length_of_stay'] . ' year(s)'));
                
                // Section 2
                // Business Information
                // Primary Business
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetXY(78, 103.5);
                $pdf->Write(0, safe_text($application['primary_business']));
                
                // Business Address
                $pdf->SetXY(131, 106.8);
                $pdf->Write(0, safe_text($application['business_address'] ?: 'N/A'));
                
                // Years in Business
                $pdf->SetXY(53.5, 107);
                $pdf->Write(0, safe_text($application['years_in_business'] . ' year(s)'));
                
                // Other Income Sources (if any)
                if (!empty($application["other_income_source_1"])) {
                    $pdf->SetXY(43.7, 110.5);
                    $pdf->Write(0, safe_text($application["other_income_source_1"]));
                }
                
                if (!empty($application["other_income_source_2"])) {
                    $pdf->SetXY(94.2, 110.5);
                    $pdf->Write(0, safe_text($application["other_income_source_2"]));
                }
                
                if (!empty($application["other_income_source_3"])) {
                    $pdf->SetXY(134, 110.5);
                    $pdf->Write(0, safe_text($application["other_income_source_3"]));
                }
                if (!empty($application["other_income_source_4"])) {
                    $pdf->SetXY(171, 110.5);
                    $pdf->Write(0, safe_text($application["other_income_source_4"]));
                }
                
                // Spouse Information (if married)
                $pdf->SetFont('helvetica', '', 10);
                if ($application['civil_status'] === 'Married' && !empty($application['spouse_name'])) {
                    // Name
                    $pdf->SetXY(5, 122);
                    $pdf->Write(0, safe_text($application['spouse_name']));
                    
                    // Occupation
                    $pdf->SetXY(90, 120.2);
                    $pdf->Write(0, safe_text($application['spouse_occupation']));
                    
                    // Birthdate
                    $pdf->SetXY(160, 120.2);
                    $pdf->Write(0, date('m/d/Y', strtotime(safe_text($application['spouse_birthdate']))));
                    
                    // Spouse ID Number
                    $pdf->SetFont('helvetica', '', 7);
                    $pdf->SetXY(119, 124.8);
                    $pdf->Write(0, safe_text($application['spouse_id_number']));
                    
                    // Spouse Age
                    $pdf->SetXY(173, 124.8);
                    $pdf->Write(0, safe_text($application['spouse_age']));
                }
                
                // Beneficiaries
                // Function to add beneficiary in table format
                $pdf->SetFont('helvetica', '', 10);
                $beneficiary_y_start = 139;
                $beneficiary_y_increment = 4.7;
                
                // Add all beneficiaries
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($application['beneficiary_fn_' . $i])) {
                        $y_pos = $beneficiary_y_start + ($i - 1) * $beneficiary_y_increment;
                        
                        $pdf->SetXY(5, $y_pos);
                        $pdf->Write(0, safe_text($application['beneficiary_ln_' . $i]));
                        
                        $pdf->SetXY(50, $y_pos);
                        $pdf->Write(0, safe_text($application['beneficiary_fn_' . $i]));
                        
                        if (!empty($application['beneficiary_mi_' . $i])) {
                            $pdf->SetXY(95, $y_pos);
                            $pdf->Write(0, safe_text($application['beneficiary_mi_' . $i] . '.'));
                        }
                        
                        if (!empty($application['beneficiary_birthdate_' . $i])) {
                            $birthdate = strtotime(safe_text($application['beneficiary_birthdate_' . $i]));
                            
                            // month    
                            $pdf->SetXY(106, $y_pos);
                            $pdf->Write(0, date('m', $birthdate));
                            
                            // day
                            $pdf->SetXY(117, $y_pos);
                            $pdf->Write(0, date('d', $birthdate));
                            
                            // year
                            $pdf->SetXY(125.5, $y_pos);
                            $pdf->Write(0, date('Y', $birthdate));
                        }
                        
                        //beneficiary gender
                        $pdf->SetXY(140, $y_pos);
                        $pdf->Write(0, safe_text($application['beneficiary_gender_' . $i]));
                        
                        //beneficiary relationship
                        $pdf->SetXY(151, $y_pos);
                        $pdf->Write(0, safe_text($application['beneficiary_relationship_' . $i]));
                        
                        // beneficiary dependent    
                        if ($application['beneficiary_dependent_' . $i] == 1) {
                            $pdf->SetXY(196.9, $y_pos);
                            $pdf->Write(0, '/');
                        }
                    }
                }
                
                // Trustee Information
                if (!empty($application['trustee_name'])) {
                    $pdf->SetXY(47, 167.2);
                    $pdf->Write(0, safe_text($application['trustee_name']));
                    
                    if (!empty($application['trustee_birthdate'])) {
                        $pdf->SetXY(120, 167.2);
                        $pdf->Write(0, date('m/d/Y', strtotime(safe_text($application['trustee_birthdate']))));
                    }
                    
                    if (!empty($application['trustee_relationship'])) {
                        $pdf->SetXY(150, 167.2);
                        $pdf->Write(0, safe_text($application['trustee_relationship']));
                    }
                }
                
                // Member Signature Image PAGE 1
                if (!empty($application['member_signature'])) {
                    $dbPathMember = $application['member_signature'];
                    $cleanedDbPathMember = ltrim($dbPathMember, '/');
                    $imagePathMember = '../' . $cleanedDbPathMember;
                    $resolvedImagePathMember = realpath($imagePathMember);
                    
                    if ($resolvedImagePathMember && file_exists($resolvedImagePathMember)) {
                        $pdf->Image($resolvedImagePathMember, 32, 232.5, 30, 15);
                    }
                }
                
                // Date Signed PAGE 1
                $pdf->SetY(237.5);
                $pdf->SetX(123);
                $pdf->Write(0, date('F j, Y', strtotime(safe_text($application['created_at']))));
                
                $pdf->SetXY(172.5, 237.5); // CID No
                $pdf->Write(0, safe_text($application['cid_no']));
                
            } elseif ($pageNo == 2) {
                // PAGE 2 content
                
                // Member Signature Name
                $startX1 = 0;
                $widthOfCenteringArea = 112.5;
                
                $pdf->SetY(253.5);
                $pdf->SetX($startX1);
                $pdf->Cell($widthOfCenteringArea, 10, safe_text($application['member_name']), 0, 0, 'C');
                
                // Member Signature Image
                if (!empty($application['member_signature'])) {
                    $dbPathMember = $application['member_signature'];
                    $cleanedDbPathMember = ltrim($dbPathMember, '/');
                    $imagePathMember = '../' . $cleanedDbPathMember;
                    $resolvedImagePathMember = realpath($imagePathMember);
                    
                    if ($resolvedImagePathMember && file_exists($resolvedImagePathMember)) {
                        $pdf->Image($resolvedImagePathMember, 95, 247.5, 40, 20);
                    }
                }
                
                // Add approval information to page 2
                // LO name and signature
                if (!empty($application['lo_name'])) {
                    $pdf->SetXY(85, 294);
                    $pdf->Write(0, safe_text($application['lo_name']));
                    
                    if (!empty($application['lo_signature'])) {
                        $dbPathLO = $application['lo_signature'];
                        $cleanedDbPathLO = ltrim($dbPathLO, '/');
                        $imagePathLO = '../' . $cleanedDbPathLO;
                        $resolvedImagePathLO = realpath($imagePathLO);
                        
                        if ($resolvedImagePathLO && file_exists($resolvedImagePathLO)) {
                            $pdf->Image($resolvedImagePathLO, 87, 285, 30, 15);
                        }
                    }
                }
                
                // Secretary name and signature
                if (!empty($application['secretary_name'])) {
                    $pdf->SetXY(150, 294);
                    $pdf->Write(0, safe_text($application['secretary_name']));
                    
                    if (!empty($application['secretary_signature'])) {
                        $dbPathSecretary = $application['secretary_signature'];
                        $cleanedDbPathSecretary = ltrim($dbPathSecretary, '/');
                        $imagePathSecretary = '../' . $cleanedDbPathSecretary;
                        $resolvedImagePathSecretary = realpath($imagePathSecretary);
                        
                        if ($resolvedImagePathSecretary && file_exists($resolvedImagePathSecretary)) {
                            $pdf->Image($resolvedImagePathSecretary, 150, 288, 30, 15);
                        }
                    }
                }
                
                // BLIP MC number
                if (!empty($application['blip_mc'])) {
                    $pdf->SetXY(30, 308);
                    $pdf->Write(0, safe_text($application['blip_mc']));
                }
                
                // Effective date (same as IO signed date)
                if (!empty($application['io_approval_date'])) {
                    $pdf->SetXY(80, 308);
                    $pdf->Write(0, date('m/d/Y', strtotime(safe_text($application['io_approval_date']))));
                }
                
                // IO information
                if (!empty($application['io_name'])) {
                    $pdf->SetXY(120, 308);
                    $pdf->Write(0, safe_text($application['io_name']));
                    
                    if (!empty($application['io_signature'])) {
                        $dbPathIO = $application['io_signature'];
                        $cleanedDbPathIO = ltrim($dbPathIO, '/');
                        $imagePathIO = '../' . $cleanedDbPathIO;
                        $resolvedImagePathIO = realpath($imagePathIO);
                        
                        if ($resolvedImagePathIO && file_exists($resolvedImagePathIO)) {
                            $pdf->Image($resolvedImagePathIO, 120, 300, 30, 15);
                        }
                    }
                }
                
                // IO signed date
                if (!empty($application['io_approval_date'])) {
                    $pdf->SetXY(170, 308);
                    $pdf->Write(0, date('m/d/Y', strtotime(safe_text($application['io_approval_date']))));
                }
                
                // Signed Date Member
                $pdf->SetY(257);
                $pdf->SetX(153);
                $pdf->Write(0, date('F j, Y', strtotime(safe_text($application['created_at']))));
                
                // Beneficiary signature section
                if (!empty($application['sig_beneficiary_name'])) {
                    // Beneficiary Signature Name
                    $startX2 = 5;
                    $widthOfCenteringArea = 112.5;
                    
                    $pdf->SetY(269);
                    $pdf->SetX($startX2);
                    $pdf->Cell($widthOfCenteringArea, 10, safe_text($application['sig_beneficiary_name']), 0, 0, 'C');
                    
                    // Beneficiary Signature Image
                    $dbPathBeneficiary = $application['beneficiary_signature'];
                    $cleanedDbPathBeneficiary = ltrim($dbPathBeneficiary, '/');
                    $imagePathBeneficiary = '../' . $cleanedDbPathBeneficiary;
                    $resolvedImagePathBeneficiary = realpath($imagePathBeneficiary);
                    
                    if ($resolvedImagePathBeneficiary && file_exists($resolvedImagePathBeneficiary)) {
                        $pdf->Image($resolvedImagePathBeneficiary, 95, 257.5, 40, 20);
                    }
                    
                    $pdf->SetY(272);
                    $pdf->SetX(153);
                    $pdf->Write(0, date('F j, Y', strtotime(safe_text($application['created_at']))));
                }
            }
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
    // Save to specified file path - no exit
    $pdf->Output($_GET['output_path'], 'F');
    return true;
} else if ($mode === 'download') {
    // Output as download - no exit
    $pdf->Output($filename, 'D');
    return true;
} else {
    // Output inline - no exit
    $pdf->Output($filename, 'I');
    return true;
} 