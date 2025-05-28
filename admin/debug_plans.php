<?php
require_once '../includes/config.php';
require_admin_login();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get application ID
$id = 34; // Hardcoded for debugging

// Fetch application
$stmt = $pdo->prepare("SELECT id, plans FROM members_information WHERE id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h2>Application #{$id} Plans Data:</h2>";
echo "<p>Raw plans value: " . htmlspecialchars($application['plans']) . "</p>";

// Decode plans JSON
$plans = json_decode($application['plans'], true);
echo "<p>json_decode result: </p>";
echo "<pre>";
print_r($plans);
echo "</pre>";

// Test loop
echo "<h3>Testing plans loop:</h3>";
foreach ($plans as $plan) {
    echo "Plan: {$plan}<br>";
}
?> 