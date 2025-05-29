<?php
// Email configuration with environment variable support for Docker/Railway deployment

// SendGrid API key
$sendgrid_api_key = getenv('SENDGRID_API_KEY') ?: 'SG.your_sendgrid_api_key';

// Email addresses
$noreply_email = 'noreply@tspi.org';
$contact_email = 'contact@tspi.org';
$admin_email = 'admin@tspi.org';

// Email subjects
$verification_subject = 'TSPI Email Verification';
$contact_form_subject = 'New Contact Form Submission';
$membership_application_subject = 'New Membership Application';

// Email templates directory
$email_templates_dir = __DIR__ . '/templates/emails/';

// Function to send email using SendGrid
function send_email($to, $subject, $html_content, $from = null) {
    global $sendgrid_api_key, $noreply_email;
    
    if (empty($from)) {
        $from = $noreply_email;
    }
    
    $email = new \stdClass();
    $email->from = $from;
    $email->subject = $subject;
    $email->to = $to;
    $email->html = $html_content;
    
    // Use SendGrid API to send email
    $url = 'https://api.sendgrid.com/v3/mail/send';
    $headers = [
        'Authorization: Bearer ' . $sendgrid_api_key,
        'Content-Type: application/json'
    ];
    
    $data = [
        'personalizations' => [
            [
                'to' => [
                    ['email' => $to]
                ]
            ]
        ],
        'from' => [
            'email' => $from
        ],
        'subject' => $subject,
        'content' => [
            [
                'type' => 'text/html',
                'value' => $html_content
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    
    curl_close($ch);
    
    if ($err) {
        // Log error
        error_log("Email sending failed: " . $err);
        return false;
    }
    
    return true;
}
?> 