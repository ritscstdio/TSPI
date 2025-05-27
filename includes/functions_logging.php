<?php
/**
 * Logging functions for TSPI CMS
 */

/**
 * Log a message to a specified log file
 * 
 * @param string $message The message to log
 * @param string $type The type of log message (info, error, debug)
 * @param string $logfile The name of the log file (without path or extension)
 * @return bool True if log was written successfully, false otherwise
 */
function log_message($message, $type = 'info', $logfile = 'system') {
    $log_dir = dirname(__DIR__) . '/logs';
    
    // Create logs directory if it doesn't exist
    if (!is_dir($log_dir) && !mkdir($log_dir, 0755, true)) {
        return false;
    }
    
    $log_path = $log_dir . '/' . $logfile . '.log';
    
    // Format the log message
    $formatted_message = date('Y-m-d H:i:s') . ' [' . strtoupper($type) . '] ' . $message . PHP_EOL;
    
    // Write to the log file
    return file_put_contents($log_path, $formatted_message, FILE_APPEND) !== false;
}

/**
 * Log approval activity
 * 
 * @param int $application_id The ID of the application
 * @param string $user_role The role of the user performing the action
 * @param string $action The action being performed (approve, reject)
 * @param array $data Any relevant data to include in the log
 * @return bool True if log was written successfully, false otherwise
 */
function log_approval_activity($application_id, $user_role, $action, $data = []) {
    $message = "Application ID: $application_id | Role: $user_role | Action: $action";
    
    if (!empty($data)) {
        $message .= " | Data: " . json_encode($data);
    }
    
    return log_message($message, 'info', 'approval_activity');
} 