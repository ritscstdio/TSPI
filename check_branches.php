<?php
require_once 'includes/config.php';

$result = mysqli_query($conn, 'SHOW TABLES LIKE "branches"');
if (mysqli_num_rows($result) > 0) {
    echo "Branches table exists\n";
    
    // Check if it has data
    $data_result = mysqli_query($conn, 'SELECT COUNT(*) as count FROM branches');
    $row = mysqli_fetch_assoc($data_result);
    echo "Table has " . $row['count'] . " rows\n";
} else {
    echo "Branches table does NOT exist\n";
}
?> 