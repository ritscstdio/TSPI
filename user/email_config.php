<?php
/**
 * Email Configuration
 * 
 * This file contains functionality for sending emails using SendGrid SMTP service
 * via PHPMailer.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get SendGrid API key from environment variables
$sendgrid_api_key = getenv('SENDGRID_API_KEY') ?: $_ENV['SENDGRID_API_KEY'] ?? null;

// If SENDGRID_API_KEY is not set in environment, try to include the secrets file
// This allows for development environment to still use the secrets file if available
if (!$sendgrid_api_key) {
    $secrets_file = __DIR__ . '/email_secrets.php';
    if (file_exists($secrets_file)) {
        require_once $secrets_file;
        $sendgrid_api_key = defined('SENDGRID_API_KEY') ? SENDGRID_API_KEY : null;
    }
}

// Exit with error if API key is still not available
if (!$sendgrid_api_key) {
    error_log('SendGrid API key not found in environment or secrets file');
}

// Configuration for email sending
$email_config = [
    'sendgrid_api_key' => $sendgrid_api_key,
    'from_email' => 'no-reply@tspi.site',
    'from_name' => 'TSPI (Ketano)',
    'reply_to' => 'reply@tspi.site'
];

/**
 * Send email using SendGrid API with click tracking disabled
 */
function send_sendgrid_email($to, $subject, $text_message, $html_message, $attachments = []) {
    global $email_config;
    
    // Create a debug log file if it doesn't exist
    $debug_log = __DIR__ . '/email_debug.txt';
    if (!file_exists($debug_log)) {
        file_put_contents($debug_log, "Email Debug Log Created: " . date('Y-m-d H:i:s') . "\n\n");
    }
    
    // Log attempt
    file_put_contents($debug_log, "------------------------------\n", FILE_APPEND);
    file_put_contents($debug_log, "Sending email via SendGrid API at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    file_put_contents($debug_log, "To: $to\n", FILE_APPEND);
    file_put_contents($debug_log, "Subject: $subject\n", FILE_APPEND);
    
    $data = [
        'personalizations' => [
            [
                'to' => [
                    [
                        'email' => $to
                    ]
                ],
                'subject' => $subject
            ]
        ],
        'from' => [
            'email' => $email_config['from_email'],
            'name' => $email_config['from_name']
        ],
        'reply_to' => [
            'email' => $email_config['reply_to']
        ],
        'content' => [
            [
                'type' => 'text/plain',
                'value' => $text_message
            ],
            [
                'type' => 'text/html',
                'value' => $html_message
            ]
        ],
        // DISABLE CLICK TRACKING - This is the key addition
        'tracking_settings' => [
            'click_tracking' => [
                'enable' => false,
                'enable_text' => false
            ],
            'open_tracking' => [
                'enable' => false
            ],
            'subscription_tracking' => [
                'enable' => false
            ]
        ]
    ];
    
    // Add attachments if any
    if (!empty($attachments)) {
        $data['attachments'] = [];
        foreach ($attachments as $file) {
            if (file_exists($file) && is_readable($file)) {
                $content = base64_encode(file_get_contents($file));
                $data['attachments'][] = [
                    'content' => $content,
                    'type' => 'application/pdf',
                    'filename' => basename($file),
                    'disposition' => 'attachment'
                ];
            }
        }
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.sendgrid.com/v3/mail/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $email_config['sendgrid_api_key'],
            'Content-Type: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Log for debugging
    if ($httpCode < 200 || $httpCode >= 300) {
        file_put_contents($debug_log, "SendGrid Error - HTTP Code: $httpCode, Response: $response, cURL Error: $error\n", FILE_APPEND);
    } else {
        file_put_contents($debug_log, "RESULT: Email sent successfully (HTTP Code: $httpCode)\n", FILE_APPEND);
    }
    
    return $httpCode >= 200 && $httpCode < 300;
}

// Use PHPMailer to send emails via SendGrid
function send_email($to, $subject, $message, $headers = '') {
    // Check if message is already HTML
    $isHTML = (strpos($message, '<html') !== false);
    
    if ($isHTML) {
        $html_message = $message;
        // Create plain text version by stripping HTML
        $text_message = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $message));
    } else {
        // Convert plain text to HTML and keep the plain text as text_message
        $text_message = $message;
        $html_message = nl2br(htmlspecialchars($message));
    }
    
    // Use the new SendGrid API method instead of PHPMailer
    return send_sendgrid_email($to, $subject, $text_message, $html_message);
}

/**
 * Development bypass - logs the email and also attempts to send it
 * for testing purposes in development environments
 */
function dev_send_email($to, $subject, $message, $headers = '') {
    // Check if message is already HTML
    $isHTML = (strpos($message, '<html') !== false);
    
    if ($isHTML) {
        $html_message = $message;
        // Create plain text version by stripping HTML
        $text_message = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $message));
    } else {
        // Convert plain text to HTML and keep the plain text as text_message
        $text_message = $message;
        $html_message = nl2br(htmlspecialchars($message));
    }
    
    // Also try sending via SendGrid API in development for testing
    $api_result = send_sendgrid_email($to, $subject, $text_message, $html_message);
    
    // Log the email for development purposes
    $log_file = __DIR__ . '/email_log.txt';
    $log_content = "Time: " . date('Y-m-d H:i:s') . "\n";
    $log_content .= "To: $to\nSubject: $subject\nHeaders: $headers\nMessage:\n$message\n";
    $log_content .= "SendGrid API Send Attempt: " . ($api_result ? "SUCCESS" : "FAILED") . "\n\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);
    
    // Return the actual result instead of always returning true
    return $api_result;
}

/**
 * Function to send email with attachments using SendGrid API
 * 
 * @param string $to Email recipient
 * @param string $subject Email subject
 * @param string $text_message Plain text message body
 * @param string $html_message HTML message body
 * @param array $attachments Array of attachment file paths
 * @return bool True if email was sent successfully, false otherwise
 */
function send_email_with_attachments($to, $subject, $text_message, $html_message = '', $attachments = []) {
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
    
    // Use the SendGrid API method
    $result = send_sendgrid_email($to, $subject, $text_message, $html_message, $attachments);
    
    // Clean up temporary PDF files if needed
    if ($result) {
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                @unlink($attachment);
                file_put_contents($debug_log, "Deleted attachment: $attachment\n", FILE_APPEND);
            }
        }
    }
    
    return $result;
} 