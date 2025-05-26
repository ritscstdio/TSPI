<?php
$page_title = "Approve Application";
$body_class = "admin-approve-application-page";
require_once '../includes/config.php';
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

// Fetch all branches for the dropdown
$branches_stmt = $pdo->query("SELECT * FROM branches ORDER BY branch ASC");
$branches = $branches_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $branch = $_POST['branch'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $signature = $_POST['signature'] ?? '';
    $plans = isset($_POST['plans']) ? $_POST['plans'] : [];
    $classification = $_POST['classification'] ?? '';
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
        
        if (empty($plans)) {
            $errors[] = "At least one plan must be selected.";
        }
        
        if (empty($classification)) {
            $errors[] = "Classification is required.";
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
        
        // JSON encode plans and classification
        $plansJson = json_encode($plans);
        $classificationJson = json_encode([$classification]);
        
        // Update the application
        $sql = "UPDATE members_information SET 
                $status_field = ?, 
                $name_field = ?, 
                $signature_field = ?, 
                $date_field = NOW(), 
                $notes_field = ?,
                branch = ?,
                plans = ?,
                classification = ?
                WHERE id = ?";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $action, 
            $officer_name, 
            $signaturePath, 
            $notes,
            $branch,
            $plansJson,
            $classificationJson,
            $application_id
        ]);
        
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
        .approval-form {
            max-width: 800px;
            margin: 0 auto;
        }
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .signature-pad-container {
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            margin: 1rem 0;
        }
        #signature-pad {
            width: 100%;
            height: 200px;
        }
        .signature-buttons {
            margin-top: 10px;
        }
        .approval-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .approval-option {
            flex: 1;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        .approval-option.selected {
            border-color: #4CAF50;
            background-color: rgba(76, 175, 80, 0.1);
        }
        .approval-option.reject.selected {
            border-color: #F44336;
            background-color: rgba(244, 67, 54, 0.1);
        }
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-col {
            flex: 1;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            margin-right: 20px;
        }
        .checkbox-item input {
            margin-right: 5px;
        }
        .disclaimer-box {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: #f5f5f5;
            border-left: 4px solid #4CAF50;
        }
        .action-buttons {
            margin-top: 1.5rem;
            display: flex;
            justify-content: space-between;
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
                    <a href="applications.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Applications</a>
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
                        <p><strong>Applicant:</strong> <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
                        <p><strong>Submitted:</strong> <?php echo date('F j, Y g:i a', strtotime($application['created_at'])); ?></p>
                        <p>
                            <a href="view_application.php?id=<?php echo $application_id; ?>" class="btn btn-secondary" target="_blank">
                                <i class="fas fa-eye"></i> View Full Application
                            </a>
                        </p>
                    </div>
                    
                    <form method="post" action="" id="approval-form">
                        <div class="form-section">
                            <h2>Approval Decision</h2>
                            <div class="approval-options">
                                <div class="approval-option" data-value="approve">
                                    <i class="fas fa-check-circle fa-2x" style="color: #4CAF50;"></i>
                                    <h3>Approve</h3>
                                    <p>Endorse this application for approval</p>
                                </div>
                                <div class="approval-option reject" data-value="reject">
                                    <i class="fas fa-times-circle fa-2x" style="color: #F44336;"></i>
                                    <h3>Reject</h3>
                                    <p>Decline this application</p>
                                </div>
                            </div>
                            <input type="hidden" name="action" id="action-input" value="">
                        </div>
                        
                        <div class="form-section" id="approval-details">
                            <h2>Approval Details</h2>
                            
                            <div class="form-group">
                                <label for="branch">Branch Assignment</label>
                                <select id="branch" name="branch" required>
                                    <option value="">Select Branch</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?php echo htmlspecialchars($branch['branch']); ?>" 
                                                data-center="<?php echo htmlspecialchars($branch['center_no']); ?>">
                                            <?php echo htmlspecialchars($branch['branch']); ?> 
                                            (Center: <?php echo htmlspecialchars($branch['center_no']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Member Classification</label>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="radio" id="class_tkp" name="classification" value="TKP" 
                                            <?php echo (json_decode($application['classification'] ?? '[]')[0] ?? '') === 'TKP' ? 'checked' : ''; ?>>
                                        <label for="class_tkp">TKP (Borrower)</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="radio" id="class_tpp" name="classification" value="TPP"
                                            <?php echo (json_decode($application['classification'] ?? '[]')[0] ?? '') === 'TPP' ? 'checked' : ''; ?>>
                                        <label for="class_tpp">TPP (Borrower)</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="radio" id="class_kapamilya" name="classification" value="Kapamilya"
                                            <?php echo (json_decode($application['classification'] ?? '[]')[0] ?? '') === 'Kapamilya' ? 'checked' : ''; ?>>
                                        <label for="class_kapamilya">Kapamilya</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Selected Plans</label>
                                <div class="checkbox-group">
                                    <?php 
                                    $selectedPlans = json_decode($application['plans'] ?? '[]', true) ?: [];
                                    ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="plan_blip" name="plans[]" value="BLIP" 
                                            <?php echo in_array('BLIP', $selectedPlans) ? 'checked' : ''; ?>>
                                        <label for="plan_blip">Basic Life (BLIP)</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="plan_lpip" name="plans[]" value="LPIP"
                                            <?php echo in_array('LPIP', $selectedPlans) ? 'checked' : ''; ?>>
                                        <label for="plan_lpip">Life Plus (LPIP)</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="plan_lmip" name="plans[]" value="LMIP"
                                            <?php echo in_array('LMIP', $selectedPlans) ? 'checked' : ''; ?>>
                                        <label for="plan_lmip">Life Max (LMIP)</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Notes/Comments</label>
                                <textarea id="notes" name="notes" rows="4" placeholder="Enter any notes or comments about this application"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-section" id="signature-section">
                            <h2>Officer Signature</h2>
                            <div class="form-group">
                                <label for="officer_name">Your Name</label>
                                <input type="text" id="officer_name" name="officer_name" value="<?php echo htmlspecialchars($current_user['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Signature</label>
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
                                        I, <strong id="officer_name_display"><?php echo htmlspecialchars($current_user['name'] ?? ''); ?></strong>, 
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
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'black'
            });
            
            // Resize canvas
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear(); // Clear the canvas
            }
            
            // Set up the resize handler
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas(); // Call on init
            
            // Clear signature button
            document.getElementById('clear-signature').addEventListener('click', function() {
                signaturePad.clear();
                document.getElementById('signature-data').value = '';
            });
            
            // Handle form submission to save signature data
            document.getElementById('approval-form').addEventListener('submit', function(e) {
                if (document.getElementById('action-input').value === 'approve') {
                    // Only require signature for approval
                    if (signaturePad.isEmpty()) {
                        e.preventDefault();
                        alert('Please provide your signature before submitting.');
                        return false;
                    }
                    
                    // Save signature data to hidden input
                    document.getElementById('signature-data').value = signaturePad.toDataURL();
                }
            });
            
            // Toggle between approval and rejection UI
            const approvalOptions = document.querySelectorAll('.approval-option');
            const actionInput = document.getElementById('action-input');
            const approvalDetails = document.getElementById('approval-details');
            const signatureSection = document.getElementById('signature-section');
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
                        signatureSection.style.display = 'block';
                        rejectionSection.style.display = 'none';
                    } else {
                        approvalDetails.style.display = 'none';
                        signatureSection.style.display = 'block'; // We still want signature for rejection
                        rejectionSection.style.display = 'block';
                    }
                });
            });
            
            // Update officer name in disclaimer text when input changes
            const officerNameInput = document.getElementById('officer_name');
            const officerNameDisplay = document.getElementById('officer_name_display');
            
            officerNameInput.addEventListener('input', function() {
                officerNameDisplay.textContent = this.value || '[Your Name]';
            });
            
            // Make form fields required if approving
            document.getElementById('approval-form').addEventListener('submit', function(e) {
                const action = document.getElementById('action-input').value;
                
                if (!action) {
                    e.preventDefault();
                    alert('Please select either Approve or Reject.');
                    return false;
                }
                
                if (action === 'approve') {
                    const branch = document.getElementById('branch').value;
                    
                    if (!branch) {
                        e.preventDefault();
                        alert('Please select a branch.');
                        return false;
                    }
                    
                    // Check if at least one plan is selected
                    const plans = document.querySelectorAll('input[name="plans[]"]:checked');
                    if (plans.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one plan.');
                        return false;
                    }
                    
                    // Check if classification is selected
                    const classification = document.querySelector('input[name="classification"]:checked');
                    if (!classification) {
                        e.preventDefault();
                        alert('Please select a member classification.');
                        return false;
                    }
                }
            });
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 