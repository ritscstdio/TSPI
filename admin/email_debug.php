<?php
/**
 * Email Debug Viewer
 * 
 * This file displays detailed information about email attempts
 */

require_once '../includes/config.php';
require_admin_login();

// Ensure user has permission to access this page (admin or secretary)
$current_user = get_admin_user();
if (!in_array($current_user['role'], ['admin', 'secretary'])) {
    $_SESSION['message'] = "You don't have permission to access this page.";
    redirect('/admin/index.php');
    exit;
}

// Get application ID from URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    $_SESSION['message'] = "No application ID specified.";
    redirect('/admin/applications.php');
    exit;
}

// Load email log files for this application
$log_files = glob("../logs/email_*{$id}*.{txt,json}", GLOB_BRACE);
$status_file = "../logs/email_status_{$id}.json";
$exception_file = "../logs/email_exception_{$id}.txt";

// Check if status file exists
$status_data = file_exists($status_file) ? json_decode(file_get_contents($status_file), true) : null;

// Page title
$page_title = "Email Debug for Application #{$id}";
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
        .debug-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .data-item {
            margin-bottom: 10px;
        }
        
        .data-item strong {
            display: inline-block;
            min-width: 150px;
            margin-right: 10px;
        }
        
        pre.code-block {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
            font-family: monospace;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
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
                    <h1>Email Debug for Application #<?php echo $id; ?></h1>
                    <div>
                        <a href="test_email.php?id=<?php echo $id; ?>" class="btn">
                            <i class="fas fa-paper-plane"></i> Test Email
                        </a>
                        <a href="view_application.php?id=<?php echo $id; ?>" class="btn">
                            <i class="fas fa-arrow-left"></i> Back to Application
                        </a>
                    </div>
                </div>
                
                <!-- Email Status -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Email Status</h2>
                    </div>
                    <div class="admin-card-body">
                        <?php if ($status_data): ?>
                        <div class="debug-section">
                            <div class="data-item">
                                <strong>Status:</strong>
                                <span class="status-badge <?php echo $status_data['mail_sent'] === 'yes' ? 'status-success' : 'status-error'; ?>">
                                    <?php echo $status_data['mail_sent'] === 'yes' ? 'Sent Successfully' : 'Failed'; ?>
                                </span>
                            </div>
                            
                            <div class="data-item">
                                <strong>Recipient:</strong>
                                <?php echo htmlspecialchars($status_data['recipient'] ?? 'N/A'); ?>
                            </div>
                            
                            <div class="data-item">
                                <strong>Subject:</strong>
                                <?php echo htmlspecialchars($status_data['subject'] ?? 'N/A'); ?>
                            </div>
                            
                            <div class="data-item">
                                <strong>Sent at:</strong>
                                <?php echo htmlspecialchars($status_data['time'] ?? 'N/A'); ?>
                            </div>
                            
                            <?php if (isset($status_data['error']) && !empty($status_data['error'])): ?>
                            <div class="data-item">
                                <strong>Error:</strong>
                                <pre class="code-block"><?php print_r($status_data['error']); ?></pre>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <p>No email status information available for this application.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Exception Details -->
                <?php if (file_exists($exception_file)): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Exception Details</h2>
                    </div>
                    <div class="admin-card-body">
                        <pre class="code-block"><?php echo htmlspecialchars(file_get_contents($exception_file)); ?></pre>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Email Content -->
                <?php if (!empty($log_files)): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Email Content Logs</h2>
                    </div>
                    <div class="admin-card-body">
                        <?php foreach ($log_files as $index => $log_file): ?>
                        <div class="debug-section">
                            <h3>Log #<?php echo $index + 1; ?> (<?php echo date('Y-m-d H:i:s', filemtime($log_file)); ?>)</h3>
                            <pre class="code-block"><?php echo htmlspecialchars(file_get_contents($log_file)); ?></pre>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- PHP Mail Configuration -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>PHP Mail Configuration</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="debug-section">
                            <div class="data-item">
                                <strong>PHP mail() Function:</strong>
                                <?php echo function_exists('mail') ? 'Enabled' : 'Disabled'; ?>
                            </div>
                            
                            <div class="data-item">
                                <strong>SMTP Host:</strong>
                                <?php echo ini_get('SMTP') ?: 'Not configured'; ?>
                            </div>
                            
                            <div class="data-item">
                                <strong>SMTP Port:</strong>
                                <?php echo ini_get('smtp_port') ?: 'Not configured'; ?>
                            </div>
                            
                            <div class="data-item">
                                <strong>Sendmail Path:</strong>
                                <?php echo ini_get('sendmail_path') ?: 'Not configured'; ?>
                            </div>
                            
                            <div class="data-item">
                                <strong>PHP Version:</strong>
                                <?php echo phpversion(); ?>
                            </div>
                        </div>
                        
                        <h3 style="margin-top: 20px;">Troubleshooting Steps</h3>
                        <ul style="margin-left: 20px; line-height: 1.6;">
                            <li>Check that the SMTP server is properly configured in php.ini</li>
                            <li>Verify that the email recipient address is valid</li>
                            <li>Ensure your server has outbound email access (not blocked by firewall)</li>
                            <li>Try using an alternative SMTP service if PHP mail() is not working</li>
                            <li>Consider implementing a proper SMTP library like PHPMailer or Swift Mailer</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 