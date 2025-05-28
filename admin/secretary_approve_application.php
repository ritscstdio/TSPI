<?php
/**
 * Final Approval for Application
 * 
 * This page handles the final approval process by a secretary
 * It includes signature capture and sending email notification to the applicant
 */

$page_title = "Final Approval";
$body_class = "admin-final-approval-page";
require_once '../includes/config.php';
require_once '../includes/functions_logging.php';
require_admin_login();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user has secretary privileges
$current_user = get_admin_user();
// Set $current_admin for header.php to use
$current_admin = $current_user;

if ($current_user['role'] !== 'secretary') {
    $_SESSION['message'] = "You don't have permission to perform final approval.";
    log_message("Access denied: User {$current_user['username']} attempted to access secretary approval without proper role", 'warning', 'access');
    redirect('/admin/index.php');
    exit;
}

// Ensure an 'id' parameter is provided
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No application ID specified.";
    redirect('/admin/applications.php');
    exit;
}
$id = $_GET['id'];

// Log access to this page
log_message("Secretary {$current_user['username']} accessed final approval for application ID: $id", 'info', 'access');

// Fetch the application details
$stmt = $pdo->prepare("SELECT * FROM members_information WHERE id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    $_SESSION['message'] = "Application not found.";
    log_message("Error: Secretary attempted to approve non-existent application ID: $id", 'error', 'approval_error');
    redirect('/admin/applications.php');
    exit;
}

// Check if both IO and LO have approved
if ($application['io_approved'] !== 'approved' || $application['lo_approved'] !== 'approved') {
    $_SESSION['message'] = "Cannot perform final approval. Both Insurance Officer and Loan Officer must approve first.";
    log_message("Error: Secretary attempted to approve application ID: $id without prior IO/LO approval", 'warning', 'approval_error');
    redirect('/admin/view_application.php?id=' . $id);
    exit;
}

// Check if application is already approved by secretary
if ($application['secretary_approved'] === 'approved') {
    $_SESSION['message'] = "This application has already been given final approval.";
    redirect('/admin/view_application.php?id=' . $id);
    exit;
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug - Log POST data
        file_put_contents('../logs/secretary_approval_post_data_' . time() . '.json', json_encode($_POST, JSON_PRETTY_PRINT));

        $secretary_name = $_POST['secretary_name'] ?? '';
        $secretary_comments = $_POST['secretary_comments'] ?? '';
        $signature_data = $_POST['signature'] ?? '';
        $approval_action = $_POST['approval_action'] ?? '';
        $send_email = isset($_POST['send_email']) ? true : false; // Check if send_email is set
        
        // Validate inputs
        if (empty($secretary_name)) {
            $errors[] = "Your name is required";
        }
        
        if (empty($signature_data)) {
            $errors[] = "Signature is required";
        }
        
        if (empty($approval_action) || !in_array($approval_action, ['approved', 'rejected'])) {
            $errors[] = "Please select a valid approval action";
        }
        
        if (empty($errors)) {
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
                
                if ($signature_data === false) {
                    throw new Exception("Failed to decode signature data");
                }
                
                // Generate unique filename
                $signature_filename = 'secretary_sig_' . $id . '_' . time() . '.png';
                $signature_path = $uploads_dir . '/' . $signature_filename;
                
                // Save image
                $result = file_put_contents($signature_path, $signature_data);
                if ($result === false) {
                    throw new Exception("Failed to save signature image");
                }
                $signature_db_path = 'uploads/signatures/' . $signature_filename;
            }
            
            // Debug - Log signature data
            $signature_debug = [
                'signature_filename' => $signature_filename,
                'signature_path' => $signature_path ?? 'not set',
                'signature_db_path' => $signature_db_path ?? 'not set',
            ];
            file_put_contents('../logs/secretary_signature_debug_' . time() . '.json', json_encode($signature_debug, JSON_PRETTY_PRINT));
            
            // Update application status
            $stmt = $pdo->prepare("
                UPDATE members_information 
                SET 
                    secretary_approved = ?, 
                    secretary_name = ?, 
                    secretary_signature = ?, 
                    secretary_comments = ?, 
                    secretary_approval_date = NOW()
                WHERE id = ?
            ");
            
            $update_result = $stmt->execute([
                $approval_action, 
                $secretary_name, 
                $signature_db_path, 
                $secretary_comments, 
                $id
            ]);
            
            if (!$update_result) {
                throw new Exception("Database update failed: " . implode(", ", $stmt->errorInfo()));
            }
            
            // Generate and assign MC numbers if approved
            if ($approval_action === 'approved') {
                // Get the plans from application
                $plansStmt = $pdo->prepare("SELECT plans FROM members_information WHERE id = ?");
                $plansStmt->execute([$id]);
                $plansData = $plansStmt->fetch(PDO::FETCH_ASSOC);
                $plans = json_decode($plansData['plans'] ?? '[]', true);
                
                // Function to generate a unique 6-digit number
                function generateUniqueMcNumber($pdo, $columnName) {
                    $isUnique = false;
                    $mcNumber = '';
                    
                    while (!$isUnique) {
                        // Generate a random 6-digit number
                        $mcNumber = sprintf('%06d', rand(1, 999999));
                        
                        // Check if it exists already
                        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM members_information WHERE $columnName = ?");
                        $checkStmt->execute([$mcNumber]);
                        $exists = (int)$checkStmt->fetchColumn() > 0;
                        
                        if (!$exists) {
                            $isUnique = true;
                        }
                    }
                    
                    return $mcNumber;
                }
                
                // Initialize update columns and values
                $updateColumns = [];
                $updateValues = [];
                
                // Check each plan and generate a number if needed
                if (in_array('BLIP', $plans)) {
                    $blipMc = generateUniqueMcNumber($pdo, 'blip_mc');
                    $updateColumns[] = 'blip_mc = ?';
                    $updateValues[] = $blipMc;
                }
                
                if (in_array('LPIP', $plans)) {
                    $lpipMc = generateUniqueMcNumber($pdo, 'lpip_mc');
                    $updateColumns[] = 'lpip_mc = ?';
                    $updateValues[] = $lpipMc;
                }
                
                if (in_array('LMIP', $plans)) {
                    $lmipMc = generateUniqueMcNumber($pdo, 'lmip_mc');
                    $updateColumns[] = 'lmip_mc = ?';
                    $updateValues[] = $lmipMc;
                }
                
                // If there are MC numbers to update, run the update query
                if (!empty($updateColumns)) {
                    $updateValues[] = $id; // Add ID for WHERE clause
                    $updateQuery = "UPDATE members_information SET " . implode(', ', $updateColumns) . " WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateQuery);
                    $updateResult = $updateStmt->execute($updateValues);
                    
                    if (!$updateResult) {
                        throw new Exception("Failed to update MC numbers: " . implode(", ", $updateStmt->errorInfo()));
                    }
                    
                    // Log the MC numbers generation
                    log_message("Generated MC numbers for application ID: {$id}", 'info', 'approval');
                }
            }
            
            if (function_exists('log_approval_activity')) {
                log_approval_activity($id, 'secretary', $approval_action, [
                    'secretary_name' => $secretary_name,
                    'has_signature' => !empty($signature_db_path),
                    'send_email' => 'yes'
                ]);
            }
            
            // Verify the update worked by querying the database again
            $verify_stmt = $pdo->prepare("SELECT secretary_approved, status FROM members_information WHERE id = ?");
            $verify_stmt->execute([$id]);
            $verify_result = $verify_stmt->fetch(PDO::FETCH_ASSOC);
            
            log_message("Verification check - Secretary approval now set to: {$verify_result['secretary_approved']}, Status: {$verify_result['status']}", 'debug', 'approval_debug');
            
            // Commit the database changes first
            $pdo->commit();
            $success = true;
            
            // Store message for redirect
            if ($approval_action === 'approved') {
                $_SESSION['message'] = "Application has been approved successfully.";
            } else {
                $_SESSION['message'] = "Application has been " . $approval_action . " successfully.";
            }
            
            // Set a flag for redirect that we'll check at the end of the try block
            $should_redirect = true;
            
            // Handle email notification in a separate try-catch block
            // This way, if email fails, we've already committed the DB changes
            if ($approval_action === 'approved' && !empty($application['email']) && $send_email) {
                try {
                    // Log that we're attempting to send email
                    log_message("Starting email sending process for application ID: {$id}", 'info', 'email_process');
                    
                    // Ensure email_config is included
                    if (!function_exists('send_email_with_attachments')) {
                        require_once '../user/email_config.php';
                    }
                    
                    // Get plans from application
                    $plans = json_decode($application['plans'] ?? '[]', true) ?: [];
                    
                    // Generate PDF files for attachments
                    $attachments = [];
                    
                    // Create directory for temporary PDF files if it doesn't exist
                    $temp_dir = '../uploads/temp';
                    if (!is_dir($temp_dir)) {
                        if (!mkdir($temp_dir, 0755, true)) {
                            log_message("Failed to create temporary directory: $temp_dir", 'error', 'email');
                        }
                    }
                    
                    // Ensure the directory exists and is writable
                    if (!is_dir($temp_dir) || !is_writable($temp_dir)) {
                        log_message("Temporary directory $temp_dir does not exist or is not writable", 'error', 'email');
                        $temp_dir = sys_get_temp_dir(); // Fallback to system temp directory
                    }
                    
                    // Get absolute path to temp directory
                    $abs_temp_dir = realpath($temp_dir);
                    if (!$abs_temp_dir) {
                        $abs_temp_dir = sys_get_temp_dir(); // Fallback to system temp directory
                    }
                    
                    // Generate and save application PDF
                    $app_pdf_path = $abs_temp_dir . '/application_' . $id . '.pdf';
                    try {
                        // Capture the output from generate_application_pdf.php
                        ob_start();
                        // Fix for undefined array key "mode"
                        $old_mode = isset($_GET['mode']) ? $_GET['mode'] : null;
                        $_GET['mode'] = 'save';
                        // Use absolute path for the PDF file
                        $_GET['output_path'] = realpath($temp_dir) . '/application_' . $id . '.pdf';
                        
                        // Temporarily suppress deprecation warnings for TCPDF
                        $oldErrorLevel = error_reporting();
                        error_reporting($oldErrorLevel & ~E_DEPRECATED);
                        
                        include 'generate_application_pdf.php';
                        
                        // Restore error reporting
                        error_reporting($oldErrorLevel);
                        
                        ob_end_clean();
                        $_GET['mode'] = $old_mode;
                        
                        if (file_exists($_GET['output_path'])) {
                            $attachments[] = $_GET['output_path'];
                        }
                    } catch (Exception $e) {
                        log_message("Error generating application PDF: " . $e->getMessage(), 'error', 'email');
                    }
                    
                    // Generate certificate PDFs for each plan
                    foreach ($plans as $plan) {
                        $cert_pdf_path = realpath($temp_dir) . '/certificate_' . $id . '_' . $plan . '.pdf';
                        try {
                            // Capture the output from generate_certificate.php
                            ob_start();
                            // Fix for undefined array keys
                            $old_mode = isset($_GET['mode']) ? $_GET['mode'] : null;
                            $old_plan = isset($_GET['plan']) ? $_GET['plan'] : null;
                            $_GET['mode'] = 'save';
                            $_GET['plan'] = $plan;
                            $_GET['output_path'] = $cert_pdf_path;
                            
                            // Temporarily suppress deprecation warnings for TCPDF
                            $oldErrorLevel = error_reporting();
                            error_reporting($oldErrorLevel & ~E_DEPRECATED);
                            
                            include 'generate_certificate.php';
                            
                            // Restore error reporting
                            error_reporting($oldErrorLevel);
                            
                            ob_end_clean();
                            $_GET['mode'] = $old_mode;
                            $_GET['plan'] = $old_plan;
                            
                            if (file_exists($cert_pdf_path)) {
                                $attachments[] = $cert_pdf_path;
                            }
                        } catch (Exception $e) {
                            log_message("Error generating certificate PDF for $plan: " . $e->getMessage(), 'error', 'email');
                        }
                    }
                    
                    // Set up email message with attachments
                    $html_message = <<<HTML
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            h1 { color: #0070f3; font-size: 24px; margin-bottom: 20px; }
                            p { margin-bottom: 15px; }
                            .footer { margin-top: 30px; font-size: 14px; color: #666; border-top: 1px solid #eee; padding-top: 20px; }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <h1>TSPI Membership Approved</h1>
                            <p>Dear {$application['first_name']} {$application['last_name']},</p>
                            <p>We are pleased to inform you that your TSPI membership application has been approved.</p>
                            <p>Your membership ID is: <strong>{$application['cid_no']}</strong></p>
                            <p>Attached to this email, you will find:</p>
                            <ul>
                                <li>Your TSPI Membership Application Form</li>
                                <li>Your TSPI Membership Certificate(s)</li>
                            </ul>
                            <p>If you have any questions about your membership, please contact your assigned branch.</p>
                            <div class="footer">
                                <p>Thank you for choosing TSPI.</p>
                                <p>&copy; TSPI Membership Services</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    HTML;

                    $text_message = "Dear {$application['first_name']} {$application['last_name']},\n\n"
                        . "We are pleased to inform you that your TSPI membership application has been approved.\n\n"
                        . "Your membership ID is: {$application['cid_no']}\n\n"
                        . "Attached to this email, you will find:\n"
                        . "- Your TSPI Membership Application Form\n"
                        . "- Your TSPI Membership Certificate(s)\n\n"
                        . "If you have any questions about your membership, please contact your assigned branch.\n\n"
                        . "Thank you for choosing TSPI.\n\n"
                        . "TSPI Membership Services";
                    
                    // Send email with attachments
                    try {
                        // Define email parameters
                        $to = $application['email'];
                        $subject = 'TSPI Membership Application Approved';
                        
                        // Debug email data
                        $debug_email_data = [
                            'recipient' => $to,
                            'subject' => $subject,
                            'application_id' => $id,
                            'time' => date('Y-m-d H:i:s'),
                            'attachments' => $attachments
                        ];
                        
                        // Log email attempt
                        log_message("Attempting to send email to: {$to}, subject: {$subject}, app ID: {$id}", 'info', 'email_debug');
                        
                        // Use the send_email_with_attachments function if it exists
                        if (function_exists('send_email_with_attachments')) {
                            $mail_sent = send_email_with_attachments($to, $subject, $text_message, $html_message, $attachments);
                        } else {
                            // Fallback to basic email without attachments
                            $mail_sent = send_email($to, $subject, $html_message);
                        }
                        
                        // Write debug info to file
                        $debug_email_data['mail_sent'] = $mail_sent ? 'yes' : 'no';
                        file_put_contents('../logs/email_status_' . $id . '.json', json_encode($debug_email_data, JSON_PRETTY_PRINT));
                        
                        // Log email sending result
                        if ($mail_sent) {
                            log_message("Email notification sent successfully to {$application['email']} for application ID: {$id}", 'info', 'email');
                            $_SESSION['message'] .= " An email notification with certificates has been sent to the applicant.";
                        } else {
                            log_message("Failed to send email notification to {$application['email']} for application ID: {$id}", 'error', 'email');
                            $_SESSION['message'] .= " But there was an issue sending the email notification.";
                        }
                    } catch (Exception $e) {
                        log_message("Email sending error: " . $e->getMessage(), 'error', 'email');
                        file_put_contents('../logs/email_exception_' . $id . '.txt', $e->getMessage() . "\n" . $e->getTraceAsString());
                        $_SESSION['message'] .= " But there was an error sending the email: " . $e->getMessage();
                    }
                } catch (Exception $emailEx) {
                    // Log email generation/sending errors but don't block the redirect
                    log_message("Email process error: " . $emailEx->getMessage(), 'error', 'email_process');
                    $_SESSION['message'] .= " Email could not be sent: " . $emailEx->getMessage();
                }
            }
            
            // Always redirect if we should - this ensures the redirect happens even if email processing fails
            if ($should_redirect) {
                log_message("Redirecting to view_application.php with id={$id}", 'info', 'redirect');
                header("Location: view_application.php?id=" . $id);
                exit;
            }
        }
    } catch (Exception $e) {
        // If an exception occurred, roll back the transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Log the error and add to errors array
        $errors[] = "An error occurred: " . $e->getMessage();
        log_message("Error in secretary approval process: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'error', 'approval_error');
        
        // Write detailed error to log file for debugging
        file_put_contents('../logs/secretary_approval_error_' . time() . '.txt', 
            "Error: " . $e->getMessage() . "\n" . 
            "Trace: " . $e->getTraceAsString() . "\n" . 
            "POST data: " . json_encode($_POST, JSON_PRETTY_PRINT) . "\n"
        );
    }
}
?><!DOCTYPE html>
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

        .admin-card {
            margin-bottom: 2rem;
            padding: 0;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .admin-card:hover {
            box-shadow: var(--shadow-md);
        }

        .admin-card-header {
            padding: 1.25rem 1.5rem;
            background-color: var(--gray-100);
            border-bottom: 1px solid var(--gray-200);
        }
        
        .admin-card-header h2 {
            margin: 0;
            font-size: 1.4rem;
            color: var(--gray-800);
        }
        
        .admin-card-body {
            padding: 1.5rem;
        }
        
        /* Signature pad styling */
        .signature-container {
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
        
        .signature-container:hover {
            border-color: var(--primary-color);
        }

        #signature-pad {
            width: 100%;
            height: 100%;
            touch-action: none;
        }

        .signature-actions {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
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
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 10px 0;
        }
        
        .radio-group label {
            display: flex;
            align-items: center;
            margin-right: 20px;
        }
        
        .radio-group input[type="radio"] {
            margin-right: 8px;
            width: auto;
        }

        /* Application summary */
        .application-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .summary-item {
            flex: 1;
            min-width: 250px;
            margin-bottom: 0.8rem;
        }

        .summary-item strong {
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
            color: var(--gray-700);
        }
        
        .form-hint {
            margin: 5px 0 10px;
            color: var(--gray-600);
            font-size: 0.9rem;
        }
        
        /* Buttons */
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
            text-decoration: none;
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
        
        .btn-info {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }
        
        .btn-info:hover {
            background-color: #cce5ff;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        /* Form actions */
        .form-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        /* Document preview section */
        .pdf-preview {
            margin-top: 2rem;
        }
        
        .preview-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            position: relative;
            z-index: 10;
        }
        
        .preview-links .dropdown-menu {
            position: relative;
            z-index: 50;
            display: inline-block;
        }
        
        .preview-links .dropdown-content {
            position: absolute;
            left: 0;
            top: 100%;
            background-color: white;
            min-width: 220px;
            box-shadow: var(--shadow-md);
            border-radius: var(--border-radius);
            z-index: 1000;
            border: 1px solid var(--gray-200);
            display: none;
            overflow: visible;
        }
        
        .preview-links .dropdown-menu:hover .dropdown-content,
        .preview-links .dropdown-content:hover {
            display: block;
        }
        
        .dropdown-item {
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            color: var(--gray-800);
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: var(--gray-100);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .preview-links {
                flex-direction: column;
            }
            
            .approval-options {
                flex-direction: column;
            }
        }

        .approval-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
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

        .signature-actions {
            margin-top: 10px;
            display: flex;
            justify-content: flex-start;
        }
        
        .admin-card.pdf-preview {
            position: relative;
            overflow: visible;
        }

        /* Update textarea CSS to be non-resizable */
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.2s;
            font-family: inherit;
            resize: none;
        }
        
        .checkbox-item {
            display: flex;
            align-items: flex-start; /* Align items to the start for checkbox */
            gap: 8px; /* Add gap between checkbox and label */
        }

        .checkbox-item input[type="checkbox"] {
            margin-top: 0.2em; /* Adjust checkbox position */
            width: auto; /* Override default width */
        }

        /* Add disclaimer box style */
        .disclaimer-box {
            margin-top: 1.5rem;
            padding: 1.25rem;
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--accent-color);
        }
        
        /* Fix for dropdown menu in header */
        .dropdown-menu.show {
            display: block !important;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        .message.error ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        /* ID Preview Styles */
        .id-previews-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 15px 0;
        }
        
        .id-preview-card {
            flex: 1;
            min-width: 200px;
            max-width: 300px;
            background-color: white;
            border-radius: var(--border-radius);
            padding: 15px;
            box-shadow: var(--shadow-sm);
        }
        
        .id-preview-card h3 {
            margin: 0 0 15px;
            font-size: 16px;
            color: var(--gray-700);
        }
        
        .id-image-preview {
            position: relative;
            margin-bottom: 10px;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .id-image-preview:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: translateY(-3px);
        }
        
        .id-image-preview img {
            width: 100%;
            display: block;
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

        /* Add loading overlay styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .loading-spinner i {
            color: #0070f3;
            margin-bottom: 15px;
        }
        
        .loading-spinner p {
            margin: 10px 0 0;
            color: #333;
            font-weight: 500;
        }
    </style>
</head>
<body class="<?php echo $body_class; ?>">

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <?php include 'includes/header.php'; ?>
        <div class="content-container dashboard-container">
            <?php if (!empty($errors)): ?>
                <div class="message error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="page-header">
                <h1>Final Application Approval</h1>
                <a href="applications.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Applications</a>
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
            
            <!-- Valid ID Previews Card -->
            <?php if (!empty($application['valid_id_path']) || !empty($application['spouse_valid_id_path'])): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Valid ID Previews</h2>
                </div>
                <div class="admin-card-body">
                    <div class="id-previews-container">
                        <?php if (!empty($application['valid_id_path'])): ?>
                        <div class="id-preview-card">
                            <h3>Member's Valid ID</h3>
                            <div class="id-image-preview">
                                <a href="<?php echo SITE_URL . '/' . $application['valid_id_path']; ?>" target="_blank" class="id-preview-link">
                                    <img src="<?php echo SITE_URL . '/' . $application['valid_id_path']; ?>" alt="Member Valid ID">
                                    <div class="preview-overlay">
                                        <span class="view-full-size">View Full Size</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($application['spouse_valid_id_path'])): ?>
                        <div class="id-preview-card">
                            <h3>Spouse's Valid ID</h3>
                            <div class="id-image-preview">
                                <a href="<?php echo SITE_URL . '/' . $application['spouse_valid_id_path']; ?>" target="_blank" class="id-preview-link">
                                    <img src="<?php echo SITE_URL . '/' . $application['spouse_valid_id_path']; ?>" alt="Spouse Valid ID">
                                    <div class="preview-overlay">
                                        <span class="view-full-size">View Full Size</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Approval Decision</h2>
                </div>
                <div class="admin-card-body">
                    <div class="approval-options">
                        <div class="approval-option" data-value="approved">
                            <i class="fas fa-check-circle fa-2x" style="color: #00c853;"></i>
                            <h3>Approve</h3>
                            <p>Endorse this application for final approval</p>
                        </div>
                        <div class="approval-option reject" data-value="rejected">
                            <i class="fas fa-times-circle fa-2x" style="color: #f44336;"></i>
                            <h3>Reject</h3>
                            <p>Decline this application</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Secretary Final Approval</h2>
                    <?php if (!empty($application['email'])): ?>
                    <a href="test_email.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-info" style="float: right;">
                        <i class="fas fa-envelope"></i> Test Email System
                    </a>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <form method="post" id="final-approval-form">
                        <!-- Secretary's name is now hidden, using the current user's name -->
                        <input type="hidden" id="secretary_name" name="secretary_name" value="<?php echo $current_user['name'] ?? ''; ?>">
                        <input type="hidden" id="action-input" name="approval_action" value="">
                        <!-- Always set send_email to true -->
                        <input type="hidden" name="send_email" value="1">
                        
                        <div class="form-group">
                            <label for="secretary_comments">Comments (optional):</label>
                            <textarea id="secretary_comments" name="secretary_comments" rows="3"></textarea>
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
                        
                        <div class="form-group disclaimer-box">
                            <div class="checkbox-item">
                                <input type="checkbox" id="disclaimer_agreement" name="disclaimer_agreement" required>
                                <label for="disclaimer_agreement">
                                    I, <strong><?php echo htmlspecialchars($current_user['name'] ?? ''); ?></strong>, 
                                    acting in my capacity as a Secretary, 
                                    confirm that I have reviewed this application thoroughly and take responsibility for my final decision.
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="view_application.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Final Decision</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

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
        const container = canvas.parentElement;
        const savedData = signaturePad.toDataURL();
        
        canvas.width = container.offsetWidth * ratio;
        canvas.height = container.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        
        // Restore signature after resize if it exists
        if (savedData && !signaturePad.isEmpty()) {
            signaturePad.fromDataURL(savedData);
        } else {
            signaturePad.clear();
        }
    }
    
    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();
    
    // Clear signature button
    document.getElementById('clear-signature').addEventListener('click', function() {
        signaturePad.clear();
        document.getElementById('signature-data').value = '';
    });
    
    // Save on any change to ensure we catch all signature actions
    signaturePad.addEventListener('endStroke', function() {
        document.getElementById('signature-data').value = signaturePad.toDataURL();
    });
    
    // Form submission - improved with loading overlay
    const form = document.getElementById('final-approval-form');
    let isSubmitting = false; // Flag to prevent double submissions
    
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Prevent double submission
        if (isSubmitting) {
            return;
        }
        
        if (signaturePad.isEmpty()) {
            alert('Please provide your signature before submitting.');
            return;
        }
        
        // Check if disclaimer is checked
        const disclaimerChecked = document.getElementById('disclaimer_agreement').checked;
        if (!disclaimerChecked) {
            alert('Please confirm the disclaimer before proceeding.');
            return;
        }
        
        // Check if an approval option is selected
        const actionInput = document.getElementById('action-input');
        if (!actionInput.value) {
            alert('Please select either Approve or Reject before proceeding.');
            return;
        }
        
        // Save signature data to the hidden input
        document.getElementById('signature-data').value = signaturePad.toDataURL();
        
        // Set the submitting flag
        isSubmitting = true;
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        // Create and append the loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p>Processing your request...</p>
                <p class="small">Please don't close this page</p>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
        
        // Submit the form
        setTimeout(function() {
            form.submit();
        }, 100);
    });
    
    // Handle approval option selection
    const approvalOptions = document.querySelectorAll('.approval-option');
    const actionInput = document.getElementById('action-input');
    
    approvalOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            approvalOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Set the action value
            actionInput.value = this.getAttribute('data-value');
        });
    });
    
    // Auto-select the approve option by default
    const defaultOption = document.querySelector('.approval-option[data-value="approved"]');
    if (defaultOption) {
        defaultOption.click();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html> 