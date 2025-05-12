<?php
require_once '../../includes/config.php'; // Adjust path as needed
require_login(); // Ensure user is logged in

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unexpected error occurred.'];
$current_user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_new_password'] ?? '';

    // Validate basic inputs
    if (empty($name) || empty($email)) {
        $response['message'] = 'Name and Email are required.';
        echo json_encode($response);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    // Fetch current user details to check for changes and verify password
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$current_user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $response['message'] = 'User not found.'; // Should not happen if logged in
        echo json_encode($response);
        exit;
    }

    $update_fields = ['name' => $name];
    $params = [$name];
    $requires_current_password = false;

    // Check if email is being changed
    if ($email !== $user['email']) {
        // Check if new email is already taken by another user
        $stmt_check_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check_email->execute([$email, $current_user_id]);
        if ($stmt_check_email->fetch()) {
            $response['message'] = 'This email address is already in use by another account.';
            echo json_encode($response);
            exit;
        }
        $update_fields['email'] = $email;
        $params[] = $email;
        $requires_current_password = true;
    }

    // Check if password is being changed
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) { // Basic length validation
            $response['message'] = 'New password must be at least 6 characters long.';
            echo json_encode($response);
            exit;
        }
        if ($new_password !== $confirm_password) {
            $response['message'] = 'New passwords do not match.';
            echo json_encode($response);
            exit;
        }
        $update_fields['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        $params[] = $update_fields['password'];
        $requires_current_password = true;
    }

    // If email or password is changed, current password must be verified
    if ($requires_current_password) {
        if (empty($current_password)) {
            $response['message'] = 'Current password is required to change email or password.';
            echo json_encode($response);
            exit;
        }
        if (!password_verify($current_password, $user['password'])) {
            $response['message'] = 'Incorrect current password.';
            echo json_encode($response);
            exit;
        }
    }

    if (count($update_fields) > 0) {
        $sql_set_parts = [];
        foreach (array_keys($update_fields) as $field) {
            $sql_set_parts[] = "`{$field}` = ?";
        }
        $sql = "UPDATE users SET " . implode(", ", $sql_set_parts) . " WHERE id = ?";
        $params[] = $current_user_id;

        try {
            $stmt_update = $pdo->prepare($sql);
            if ($stmt_update->execute($params)) {
                $response['success'] = true;
                $response['message'] = 'Profile updated successfully!';
                // Update session if name changed
                if (isset($update_fields['name'])) {
                    $_SESSION['user_name'] = $name; // Assuming session stores user_name
                     $response['new_name'] = $name;
                }
            } else {
                $response['message'] = 'Failed to update profile.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        // This case should ideally not be hit if name is always part of update_fields
        // Or, if only name changed and it was the same as before.
        $response['success'] = true; // No actual DB change but no error.
        $response['message'] = 'No changes detected.';
    }

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response); 