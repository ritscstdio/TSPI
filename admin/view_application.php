<?php
$page_title = "View Application";
$body_class = "admin-view-application-page";
require_once '../includes/config.php';
require_admin_login();

// Ensure an 'id' parameter is provided (allow '0' as valid)
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No application ID specified.";
    redirect('/admin/applications.php');
}
$id = $_GET['id'];

// Fetch the application details
$stmt = $pdo->prepare("SELECT * FROM members_information WHERE id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    $_SESSION['message'] = "Application not found.";
    redirect('/admin/applications.php');
}

// Generate PDF logic
if (isset($_POST['generate_pdf'])) {
    // Use direct HTTP header for redirect to ensure correct path
    header("Location: generate_application_pdf.php?id=" . $application['id']);
    exit;
}

// Get current user role
$current_user = get_admin_user();
$user_role = $current_user['role'] ?? '';

// Check if this user can approve this application
$can_approve_as_io = ($user_role === 'insurance_officer' && $application['io_approved'] === 'pending');
$can_approve_as_lo = ($user_role === 'loan_officer' && $application['lo_approved'] === 'pending');
$can_final_approve = ($user_role === 'secretary' && 
                    $application['io_approved'] === 'approved' && 
                    $application['lo_approved'] === 'approved' && 
                    $application['secretary_approved'] !== 'approved');

// Admin and Moderator cannot approve/deny applications
$can_approve_deny_application = !in_array($user_role, ['admin', 'moderator']);

// Check if any beneficiaries exist
$hasBeneficiaries = false;
for ($i = 1; $i <= 4; $i++) {
    if (!empty($application["beneficiary_fn_{$i}"]) || !empty($application["beneficiary_ln_{$i}"])) {
        $hasBeneficiaries = true;
        break;
    }
}

// Create a helper function to display N/A for empty values
function display_value($value, $is_date = false) {
    if (empty($value) || $value === '') {
        return 'N/A';
    }
    
    if ($is_date) {
        return date('F j, Y', strtotime($value));
    }
    
    return htmlspecialchars($value);
}

// Store certificate dropdown HTML in a variable to use later
$certificate_dropdown_html = '';
$plans = json_decode($application['plans'], true) ?: [];
if (count($plans) > 1): // If multiple plans exist
    $certificate_dropdown_html = '
    <div class="certificate-dropdown">
        <a href="#" class="btn-success dropdown-toggle">
            <i class="fas fa-certificate"></i> View Certificate <i class="fas fa-caret-down"></i>
        </a>
        <div class="dropdown-content dropdown-content-up">';
    foreach ($plans as $plan) {
        $certificate_dropdown_html .= '
        <a href="generate_certificate.php?id='.$application['id'].'&mode=preview&plan='.urlencode($plan).'">
            '.htmlspecialchars($plan).' Certificate
        </a>';
    }
    $certificate_dropdown_html .= '
        </div>
    </div>';
else: // Single plan or no plans
    $certificate_dropdown_html = '
    <a href="generate_certificate.php?id='.$application['id'].'&mode=preview" class="btn-success">
        <i class="fas fa-certificate"></i> View Certificate
    </a>';
endif;

// Now the HTML begins
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
            
            <div class="content-container dashboard-container">
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="page-header">
                    <h1>View Membership Application</h1>
                </div>
                
                <div class="application-section">
                    <h2>Approval Status</h2>
                    <div class="approval-status">
                        <div class="status-item">
                            <span class="status-label">Application Status:</span>
                            <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                                <?php echo ucfirst($application['status']); ?>
                            </span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label">Insurance Officer:</span>
                            <span class="status-badge status-<?php echo strtolower($application['io_approved']); ?>">
                                <?php echo ucfirst($application['io_approved']); ?>
                            </span>
                            <?php if ($application['io_approved'] !== 'pending'): ?>
                                <span class="status-info">
                                    by <?php echo htmlspecialchars($application['io_name'] ?: 'Unknown'); ?>
                                    on <?php echo $application['io_approval_date'] ? date('M j, Y', strtotime($application['io_approval_date'])) : 'N/A'; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label">Loan Officer:</span>
                            <span class="status-badge status-<?php echo strtolower($application['lo_approved']); ?>">
                                <?php echo ucfirst($application['lo_approved']); ?>
                            </span>
                            <?php if ($application['lo_approved'] !== 'pending'): ?>
                                <span class="status-info">
                                    by <?php echo htmlspecialchars($application['lo_name'] ?: 'Unknown'); ?>
                                    on <?php echo $application['lo_approval_date'] ? date('M j, Y', strtotime($application['lo_approval_date'])) : 'N/A'; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- New: Secretary Approval Status -->
                        <?php if ($application['secretary_approved'] || $application['io_approved'] === 'approved' && $application['lo_approved'] === 'approved'): ?>
                        <div class="status-item">
                            <span class="status-label">Secretary:</span>
                            <span class="status-badge status-<?php echo strtolower($application['secretary_approved'] ?: 'pending'); ?>">
                                <?php echo ucfirst($application['secretary_approved'] ?: 'Pending'); ?>
                            </span>
                            <?php if ($application['secretary_approved'] && $application['secretary_approved'] !== 'pending'): ?>
                                <span class="status-info">
                                    by <?php echo htmlspecialchars($application['secretary_name'] ?: 'Unknown'); ?>
                                    on <?php echo $application['secretary_approval_date'] ? date('M j, Y', strtotime($application['secretary_approval_date'])) : 'N/A'; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="approval-actions">
                        <?php if ($can_approve_as_io): ?>
                        <a href="approve_application.php?id=<?php echo $application['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Sign as Insurance Officer
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($can_approve_as_lo): ?>
                        <a href="approve_application.php?id=<?php echo $application['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Sign as Loan Officer
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($can_final_approve): ?>
                        <a href="secretary_approve_application.php?id=<?php echo $application['id']; ?>" class="btn btn-success">
                            <i class="fas fa-stamp"></i> Final Approval
                        </a>
                        <?php endif; ?>
                    </div>
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
                                    <span><?php echo display_value($application['branch']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>CID No:</label>
                                    <span><?php echo display_value($application['cid_no']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Center No:</label>
                                    <span><?php echo display_value($application['center_no']); ?></span>
                                </div>
                                
                                <?php if (!empty($application['blip_mc']) || !empty($application['lpip_mc']) || !empty($application['lmip_mc'])): ?>
                                <!-- Display MC numbers if they exist -->
                                <?php if (!empty($application['blip_mc'])): ?>
                                <div class="info-item">
                                    <label>BLIP MC No:</label>
                                    <span><?php echo display_value($application['blip_mc']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($application['lpip_mc'])): ?>
                                <div class="info-item">
                                    <label>LPIP MC No:</label>
                                    <span><?php echo display_value($application['lpip_mc']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($application['lmip_mc'])): ?>
                                <div class="info-item">
                                    <label>LMIP MC No:</label>
                                    <span><?php echo display_value($application['lmip_mc']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Plans:</label>
                                    <span>
                                        <?php 
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
                                    <span><?php echo display_value($application['first_name'] . ' ' . $application['middle_name'] . ' ' . $application['last_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Gender:</label>
                                    <span><?php echo display_value($application['gender']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Civil Status:</label>
                                    <span><?php echo display_value($application['civil_status']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Birthdate:</label>
                                    <span><?php echo display_value($application['birthdate'], true); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Age:</label>
                                    <span><?php echo display_value($application['age']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Birth Place:</label>
                                    <span><?php echo display_value($application['birth_place']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Email:</label>
                                    <span><?php echo display_value($application['email']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Phone:</label>
                                    <span><?php echo $application['cell_phone'] ? '+63'.htmlspecialchars($application['cell_phone']) : 'N/A'; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Telephone:</label>
                                    <span><?php echo display_value($application['contact_no']); ?></span>
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
                                    <label>Barangay Code:</label>
                                    <span><?php echo htmlspecialchars($application['present_brgy_code']); ?></span>
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
                                    <label>Barangay Code:</label>
                                    <span><?php echo htmlspecialchars($application['permanent_brgy_code']); ?></span>
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
                
                <!-- Only display the beneficiaries section if beneficiaries exist -->
                <?php if ($hasBeneficiaries): ?>
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
                <?php endif; ?>
                
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
                        <div class="signature-container">
                            <?php if (!empty($application['member_signature'])): ?>
                            <div class="signature-block">
                                <h3>Member's Signature</h3>
                                <div class="signature-image">
                                    <img src="<?php echo SITE_URL . '/' . $application['member_signature']; ?>" alt="Member Signature">
                                </div>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($application['member_name']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($application['beneficiary_signature'])): ?>
                            <div class="signature-block">
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
                
                <!-- Valid ID Previews Section -->
                <?php if (!empty($application['valid_id_path']) || !empty($application['spouse_valid_id_path'])): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Valid ID Previews</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="signature-container">
                            <?php if (!empty($application['valid_id_path'])): ?>
                            <div class="signature-block id-preview-block">
                                <h3>Member's Valid ID</h3>
                                <div class="id-image-preview">
                                    <a href="<?php echo SITE_URL . '/' . $application['valid_id_path']; ?>" target="_blank" class="id-preview-link">
                                        <img src="<?php echo SITE_URL . '/' . $application['valid_id_path']; ?>" alt="Member Valid ID">
                                        <div class="preview-overlay">
                                            <span class="view-full-size">View Full Size</span>
                                        </div>
                                    </a>
                                </div>
                                <p><strong>ID Type:</strong> <?php echo htmlspecialchars($application['id_number'] ? 'TIN/SSS/GSIS' : 'Other ID'); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($application['spouse_valid_id_path'])): ?>
                            <div class="signature-block id-preview-block">
                                <h3>Spouse's Valid ID</h3>
                                <div class="id-image-preview">
                                    <a href="<?php echo SITE_URL . '/' . $application['spouse_valid_id_path']; ?>" target="_blank" class="id-preview-link">
                                        <img src="<?php echo SITE_URL . '/' . $application['spouse_valid_id_path']; ?>" alt="Spouse Valid ID">
                                        <div class="preview-overlay">
                                            <span class="view-full-size">View Full Size</span>
                                        </div>
                                    </a>
                                </div>
                                <p><strong>ID Type:</strong> <?php echo htmlspecialchars($application['spouse_id_number'] ? 'TIN/SSS/GSIS' : 'Other ID'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons-bottom modern-buttons">
                    <a href="applications.php" class="btn-secondary">Back to List</a>
                    
                    <?php if ($application['io_approved'] === 'approved' && $application['lo_approved'] === 'approved'): ?>
                        <?php echo $certificate_dropdown_html; ?>
                    <?php endif; ?>
                    <a href="generate_application_pdf.php?id=<?php echo $application['id']; ?>&mode=preview" class="btn-primary">
                        <i class="fas fa-eye"></i> View PDF
                    </a>
                    <!-- Add links to new document types if IO and LO have both approved -->
                    <?php if ($application['io_approved'] === 'approved' && $application['lo_approved'] === 'approved'): ?>
                        <a href="generate_final_report.php?id=<?php echo $application['id']; ?>&mode=preview" class="btn-info">
                            <i class="fas fa-file-contract"></i> View Final Report
                        </a>
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
        justify-content: flex-end;
    }
    
    /* Modern hover effects for bottom buttons */
    .action-buttons-bottom a {
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 10px 18px;
    }
    
    .action-buttons-bottom a:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
        text-decoration: none;
        display: inline-block;
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
    
    .btn-info {
        background-color: #17a2b8;
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
    
    /* Signature layout: align in row */
    .signature-container {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }
    
    .signature-block {
        flex: 1;
        min-width: 200px;
    }
    
    .approval-status {
        margin: 10px 0;
    }
    .status-item {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    .status-label {
        font-weight: bold;
        width: 150px;
    }
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        margin-right: 10px;
    }
  
    .status-info {
        font-size: 14px;
        color: #555;
    }
    .approval-actions {
        margin-top: 20px;
        margin-bottom: 20px;
    }
    
    .certificate-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .dropdown-toggle {
        cursor: pointer;
    }
    
    .certificate-dropdown .dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        min-width: 220px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        z-index: 1;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .certificate-dropdown .dropdown-content-up {
        bottom: 100%;
        top: auto;
        margin-bottom: 5px;
    }
    
    .certificate-dropdown .dropdown-content a {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        background-color: #fff;
        transition: background-color 0.2s;
    }
    
    .certificate-dropdown .dropdown-content a:hover {
        background-color: #f8f9fa;
    }
    
    /* Fix dropdown hover issue */
    .certificate-dropdown .dropdown-content:before {
        content: '';
        display: block;
        position: absolute;
        height: 20px;
        width: 100%;
        bottom: -10px;
        left: 0;
    }
    
    /* Change from hover to click toggle */
    .certificate-dropdown.show .dropdown-content {
        display: block;
    }
    
    /* ID Preview Styles */
    .id-preview-block {
        margin-bottom: 15px;
    }
    
    .id-image-preview {
        position: relative;
        max-width: 300px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .id-image-preview:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transform: translateY(-3px);
    }
    
    .id-image-preview img {
        width: 100%;
        display: block;
        transition: all 0.3s ease;
    }
    
    .id-preview-link {
        display: block;
        position: relative;
    }
    
    .preview-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .id-preview-link:hover .preview-overlay {
        opacity: 1;
    }
    
    .view-full-size {
        color: white;
        background-color: rgba(0, 0, 0, 0.7);
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: bold;
    }
    </style>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Add click handler for certificate dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggle = document.querySelector('.dropdown-toggle');
        const certificateDropdown = document.querySelector('.certificate-dropdown');
        
        if (dropdownToggle && certificateDropdown) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                certificateDropdown.classList.toggle('show');
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function closeDropdown(event) {
                    if (!certificateDropdown.contains(event.target)) {
                        certificateDropdown.classList.remove('show');
                        document.removeEventListener('click', closeDropdown);
                    }
                });
            });
        }
    });
    </script>
</body>
</html> 