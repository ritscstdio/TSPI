<?php
$page_title = "View Application";
$body_class = "admin-view-application-page";
require_once '../includes/config.php';
require_admin_login();

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = "No application ID specified.";
    redirect('applications.php');
}

// Fetch the application details
$stmt = $pdo->prepare("SELECT * FROM members_information WHERE id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    $_SESSION['message'] = "Application not found.";
    redirect('applications.php');
}

// Generate PDF logic
if (isset($_POST['generate_pdf'])) {
    require_once '../vendor/autoload.php'; // Make sure TCPDF is installed
    
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('TSPI CMS');
    $pdf->SetAuthor('TSPI Admin');
    $pdf->SetTitle('Membership Application - ' . $application['first_name'] . ' ' . $application['last_name']);
    $pdf->SetSubject('Membership Application');
    
    // Remove header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 12);
    
    // Title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'TSPI Membership Application Form', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(5);
    
    // Application ID and Date
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Application #' . $application['id'] . ' - Submitted: ' . $application['created_at'], 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(5);
    
    // Basic Information
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Personal Information', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    // Convert JSON data to arrays
    $plans = $application['plans'] ? json_decode($application['plans'], true) : [];
    $classification = $application['classification'] ? json_decode($application['classification'], true) : [];
    
    // Personal Information Table
    $pdf->SetFillColor(240, 240, 240);
    
    // Function to add a row to the PDF
    function addRow($pdf, $label, $value, $fill = false) {
        $pdf->Cell(60, 10, $label, 1, 0, 'L', $fill);
        $pdf->Cell(130, 10, $value, 1, 1, 'L', $fill);
    }
    
    // Branch & ID Information
    addRow($pdf, 'Branch', $application['branch'], true);
    addRow($pdf, 'CID No.', $application['cid_no']);
    addRow($pdf, 'Center No.', $application['center_no'] ?: 'N/A', true);
    
    // Plans and Classification
    addRow($pdf, 'Plans', implode(', ', $plans));
    addRow($pdf, 'Classification', implode(', ', $classification), true);
    
    // Basic Personal Information
    addRow($pdf, 'Name', $application['first_name'] . ' ' . $application['middle_name'] . ' ' . $application['last_name']);
    addRow($pdf, 'Gender', $application['gender'], true);
    addRow($pdf, 'Civil Status', $application['civil_status']);
    addRow($pdf, 'Birth Date', date('F j, Y', strtotime($application['birthdate'])), true);
    addRow($pdf, 'Age', $application['age']);
    addRow($pdf, 'Birth Place', $application['birth_place'], true);
    addRow($pdf, 'Email', $application['email']);
    addRow($pdf, 'Phone', '+63' . $application['cell_phone'], true);
    addRow($pdf, 'Telephone', $application['contact_no'] ?: 'N/A');
    addRow($pdf, 'Nationality', $application['nationality'], true);
    addRow($pdf, 'ID Number', $application['id_number']);
    
    // Mother's Maiden Name
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, "Mother's Maiden Name", 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    addRow($pdf, 'Last Name', $application['mothers_maiden_last_name'], true);
    addRow($pdf, 'First Name', $application['mothers_maiden_first_name']);
    addRow($pdf, 'Middle Name', $application['mothers_maiden_middle_name'] ?: 'N/A', true);
    
    // Add a new page for addresses and other information
    $pdf->AddPage();
    
    // Present Address
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Present Address', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    $presentAddress = $application['present_address'] . ', ' . 
                      $application['present_barangay_text'] . ', ' . 
                      $application['present_city_text'] . ', ' . 
                      $application['present_province_text'] . ', ' . 
                      $application['present_region_text'];
    
    addRow($pdf, 'Complete Address', $presentAddress, true);
    addRow($pdf, 'ZIP Code', $application['present_zip_code']);
    
    // Permanent Address
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Permanent Address', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    $permanentAddress = $application['permanent_address'] . ', ' . 
                       $application['permanent_barangay_text'] . ', ' . 
                       $application['permanent_city_text'] . ', ' . 
                       $application['permanent_province_text'] . ', ' . 
                       $application['permanent_region_text'];
    
    addRow($pdf, 'Complete Address', $permanentAddress, true);
    addRow($pdf, 'ZIP Code', $application['permanent_zip_code']);
    
    // Home Ownership
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Home Ownership', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    addRow($pdf, 'Home Ownership', $application['home_ownership'], true);
    addRow($pdf, 'Length of Stay', $application['length_of_stay'] . ' year(s)');
    
    // Business Information
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Business Information', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    addRow($pdf, 'Primary Business', $application['primary_business'], true);
    addRow($pdf, 'Years in Business', $application['years_in_business'] . ' year(s)');
    addRow($pdf, 'Business Address', $application['business_address'], true);
    
    // Other Income Sources
    if (!empty($application['other_income_source_1']) || 
        !empty($application['other_income_source_2']) || 
        !empty($application['other_income_source_3']) || 
        !empty($application['other_income_source_4'])) {
        
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Other Income Sources', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        
        if (!empty($application['other_income_source_1'])) {
            addRow($pdf, 'Income Source 1', $application['other_income_source_1'], true);
        }
        if (!empty($application['other_income_source_2'])) {
            addRow($pdf, 'Income Source 2', $application['other_income_source_2']);
        }
        if (!empty($application['other_income_source_3'])) {
            addRow($pdf, 'Income Source 3', $application['other_income_source_3'], true);
        }
        if (!empty($application['other_income_source_4'])) {
            addRow($pdf, 'Income Source 4', $application['other_income_source_4']);
        }
    }
    
    // Add another page for beneficiaries and spouse information
    $pdf->AddPage();
    
    // Spouse Information (if married)
    if ($application['civil_status'] === 'Married' && !empty($application['spouse_name'])) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Spouse Information', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        
        addRow($pdf, 'Spouse Name', $application['spouse_name'], true);
        if (!empty($application['spouse_birthdate'])) {
            addRow($pdf, 'Birth Date', date('F j, Y', strtotime($application['spouse_birthdate'])));
        }
        if (!empty($application['spouse_occupation'])) {
            addRow($pdf, 'Occupation', $application['spouse_occupation'], true);
        }
        if (!empty($application['spouse_id_number'])) {
            addRow($pdf, 'ID Number', $application['spouse_id_number']);
        }
        
        $pdf->Ln(5);
    }
    
    // Beneficiaries
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Beneficiaries', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    // Table header for beneficiaries
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(60, 10, 'Name', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Birthdate', 1, 0, 'C', true);
    $pdf->Cell(15, 10, 'Gender', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Relationship', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Dependent', 1, 1, 'C', true);
    
    // Reset fill color
    $pdf->SetFillColor(240, 240, 240);
    
    // Function to add a beneficiary row
    function addBeneficiaryRow($pdf, $firstName, $lastName, $mi, $birthdate, $gender, $relationship, $dependent, $fill = false) {
        if (empty($firstName) && empty($lastName)) return;
        
        $name = $firstName . ' ' . ($mi ? $mi . '. ' : '') . $lastName;
        $birthdate = $birthdate ? date('m/d/Y', strtotime($birthdate)) : 'N/A';
        
        $pdf->Cell(60, 10, $name, 1, 0, 'L', $fill);
        $pdf->Cell(25, 10, $birthdate, 1, 0, 'C', $fill);
        $pdf->Cell(15, 10, $gender ?: 'N/A', 1, 0, 'C', $fill);
        $pdf->Cell(50, 10, $relationship ?: 'N/A', 1, 0, 'L', $fill);
        $pdf->Cell(40, 10, $dependent ? 'Yes' : 'No', 1, 1, 'C', $fill);
    }
    
    // Add beneficiary rows
    addBeneficiaryRow(
        $pdf, 
        $application['beneficiary_fn_1'], 
        $application['beneficiary_ln_1'], 
        $application['beneficiary_mi_1'], 
        $application['beneficiary_birthdate_1'], 
        $application['beneficiary_gender_1'], 
        $application['beneficiary_relationship_1'], 
        $application['beneficiary_dependent_1'], 
        true
    );
    
    addBeneficiaryRow(
        $pdf, 
        $application['beneficiary_fn_2'], 
        $application['beneficiary_ln_2'], 
        $application['beneficiary_mi_2'], 
        $application['beneficiary_birthdate_2'], 
        $application['beneficiary_gender_2'], 
        $application['beneficiary_relationship_2'], 
        $application['beneficiary_dependent_2']
    );
    
    addBeneficiaryRow(
        $pdf, 
        $application['beneficiary_fn_3'], 
        $application['beneficiary_ln_3'], 
        $application['beneficiary_mi_3'], 
        $application['beneficiary_birthdate_3'], 
        $application['beneficiary_gender_3'], 
        $application['beneficiary_relationship_3'], 
        $application['beneficiary_dependent_3'], 
        true
    );
    
    addBeneficiaryRow(
        $pdf, 
        $application['beneficiary_fn_4'], 
        $application['beneficiary_ln_4'], 
        $application['beneficiary_mi_4'], 
        $application['beneficiary_birthdate_4'], 
        $application['beneficiary_gender_4'], 
        $application['beneficiary_relationship_4'], 
        $application['beneficiary_dependent_4']
    );
    
    // Trustee Information
    if (!empty($application['trustee_name'])) {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Trustee Information', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        
        addRow($pdf, 'Trustee Name', $application['trustee_name'], true);
        if (!empty($application['trustee_birthdate'])) {
            addRow($pdf, 'Birth Date', date('F j, Y', strtotime($application['trustee_birthdate'])));
        }
        if (!empty($application['trustee_relationship'])) {
            addRow($pdf, 'Relationship', $application['trustee_relationship'], true);
        }
    }
    
    // Add a final page for signatures if they exist
    if (!empty($application['member_signature']) || !empty($application['beneficiary_signature'])) {
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Signatures', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        
        if (!empty($application['member_signature'])) {
            $pdf->Cell(0, 10, "Member's Signature:", 0, 1);
            // Add member signature image
            if (file_exists("../" . $application['member_signature'])) {
                $pdf->Image("../" . $application['member_signature'], 15, $pdf->GetY(), 80, 0, 'PNG');
                $pdf->Ln(30); // Add space after the signature
            } else {
                $pdf->Cell(0, 10, "[Signature file not found]", 0, 1);
            }
            
            $pdf->Cell(0, 10, "Member Name: " . $application['member_name'], 0, 1);
            $pdf->Ln(5);
        }
        
        if (!empty($application['beneficiary_signature'])) {
            $pdf->Cell(0, 10, "Beneficiary's Signature:", 0, 1);
            // Add beneficiary signature image
            if (file_exists("../" . $application['beneficiary_signature'])) {
                $pdf->Image("../" . $application['beneficiary_signature'], 15, $pdf->GetY(), 80, 0, 'PNG');
                $pdf->Ln(30); // Add space after the signature
            } else {
                $pdf->Cell(0, 10, "[Signature file not found]", 0, 1);
            }
            
            $pdf->Cell(0, 10, "Beneficiary Name: " . $application['sig_beneficiary_name'], 0, 1);
        }
    }
    
    // Status information
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Application Status: ' . ucfirst($application['status']), 0, 1);
    
    // Output the PDF
    $filename = 'TSPI_Membership_' . $application['id'] . '_' . str_replace(' ', '_', $application['last_name']) . '.pdf';
    $pdf->Output($filename, 'D'); // 'D' means download
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - TSPI CMS</title>
    <link rel="icon" type="image/png" href="../src/assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="content-container">
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="page-header">
                    <h1>View Membership Application</h1>
                    <div class="action-buttons">
                        <a href="applications.php" class="btn-secondary">Back to List</a>
                        
                        <form method="post" style="display: inline-block;">
                            <button type="submit" name="generate_pdf" class="btn-primary">
                                <i class="fas fa-file-pdf"></i> Generate PDF
                            </button>
                        </form>
                        
                        <?php if ($application['status'] === 'pending'): ?>
                            <a href="verify_application.php?id=<?php echo $application['id']; ?>&action=approved" class="btn-success">Approve</a>
                            <a href="verify_application.php?id=<?php echo $application['id']; ?>&action=rejected" class="btn-danger">Reject</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="application-status status-<?php echo strtolower($application['status']); ?>">
                    Status: <?php echo ucfirst($application['status']); ?>
                </div>
                
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Personal Information</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Branch:</label>
                                    <span><?php echo htmlspecialchars($application['branch']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>CID No:</label>
                                    <span><?php echo htmlspecialchars($application['cid_no']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Center No:</label>
                                    <span><?php echo htmlspecialchars($application['center_no'] ?: 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Plans:</label>
                                    <span>
                                        <?php 
                                        $plans = json_decode($application['plans'], true);
                                        echo $plans ? htmlspecialchars(implode(', ', $plans)) : 'None';
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Classification:</label>
                                    <span>
                                        <?php 
                                        $classification = json_decode($application['classification'], true);
                                        echo $classification ? htmlspecialchars(implode(', ', $classification)) : 'None';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Full Name:</label>
                                    <span><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['middle_name'] . ' ' . $application['last_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Gender:</label>
                                    <span><?php echo htmlspecialchars($application['gender']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Civil Status:</label>
                                    <span><?php echo htmlspecialchars($application['civil_status']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Birthdate:</label>
                                    <span><?php echo date('F j, Y', strtotime($application['birthdate'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Age:</label>
                                    <span><?php echo htmlspecialchars($application['age']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Birth Place:</label>
                                    <span><?php echo htmlspecialchars($application['birth_place']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Email:</label>
                                    <span><?php echo htmlspecialchars($application['email']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Phone:</label>
                                    <span>+63<?php echo htmlspecialchars($application['cell_phone']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Telephone:</label>
                                    <span><?php echo htmlspecialchars($application['contact_no'] ?: 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Nationality:</label>
                                    <span><?php echo htmlspecialchars($application['nationality']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>ID Number:</label>
                                    <span><?php echo htmlspecialchars($application['id_number']); ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($application['other_valid_ids'])): ?>
                            <div class="info-group full-width">
                                <div class="info-item">
                                    <label>Other Valid IDs:</label>
                                    <div class="list-data">
                                        <ul>
                                            <?php 
                                            $otherIds = json_decode($application['other_valid_ids'], true);
                                            if (is_array($otherIds)):
                                                foreach ($otherIds as $id):
                                                    echo '<li>' . htmlspecialchars($id) . '</li>';
                                                endforeach;
                                            endif;
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Mother's Maiden Name</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Last Name:</label>
                                    <span><?php echo htmlspecialchars($application['mothers_maiden_last_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>First Name:</label>
                                    <span><?php echo htmlspecialchars($application['mothers_maiden_first_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Middle Name:</label>
                                    <span><?php echo htmlspecialchars($application['mothers_maiden_middle_name'] ?: 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Present Address</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Address:</label>
                                    <span><?php echo htmlspecialchars($application['present_address']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Barangay:</label>
                                    <span><?php echo htmlspecialchars($application['present_barangay_text']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>City/Municipality:</label>
                                    <span><?php echo htmlspecialchars($application['present_city_text']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Province:</label>
                                    <span><?php echo htmlspecialchars($application['present_province_text']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Region:</label>
                                    <span><?php echo htmlspecialchars($application['present_region_text']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>ZIP Code:</label>
                                    <span><?php echo htmlspecialchars($application['present_zip_code']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Permanent Address</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Address:</label>
                                    <span><?php echo htmlspecialchars($application['permanent_address']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Barangay:</label>
                                    <span><?php echo htmlspecialchars($application['permanent_barangay_text']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>City/Municipality:</label>
                                    <span><?php echo htmlspecialchars($application['permanent_city_text']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Province:</label>
                                    <span><?php echo htmlspecialchars($application['permanent_province_text']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Region:</label>
                                    <span><?php echo htmlspecialchars($application['permanent_region_text']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>ZIP Code:</label>
                                    <span><?php echo htmlspecialchars($application['permanent_zip_code']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Home Ownership:</label>
                                    <span><?php echo htmlspecialchars($application['home_ownership']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Length of Stay:</label>
                                    <span><?php echo htmlspecialchars($application['length_of_stay']); ?> year(s)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Business Information</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Primary Business:</label>
                                    <span><?php echo htmlspecialchars($application['primary_business']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Years in Business:</label>
                                    <span><?php echo htmlspecialchars($application['years_in_business']); ?> year(s)</span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Business Address:</label>
                                    <span><?php echo htmlspecialchars($application['business_address']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($application['other_income_source_1']) || 
                                 !empty($application['other_income_source_2']) || 
                                 !empty($application['other_income_source_3']) || 
                                 !empty($application['other_income_source_4'])): ?>
                        <hr>
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Other Income Sources:</label>
                                    <div class="list-data">
                                        <ul>
                                            <?php if (!empty($application['other_income_source_1'])): ?>
                                                <li><?php echo htmlspecialchars($application['other_income_source_1']); ?></li>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($application['other_income_source_2'])): ?>
                                                <li><?php echo htmlspecialchars($application['other_income_source_2']); ?></li>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($application['other_income_source_3'])): ?>
                                                <li><?php echo htmlspecialchars($application['other_income_source_3']); ?></li>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($application['other_income_source_4'])): ?>
                                                <li><?php echo htmlspecialchars($application['other_income_source_4']); ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($application['civil_status'] === 'Married' && !empty($application['spouse_name'])): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Spouse Information</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Name:</label>
                                    <span><?php echo htmlspecialchars($application['spouse_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Birthdate:</label>
                                    <span><?php echo !empty($application['spouse_birthdate']) ? date('F j, Y', strtotime($application['spouse_birthdate'])) : 'N/A'; ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Occupation:</label>
                                    <span><?php echo !empty($application['spouse_occupation']) ? htmlspecialchars($application['spouse_occupation']) : 'N/A'; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>ID Number:</label>
                                    <span><?php echo !empty($application['spouse_id_number']) ? htmlspecialchars($application['spouse_id_number']) : 'N/A'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Beneficiaries</h2>
                    </div>
                    <div class="admin-card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Birthdate</th>
                                    <th>Gender</th>
                                    <th>Relationship</th>
                                    <th>Dependent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                    <?php if (!empty($application["beneficiary_fn_{$i}"]) || !empty($application["beneficiary_ln_{$i}"])): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                echo htmlspecialchars(
                                                    $application["beneficiary_fn_{$i}"] . ' ' . 
                                                    ($application["beneficiary_mi_{$i}"] ? $application["beneficiary_mi_{$i}"] . '. ' : '') . 
                                                    $application["beneficiary_ln_{$i}"]
                                                ); 
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                echo !empty($application["beneficiary_birthdate_{$i}"]) ? 
                                                     date('m/d/Y', strtotime($application["beneficiary_birthdate_{$i}"])) : 
                                                     'N/A'; 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($application["beneficiary_gender_{$i}"] ?: 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($application["beneficiary_relationship_{$i}"] ?: 'N/A'); ?></td>
                                            <td><?php echo $application["beneficiary_dependent_{$i}"] ? 'Yes' : 'No'; ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php if (!empty($application['trustee_name'])): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Trustee Information</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Name:</label>
                                    <span><?php echo htmlspecialchars($application['trustee_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Birthdate:</label>
                                    <span><?php echo !empty($application['trustee_birthdate']) ? date('F j, Y', strtotime($application['trustee_birthdate'])) : 'N/A'; ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Relationship:</label>
                                    <span><?php echo htmlspecialchars($application['trustee_relationship']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Signatures</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="row">
                            <?php if (!empty($application['member_signature'])): ?>
                            <div class="col-md-6">
                                <h3>Member's Signature</h3>
                                <div class="signature-image">
                                    <img src="<?php echo SITE_URL . '/' . $application['member_signature']; ?>" alt="Member Signature">
                                </div>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($application['member_name']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($application['beneficiary_signature'])): ?>
                            <div class="col-md-6">
                                <h3>Beneficiary's Signature</h3>
                                <div class="signature-image">
                                    <img src="<?php echo SITE_URL . '/' . $application['beneficiary_signature']; ?>" alt="Beneficiary Signature">
                                </div>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($application['sig_beneficiary_name']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons-bottom">
                    <a href="applications.php" class="btn-secondary">Back to List</a>
                    
                    <form method="post" style="display: inline-block;">
                        <button type="submit" name="generate_pdf" class="btn-primary">
                            <i class="fas fa-file-pdf"></i> Generate PDF
                        </button>
                    </form>
                    
                    <?php if ($application['status'] === 'pending'): ?>
                        <a href="verify_application.php?id=<?php echo $application['id']; ?>&action=approved" class="btn-success">Approve</a>
                        <a href="verify_application.php?id=<?php echo $application['id']; ?>&action=rejected" class="btn-danger">Reject</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <style>
    /* Custom styles for application view */
    .admin-card {
        margin-bottom: 20px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .admin-card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        background-color: #f8f9fa;
        border-radius: 5px 5px 0 0;
    }
    
    .admin-card-header h2 {
        margin: 0;
        font-size: 1.25rem;
        color: #333;
    }
    
    .admin-card-body {
        padding: 20px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .info-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .info-group.full-width {
        grid-column: 1 / -1;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-item label {
        font-weight: bold;
        color: #666;
        margin-bottom: 3px;
    }
    
    .list-data ul {
        margin: 0;
        padding-left: 20px;
    }
    
    hr {
        margin: 20px 0;
        border: none;
        border-top: 1px solid #eee;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .action-buttons,
    .action-buttons-bottom {
        display: flex;
        gap: 10px;
    }
    
    .action-buttons-bottom {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .application-status {
        margin-bottom: 20px;
        padding: 10px 15px;
        border-radius: 4px;
        font-weight: bold;
    }
    
    .application-status.status-pending {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .application-status.status-approved {
        background-color: #d4edda;
        color: #155724;
    }
    
    .application-status.status-rejected {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: #fff;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary {
        background-color: #007bff;
        color: #fff;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .btn-success {
        background-color: #28a745;
        color: #fff;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-danger {
        background-color: #dc3545;
        color: #fff;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
    }
    
    .signature-image {
        max-width: 300px;
        margin-bottom: 15px;
    }
    
    .signature-image img {
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
    }
    </style>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 