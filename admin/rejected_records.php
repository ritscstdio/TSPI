<?php
$page_title = "Rejected Applications";
$body_class = "admin-rejected-applications-page";
require_once '../includes/config.php';
require_admin_login();

// Fetch all rejected applications
$stmt = $pdo->query("SELECT * FROM members_information WHERE status = 'rejected' OR io_approved = 'rejected' OR lo_approved = 'rejected' OR secretary_approved = 'rejected' ORDER BY created_at DESC");
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
    <link rel="stylesheet" href="css/global.css">
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
            gap: 6px;
        }
        .badge-io, .badge-lo, .badge-secretary {
            font-size: 11px;
            padding: 3px 5px;
            border-radius: 3px;
            display: block;
            white-space: nowrap;
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
        
        /* Clickable row styling */
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .clickable-row:hover {
            background-color: #f5f5f5;
        }
        
        /* Ensure table cells have proper vertical alignment */
        td, th {
            vertical-align: middle;
            padding: 10px 8px;
        }
        
        /* Improve responsive behavior of table */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                min-width: 650px; /* Ensure table is wide enough for scrolling on small screens */
            }
            
            .approval-badges {
                min-width: 120px; /* Ensure badge column is wide enough */
            }
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
                    <h1>Rejected Membership Applications</h1>
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
                                    <th>Rejection Reason</th>
                                    <th>Approvals</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($applications)): ?>
                                    <tr>
                                        <td colspan="7">No rejected applications found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($applications as $app): ?>
                                    <tr class="clickable-row" data-href="view_application.php?id=<?php echo $app['id']; ?>">
                                        <td><?php echo $app['id']; ?></td>
                                        <td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($app['email']); ?></td>
                                        <td><?php echo !empty($app['branch']) ? htmlspecialchars($app['branch']) : 'Not Assigned'; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($app['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                            // Display the comments from whoever rejected the application
                                            if ($app['io_approved'] === 'rejected') {
                                                echo !empty($app['io_comments']) ? htmlspecialchars($app['io_comments']) : 'No reason provided';
                                            } elseif ($app['lo_approved'] === 'rejected') {
                                                echo !empty($app['lo_comments']) ? htmlspecialchars($app['lo_comments']) : 'No reason provided';
                                            } elseif ($app['secretary_approved'] === 'rejected') {
                                                echo !empty($app['secretary_comments']) ? htmlspecialchars($app['secretary_comments']) : 'No reason provided';
                                            } else {
                                                echo 'No reason provided';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="approval-badges">
                                                <span class="badge-io status-<?php echo strtolower($app['io_approved']); ?>">
                                                    IO: <?php echo ucfirst($app['io_approved']); ?>
                                                    <?php if ($app['io_approved'] !== 'pending'): ?>
                                                        by <?php echo htmlspecialchars($app['io_name'] ?: 'Unknown'); ?>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="badge-lo status-<?php echo strtolower($app['lo_approved']); ?>">
                                                    LO: <?php echo ucfirst($app['lo_approved']); ?>
                                                    <?php if ($app['lo_approved'] !== 'pending'): ?>
                                                        by <?php echo htmlspecialchars($app['lo_name'] ?: 'Unknown'); ?>
                                                    <?php endif; ?>
                                                </span>
                                                <?php if ($app['secretary_approved']): ?>
                                                <span class="badge-secretary status-<?php echo strtolower($app['secretary_approved']); ?>">
                                                    Secretary: <?php echo ucfirst($app['secretary_approved']); ?>
                                                    <?php if ($app['secretary_approved'] !== 'pending'): ?>
                                                        by <?php echo htmlspecialchars($app['secretary_name'] ?: 'Unknown'); ?>
                                                    <?php endif; ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.clickable-row');
            rows.forEach(row => {
                row.addEventListener('click', function() {
                    window.location.href = this.dataset.href;
                });
            });
        });
    </script>
</body>
</html> 