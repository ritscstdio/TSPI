<?php
require_once 'includes/config.php';

// Check if branches table exists
$table_check = mysqli_query($conn, 'SHOW TABLES LIKE "branches"');
if (mysqli_num_rows($table_check) === 0) {
    // Create branches table
    $create_table_sql = "CREATE TABLE IF NOT EXISTS branches (
        id int(11) NOT NULL AUTO_INCREMENT,
        branch varchar(100) NOT NULL,
        region varchar(100) NOT NULL,
        address text NOT NULL,
        address_link text NOT NULL,
        contact_num varchar(100) NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if (mysqli_query($conn, $create_table_sql)) {
        echo "Branches table created successfully.\n";
    } else {
        echo "Error creating branches table: " . mysqli_error($conn) . "\n";
        exit;
    }
}

// Check if branches table has data
$data_check = mysqli_query($conn, 'SELECT COUNT(*) as count FROM branches');
$row = mysqli_fetch_assoc($data_check);
if ($row['count'] === 0) {
    echo "Branches table is empty. You should import the SQL data.\n";
} else {
    echo "Branches table already has " . $row['count'] . " rows of data.\n";
}

// Test if we can properly query branches
$branch_test = mysqli_query($conn, 'SELECT id, branch FROM branches LIMIT 5');
if ($branch_test && mysqli_num_rows($branch_test) > 0) {
    echo "Successfully retrieved branches from database:\n";
    while ($branch = mysqli_fetch_assoc($branch_test)) {
        echo "- " . $branch['id'] . ": " . $branch['branch'] . "\n";
    }
} else {
    echo "Error retrieving branches: " . mysqli_error($conn) . "\n";
}

echo "\nDone!";
?> 