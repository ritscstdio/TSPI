<?php
require_once 'includes/config.php';

echo "<h1>Testing Branch Dropdown</h1>";

// Fetch branches from the database
$branch_query = "SELECT id, branch FROM branches ORDER BY branch";
$branch_result = mysqli_query($conn, $branch_query);

if (!$branch_result) {
    echo "Query error: " . mysqli_error($conn);
} else if (mysqli_num_rows($branch_result) > 0) {
    echo "<p>Found " . mysqli_num_rows($branch_result) . " branches:</p>";
    echo "<select>";
    echo "<option value='' disabled selected>Select Branch</option>";
    
    while ($branch_row = mysqli_fetch_assoc($branch_result)) {
        echo '<option value="' . $branch_row['branch'] . '">' . $branch_row['branch'] . '</option>';
    }
    
    echo "</select>";
} else {
    echo "<p>No branches found in the database.</p>";
}
?> 