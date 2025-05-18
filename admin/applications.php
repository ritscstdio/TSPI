<?php
$page_title = "Applications";
require_once '../includes/config.php';
// Only allow admins
if (!function_exists('is_admin') || !is_admin()) {
    redirect('/');
}

// Fetch all applications
$stmt = $pdo->query("SELECT * FROM members_information ORDER BY created_at DESC");
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<main class="container">
    <h1>Membership Applications</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applications as $app): ?>
            <tr>
                <td><?php echo $app['id']; ?></td>
                <td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                <td><?php echo htmlspecialchars($app['email']); ?></td>
                <td><?php echo $app['created_at']; ?></td>
                <td><?php echo ucfirst($app['status']); ?></td>
                <td>
                    <?php if ($app['status'] === 'pending'): ?>
                        <a href="verify_application.php?id=<?php echo $app['id']; ?>&action=approved" class="btn btn-success btn-sm">Approve</a>
                        <a href="verify_application.php?id=<?php echo $app['id']; ?>&action=rejected" class="btn btn-danger btn-sm">Reject</a>
                    <?php else: ?>
                        <em>No actions</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
<?php include '../includes/footer.php'; ?> 