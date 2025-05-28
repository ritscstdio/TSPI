<?php
$page_title = "Email Test";
require_once '../includes/config.php';
require_once './email_config.php';

// Only accessible in development mode
if ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1') {
    die("This test page is only available in development environments.");
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $test_email = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);
    
    if (!$test_email) {
        $error = "Please enter a valid email address";
    } else {
        try {
            // Send test email
            $subject = "TSPI Email Test";
            $message = "This is a test email from your TSPI application to verify that email sending is working correctly.\n\n";
            $message .= "If you received this email, your email configuration is working!\n\n";
            $message .= "Time sent: " . date('Y-m-d H:i:s') . "\n";
            $message .= "Regards,\nTSPI Team";
            
            // Debug info for logging
            $debug_info = [
                'to' => $test_email,
                'subject' => $subject,
                'time' => date('Y-m-d H:i:s')
            ];
            
            // Try to send the email
            $result = send_email($test_email, $subject, $message);
            
            // Log the result
            $debug_info['result'] = $result ? 'success' : 'failed';
            file_put_contents('email_log_' . time() . '.json', json_encode($debug_info, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
            file_put_contents('email_error_' . time() . '.txt', $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}

include '../includes/header.php';
?>

<main class="container">
    <div class="email-test-container">
        <h1>Email Configuration Tester</h1>
        <p>Use this tool to verify your SendGrid SMTP configuration is working correctly.</p>
        
        <?php if ($result === true): ?>
            <div class="message success">
                <p><strong>Success!</strong> Test email was sent to <?php echo htmlspecialchars($_POST['test_email']); ?>.</p>
                <p>Please check your inbox (and spam folder) to confirm delivery.</p>
            </div>
        <?php elseif ($result === false): ?>
            <div class="message error">
                <p><strong>Failed!</strong> Email could not be sent.</p>
                <p>Check your PHP error log and email_debug.txt for more details.</p>
                <p>You may also want to verify:</p>
                <ul>
                    <li>Your SendGrid API key is correct and active</li>
                    <li>The sender email address is registered with SendGrid</li>
                    <li>PHP has network access to smtp.sendgrid.net:587</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="test-form">
            <form method="post" action="">
                <div class="form-group">
                    <label for="test_email">Email Address to Test:</label>
                    <input type="email" id="test_email" name="test_email" 
                           value="<?php echo isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : ''; ?>" 
                           required placeholder="your@email.com">
                </div>
                <button type="submit" class="btn btn-primary">Send Test Email</button>
            </form>
        </div>
        
        <div class="config-info">
            <h2>Current Email Configuration</h2>
            <p>Your email system is currently configured to use:</p>
            <ul>
                <li><strong>SMTP Server:</strong> smtp.sendgrid.net</li>
                <li><strong>Port:</strong> 587</li>
                <li><strong>Encryption:</strong> STARTTLS</li>
                <li><strong>Username:</strong> apikey</li>
                <li><strong>Password:</strong> [HIDDEN]</li>
                <li><strong>From Address:</strong> no-reply@tspi.site</li>
                <li><strong>From Name:</strong> Ketano</li>
                <li><strong>Reply-To:</strong> reply@tspi.site</li>
            </ul>
            
            <h3>Email Log</h3>
            <p>Recent email attempts are logged in: <code>user/email_debug.txt</code></p>
            <?php 
            $log_file = __DIR__ . '/email_debug.txt';
            if (file_exists($log_file)): 
                $log_content = file_get_contents($log_file);
                $log_entries = array_filter(explode("------------------------------", $log_content));
                $log_entries = array_slice($log_entries, -3); // Last 3 entries
            ?>
                <div class="log-preview">
                    <h4>Last <?php echo count($log_entries); ?> Log Entries:</h4>
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
            </ol>
        </div>
    </div>
</main>

<style>
.email-test-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.email-test-container h1 {
    margin-top: 0;
    color: #333;
}

.test-form {
    margin: 2rem 0;
    padding: 1.5rem;
    background-color: #f8f9fa;
    border-radius: 4px;
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

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-primary {
    background-color: #0056b3;
    color: white;
}

.btn-primary:hover {
    background-color: #004494;
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
</style>

<?php include '../includes/footer.php'; ?> 