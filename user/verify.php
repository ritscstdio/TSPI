<?php
$page_title = "Verify Email";
$body_class = "verify-page";
require_once '../includes/config.php';

$verification_code = $_GET['code'] ?? '';
$verified = false;
$expired = false;
$error = false;

if (empty($verification_code)) {
    $error = "Verification code is missing";
} else {
    try {
        // Check if verification code exists and is valid
        $stmt = $pdo->prepare("
            SELECT v.*, u.username, u.email 
            FROM email_verifications v
            JOIN users u ON v.user_id = u.id
            WHERE v.verification_code = ?
        ");
        $stmt->execute([$verification_code]);
        $verification = $stmt->fetch();
        
        if (!$verification) {
            $error = "Invalid verification code";
        } else {
            // Check if code has expired
            $expires_at = strtotime($verification['expires_at']);
            if (time() > $expires_at) {
                $expired = true;
                $error = "Verification code has expired";
                
                // Get the user email
                $stmt = $pdo->prepare("
                    SELECT u.email, u.username, u.id 
                    FROM email_verifications v
                    JOIN users u ON v.user_id = u.id
                    WHERE v.verification_code = ?
                ");
                $stmt->execute([$verification_code]);
                $user = $stmt->fetch();
            } else {
                // Verify the account
                $pdo->beginTransaction();
                
                // If this is a "change email" verification, swap in the new email; otherwise just activate
                if (!empty($verification['new_email'])) {
                    $stmt = $pdo->prepare("
                      UPDATE users 
                      SET email = ?, status = 'active' 
                      WHERE id = ?
                    ");
                    $stmt->execute([
                      $verification['new_email'],
                      $verification['user_id']
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                      UPDATE users 
                      SET status = 'active' 
                      WHERE id = ?
                    ");
                    $stmt->execute([$verification['user_id']]);
                }
                
                // Delete the verification code
                $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE verification_code = ?");
                $stmt->execute([$verification_code]);
                
                $pdo->commit();
                $verified = true;
                
                // Set username and email for displaying on page
                $username = $verification['username'];
                $email = $verification['email'];
            }
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "An error occurred during verification: " . $e->getMessage();
    }
}

// Handle resending verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend']) && $expired) {
    try {
        // Generate new verification code
        $new_verification_code = bin2hex(random_bytes(16));
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Update verification code
        $stmt = $pdo->prepare("
            UPDATE email_verifications 
            SET verification_code = ?, expires_at = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$new_verification_code, $expires_at, $user['id']]);
        
        // Send new verification email
        $verify_url = SITE_URL . "/user/verify.php?code=" . $new_verification_code;
        $to = $user['email'];
        $subject = "Verify your email address";
        $message = "Hello " . $user['username'] . ",\n\n";
        $message .= "A new verification link has been requested. Please verify your email address by clicking the link below:\n\n";
        $message .= $verify_url . "\n\n";
        $message .= "This link will expire in 24 hours.\n\n";
        $message .= "Regards,\nTSPI Team";
        $headers = "From: " . ADMIN_EMAIL;
        
        // Send email via configured mailer
        require_once __DIR__ . '/email_config.php';
        $email_sent = send_email($to, $subject, $message, $headers);
        
        $resent = true;
        $resend_success = $email_sent;
    } catch (PDOException $e) {
        $resend_error = "Failed to resend verification email: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<main class="container verify-container">
    <div class="auth-box">
        <?php if ($verified): ?>
            <div class="message success">
                <h1><i class="fas fa-check-circle"></i> Email Verified</h1>
                <p>Thank you, <?php echo sanitize($username); ?>! Your email address (<?php echo sanitize($email); ?>) has been verified.</p>
                <p>Your account is now active. You can now <a href="<?php echo SITE_URL; ?>/user/login.php">login</a> to your account.</p>
            </div>
        <?php elseif ($expired && isset($resent)): ?>
            <div class="message info">
                <h1><i class="fas fa-envelope"></i> <?php echo $resend_success ? "Verification Email Sent" : "Verification Email Issue"; ?></h1>
                <p><?php echo $resend_success ? "A new verification link has been sent to " . sanitize($user['email']) : "We encountered an issue sending to " . sanitize($user['email']); ?>.</p>
                <p><?php echo $resend_success ? "Please check your email and click on the verification link to complete the registration process." : "Please try again later or use the verification link below."; ?></p>
            </div>
            
            <?php if (!$resend_success && isset($user) && isset($new_verification_code)): ?>
                <div class="dev-verification-link" style="border-color: #dc3545;">
                    <p><strong>Email Delivery Failed:</strong> Please use this link to verify your account.</p>
                    <p>Use this link to verify your account:</p>
                    <a href="<?php echo SITE_URL . "/user/verify.php?code=" . $new_verification_code; ?>" target="_blank">
                        <?php echo SITE_URL . "/user/verify.php?code=" . $new_verification_code; ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php elseif ($expired): ?>
            <div class="message warning">
                <h1><i class="fas fa-exclamation-triangle"></i> Verification Link Expired</h1>
                <p>The verification link has expired.</p>
                <p>Would you like to receive a new verification link?</p>
                <form method="post" action="">
                    <input type="hidden" name="resend" value="1">
                    <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                </form>
            </div>
            <?php if (isset($resend_error)): ?>
                <div class="message error">
                    <p><?php echo $resend_error; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($user) && isset($new_verification_code)): ?>
                <div class="dev-verification-link">
                    <p><strong>Development Testing Only:</strong> Email sending may not work in local environment.</p>
                    <p>Use this link to verify your account:</p>
                    <a href="<?php echo SITE_URL . "/user/verify.php?code=" . $new_verification_code; ?>" target="_blank">
                        <?php echo SITE_URL . "/user/verify.php?code=" . $new_verification_code; ?>
                    </a>
                </div>
                <style>
                    .dev-verification-link {
                        margin-top: 15px;
                        padding: 10px;
                        background-color: #f8f9fa;
                        border: 1px dashed #ccc;
                        border-radius: 4px;
                    }
                    .dev-verification-link p:last-child {
                        margin-bottom: 5px;
                    }
                </style>
            <?php endif; ?>
        <?php else: ?>
            <div class="message error">
                <h1><i class="fas fa-times-circle"></i> Verification Failed</h1>
                <p><?php echo $error; ?></p>
                <p>If you're having trouble with the verification process, please <a href="<?php echo SITE_URL; ?>/contact.php">contact support</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.verify-container {
    max-width: 600px;
    margin: 2rem auto;
    padding: 0 1rem;
    padding-top: 70px;
}

.auth-box {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.auth-box h1 {
    margin-top: 0;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    font-size: 1.5rem;
}

.auth-box h1 i {
    margin-right: 0.5rem;
    font-size: 1.8rem;
}

.message {
    padding: 1.5rem;
    border-radius: 4px;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
}

.message.success h1 {
    color: #155724;
}

.message.success i {
    color: #28a745;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
}

.message.error h1 {
    color: #721c24;
}

.message.error i {
    color: #dc3545;
}

.message.warning {
    background-color: #fff3cd;
    color: #856404;
}

.message.warning h1 {
    color: #856404;
}

.message.warning i {
    color: #ffc107;
}

.message.info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.message.info h1 {
    color: #0c5460;
}

.message.info i {
    color: #17a2b8;
}

.message p {
    margin: 0.5rem 0;
}

.message p:last-child {
    margin-bottom: 0;
}

.message a {
    color: inherit;
    text-decoration: underline;
    font-weight: 600;
}

.message form {
    margin-top: 1rem;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    font-size: 0.9rem;
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
</style>

<?php
include '../includes/footer.php';
?> 