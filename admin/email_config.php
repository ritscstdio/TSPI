<?php
/**
 * Email Configuration
 * 
 * This file contains the email configuration settings for the TSPI CMS.
 */

// Email settings
$email_config = [
    'from_email' => 'noreply@tspi.org',
    'from_name' => 'TSPI Membership',
    'reply_to' => 'support@tspi.org',
    'smtp_host' => '', // Leave blank to use PHP's mail() function
    'smtp_port' => 587,
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_secure' => 'tls'
];

/**
 * Helper function to generate HTML email content for applications
 * 
 * @param array $application Application data
 * @param string $status Status of the application (approved, rejected)
 * @return string HTML content
 */
function generate_application_email_html($application, $status) {
    $html = '<html>
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
            <h1>TSPI Membership ' . ucfirst($status) . '</h1>
            <p>Dear ' . htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) . ',</p>';
            
    if ($status === 'approved') {
        $html .= '<p>We are pleased to inform you that your TSPI membership application has been approved.</p>
        <p>Your membership ID is: <strong>' . htmlspecialchars($application['cid_no']) . '</strong></p>
        <p>Please find attached your official application form and membership certificate(s).</p>
        <p>If you have any questions about your membership, please contact your assigned branch.</p>';
    } else {
        $html .= '<p>We regret to inform you that your TSPI membership application has been rejected.</p>
        <p>If you have any questions regarding this decision, please contact us for more information.</p>';
    }
    
    $html .= '<div class="footer">
                <p>Thank you for choosing TSPI.</p>
                <p>&copy; TSPI Membership Services</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Helper function to generate plain text email content for applications
 * 
 * @param array $application Application data
 * @param string $status Status of the application (approved, rejected)
 * @return string Plain text content
 */
function generate_application_email_text($application, $status) {
    $text = "Dear {$application['first_name']} {$application['last_name']},\n\n";
    
    if ($status === 'approved') {
        $text .= "We are pleased to inform you that your TSPI membership application has been approved.\n\n";
        $text .= "Your membership ID is: {$application['cid_no']}\n\n";
        $text .= "Please find attached your official application form and membership certificate(s).\n\n";
        $text .= "If you have any questions about your membership, please contact your assigned branch.\n\n";
    } else {
        $text .= "We regret to inform you that your TSPI membership application has been rejected.\n\n";
        $text .= "If you have any questions regarding this decision, please contact us for more information.\n\n";
    }
    
    $text .= "Thank you for choosing TSPI.\n\n";
    $text .= "TSPI Membership Services";
    
    return $text;
}

/**
 * Function to send email with attachments
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $text_message Plain text message
 * @param string $html_message HTML message
 * @param array $attachments Array of attachment file paths
 * @return boolean True if email sent successfully, false otherwise
 */
function send_application_email($to, $subject, $text_message, $html_message, $attachments = []) {
    global $email_config;
    
    // Check if PHPMailer is available
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                // Server settings
                if (!empty($email_config['smtp_host'])) {
                    $mail->isSMTP();
                    $mail->Host = $email_config['smtp_host'];
                    $mail->Port = $email_config['smtp_port'];
                    
                    if (!empty($email_config['smtp_username']) && !empty($email_config['smtp_password'])) {
                        $mail->SMTPAuth = true;
                        $mail->Username = $email_config['smtp_username'];
                        $mail->Password = $email_config['smtp_password'];
                    }
                    
                    if (!empty($email_config['smtp_secure'])) {
                        $mail->SMTPSecure = $email_config['smtp_secure'];
                    }
                }
                
                // Recipients
                $mail->setFrom($email_config['from_email'], $email_config['from_name']);
                $mail->addAddress($to);
                $mail->addReplyTo($email_config['reply_to']);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $html_message;
                $mail->AltBody = $text_message;
                
                // Attachments
                foreach ($attachments as $file) {
                    if (file_exists($file) && is_readable($file)) {
                        $mail->addAttachment($file);
                    }
                }
                
                // Send the email
                return $mail->send();
            } catch (Exception $e) {
                // Log the error
                error_log("PHPMailer Error: " . $e->getMessage());
                
                // Fall back to standard mail
                return fallback_mail_send($to, $subject, $text_message, $html_message, $attachments);
            }
        } else {
            // PHPMailer class not found, fall back to standard mail
            return fallback_mail_send($to, $subject, $text_message, $html_message, $attachments);
        }
    } else {
        // Vendor autoload not found, fall back to standard mail
        return fallback_mail_send($to, $subject, $text_message, $html_message, $attachments);
    }
}

/**
 * Fallback function for sending emails using PHP's mail() function
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $text_message Plain text message
 * @param string $html_message HTML message
 * @param array $attachments Array of attachment file paths
 * @return boolean True if email sent successfully, false otherwise
 */
function fallback_mail_send($to, $subject, $text_message, $html_message, $attachments = []) {
    global $email_config;
    
    // Create email headers
    $headers = "From: {$email_config['from_name']} <{$email_config['from_email']}>\r\n";
    $headers .= "Reply-To: {$email_config['reply_to']}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $boundary = md5(time());
    $headers .= "Content-Type: multipart/mixed; boundary=\"".$boundary."\"\r\n";
    
    // Create email body
    $message = "--".$boundary."\r\n";
    $message .= "Content-Type: multipart/alternative; boundary=\"alt-".$boundary."\"\r\n\r\n";
    
    // Plain text version
    $message .= "--alt-".$boundary."\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $text_message."\r\n\r\n";
    
    // HTML version
    $message .= "--alt-".$boundary."\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $html_message."\r\n\r\n";
    $message .= "--alt-".$boundary."--\r\n\r\n";
    
    // Attach files
    foreach ($attachments as $file) {
        if (file_exists($file) && is_readable($file)) {
            $attachment = file_get_contents($file);
            $attachment = chunk_split(base64_encode($attachment));
            $filename = basename($file);
            
            $message .= "--".$boundary."\r\n";
            $message .= "Content-Type: application/pdf; name=\"".$filename."\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
            $message .= $attachment."\r\n\r\n";
        }
    }
    
    $message .= "--".$boundary."--";
    
    // Send the email
    return mail($to, $subject, $message, $headers);
} 