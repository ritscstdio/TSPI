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
                
                <div class="action-buttons-bottom modern-buttons">
                    <a href="applications.php" class="btn-secondary">Back to List</a>
                    
                    <a href="generate_application_pdf.php?id=<?php echo $application['id']; ?>&mode=preview" class="btn-primary">
                        <i class="fas fa-eye"></i> Preview PDF
                    </a>
                    
                    <a href="generate_application_pdf.php?id=<?php echo $application['id']; ?>&mode=download" class="btn-primary">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                    
                    <a href="generate_application_pdf.php?id=<?php echo $application['id']; ?>&mode=preview&debug=1" class="btn-warning">
                        <i class="fas fa-bug"></i> Debug PDF Grid
                    </a>
                    
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
    </style>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 