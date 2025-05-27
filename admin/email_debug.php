<?php
/**
 * Email Debugging Tool
 * 
 * This file helps debug email sending issues by testing different methods:
 * 1. PHP's mail() function
 * 2. PHPMailer library
 */

$page_title = "Email Debug";
$body_class = "admin-email-debug";
require_once '../includes/config.php';
require_admin_login();

// Only administrators can access this page
$current_user = get_admin_user();
if ($current_user['role'] !== 'admin') {
    $_SESSION['message'] = "Only administrators can access the email debugging tool.";
    redirect('/admin/index.php');
}

$test_recipient = isset($_POST['recipient']) ? $_POST['recipient'] : '';
$test_method = isset($_POST['method']) ? $_POST['method'] : 'mail';
$mail_result = null;
$debug_output = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    if (empty($test_recipient) || !filter_var($test_recipient, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        ob_start(); // Capture any output or errors
        
        $subject = 'TSPI Email System Test';
        $message = 'This is a test email from the TSPI system. If you received this, email sending is working correctly.';
        
        switch ($test_method) {
            case 'mail':
                // Test PHP's mail() function
                $headers = "From: " . ADMIN_EMAIL . "\r\n";
                $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();
                
                $mail_result = mail($test_recipient, $subject, $message, $headers);
                $debug_output .= "Using PHP's mail() function\n";
                $debug_output .= "Headers: " . print_r($headers, true) . "\n";
                if ($mail_result === false) {
                    $debug_output .= "mail() function returned FALSE\n";
                    // Check if we can get more info
                    if (function_exists('error_get_last')) {
                        $debug_output .= "Last error: " . print_r(error_get_last(), true) . "\n";
                    }
                }
                break;
                
            case 'phpmailer':
                // Test PHPMailer
                require_once '../vendor/autoload.php';
                
                $debug_output .= "Using PHPMailer library\n";
                
                try {
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    
                    // Server settings
                    $mail->SMTPDebug = 2; // Enable verbose debug output
                    $mail->isSMTP(); // Send using SMTP
                    
                    // Check if email config exists
                    if (file_exists(__DIR__ . '/email_config.php')) {
                        require_once __DIR__ . '/email_config.php';
                        
                        $debug_output .= "Found email_config.php\n";
                        
                        // If config defines SMTP constants, use them
                        if (defined('SMTP_HOST')) {
                            $mail->Host = SMTP_HOST;
                            $debug_output .= "Using SMTP host: " . SMTP_HOST . "\n";
                            
                            if (defined('SMTP_PORT')) {
                                $mail->Port = SMTP_PORT;
                                $debug_output .= "Using SMTP port: " . SMTP_PORT . "\n";
                            }
                            
                            if (defined('SMTP_SECURE')) {
                                $mail->SMTPSecure = SMTP_SECURE;
                                $debug_output .= "Using SMTP secure: " . SMTP_SECURE . "\n";
                            }
                            
                            if (defined('SMTP_AUTH') && SMTP_AUTH) {
                                $mail->SMTPAuth = true;
                                $debug_output .= "Using SMTP authentication\n";
                                
                                if (defined('SMTP_USERNAME') && defined('SMTP_PASSWORD')) {
                                    $mail->Username = SMTP_USERNAME;
                                    $mail->Password = '********'; // Don't log actual password
                                    $debug_output .= "Using SMTP username: " . SMTP_USERNAME . "\n";
                                } else {
                                    $debug_output .= "WARNING: SMTP_AUTH is true but SMTP_USERNAME or SMTP_PASSWORD not defined\n";
                                }
                            } else {
                                $mail->SMTPAuth = false;
                                $debug_output .= "SMTP authentication disabled\n";
                            }
                        } else {
                            $debug_output .= "WARNING: No SMTP_HOST defined in email_config.php\n";
                            // Fallback to default settings
                            $mail->Host = 'localhost';
                            $mail->Port = 25;
                            $mail->SMTPAuth = false;
                        }
                    } else {
                        $debug_output .= "WARNING: email_config.php not found, using default settings\n";
                        // Default settings
                        $mail->Host = 'localhost';
                        $mail->Port = 25;
                        $mail->SMTPAuth = false;
                    }
                    
                    // Recipients
                    $mail->setFrom(ADMIN_EMAIL, 'TSPI System');
                    $mail->addAddress($test_recipient);
                    
                    // Content
                    $mail->isHTML(false);
                    $mail->Subject = $subject;
                    $mail->Body = $message;
                    
                    // Send and capture results
                    $mail_result = $mail->send();
                    $debug_output .= "PHPMailer result: Message sent successfully\n";
                    
                } catch (Exception $e) {
                    $mail_result = false;
                    $debug_output .= "PHPMailer exception: " . $e->getMessage() . "\n";
                    $debug_output .= "PHPMailer error info: " . (isset($mail) ? $mail->ErrorInfo : 'N/A') . "\n";
                }
                
                break;
                
            case 'custom':
                // Test custom email function if it exists
                $debug_output .= "Testing custom email function\n";
                
                if (file_exists(__DIR__ . '/email_config.php')) {
                    require_once __DIR__ . '/email_config.php';
                    $debug_output .= "Found email_config.php\n";
                    
                    if (function_exists('send_email')) {
                        $debug_output .= "Found send_email() function\n";
                        try {
                            $mail_result = send_email($test_recipient, $subject, $message, "From: " . ADMIN_EMAIL);
                            $debug_output .= "send_email() result: " . ($mail_result ? "SUCCESS" : "FAILURE") . "\n";
                        } catch (Exception $e) {
                            $mail_result = false;
                            $debug_output .= "send_email() exception: " . $e->getMessage() . "\n";
                        }
                    } else {
                        $debug_output .= "WARNING: send_email() function not found\n";
                        $mail_result = false;
                    }
                    
                    if (function_exists('dev_send_email')) {
                        $debug_output .= "Found dev_send_email() function\n";
                        try {
                            $mail_result = dev_send_email($test_recipient, $subject, $message, "From: " . ADMIN_EMAIL);
                            $debug_output .= "dev_send_email() result: " . ($mail_result ? "SUCCESS" : "FAILURE") . "\n";
                        } catch (Exception $e) {
                            $mail_result = false;
                            $debug_output .= "dev_send_email() exception: " . $e->getMessage() . "\n";
                        }
                    } else {
                        $debug_output .= "WARNING: dev_send_email() function not found\n";
                    }
                } else {
                    $debug_output .= "WARNING: email_config.php not found\n";
                    $mail_result = false;
                }
                break;
        }
        
        // Capture output buffer
        $debug_output .= ob_get_clean();
    }
}

// Check PHPMailer installation
$phpmailer_installed = class_exists('PHPMailer\PHPMailer\PHPMailer');
$phpmailer_version = $phpmailer_installed ? PHPMailer\PHPMailer\PHPMailer::VERSION : 'Not installed';

// Check PHP mail configuration
$php_mail_enabled = function_exists('mail') && !in_array('mail', explode(',', ini_get('disable_functions')));
$sendmail_path = ini_get('sendmail_path');

include 'includes/header.php';
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
        .admin-card {
            margin-bottom: 2rem;
            padding: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .admin-card-header {
            padding: 1.25rem 1.5rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .admin-card-header h2 {
            margin: 0;
            font-size: 1.4rem;
            color: #343a40;
        }
        
        .admin-card-body {
            padding: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 1rem;
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 112, 243, 0.1);
            outline: none;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
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
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .debug-output {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow: auto;
            font-size: 0.9rem;
            margin-top: 1.5rem;
        }
        
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        
        .status-unknown {
            color: #6c757d;
            font-weight: bold;
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
                    <h1>Email System Debug</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                
                <?php if (!empty($error_message)): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="grid-container">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2>Email Configuration Status</h2>
                        </div>
                        <div class="admin-card-body">
                            <ul>
                                <li>
                                    PHP mail() function: 
                                    <?php if ($php_mail_enabled): ?>
                                        <span class="status-ok">Enabled</span>
                                    <?php else: ?>
                                        <span class="status-error">Disabled</span>
                                    <?php endif; ?>
                                </li>
                                <li>Sendmail path: <?php echo !empty($sendmail_path) ? $sendmail_path : '<span class="status-error">Not configured</span>'; ?></li>
                                <li>
                                    PHPMailer: 
                                    <?php if ($phpmailer_installed): ?>
                                        <span class="status-ok">Installed</span> (v<?php echo $phpmailer_version; ?>)
                                    <?php else: ?>
                                        <span class="status-error">Not installed</span>
                                    <?php endif; ?>
                                </li>
                                <li>
                                    email_config.php: 
                                    <?php if (file_exists(__DIR__ . '/email_config.php')): ?>
                                        <span class="status-ok">Found</span>
                                    <?php else: ?>
                                        <span class="status-error">Not found</span>
                                    <?php endif; ?>
                                </li>
                                <li>
                                    Admin email: 
                                    <?php if (defined('ADMIN_EMAIL')): ?>
                                        <?php echo ADMIN_EMAIL; ?>
                                    <?php else: ?>
                                        <span class="status-error">Not defined</span>
                                    <?php endif; ?>
                                </li>
                                <li>
                                    Custom send_email() function: 
                                    <?php if (function_exists('send_email')): ?>
                                        <span class="status-ok">Found</span>
                                    <?php else: ?>
                                        <span class="status-error">Not found</span>
                                    <?php endif; ?>
                                </li>
                                <li>
                                    Development send_email() function: 
                                    <?php if (function_exists('dev_send_email')): ?>
                                        <span class="status-ok">Found</span>
                                    <?php else: ?>
                                        <span class="status-unknown">Not found</span> (This is only needed in development)
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2>Test Email Sending</h2>
                        </div>
                        <div class="admin-card-body">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="recipient">Test Recipient Email:</label>
                                    <input type="email" id="recipient" name="recipient" value="<?php echo htmlspecialchars($test_recipient); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="method">Email Method:</label>
                                    <select id="method" name="method">
                                        <option value="mail" <?php echo $test_method === 'mail' ? 'selected' : ''; ?>>PHP mail() Function</option>
                                        <option value="phpmailer" <?php echo $test_method === 'phpmailer' ? 'selected' : ''; ?> <?php echo !$phpmailer_installed ? 'disabled' : ''; ?>>PHPMailer Library</option>
                                        <option value="custom" <?php echo $test_method === 'custom' ? 'selected' : ''; ?>>Custom Email Function</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="test_email" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Test Email
                                </button>
                            </form>
                            
                            <?php if ($mail_result !== null): ?>
                                <div class="form-group" style="margin-top: 1.5rem;">
                                    <label>Test Result:</label>
                                    <div class="debug-output">
                                        <?php if ($mail_result): ?>
                                            <span class="status-ok">SUCCESS!</span> Test email was sent successfully.
                                        <?php else: ?>
                                            <span class="status-error">FAILED!</span> Test email could not be sent.
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($debug_output)): ?>
                                            <hr>
                                            <h4>Debug Output:</h4>
                                            <?php echo nl2br(htmlspecialchars($debug_output)); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 