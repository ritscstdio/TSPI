<?php
$page_title = "Approve Application";
$body_class = "admin-approve-application-page";
require_once '../includes/config.php';
require_once '../includes/functions_logging.php';
require_admin_login();

// Only allow Insurance Officer or Loan Officer roles
$current_user = get_admin_user();
$user_role = $current_user['role'] ?? '';

if ($user_role !== 'insurance_officer' && $user_role !== 'loan_officer') {
    $_SESSION['message'] = "You don't have permission to access this page.";
    redirect('/admin/applications.php');
}

// Get application ID from URL
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$application_id) {
    $_SESSION['message'] = "Invalid application ID.";
    redirect('/admin/applications.php');
}

// Log the page access
log_message("User {$current_user['username']} ({$user_role}) accessed approval page for application ID: {$application_id}", 'info', 'access');

// Fetch application data
$stmt = $pdo->prepare("SELECT * FROM members_information WHERE id = ?");
$stmt->execute([$application_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    $_SESSION['message'] = "Application not found.";
    redirect('/admin/applications.php');
}

// Check if this user has already approved this application
if (($user_role === 'insurance_officer' && $application['io_approved'] !== 'pending') ||
    ($user_role === 'loan_officer' && $application['lo_approved'] !== 'pending')) {
    $_SESSION['message'] = "This application has already been processed by " . ($user_role === 'insurance_officer' ? 'an Insurance Officer' : 'a Loan Officer') . ".";
    redirect('/admin/applications.php');
}

// Fetch all branches grouped by region for the dropdown
$branches_stmt = $pdo->query("SELECT DISTINCT region FROM branches ORDER BY region ASC");
$regions = $branches_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if branch has already been assigned by another officer
$branchAssigned = false;
$otherOfficer = '';

if (!empty($application['branch'])) {
    if ($user_role === 'insurance_officer' && $application['lo_approved'] === 'approved') {
        $branchAssigned = true;
        $otherOfficer = 'Loan Officer';
    } elseif ($user_role === 'loan_officer' && $application['io_approved'] === 'approved') {
        $branchAssigned = true;
        $otherOfficer = 'Insurance Officer';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $branch = $_POST['branch'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $signature = $_POST['signature'] ?? '';
    $officer_name = $_POST['officer_name'] ?? '';
    $disclaimer_checked = isset($_POST['disclaimer_agreement']) ? 1 : 0;
    
    $errors = [];
    
    if ($action !== 'approve' && $action !== 'reject') {
        $errors[] = "Invalid action selected.";
    }
    
    if ($action === 'approve') {
        if (empty($branch)) {
            $errors[] = "Branch selection is required.";
        }
        
        if (empty($signature)) {
            $errors[] = "Signature is required.";
        }
        
        if (empty($officer_name)) {
            $errors[] = "Officer name is required.";
        }
        
        if (!$disclaimer_checked) {
            $errors[] = "You must confirm the approval disclaimer.";
        }
    }
    
    if (empty($errors)) {
        // Process signature if provided
        $signaturePath = null;
        if (!empty($signature) && $signature !== 'data:,') {
            $uploadsDir = UPLOADS_DIR . '/signatures';
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
            
            [$meta, $data] = explode(',', $signature);
            $decoded = base64_decode($data);
            if (!empty($decoded)) {
                $role_prefix = ($user_role === 'insurance_officer') ? 'io' : 'lo';
                $fname = $role_prefix . '_' . $application_id . '_' . time() . '.png';
                file_put_contents($uploadsDir . '/' . $fname, $decoded);
                $signaturePath = 'uploads/signatures/' . $fname;
            }
        }
        
        // Update application based on the officer role
        if ($user_role === 'insurance_officer') {
            $status_field = 'io_approved';
            $name_field = 'io_name';
            $signature_field = 'io_signature';
            $date_field = 'io_approval_date';
            $notes_field = 'io_notes';
        } else { // loan_officer
            $status_field = 'lo_approved';
            $name_field = 'lo_name';
            $signature_field = 'lo_signature';
            $date_field = 'lo_approval_date';
            $notes_field = 'lo_notes';
        }
        
        // Get center_no from input if provided
        $center_no = $_POST['center_no'] ?? '';
        
        // Log the approval action with details
        log_approval_activity($application_id, $user_role, $action, [
            'officer_name' => $officer_name,
            'status_field' => $status_field,
            'has_signature' => !empty($signaturePath),
            'branch' => $branch,
            'center_no' => $center_no
        ]);
        
        // Make sure the action value is explicitly correct - should be "approved" not "approve"
        $statusValue = ($action === "approve") ? "approved" : "rejected";
        
        // Update the application - don't modify plans or classification
        $sql = "UPDATE members_information SET 
                $status_field = ?, 
                $name_field = ?, 
                $signature_field = ?, 
                $date_field = NOW(), 
                $notes_field = ?,
                branch = ?";

        // Only update center_no if it's empty or not previously set
        if (empty($application['center_no'])) {
            $sql .= ", center_no = ?";
            $params = [
                $statusValue, 
                $officer_name, 
                $signaturePath, 
                $notes,
                $branch,
                $center_no,
                $application_id
            ];
        } else {
            $sql .= " WHERE id = ?";
            $params = [
                $statusValue, 
                $officer_name, 
                $signaturePath, 
                $notes,
                $branch,
                $application_id
            ];
        }

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        // Debug information
        $debug_info = "Update " . ($result ? "succeeded" : "failed") . ". ";
        $debug_info .= "Role: $user_role, Status field: $status_field, Action: $statusValue, ";
        $debug_info .= "App ID: $application_id";
        
        // Write to log file for debugging
        log_message($debug_info, 'debug', 'approval_debug');
        
        // Verify the update worked by querying the database again
        $verify_stmt = $pdo->prepare("SELECT $status_field FROM members_information WHERE id = ?");
        $verify_stmt->execute([$application_id]);
        $verify_result = $verify_stmt->fetchColumn();
        
        log_message("Verification check - Field $status_field is now set to: $verify_result", 'debug', 'approval_debug');
        
        $_SESSION['message'] = "Application " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully.";
        redirect('/admin/applications.php');
    }
}

// Page content starts below
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
    <style>
        /* Modern styling */
        :root {
            --primary-color: #0070f3;
            --primary-light: #e3f2fd;
            --primary-dark: #005bc1;
            --accent-color: #00c853;
            --danger-color: #f44336;
            --warning-color: #ffc107;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
            --border-radius: 8px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }

        .admin-main {
            padding: 1rem;
        }
        
        .content-container {
            padding: 20px;
        }

        .approval-form {
            max-width: 100%;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray-300);
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        
        .page-header .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            background-color: var(--gray-200);
            color: var(--gray-800);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid var(--gray-300);
        }
        
        .page-header .btn:hover {
            background-color: var(--gray-300);
            box-shadow: var(--shadow-sm);
        }

        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            box-shadow: var(--shadow-md);
        }

        .form-section h2 {
            margin-top: 0;
            font-size: 1.4rem;
            color: var(--gray-800);
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        
        /* Signature pad styling */
        .signature-pad-container {
            border: 2px dashed var(--gray-300);
            border-radius: var(--border-radius);
            background: white;
            margin: 1rem 0;
            overflow: hidden;
            transition: all 0.2s ease;
            position: relative;
            width: 400px;
            height: 200px;
            max-width: 100%;
        }
        
        .signature-pad-container:hover {
            border-color: var(--primary-color);
        }

        #signature-pad {
            width: 100%;
            height: 100%;
            touch-action: none;
        }

        .signature-buttons {
            margin-top: 10px;
            display: flex;
            justify-content: flex-start;
        }
        
        /* Make textarea non-resizable */
        .form-group textarea {
            resize: none;
            min-height: 80px;
        }
        
        /* Approval options */
        .approval-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .approval-options {
                flex-direction: column;
            }
        }

        .approval-option {
            flex: 1;
            padding: 1.5rem;
            border: 2px solid var(--gray-300);
            border-radius: var(--border-radius);
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            background-color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .approval-option h3 {
            margin: 10px 0;
        }
        
        .approval-option p {
            color: var(--gray-600);
            margin: 0;
        }

        .approval-option:hover {
            border-color: var(--primary-light);
            transform: translateY(-2px);
        }

        .approval-option.selected {
            border-color: var(--accent-color);
            background-color: rgba(0, 200, 83, 0.05);
            transform: translateY(-2px);
        }

        .approval-option.reject.selected {
            border-color: var(--danger-color);
            background-color: rgba(244, 67, 54, 0.05);
        }
        
        /* Forms styling */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-col {
            flex: 1;
            min-width: 250px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 112, 243, 0.1);
            outline: none;
        }
        
        /* Checkboxes and radio buttons */
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 10px 0;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            margin-right: 20px;
        }
        
        .checkbox-item input[type="checkbox"],
        .checkbox-item input[type="radio"] {
            margin-right: 8px;
            width: auto;
        }
        
        /* Disclaimer box */
        .disclaimer-box {
            margin-top: 1.5rem;
            padding: 1.25rem;
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--accent-color);
        }
        
        /* Buttons */
        .action-buttons {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: var(--gray-500);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: var(--gray-600);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: var(--gray-900);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        /* Application Overview styling */
        .application-details {
            margin-bottom: 20px;
        }
        
        .details-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            gap: 20px;
        }
        
        .details-item {
            flex: 1;
            min-width: 250px;
        }
        
        .details-item label {
            display: block;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--gray-700);
        }
        
        .details-item span {
            color: var(--gray-900);
        }
        
        .details-section-title {
            font-weight: 700;
            font-size: 14px;
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--gray-300);
            color: var(--gray-700);
            text-transform: uppercase;
        }
        
        .details-table-container {
            margin: 15px 0;
            overflow-x: auto;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .details-table th, .details-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid var(--gray-200);
        }
        
        .details-table th {
            background: var(--gray-100);
            font-weight: 600;
            color: var(--gray-700);
        }
        
        .details-table tr:hover {
            background-color: rgba(0, 112, 243, 0.02);
        }
        
        .signatures-container {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin: 15px 0;
        }
        
        .signature-preview {
            max-width: 200px;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
            padding: 15px;
        }
        
        .signature-preview h4 {
            margin: 0 0 10px;
            font-size: 14px;
            color: var(--gray-700);
        }
        
        .signature-preview img {
            max-width: 100%;
            border: 1px solid var(--gray-200);
            padding: 5px;
            background: white;
        }
        
        .signature-preview p {
            margin: 8px 0 0;
            font-size: 13px;
            color: var(--gray-600);
        }
        
        /* Branch Assignment Notice */
        .branch-assigned-notice {
            background-color: var(--primary-light);
            border-left: 4px solid var(--primary-color);
        }
        
        .branch-assigned-notice h3 {
            color: var(--primary-color);
            margin-top: 0;
        }
        
        .assigned-branch-details {
            padding: 15px;
            background: white;
            border-radius: var(--border-radius);
            margin-top: 15px;
            box-shadow: var(--shadow-sm);
        }

        /* Info box */
        .info-box {
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 1px solid var(--gray-300);
        }

        .info-box p {
            margin: 0;
            color: var(--gray-700);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="content-container">
                <div class="page-header">
                    <h1><?php echo $user_role === 'insurance_officer' ? 'Insurance Officer' : 'Loan Officer'; ?> Application Approval</h1>
                    <a href="applications.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Applications</a>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="approval-form">
                    <div class="form-section">
                        <h2>Application Overview</h2>
                        <div class="application-details">
                            <div class="details-row">
                                <div class="details-item">
                                    <label>Applicant:</label>
                                    <span><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></span>
                                </div>
                                <div class="details-item">
                                    <label>Email:</label>
                                    <span><?php echo htmlspecialchars($application['email']); ?></span>
                                </div>
                            </div>
                            <div class="details-row">
                                <div class="details-item">
                                    <label>CID No:</label>
                                    <span><?php echo htmlspecialchars($application['cid_no']); ?></span>
                                </div>
                                <div class="details-item">
                                    <label>Submitted:</label>
                                    <span><?php echo date('F j, Y g:i a', strtotime($application['created_at'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="details-section-title">Plans & Classification</div>
                            <div class="details-row">
                                <div class="details-item">
                                    <label>Plans:</label>
                                    <span>
                                        <?php 
                                        $plans = json_decode($application['plans'] ?? '[]', true) ?: [];
                                        echo htmlspecialchars(implode(', ', $plans)); 
                                        ?>
                                    </span>
                                </div>
                                <div class="details-item">
                                    <label>Classification:</label>
                                    <span>
                                        <?php 
                                        $classification = json_decode($application['classification'] ?? '[]', true) ?: [];
                                        echo htmlspecialchars(implode(', ', $classification)); 
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="details-section-title">Address Information</div>
                            <div class="details-row">
                                <div class="details-item">
                                    <label>Present Address:</label>
                                    <span><?php echo htmlspecialchars($application['present_address']); ?></span>
                                </div>
                                <div class="details-item">
                                    <label>Permanent Address:</label>
                                    <span><?php echo htmlspecialchars($application['permanent_address']); ?></span>
                                </div>
                            </div>
                            
                            <div class="details-section-title">Business Information</div>
                            <div class="details-row">
                                <div class="details-item">
                                    <label>Primary Business:</label>
                                    <span><?php echo htmlspecialchars($application['primary_business']); ?></span>
                                </div>
                                <div class="details-item">
                                    <label>Business Address:</label>
                                    <span><?php echo htmlspecialchars($application['business_address'] ?: 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <?php
                            // Check if beneficiaries exist
                            $hasBeneficiaries = false;
                            for ($i = 1; $i <= 5; $i++) {
                                if (!empty($application["beneficiary_fn_{$i}"])) {
                                    $hasBeneficiaries = true;
                                    break;
                                }
                            }
                            
                            if ($hasBeneficiaries): ?>
                            <div class="details-section-title">Beneficiaries</div>
                            <div class="details-table-container">
                                <table class="details-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Birthdate</th>
                                            <th>Dependent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if (!empty($application["beneficiary_fn_{$i}"])): ?>
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
                                                    <td><?php echo htmlspecialchars($application["beneficiary_relationship_{$i}"] ?: 'N/A'); ?></td>
                                                    <td>
                                                        <?php 
                                                        echo !empty($application["beneficiary_birthdate_{$i}"]) ? 
                                                            date('m/d/Y', strtotime($application["beneficiary_birthdate_{$i}"])) : 
                                                            'N/A'; 
                                                        ?>
                                                    </td>
                                                    <td><?php echo $application["beneficiary_dependent_{$i}"] ? 'Yes' : 'No'; ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($application['trustee_name'])): ?>
                            <div class="details-section-title">Trustee Information</div>
                            <div class="details-row">
                                <div class="details-item">
                                    <label>Name:</label>
                                    <span><?php echo htmlspecialchars($application['trustee_name']); ?></span>
                                </div>
                                <div class="details-item">
                                    <label>Relationship:</label>
                                    <span><?php echo htmlspecialchars($application['trustee_relationship'] ?: 'N/A'); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($application['member_signature']) || !empty($application['beneficiary_signature'])): ?>
                            <div class="details-section-title">Signatures</div>
                            <div class="signatures-container">
                                <?php if (!empty($application['member_signature'])): ?>
                                <div class="signature-preview">
                                    <h4>Member's Signature</h4>
                                    <img src="../<?php echo $application['member_signature']; ?>" alt="Member Signature">
                                    <p><?php echo htmlspecialchars($application['member_name']); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($application['beneficiary_signature'])): ?>
                                <div class="signature-preview">
                                    <h4>Beneficiary's Signature</h4>
                                    <img src="../<?php echo $application['beneficiary_signature']; ?>" alt="Beneficiary Signature">
                                    <p><?php echo htmlspecialchars($application['sig_beneficiary_name']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($branchAssigned): ?>
                    <div class="form-section branch-assigned-notice">
                        <h3>Branch Already Assigned</h3>
                        <p>This application has already been assigned to a branch by the <?php echo $otherOfficer; ?>.</p>
                        <div class="assigned-branch-details">
                            <strong>Branch:</strong> <?php echo htmlspecialchars($application['branch']); ?>
                            <?php if (!empty($application['center_no'])): ?>
                            <br><strong>Center No:</strong> <?php echo htmlspecialchars($application['center_no']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="" id="approval-form">
                        <div class="form-section">
                            <h2>Approval Decision</h2>
                            <div class="approval-options">
                                <div class="approval-option" data-value="approve">
                                    <i class="fas fa-check-circle fa-2x" style="color: #00c853;"></i>
                                    <h3>Approve</h3>
                                    <p>Endorse this application for approval</p>
                                </div>
                                <div class="approval-option reject" data-value="reject">
                                    <i class="fas fa-times-circle fa-2x" style="color: #f44336;"></i>
                                    <h3>Reject</h3>
                                    <p>Decline this application</p>
                                </div>
                            </div>
                            <input type="hidden" name="action" id="action-input" value="">
                        </div>
                        
                        <?php if (!$branchAssigned): ?>
                        <div class="form-section" id="approval-details">
                            <h2>Branch Assignment</h2>
                            
                            <!-- Address Information Section -->
                            <div class="info-box">
                                <div class="details-section-title">Address Information</div>
                                <div class="details-row">
                                    <div class="details-item">
                                        <label>Present Address:</label>
                                        <span><?php echo htmlspecialchars($application['present_address']); ?></span>
                                    </div>
                                    <div class="details-item">
                                        <label>Permanent Address:</label>
                                        <span><?php echo htmlspecialchars($application['permanent_address']); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Business Information Section -->
                            <div class="info-box">
                                <div class="details-section-title">Business Information</div>
                                <div class="details-row">
                                    <div class="details-item">
                                        <label>Primary Business:</label>
                                        <span><?php echo htmlspecialchars($application['primary_business']); ?></span>
                                    </div>
                                    <div class="details-item">
                                        <label>Business Address:</label>
                                        <span><?php echo htmlspecialchars($application['business_address'] ?: 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="region">Region</label>
                                <select id="region" name="region" required>
                                    <option value="">Select Region</option>
                                    <?php foreach ($regions as $region): ?>
                                        <option value="<?php echo htmlspecialchars($region['region']); ?>">
                                            <?php echo htmlspecialchars($region['region']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="branch">Branch</label>
                                <select id="branch" name="branch" required disabled>
                                    <option value="">Select Branch</option>
                                    <!-- Branches will be loaded based on selected region -->
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Notes/Comments</label>
                                <textarea id="notes" name="notes" rows="4" placeholder="Enter any notes or comments about this application"></textarea>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="form-section" id="approval-details">
                            <h2>Additional Information</h2>
                            <div class="form-group">
                                <label for="notes">Notes/Comments</label>
                                <textarea id="notes" name="notes" rows="4" placeholder="Enter any notes or comments about this application"></textarea>
                            </div>
                            <!-- Hidden field to keep the branch value -->
                            <input type="hidden" name="branch" value="<?php echo htmlspecialchars($application['branch']); ?>">
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-section" id="signature-section">
                            <h2>Officer Signature</h2>
                            <!-- Officer name is now hidden, using their registered name -->
                            <input type="hidden" id="officer_name" name="officer_name" value="<?php echo htmlspecialchars($current_user['name'] ?? ''); ?>">
                            
                            <div class="form-group">
                                <label for="signature">Signature</label>
                                <div class="signature-pad-container">
                                    <canvas id="signature-pad"></canvas>
                                    <input type="hidden" name="signature" id="signature-data">
                                </div>
                                <div class="signature-buttons">
                                    <button type="button" id="clear-signature" class="btn btn-secondary">Clear Signature</button>
                                </div>
                            </div>
                            
                            <div class="form-group disclaimer-box">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="disclaimer_agreement" name="disclaimer_agreement" required>
                                    <label for="disclaimer_agreement">
                                        I, <strong><?php echo htmlspecialchars($current_user['name'] ?? ''); ?></strong>, 
                                        acting in my capacity as a <?php echo $user_role === 'insurance_officer' ? 'Insurance Officer' : 'Loan Officer'; ?>, 
                                        confirm that I have reviewed this application thoroughly and take responsibility for my decision.
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section" id="rejection-section" style="display: none;">
                            <h2>Rejection Details</h2>
                            <div class="form-group">
                                <label for="rejection_notes">Reason for Rejection</label>
                                <textarea id="rejection_notes" name="notes" rows="4" placeholder="Please provide detailed reasons for rejecting this application"></textarea>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="applications.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" id="submit-approval" class="btn btn-primary">Submit Decision</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize signature pad
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(0, 0, 0, 0)',
                penColor: 'black'
            });
            
            canvas._signaturePad = signaturePad; // Store reference for later
            
            // Resize signature pad for responsiveness
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const container = canvas.parentElement;
                const savedData = signaturePad.toDataURL();
                
                canvas.width = container.offsetWidth * ratio;
                canvas.height = container.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                
                // Restore signature after resize if it exists
                if (savedData && !signaturePad.isEmpty()) {
                    signaturePad.fromDataURL(savedData);
                } else {
                    signaturePad.clear();
                }
            }
            
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas(); // Call once on init
            
            // Clear signature button
            document.getElementById('clear-signature').addEventListener('click', function() {
                signaturePad.clear();
                document.getElementById('signature-data').value = '';
            });
            
            // Save on any change to ensure we catch all signature actions
            signaturePad.addEventListener('endStroke', function() {
                document.getElementById('signature-data').value = signaturePad.toDataURL();
            });
            
            // Toggle between approval and rejection UI
            const approvalOptions = document.querySelectorAll('.approval-option');
            const actionInput = document.getElementById('action-input');
            const approvalDetails = document.getElementById('approval-details');
            const rejectionSection = document.getElementById('rejection-section');
            
            approvalOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    
                    // Remove selected class from all options
                    approvalOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Set the action value
                    actionInput.value = value;
                    
                    // Toggle sections based on selection
                    if (value === 'approve') {
                        approvalDetails.style.display = 'block';
                        rejectionSection.style.display = 'none';
                    } else {
                        <?php if(!$branchAssigned): ?>
                        approvalDetails.style.display = 'none';
                        <?php endif; ?>
                        rejectionSection.style.display = 'block';
                    }
                });
            });
            
            // Auto-select the approve option by default
            const defaultOption = document.querySelector('.approval-option[data-value="approve"]');
            if (defaultOption) {
                defaultOption.click();
            }
            
            // Region and Branch selection logic
            const regionSelect = document.getElementById('region');
            const branchSelect = document.getElementById('branch');
            
            // Hidden field for center_no
            let centerNoInput = document.getElementById('center_no_input');
            if (!centerNoInput) {
                centerNoInput = document.createElement('input');
                centerNoInput.type = 'hidden';
                centerNoInput.id = 'center_no_input';
                centerNoInput.name = 'center_no';
                document.getElementById('approval-form').appendChild(centerNoInput);
            }
            
            if (regionSelect && branchSelect) {
                regionSelect.addEventListener('change', function() {
                    const selectedRegion = this.value;
                    
                    if (selectedRegion) {
                        // Enable the branch dropdown
                        branchSelect.disabled = false;
                        
                        // Clear current options except the placeholder
                        branchSelect.innerHTML = '<option value="">Select Branch</option>';
                        
                        // Fetch branches for the selected region
                        fetchBranches(selectedRegion);
                    } else {
                        // If no region is selected, disable the branch dropdown
                        branchSelect.disabled = true;
                        branchSelect.innerHTML = '<option value="">Select Branch</option>';
                        centerNoInput.value = '';
                    }
                });
                
                // Add change event for branch select to update center_no
                branchSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.hasAttribute('data-center')) {
                        centerNoInput.value = selectedOption.getAttribute('data-center');
                    } else {
                        centerNoInput.value = '';
                    }
                });
            }
            
            // Function to fetch branches by region
            function fetchBranches(region) {
                // Use fetch API to get branches from server
                fetch(`get_branches.php?region=${encodeURIComponent(region)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.branches) {
                            // Populate branch options
                            data.branches.forEach(branch => {
                                const option = document.createElement('option');
                                option.value = branch.branch;
                                option.textContent = `${branch.branch} (${branch.center_no})`;
                                option.setAttribute('data-center', branch.center_no);
                                branchSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching branches:', error);
                    });
            }
            
            // Form validation
            document.getElementById('approval-form').addEventListener('submit', function(e) {
                const action = actionInput.value;
                
                if (!action) {
                    e.preventDefault();
                    alert('Please select either Approve or Reject.');
                    return;
                }
                
                if (action === 'approve' && !signaturePad.isEmpty() && !document.getElementById('disclaimer_agreement').checked) {
                    e.preventDefault();
                    alert('Please agree to the disclaimer before proceeding.');
                    return;
                }
                
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert('Please provide your signature before submitting.');
                    return;
                }
                
                // Save signature data to hidden input
                document.getElementById('signature-data').value = signaturePad.toDataURL();
                
                // For approval, check branch is selected if not previously assigned
                if (action === 'approve' && <?php echo !$branchAssigned ? 'true' : 'false'; ?>) {
                    const branch = document.getElementById('branch').value;
                    if (!branch) {
                        e.preventDefault();
                        alert('Please select a branch.');
                        return;
                    }
                }
            });
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 