<?php
/**
 * Email Configuration
 * 
 * Contains functions to send emails using PHPMailer
 */

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Send an email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email message body (plain text)
 * @param string $html_message Optional HTML version of the message
 * @param array $attachments Optional array of file paths to attach
 * @return bool Whether the email was sent successfully
 */
function send_application_email($to, $subject, $message, $html_message = '', $attachments = []) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Configure email settings from config
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USERNAME;
        $mail->Password = EMAIL_PASSWORD;
        $mail->SMTPSecure = EMAIL_ENCRYPTION;
        $mail->Port = EMAIL_PORT;
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        
        // Add recipient
        $mail->addAddress($to);
        
        // Set email content
        $mail->isHTML(!empty($html_message));
        $mail->Subject = $subject;
        
        if (!empty($html_message)) {
            $mail->Body = $html_message;
            $mail->AltBody = $message; // Plain text alternative
        } else {
            $mail->Body = $message;
        }
        
        // Add attachments if any
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }
        
        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error
        error_log('Email sending failed: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Generate HTML email template for application status
 * 
 * @param array $application Application data
 * @param string $status Application status (approved/rejected)
 * @return string HTML email content
 */
function generate_application_email_html($application, $status) {
    $fullName = $application['first_name'] . ' ' . $application['last_name'];
    $date = date('F j, Y');
    
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>TSPI Membership Application Status</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .header img {
                max-width: 150px;
                height: auto;
            }
            .content {
                background-color: #f9f9f9;
                padding: 20px;
                border-radius: 5px;
            }
            .footer {
                margin-top: 30px;
                font-size: 12px;
                text-align: center;
                color: #777;
            }
            .approval {
                color: #28a745;
                font-weight: bold;
            }
            .rejection {
                color: #dc3545;
                font-weight: bold;
            }
            .details {
                background-color: #fff;
                padding: 15px;
                border-left: 4px solid #0056b3;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>TSPI Membership Application</h2>
        </div>
        
        <div class="content">
            <p>Dear ' . $fullName . ',</p>';
    
    if ($status === 'approved') {
        $html .= '
            <p>We are pleased to inform you that your membership application with TSPI has been <span class="approval">APPROVED</span>.</p>
            
            <p>Your application has undergone thorough review by our Insurance Officer and Loan Officer, and we are happy to welcome you as a TSPI member.</p>
            
            <div class="details">
                <p><strong>Application ID:</strong> ' . $application['id'] . '</p>
                <p><strong>Approval Date:</strong> ' . $date . '</p>
                <p><strong>Branch:</strong> ' . $application['branch'] . '</p>
                <p><strong>CID No:</strong> ' . $application['cid_no'] . '</p>
            </div>
            
            <p>Please find attached to this email:</p>
            <ol>
                <li>Your official membership application form</li>
                <li>Membership certificate</li>
            </ol>
            
            <p>We recommend that you print these documents and keep them for your records.</p>
            
            <p>For any questions or assistance, please contact our branch at [Branch Contact Number] or reply to this email.</p>';
    } else {
        $html .= '
            <p>We regret to inform you that your membership application with TSPI has been <span class="rejection">REJECTED</span>.</p>
            
            <p>After careful consideration, our team has determined that your application does not meet our current requirements.</p>
            
            <div class="details">
                <p><strong>Application ID:</strong> ' . $application['id'] . '</p>
                <p><strong>Decision Date:</strong> ' . $date . '</p>
            </div>
            
            <p>If you would like to discuss this decision or reapply in the future, please contact our branch at [Branch Contact Number] or reply to this email.</p>';
    }
    
    $html .= '
            <p>Thank you for your interest in TSPI.</p>
            
            <p>Best Regards,<br>
            TSPI Team</p>
        </div>
        
        <div class="footer">
            <p>&copy; ' . date('Y') . ' TSPI. All rights reserved.</p>
            <p>This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Generate plain text email template for application status
 * 
 * @param array $application Application data
 * @param string $status Application status (approved/rejected)
 * @return string Plain text email content
 */
function generate_application_email_text($application, $status) {
    $fullName = $application['first_name'] . ' ' . $application['last_name'];
    $date = date('F j, Y');
    
    $text = "TSPI Membership Application\n\n";
    $text .= "Dear {$fullName},\n\n";
    
    if ($status === 'approved') {
        $text .= "We are pleased to inform you that your membership application with TSPI has been APPROVED.\n\n";
        $text .= "Your application has undergone thorough review by our Insurance Officer and Loan Officer, and we are happy to welcome you as a TSPI member.\n\n";
        
        $text .= "Application Details:\n";
        $text .= "- Application ID: {$application['id']}\n";
        $text .= "- Approval Date: {$date}\n";
        $text .= "- Branch: {$application['branch']}\n";
        $text .= "- CID No: {$application['cid_no']}\n\n";
        
        $text .= "Please find attached to this email:\n";
        $text .= "1. Your official membership application form\n";
        $text .= "2. Membership certificate\n\n";
        
        $text .= "We recommend that you print these documents and keep them for your records.\n\n";
        
        $text .= "For any questions or assistance, please contact our branch at [Branch Contact Number] or reply to this email.\n\n";
    } else {
        $text .= "We regret to inform you that your membership application with TSPI has been REJECTED.\n\n";
        $text .= "After careful consideration, our team has determined that your application does not meet our current requirements.\n\n";
        
        $text .= "Application Details:\n";
        $text .= "- Application ID: {$application['id']}\n";
        $text .= "- Decision Date: {$date}\n\n";
        
        $text .= "If you would like to discuss this decision or reapply in the future, please contact our branch at [Branch Contact Number] or reply to this email.\n\n";
    }
    
    $text .= "Thank you for your interest in TSPI.\n\n";
    $text .= "Best Regards,\nTSPI Team\n\n";
    $text .= "© " . date('Y') . " TSPI. All rights reserved.\n";
    $text .= "This is an automated message. Please do not reply directly to this email.";
    
    return $text;
} 