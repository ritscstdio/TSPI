<?php
/**
 * Email Configuration
 * 
 * This file contains functionality for sending emails using SendGrid SMTP service
 * via PHPMailer.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the secrets file (which is not committed to version control)
require_once __DIR__ . '/email_secrets.php';

// Use PHPMailer to send emails via SendGrid
function send_email($to, $subject, $message, $headers = '') {
    // Load Composer's autoloader
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Create a debug log file if it doesn't exist
    $debug_log = __DIR__ . '/email_debug.txt';
    if (!file_exists($debug_log)) {
        file_put_contents($debug_log, "Email Debug Log Created: " . date('Y-m-d H:i:s') . "\n\n");
    }
    
    // Log attempt
    file_put_contents($debug_log, "------------------------------\n", FILE_APPEND);
    file_put_contents($debug_log, "Sending email at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    file_put_contents($debug_log, "To: $to\n", FILE_APPEND);
    file_put_contents($debug_log, "Subject: $subject\n", FILE_APPEND);
    
    // Instantiate PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Server settings - enable debugging
        $mail->SMTPDebug = 3;                    // Verbose debugging: 3 = show all details
        $mail->Debugoutput = function($str, $level) use ($debug_log) {
            file_put_contents($debug_log, "DEBUG[$level]: $str\n", FILE_APPEND);
        };
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.sendgrid.net';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'apikey';
        $mail->Password   = SENDGRID_API_KEY;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients - use the verified sender
        $mail->setFrom('no-reply@tspi.site', 'Ketano');
        $mail->addReplyTo('reply@tspi.site', 'Ketano');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        $result = $mail->send();
        file_put_contents($debug_log, "RESULT: Email sent successfully\n", FILE_APPEND);
        return true;
    } catch (Exception $e) {
        file_put_contents($debug_log, "ERROR: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * Development bypass - logs the email and also attempts to send it
 * for testing purposes in development environments
 */
function dev_send_email($to, $subject, $message, $headers = '') {
    // Also try sending via SMTP in development for testing
    $smtp_result = send_email($to, $subject, $message, $headers);
    
    // Log the email for development purposes
    $log_file = __DIR__ . '/email_log.txt';
    $log_content = "Time: " . date('Y-m-d H:i:s') . "\n";
    $log_content .= "To: $to\nSubject: $subject\nHeaders: $headers\nMessage:\n$message\n";
    $log_content .= "SMTP Send Attempt: " . ($smtp_result ? "SUCCESS" : "FAILED") . "\n\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);
    
    // Return the actual result instead of always returning true
    return $smtp_result;
}

/**
 * Function to send email with attachments using PHPMailer and SendGrid
 * 
 * @param string $to Email recipient
 * @param string $subject Email subject
 * @param string $text_message Plain text message body
 * @param string $html_message HTML message body
 * @param array $attachments Array of attachment file paths
 * @return bool True if email was sent successfully, false otherwise
 */
function send_email_with_attachments($to, $subject, $text_message, $html_message = '', $attachments = []) {
    // Load Composer's autoloader
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Create a debug log file if it doesn't exist
    $debug_log = __DIR__ . '/email_debug.txt';
    if (!file_exists($debug_log)) {
        file_put_contents($debug_log, "Email Debug Log Created: " . date('Y-m-d H:i:s') . "\n\n");
    }
    
    // Log attempt
    file_put_contents($debug_log, "------------------------------\n", FILE_APPEND);
    file_put_contents($debug_log, "Sending email with attachments at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    file_put_contents($debug_log, "To: $to\n", FILE_APPEND);
    file_put_contents($debug_log, "Subject: $subject\n", FILE_APPEND);
    file_put_contents($debug_log, "Attachments: " . count($attachments) . "\n", FILE_APPEND);
    
    // Instantiate PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Server settings - enable debugging
        $mail->SMTPDebug = 3;                    // Verbose debugging: 3 = show all details
        $mail->Debugoutput = function($str, $level) use ($debug_log) {
            file_put_contents($debug_log, "DEBUG[$level]: $str\n", FILE_APPEND);
        };
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.sendgrid.net';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'apikey';
        $mail->Password   = SENDGRID_API_KEY;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients - use the verified sender
        $mail->setFrom('no-reply@tspi.site', 'Ketano');
        $mail->addReplyTo('reply@tspi.site', 'Ketano');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(!empty($html_message));
        $mail->Subject = $subject;
        
        if (!empty($html_message)) {
            $mail->Body = $html_message;
            $mail->AltBody = $text_message;
        } else {
            $mail->Body = $text_message;
        }
        
        // Attachments
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $mail->addAttachment($attachment);
                file_put_contents($debug_log, "Added attachment: $attachment\n", FILE_APPEND);
            } else {
                file_put_contents($debug_log, "Attachment not found: $attachment\n", FILE_APPEND);
            }
        }
        
        $result = $mail->send();
        file_put_contents($debug_log, "RESULT: Email sent successfully\n", FILE_APPEND);
        
        // Clean up temporary PDF files
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                @unlink($attachment);
                file_put_contents($debug_log, "Deleted attachment: $attachment\n", FILE_APPEND);
            }
        }
        
        return true;
    } catch (Exception $e) {
        file_put_contents($debug_log, "ERROR: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        return false;
    }
} 