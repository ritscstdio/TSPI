<?php
/**
 * Final Approval for Application
 * 
 * This page handles the final approval process by an administrator
 * It includes signature capture and sending email notification to the applicant
 */

$page_title = "Final Approval";
$body_class = "admin-final-approval-page";
require_once '../includes/config.php';
require_admin_login();

// Check if the user has administrator privileges
$current_user = get_admin_user();
if ($current_user['role'] !== 'administrator') {
    $_SESSION['message'] = "You don't have permission to perform final approval.";
    redirect('/admin/index.php');
}

// Ensure an 'id' parameter is provided
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

// Check if both IO and LO have approved
if ($application['io_approved'] !== 'approved' || $application['lo_approved'] !== 'approved') {
    $_SESSION['message'] = "Cannot perform final approval. Both Insurance Officer and Loan Officer must approve first.";
    redirect('/admin/view_application.php?id=' . $id);
}

// Check if application is already approved by admin
if ($application['admin_approved'] === 'approved') {
    $_SESSION['message'] = "This application has already been given final approval.";
    redirect('/admin/view_application.php?id=' . $id);
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_name = $_POST['admin_name'] ?? '';
    $admin_comments = $_POST['admin_comments'] ?? '';
    $signature_data = $_POST['signature'] ?? '';
    $approval_action = $_POST['approval_action'] ?? '';
    $send_email = isset($_POST['send_email']);
    
    // Validate inputs
    if (empty($admin_name)) {
        $errors[] = "Your name is required";
    }
    
    if (empty($signature_data)) {
        $errors[] = "Signature is required";
    }
    
    if (empty($approval_action) || !in_array($approval_action, ['approved', 'rejected'])) {
        $errors[] = "Please select a valid approval action";
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Save the signature image
            $signature_filename = null;
            if (!empty($signature_data)) {
                // Create directory if not exists
                $uploads_dir = '../uploads/signatures';
                if (!is_dir($uploads_dir)) {
                    mkdir($uploads_dir, 0755, true);
                }
                
                // Decode base64 image
                $signature_data = str_replace('data:image/png;base64,', '', $signature_data);
                $signature_data = str_replace(' ', '+', $signature_data);
                $signature_data = base64_decode($signature_data);
                
                // Generate unique filename
                $signature_filename = 'admin_sig_' . $id . '_' . time() . '.png';
                $signature_path = $uploads_dir . '/' . $signature_filename;
                
                // Save image
                file_put_contents($signature_path, $signature_data);
                $signature_db_path = 'uploads/signatures/' . $signature_filename;
            }
            
            // Update application status
            $stmt = $pdo->prepare("
                UPDATE members_information 
                SET 
                    status = ?, 
                    admin_approved = ?, 
                    admin_name = ?, 
                    admin_signature = ?, 
                    admin_comments = ?, 
                    admin_approval_date = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $approval_action, 
                $approval_action, 
                $admin_name, 
                $signature_db_path, 
                $admin_comments, 
                $id
            ]);
            
            $pdo->commit();
            $success = true;
            
            // Handle email notification if checked and application is approved
            if ($send_email && $approval_action === 'approved' && !empty($application['email'])) {
                require_once 'email_config.php';
                
                // Generate temporary PDF files for attachment
                $temp_dir = '../temp';
                if (!is_dir($temp_dir)) {
                    mkdir($temp_dir, 0755, true);
                }
                
                // Generate application form PDF
                $application_pdf = $temp_dir . '/application_' . $id . '.pdf';
                ob_start();
                include 'generate_application_pdf.php';
                ob_end_clean();
                $pdf->Output($application_pdf, 'F');
                
                // Generate certificate PDF
                $certificate_pdf = $temp_dir . '/certificate_' . $id . '.pdf';
                ob_start();
                include 'generate_certificate.php';
                ob_end_clean();
                $pdf->Output($certificate_pdf, 'F');
                
                // Send email with attachments
                $html_message = generate_application_email_html($application, 'approved');
                $text_message = generate_application_email_text($application, 'approved');
                
                $attachments = [
                    $application_pdf,
                    $certificate_pdf
                ];
                
                $mail_sent = send_application_email(
                    $application['email'],
                    'TSPI Membership Application Approved',
                    $text_message,
                    $html_message,
                    $attachments
                );
                
                // Clean up temporary files
                unlink($application_pdf);
                unlink($certificate_pdf);
                
                if ($mail_sent) {
                    $_SESSION['message'] = "Application has been approved and an email notification has been sent to the applicant.";
                } else {
                    $_SESSION['message'] = "Application has been approved but there was an issue sending the email notification.";
                }
            } else {
                $_SESSION['message'] = "Application has been " . $approval_action . " successfully.";
            }
            
            redirect('/admin/view_application.php?id=' . $id);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="page-header">
                <h1>Final Application Approval</h1>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Application Details</h2>
                </div>
                <div class="admin-card-body">
                    <div class="application-summary">
                        <div class="summary-item">
                            <strong>Applicant:</strong> 
                            <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?>
                        </div>
                        <div class="summary-item">
                            <strong>Application ID:</strong> 
                            <?php echo htmlspecialchars($application['id']); ?>
                        </div>
                        <div class="summary-item">
                            <strong>Branch:</strong> 
                            <?php echo htmlspecialchars($application['branch']); ?>
                        </div>
                        <div class="summary-item">
                            <strong>CID No:</strong> 
                            <?php echo htmlspecialchars($application['cid_no']); ?>
                        </div>
                        <div class="summary-item">
                            <strong>Insurance Officer:</strong> 
                            <?php echo htmlspecialchars($application['io_name']); ?> 
                            (Approved on <?php echo date('M j, Y', strtotime($application['io_approval_date'])); ?>)
                        </div>
                        <div class="summary-item">
                            <strong>Loan Officer:</strong> 
                            <?php echo htmlspecialchars($application['lo_name']); ?> 
                            (Approved on <?php echo date('M j, Y', strtotime($application['lo_approval_date'])); ?>)
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Administrator Final Approval</h2>
                </div>
                <div class="admin-card-body">
                    <form method="post" id="final-approval-form">
                        <div class="form-group">
                            <label for="admin_name">Your Name:</label>
                            <input type="text" id="admin_name" name="admin_name" value="<?php echo $current_user['name'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_comments">Comments (optional):</label>
                            <textarea id="admin_comments" name="admin_comments" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Approval Action:</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="approval_action" value="approved" required> Approve
                                </label>
                                <label>
                                    <input type="radio" name="approval_action" value="rejected"> Reject
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group notification-options">
                            <label>Notification:</label>
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="send_email" checked> 
                                    Send email notification to applicant (requires approved status)
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="signature">Your Signature:</label>
                            <p class="form-hint">Please sign in the box below</p>
                            <div class="signature-container">
                                <canvas id="signature-pad" width="400" height="200"></canvas>
                                <input type="hidden" name="signature" id="signature-data">
                            </div>
                            <div class="signature-actions">
                                <button type="button" id="clear-signature" class="btn btn-warning">Clear</button>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="view_application.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Final Decision</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="admin-card pdf-preview">
                <div class="admin-card-header">
                    <h2>Document Previews</h2>
                </div>
                <div class="admin-card-body">
                    <div class="preview-links">
                        <a href="generate_application_pdf.php?id=<?php echo $id; ?>&mode=preview" target="_blank" class="btn btn-info">
                            <i class="fas fa-file-pdf"></i> Preview Application Form
                        </a>
                        
                        <a href="generate_certificate.php?id=<?php echo $id; ?>&mode=preview" target="_blank" class="btn btn-info">
                            <i class="fas fa-certificate"></i> Preview Certificate
                        </a>
                        
                        <a href="generate_final_report.php?id=<?php echo $id; ?>&mode=preview" target="_blank" class="btn btn-info">
                            <i class="fas fa-file-contract"></i> Preview Final Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.application-summary {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.summary-item {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-hint {
    color: #666;
    font-size: 0.9em;
    margin-top: 3px;
    margin-bottom: 10px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.radio-group, .checkbox-group {
    display: flex;
    gap: 20px;
    margin-top: 8px;
}

.radio-group label, .checkbox-group label {
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: normal;
    cursor: pointer;
}

.signature-container {
    border: 2px dashed #ccc;
    margin-bottom: 10px;
    background-color: #f9f9f9;
}

#signature-pad {
    width: 100%;
    height: 200px;
    touch-action: none;
}

.signature-actions {
    display: flex;
    justify-content: flex-end;
}

.notification-options {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid #0056b3;
}

.preview-links {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.preview-links a {
    flex: 1;
    min-width: 220px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 15px;
}

input[type="text"], textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn {
    padding: 10px 15px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.btn-primary {
    background-color: #0056b3;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize signature pad
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'black'
    });
    
    // Handle window resize
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear(); // Clear the canvas
    }
    
    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();
    
    // Clear signature button
    document.getElementById('clear-signature').addEventListener('click', function() {
        signaturePad.clear();
        document.getElementById('signature-data').value = '';
    });
    
    // Form submission - capture signature
    document.getElementById('final-approval-form').addEventListener('submit', function(e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Please provide your signature before submitting.');
            return;
        }
        
        // Save signature data to the hidden input
        document.getElementById('signature-data').value = signaturePad.toDataURL();
    });
    
    // Toggle notification options based on approval action
    const approvalInputs = document.querySelectorAll('input[name="approval_action"]');
    const emailCheckbox = document.querySelector('input[name="send_email"]');
    
    approvalInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'rejected') {
                emailCheckbox.disabled = false;
                document.querySelector('.notification-options').style.opacity = '0.5';
            } else {
                emailCheckbox.disabled = false;
                document.querySelector('.notification-options').style.opacity = '1';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 