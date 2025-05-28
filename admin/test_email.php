<?php
/**
 * Test Email Functionality
 * 
 * This script provides a simple testing interface for email functionality
 * Used for diagnosing email issues in the system
 */

require_once '../includes/config.php';
require_admin_login();
require_once '../user/email_config.php'; // Include the SendGrid email configuration

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure user has permission to access this page (admin or secretary)
$current_user = get_admin_user();
if (!in_array($current_user['role'], ['admin', 'secretary'])) {
    $_SESSION['message'] = "You don't have permission to access this page.";
    redirect('/admin/index.php');
    exit;
}

// Fetch application if ID is provided
$application = null;
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM members_information WHERE id = ?");
    $stmt->execute([$id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        $_SESSION['message'] = "Application not found.";
        redirect('/admin/applications.php');
        exit;
    }
}

// Check if email test is being sent
$test_sent = false;
$test_result = null;
$mail_error = null;
$attachments_generated = false;
$attachments_list = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Common variables
        $to = $_POST['recipient'] ?? '';
        $test_mode = $_POST['test_mode'] ?? 'simple';
        
        if ($test_mode === 'simple') {
            // Simple test email
            $subject = 'TSPI Email Test';
            $current_time = date('Y-m-d H:i:s');
            $app_id = $id ? $id : 'N/A';
            
            $message = <<<HTML
<html>
<body>
    <h1>TSPI Email Test</h1>
    <p>This is a test email from TSPI CMS.</p>
    <p>If you received this email, the email system is working correctly.</p>
    <p>Application ID: $app_id</p>
    <p>Test Time: $current_time</p>
</body>
</html>
HTML;
            
            // Debug info for logging
            $debug_info = [
                'to' => $to,
                'subject' => $subject,
                'time' => $current_time,
                'application_id' => $app_id,
                'test_mode' => 'simple'
            ];
            
            // Use the send_email function
            $test_sent = send_email($to, $subject, $message);
            
        } else {
            // Full test with attachments (like secretary approval)
            $subject = 'TSPI Membership Application Approved (TEST)';
            
            // Debug info for logging
            $debug_info = [
                'to' => $to,
                'subject' => $subject,
                'time' => date('Y-m-d H:i:s'),
                'application_id' => $id ?: 'N/A',
                'test_mode' => 'with_attachments'
            ];
            
            // Only proceed with attachment generation if we have a valid application
            if ($application) {
                // Get plans from application
                $plans = json_decode($application['plans'] ?? '[]', true) ?: [];
                
                // Debug - print plans data
                echo "<div style='background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; font-family: monospace;'>";
                echo "<h3>Debug - Plans Data:</h3>";
                echo "<p>Raw plans value: " . htmlspecialchars($application['plans']) . "</p>";
                echo "<p>Decoded plans: </p><pre>" . print_r($plans, true) . "</pre>";
                echo "</div>";
                
                // Make sure plans is always an array
                if (!is_array($plans)) {
                    $plans = [];
                }
                
                // If plans is empty but we have plan fields in the application, try to reconstruct
                if (empty($plans)) {
                    // Check individual plan fields that might exist in the database
                    $possible_plans = ['BLIP', 'LPIP', 'LMIP', 'CLIP', 'MRI', 'GLIP'];
                    foreach ($possible_plans as $plan) {
                        $plan_field = strtolower($plan) . '_mc';
                        if (!empty($application[$plan_field])) {
                            $plans[] = $plan;
                        }
                    }
                    
                    echo "<div style='background-color: #ffe9e9; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0;'>";
                    echo "<h3>Plans Reconstructed:</h3>";
                    echo "<pre>" . print_r($plans, true) . "</pre>";
                    echo "</div>";
                }
                
                // Check for forced plans from the form
                if (isset($_POST['force_plans']) && is_array($_POST['force_plans'])) {
                    $forced_plans = $_POST['force_plans'];
                    
                    // If we have forced plans, ONLY use those (don't merge with existing)
                    if (!empty($forced_plans)) {
                        echo "<div style='background-color: #e8f5e9; border: 1px solid #c8e6c9; padding: 10px; margin: 10px 0;'>";
                        echo "<h3>Using only forced plans from form:</h3>";
                        echo "<pre>" . print_r($forced_plans, true) . "</pre>";
                        echo "</div>";
                        $plans = $forced_plans;
                    } else {
                        // Add any forced plans that aren't already in the plans array
                        foreach ($forced_plans as $forced_plan) {
                            if (!in_array($forced_plan, $plans)) {
                                $plans[] = $forced_plan;
                            }
                        }
                        
                        echo "<div style='background-color: #e8f5e9; border: 1px solid #c8e6c9; padding: 10px; margin: 10px 0;'>";
                        echo "<h3>Plans After Forcing:</h3>";
                        echo "<pre>" . print_r($plans, true) . "</pre>";
                        echo "</div>";
                    }
                }
                
                // If we still have no plans, default to BLIP
                if (empty($plans)) {
                    $plans = ['BLIP'];
                    echo "<div style='background-color: #fff3e0; border: 1px solid #ffe0b2; padding: 10px; margin: 10px 0;'>";
                    echo "<h3>No plans found, defaulting to BLIP:</h3>";
                    echo "<pre>" . print_r($plans, true) . "</pre>";
                    echo "</div>";
                }
                
                // Generate PDF files for attachments
                $attachments = [];
                
                // Create directory for temporary PDF files if it doesn't exist
                $temp_dir = '../uploads/temp';
                if (!is_dir($temp_dir)) {
                    if (!mkdir($temp_dir, 0755, true)) {
                        throw new Exception("Failed to create temporary directory: $temp_dir");
                    }
                }
                
                // Ensure the directory exists and is writable
                if (!is_dir($temp_dir) || !is_writable($temp_dir)) {
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
                    $_GET['output_path'] = $abs_temp_dir . '/application_' . $id . '.pdf';
                    
                    // Temporarily suppress deprecation warnings for TCPDF
                    $oldErrorLevel = error_reporting();
                    error_reporting($oldErrorLevel & ~E_DEPRECATED);
                    
                    // Instead of include, we'll use require_once with output buffering
                    // and we'll save the current output buffer contents
                    $old_ob_level = ob_get_level();
                    require_once 'generate_application_pdf_for_inclusion.php';
                    
                    // Restore error reporting
                    error_reporting($oldErrorLevel);
                    
                    // Clean up output buffer
                    while (ob_get_level() > $old_ob_level) {
                        ob_end_clean();
                    }
                    
                    $_GET['mode'] = $old_mode;
                    
                    if (file_exists($_GET['output_path'])) {
                        $attachments[] = $_GET['output_path'];
                        $attachments_list[] = "Application PDF: " . basename($_GET['output_path']);
                    }
                } catch (Exception $e) {
                    error_log("Error generating application PDF: " . $e->getMessage());
                    $debug_info['application_pdf_error'] = $e->getMessage();
                }
                
                // Generate certificate PDFs for each plan
                foreach ($plans as $plan) {
                    $cert_pdf_path = $abs_temp_dir . '/certificate_' . $id . '_' . $plan . '.pdf';
                    try {
                        echo "<div style='background-color: #e0f7fa; border: 1px solid #b3e5fc; padding: 10px; margin: 5px 0; font-family: monospace;'>";
                        echo "Generating certificate for plan: {$plan}...";
                        
                        // Capture the output from generate_certificate.php
                        ob_start();
                        
                        // Fix for undefined array keys
                        $old_mode = isset($_GET['mode']) ? $_GET['mode'] : null;
                        $old_plan = isset($_GET['plan']) ? $_GET['plan'] : null;
                        $_GET['mode'] = 'save';
                        $_GET['plan'] = $plan;
                        $_GET['output_path'] = $cert_pdf_path;
                        
                        echo " Setting _GET variables: mode={$_GET['mode']}, plan={$_GET['plan']}, output_path={$_GET['output_path']}... ";
                        
                        // Temporarily suppress deprecation warnings for TCPDF
                        $oldErrorLevel = error_reporting();
                        error_reporting($oldErrorLevel & ~E_DEPRECATED);
                        
                        // Instead of include, we'll use require_once with proper output buffer management
                        $old_ob_level = ob_get_level();
                        echo "Including certificate generator... ";
                        
                        // Store existing error state before include
                        $pdf_error_before = isset($pdf_error) ? $pdf_error : null;
                        
                        // Execute certificate generator and capture its return value
                        $generate_result = require 'generate_certificate_without_exit.php';
                        
                        // Restore error reporting
                        error_reporting($oldErrorLevel);
                        
                        // Clean up output buffer
                        while (ob_get_level() > $old_ob_level) {
                            ob_end_clean();
                        }
                        
                        $_GET['mode'] = $old_mode;
                        $_GET['plan'] = $old_plan;
                        
                        // Check if certificate generation was successful 
                        if (file_exists($cert_pdf_path) && $generate_result !== false) {
                            $attachments[] = $cert_pdf_path;
                            $attachments_list[] = "Certificate PDF ($plan): " . basename($cert_pdf_path);
                            echo "SUCCESS! File created at {$cert_pdf_path}";
                        } else {
                            // Error details
                            $error_msg = isset($pdf_error) && $pdf_error !== $pdf_error_before 
                                ? $pdf_error 
                                : "Unknown error generating certificate";
                            echo "FAILED! File not created at {$cert_pdf_path}. Error: {$error_msg}";
                            
                            // Check if template exists
                            $template_path = '../templates/Membership-Certificate-for-Basic-Life-Insurance-Plan-' . $plan . '.pdf';
                            if (!file_exists($template_path)) {
                                echo " (Template file not found: {$template_path})";
                            }
                        }
                        echo "</div>";
                    } catch (Exception $e) {
                        echo "<div style='background-color: #ffebee; border: 1px solid #ffcdd2; padding: 10px; margin: 5px 0;'>";
                        echo "Error generating certificate PDF for $plan: " . $e->getMessage();
                        echo "</div>";
                        error_log("Error generating certificate PDF for $plan: " . $e->getMessage());
                        $debug_info['certificate_pdf_error_' . $plan] = $e->getMessage();
                    }
                }
                
                // Set up HTML message like in secretary_approve_application.php
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
        <h1>TEST - TSPI Membership Approved</h1>
        <p>Dear {$application['first_name']} {$application['last_name']},</p>
        <p>This is a TEST email. Please ignore if received by mistake.</p>
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

                $text_message = "TEST EMAIL - Please ignore if received by mistake\n\n"
                    . "Dear {$application['first_name']} {$application['last_name']},\n\n"
                    . "We are pleased to inform you that your TSPI membership application has been approved.\n\n"
                    . "Your membership ID is: {$application['cid_no']}\n\n"
                    . "Attached to this email, you will find:\n"
                    . "- Your TSPI Membership Application Form\n"
                    . "- Your TSPI Membership Certificate(s)\n\n"
                    . "If you have any questions about your membership, please contact your assigned branch.\n\n"
                    . "Thank you for choosing TSPI.\n\n"
                    . "TSPI Membership Services";
                
                // Save attachments info for display
                $attachments_generated = !empty($attachments);
                $debug_info['attachments'] = $attachments_list;
                
                // Use the send_email_with_attachments function if it exists
                if (function_exists('send_email_with_attachments')) {
                    $test_sent = send_email_with_attachments($to, $subject, $text_message, $html_message, $attachments);
                } else {
                    // Fallback to basic email without attachments
                    $test_sent = send_email($to, $subject, $html_message);
                    $debug_info['fallback'] = 'Used send_email because send_email_with_attachments not found';
                }
            } else {
                // No application, just send a simple test email
                $message = "<h1>TEST - TSPI Email System</h1><p>This is a test email with no attachments since no application was selected.</p>";
                $test_sent = send_email($to, $subject, $message);
                $debug_info['no_application'] = true;
            }
        }
        
        // Save debug info
        $debug_info['result'] = $test_sent ? 'success' : 'failed';
        
        // Ensure logs directory exists
        $log_dir = '../logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        // Save debug info to file
        file_put_contents("{$log_dir}/email_test_" . time() . ".json", json_encode($debug_info, JSON_PRETTY_PRINT));
        
        if ($test_sent) {
            $test_result = "Test email sent successfully to {$to}";
        } else {
            $test_result = "Failed to send test email. Check logs for details.";
            $mail_error = error_get_last();
        }
    } catch (Exception $e) {
        $test_result = "Error: " . $e->getMessage();
        file_put_contents('../logs/email_error_' . time() . '.txt', $e->getMessage() . "\n" . $e->getTraceAsString());
    }
}

// Title and page setup
$page_title = "Email Test Tool";
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
        .email-test-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .test-form {
            margin: 2rem 0;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: #0056b3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            margin-right: 10px;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
        }

        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .log-preview {
            margin-top: 1rem;
        }

        .log-preview pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            max-height: 300px;
            overflow-y: auto;
            font-size: 0.85rem;
            white-space: pre-wrap;
        }

        .troubleshooting {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #17a2b8;
        }

        .troubleshooting h2 {
            margin-top: 0;
            color: #333;
        }

        .troubleshooting ol {
            padding-left: 1.5rem;
        }

        .config-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .config-info h2 {
            color: #333;
            margin-top: 0;
        }

        .config-info ul {
            padding-left: 1.5rem;
        }
        
        .error-details {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-top: 10px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .attachments-list {
            background-color: #f0f8ff;
            border: 1px solid #b8daff;
            border-radius: 4px;
            padding: 10px 15px;
            margin-top: 10px;
        }
        
        .attachments-list h4 {
            margin-top: 0;
            color: #004085;
            font-size: 0.9rem;
        }
        
        .attachments-list ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .radio-option input {
            margin-right: 8px;
            width: auto;
        }
        
        .buttons-row {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="content-container">
                <div class="page-header">
                    <h1>Email Test Tool</h1>
                    <a href="<?php echo isset($id) ? "view_application.php?id={$id}" : 'applications.php'; ?>" class="btn">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                
                <div class="email-test-container">
                    <h2>Send Test Email</h2>
                    <p>Use this tool to verify that your email system is working correctly.</p>
                    <?php if ($application): ?>
                        <p>Testing with Application #<?php echo $id; ?>: <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></p>
                    <?php endif; ?>
                    
                    <!-- Test Result Messages -->
                    <?php if ($test_sent === true): ?>
                        <div class="message success">
                            <p><strong>Success!</strong> Test email was sent to <?php echo htmlspecialchars($_POST['recipient']); ?>.</p>
                            <p>Please check the inbox (and spam folder) to confirm delivery.</p>
                            <?php if ($attachments_generated): ?>
                                <div class="attachments-list">
                                    <h4>Generated Attachments:</h4>
                                    <ul>
                                        <?php foreach($attachments_list as $attachment): ?>
                                            <li><?php echo htmlspecialchars($attachment); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($test_sent === false && $test_result !== null): ?>
                        <div class="message error">
                            <p><strong>Failed!</strong> Email could not be sent.</p>
                            <p><?php echo $test_result; ?></p>
                            
                            <?php if ($mail_error): ?>
                            <div class="error-details">
                                <?php print_r($mail_error); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Simple Test Form -->
                    <div class="test-form">
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="recipient">Recipient Email:</label>
                                <input type="email" id="recipient" name="recipient" required 
                                       value="<?php echo $application ? htmlspecialchars($application['email']) : (isset($_POST['recipient']) ? htmlspecialchars($_POST['recipient']) : ''); ?>"
                                       placeholder="Enter email address">
                            </div>
                            
                            <div class="form-group">
                                <label>Test Type:</label>
                                <div class="radio-option">
                                    <input type="radio" id="test_mode_simple" name="test_mode" value="simple" checked>
                                    <label for="test_mode_simple">Simple Email Test (No Attachments)</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="test_mode_full" name="test_mode" value="with_attachments" <?php echo !$application ? 'disabled' : ''; ?>>
                                    <label for="test_mode_full">
                                        Full Test with PDF Attachments 
                                        <?php echo !$application ? '(Requires an application ID)' : ''; ?>
                                    </label>
                                </div>
                            </div>
                            
                            <?php if ($application): ?>
                            <div class="form-group" id="force_plans_container" style="display: none;">
                                <label>Force Include Plans:</label>
                                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                    <div class="radio-option">
                                        <input type="checkbox" id="force_blip" name="force_plans[]" value="BLIP">
                                        <label for="force_blip">BLIP</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="checkbox" id="force_lpip" name="force_plans[]" value="LPIP">
                                        <label for="force_lpip">LPIP</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="checkbox" id="force_lmip" name="force_plans[]" value="LMIP">
                                        <label for="force_lmip">LMIP</label>
                                    </div>
                                </div>
                                <p class="help-text" style="font-size: 0.85rem; color: #6c757d; margin-top: 5px;">
                                    Select plans to force include in the email regardless of what's stored in the database.
                                </p>
                            </div>
                            
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const testModeRadios = document.querySelectorAll('input[name="test_mode"]');
                                const forcePlansContainer = document.getElementById('force_plans_container');
                                
                                testModeRadios.forEach(radio => {
                                    radio.addEventListener('change', function() {
                                        if (this.value === 'with_attachments') {
                                            forcePlansContainer.style.display = 'block';
                                        } else {
                                            forcePlansContainer.style.display = 'none';
                                        }
                                    });
                                });
                                
                                // Initialize state
                                if (document.getElementById('test_mode_full').checked) {
                                    forcePlansContainer.style.display = 'block';
                                }
                            });
                            </script>
                            <?php endif; ?>
                            
                            <div class="buttons-row">
                                <button type="submit" name="send_test" class="btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Test Email
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Email Configuration Info -->
                    <div class="config-info">
                        <h2>Current Email Configuration</h2>
                        <p>Your email system is currently configured to use:</p>
                        <ul>
                            <li><strong>SMTP Server:</strong> smtp.sendgrid.net</li>
                            <li><strong>Port:</strong> 587</li>
                            <li><strong>Authentication:</strong> STARTTLS</li>
                            <li><strong>Username:</strong> apikey</li>
                            <li><strong>From Address:</strong> no-reply@tspi.site</li>
                            <li><strong>From Name:</strong> Ketano</li>
                        </ul>
                    </div>
                    
                    <!-- Email Log Information -->
                    <div class="email-logs">
                        <h2>Email Logs</h2>
                        <?php
                        // Check both log directories
                        $log_files = array_merge(
                            glob('../logs/email_*.{txt,json}', GLOB_BRACE) ?: [],
                            glob('../logs/test_email_*.json', GLOB_BRACE) ?: [],
                            glob('../user/email_*.{txt,log,debug}', GLOB_BRACE) ?: []
                        );
                        
                        if ($log_files && count($log_files) > 0):
                            $latest_logs = array_slice($log_files, -3); // Show latest 3 logs
                        ?>
                            <div>
                                <h3>Latest Log Files:</h3>
                                <ul>
                                    <?php foreach ($latest_logs as $log_file): ?>
                                        <li>
                                            <strong><?php echo basename($log_file); ?></strong> 
                                            (<?php echo date('Y-m-d H:i:s', filemtime($log_file)); ?>)
                                            <a href="#" class="view-log" data-log="<?php echo htmlspecialchars(file_get_contents($log_file)); ?>">View</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        // Show email_debug.txt content if exists
                        $debug_log = '../user/email_debug.txt';
                        if (file_exists($debug_log)): 
                            $log_content = file_get_contents($debug_log);
                            $log_entries = array_filter(explode("------------------------------", $log_content));
                            $log_entries = array_slice($log_entries, -3); // Last 3 entries
                        ?>
                            <div class="log-preview">
                                <h3>Latest Debug Log Entries:</h3>
                                <pre><?php echo htmlspecialchars(implode("\n------------------------------\n", $log_entries)); ?></pre>
                            </div>
                        <?php else: ?>
                            <p>No email debug log file found.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Email Troubleshooting Tips -->
                    <div class="troubleshooting">
                        <h2>Troubleshooting Tips</h2>
                        <p>If you're experiencing issues with email delivery:</p>
                        <ol>
                            <li>Ensure your SendGrid API key is valid and has the necessary permissions</li>
                            <li>Check that your sender domain is verified in SendGrid</li>
                            <li>Verify recipient email addresses are valid and not in your suppression list</li>
                            <li>Check spam/junk folders for test emails</li>
                            <li>Verify network connectivity to smtp.sendgrid.net:587</li>
                            <li>Try using a different recipient email service (Gmail, Outlook, etc.)</li>
                            <li>If seeing deprecation warnings in TCPDF, suppress them with error_reporting()</li>
                        </ol>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Log Viewer Modal -->
    <div id="log-modal" class="modal" style="display: none;">
        <div class="modal-content" style="width: 80%; max-width: 800px;">
            <span class="close">&times;</span>
            <h2>Log Details</h2>
            <pre id="log-content" style="background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; white-space: pre-wrap; max-height: 70vh; overflow-y: auto;"></pre>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Log viewer functionality
        const modal = document.getElementById('log-modal');
        const logContent = document.getElementById('log-content');
        const closeBtn = document.querySelector('.close');
        const viewLogBtns = document.querySelectorAll('.view-log');
        
        viewLogBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const logData = this.getAttribute('data-log');
                logContent.textContent = logData;
                modal.style.display = 'block';
            });
        });
        
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    </script>
    
    <style>
    /* Modal styles */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    
    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        border-radius: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
    }
    </style>
</body>
</html> 