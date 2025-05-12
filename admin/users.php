<?php
$page_title = "Users";
$body_class = "admin-users-page";
require_once '../includes/config.php';
require_login();
require_role(['admin']);

// Delete user if requested
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    // Prevent deleting yourself
    if ($user_id === $_SESSION['user_id']) {
        $_SESSION['message'] = "You cannot delete your own account.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "User deleted successfully.";
    }
    redirect('/admin/users.php');
}

// Fetch all users
$stmt = $pdo->query("SELECT id, username, name, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
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
                    <h1>Users</h1>
                </div>
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                <div class="dashboard-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr><td colspan="6">No users found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo sanitize($user['username']); ?></td>
                                            <td><?php echo sanitize($user['name']); ?></td>
                                            <td><?php echo sanitize($user['email']); ?></td>
                                            <td><?php echo ucfirst(sanitize($user['role'])); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                            <td class="actions">
                                                <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn-icon delete-btn" title="Delete" data-confirm="Are you sure you want to delete this user?"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <a href="add-user.php" class="fab-add-button">
                <i class="fas fa-user-plus"></i> Add User
            </a>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html> 