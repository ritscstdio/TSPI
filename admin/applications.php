<?php
$page_title = "Membership Applications";
$body_class = "admin-applications-page";
require_once '../includes/config.php';
require_admin_login();

// Fetch all applications
$stmt = $pdo->query("SELECT * FROM members_information ORDER BY created_at DESC");
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current user role
$current_user = get_admin_user();
$user_role = $current_user['role'] ?? '';
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
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-pending { 
            background-color: #ffe58f; 
            color: #856404;
        }
        .status-approved { 
            background-color: #b7eb8f; 
            color: #52c41a;
        }
        .status-rejected { 
            background-color: #ffccc7; 
            color: #f5222d; 
        }
        .approval-badges {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .badge-io, .badge-lo, .badge-secretary {
            font-size: 11px;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .badge-io {
            background-color: #e6f7ff;
            color: #1890ff;
        }
        .badge-lo {
            background-color: #f9f0ff;
            color: #722ed1;
        }
        .badge-secretary {
            background-color: #f6ffed;
            color: #52c41a;
        }
    </style>
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="content-container dashboard-container">
                <div class="page-header">
                    <h1>Membership Applications</h1>
                </div>
                
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="dashboard-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Branch</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Approvals</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($applications)): ?>
                                    <tr>
                                        <td colspan="8">No applications found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?php echo $app['id']; ?></td>
                                        <td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($app['email']); ?></td>
                                        <td><?php echo !empty($app['branch']) ? htmlspecialchars($app['branch']) : 'Not Assigned'; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($app['created_at'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                                <?php echo ucfirst($app['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="approval-badges">
                                                <span class="badge-io status-<?php echo strtolower($app['io_approved']); ?>">
                                                    IO: <?php echo ucfirst($app['io_approved']); ?>
                                                    <?php if ($app['io_approved'] === 'approved'): ?>
                                                        by <?php echo htmlspecialchars($app['io_name']); ?>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="badge-lo status-<?php echo strtolower($app['lo_approved']); ?>">
                                                    LO: <?php echo ucfirst($app['lo_approved']); ?>
                                                    <?php if ($app['lo_approved'] === 'approved'): ?>
                                                        by <?php echo htmlspecialchars($app['lo_name']); ?>
                                                    <?php endif; ?>
                                                <?php if ($app['io_approved'] === 'approved' && $app['lo_approved'] === 'approved'): ?>
                                                <span class="badge-secretary status-<?php echo strtolower($app['secretary_approved'] ?: 'pending'); ?>">
                                                    Secretary: <?php echo ucfirst($app['secretary_approved'] ?: 'Pending'); ?>
                                                    <?php if ($app['secretary_approved'] === 'approved'): ?>
                                                        by <?php echo htmlspecialchars($app['secretary_name']); ?>
                                                    <?php endif; ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="actions">
                                            <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                            <?php if (($user_role === 'insurance_officer' && $app['io_approved'] === 'pending') || 
                                                      ($user_role === 'loan_officer' && $app['lo_approved'] === 'pending')): ?>
                                                <a href="approve_application.php?id=<?php echo $app['id']; ?>" class="btn-icon" title="Approve"><i class="fas fa-check-circle"></i></a>
                                            <?php endif; ?>
                                            
                                            <?php if ($user_role === 'secretary' && 
                                                      $app['io_approved'] === 'approved' && 
                                                      $app['lo_approved'] === 'approved' && 
                                                      $app['secretary_approved'] !== 'approved'): ?>
                                                <a href="secretary_approve_application.php?id=<?php echo $app['id']; ?>" class="btn-icon" title="Final Approval"><i class="fas fa-stamp"></i></a>
                                            <?php endif; ?>
                                            
                                            <?php if ($app['status'] === 'approved' && $app['io_approved'] === 'approved' && 
                                                     $app['lo_approved'] === 'approved' && $app['secretary_approved'] === 'approved'): ?>
                                                <a href="generate_certificate.php?id=<?php echo $app['id']; ?>&mode=preview" class="btn-icon" title="View Certificate"><i class="fas fa-certificate"></i></a>
                                                <a href="generate_final_report.php?id=<?php echo $app['id']; ?>&mode=preview" class="btn-icon" title="View Report"><i class="fas fa-file-contract"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 