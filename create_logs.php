<?php
$logsDir = __DIR__ . '/logs';

if (!is_dir($logsDir)) {
    if (mkdir($logsDir, 0755, true)) {
        echo "Logs directory created successfully at: $logsDir";
    } else {
        echo "Failed to create logs directory at: $logsDir";
    }
} else {
    echo "Logs directory already exists at: $logsDir";
}
?> 