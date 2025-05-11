<?php
$page_title = "Edit User";
$body_class = "admin-edit-user-page";
require_once '../includes/config.php';
require_login();
require_role(['admin']);

// Available roles
$available_roles = ['admin' => 'Admin', 'editor' => 'Editor', 'comment_moderator' => 'Comment Moderator'];

// Get user ID
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$user_id) redirect('/admin/users.php');

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    $_SESSION['message'] = "User not found.";
    redirect('/admin/users.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? ''; // empty = no change
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $errors = [];

    if (!$username) $errors[] = "Username is required.";
    if (!$name) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (!array_key_exists($role, $available_roles)) $errors[] = "Please select a valid role.";

    // Check unique username/email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetchColumn() > 0) $errors[] = "Username already exists.";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetchColumn() > 0) $errors[] = "Email already exists.";

    if (empty($errors)) {
        // Update fields
        if ($password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $password_hash, $name, $email, $role, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $name, $email, $role, $user_id]);
        }
        $_SESSION['message'] = "User updated successfully.";
        redirect('/admin/users.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - TSPI CMS</title>
    <link rel="icon" type="image/png" href="../src/assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            <div class="dashboard-container">
                <div class="page-header">
                    <h1>Edit User</h1>
                    <a href="users.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Users</a>
                </div>
                <?php if (!empty($errors)): ?>
                    <div class="message error"><ul><?php foreach ($errors as $e): ?><li><?php echo $e; ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form action="" method="post" class="form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo sanitize($username ?? $user['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password (leave blank to keep unchanged)</label>
                        <input type="password" id="password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo sanitize($name ?? $user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo sanitize($email ?? $user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <?php foreach ($available_roles as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($role) ? $role : $user['role']) === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </form>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 