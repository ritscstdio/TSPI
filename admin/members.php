<?php
$page_title = "Members";
$body_class = "admin-members-page";
require_once '../includes/config.php';
require_admin_login();

// Delete user if requested
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "Member deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error deleting member: " . $e->getMessage();
    }
    redirect('/admin/members.php');
}

// Ban a user if requested
if (isset($_GET['ban'])) {
    $user_id = (int)$_GET['ban'];
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "Member banned successfully.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error banning member: " . $e->getMessage();
    }
    redirect('/admin/members.php');
}

// Unban a user if requested
if (isset($_GET['unban'])) {
    $user_id = (int)$_GET['unban'];
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "Member unbanned successfully.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error unbanning member: " . $e->getMessage();
    }
    redirect('/admin/members.php');
}

// Fetch all members with pagination
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($current_page - 1) * $per_page;

// Get total count for pagination
$stmt_count = $pdo->query("SELECT COUNT(*) FROM users");
$total_members = $stmt_count->fetchColumn();
$total_pages = ceil($total_members / $per_page);

// Get members for current page
$stmt = $pdo->prepare("SELECT id, username, name, email, role, status, created_at, profile_picture 
                       FROM users 
                       ORDER BY created_at DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();
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
                    <h1>Website Members</h1>
                </div>
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                <div class="dashboard-section">
                    <div class="table-tools">
                        <div class="table-filters">
                            <select id="status-filter" onchange="filterMembers()">
                                <option value="all">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="banned">Banned</option>
                            </select>
                        </div>
                        <div class="table-search">
                            <input type="text" id="search-input" placeholder="Search members..." onkeyup="filterMembers()">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="members-table">
                            <thead>
                                <tr>
                                    <th>Profile</th>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($members)): ?>
                                    <tr><td colspan="7">No members found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($members as $member): ?>
                                        <tr data-status="<?php echo sanitize($member['status']); ?>" data-role="<?php echo sanitize($member['role']); ?>" data-id="<?php echo $member['id']; ?>" class="clickable-row">
                                            <td class="profile-cell">
                                                <?php if (!empty($member['profile_picture']) && file_exists("../" . $member['profile_picture'])): ?>
                                                    <img src="<?php echo SITE_URL . '/' . sanitize($member['profile_picture']); ?>" alt="Profile" class="member-profile-pic">
                                                <?php else: ?>
                                                    <div class="profile-placeholder"><?php echo strtoupper(substr($member['username'], 0, 1)); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo sanitize($member['username']); ?></td>
                                            <td><?php echo sanitize($member['name']); ?></td>
                                            <td><?php echo sanitize($member['email']); ?></td>
                                            <td><span class="badge badge-<?php echo sanitize($member['role']); ?>"><?php echo ucfirst(sanitize($member['role'])); ?></span></td>
                                            <td><span class="badge badge-status badge-<?php echo sanitize($member['status']); ?>"><?php echo ucfirst(sanitize($member['status'])); ?></span></td>
                                            <td><?php echo date('M j, Y', strtotime($member['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>" class="page-link">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i === $current_page): ?>
                                <span class="page-link current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" class="page-link">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Member Details Modal -->
    <div id="member-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Member Details</h2>
            <div id="member-details"></div>
        </div>
    </div>

    <script>
    // Filter table function
    function filterMembers() {
        const statusFilter = document.getElementById('status-filter').value;
        const searchInput = document.getElementById('search-input').value.toLowerCase();
        const rows = document.querySelectorAll('#members-table tbody tr');
        
        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            const role = row.getAttribute('data-role');
            const username = row.cells[1].textContent.toLowerCase();
            const name = row.cells[2].textContent.toLowerCase();
            const email = row.cells[3].textContent.toLowerCase();
            
            const statusMatch = statusFilter === 'all' || status === statusFilter;
            const searchMatch = username.includes(searchInput) || 
                               name.includes(searchInput) || 
                               email.includes(searchInput);
            
            if (statusMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('member-modal');
        const modalContent = document.getElementById('member-details');
        const closeBtn = document.querySelector('.modal .close');
        
        // Close modal when clicking X
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Make rows clickable to show member details
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function() {
                const memberId = this.getAttribute('data-id');
                const memberStatus = this.getAttribute('data-status');
                
                // Get data from the row cells
                const profileCell = this.cells[0].innerHTML;
                const username = this.cells[1].textContent;
                const name = this.cells[2].textContent;
                const email = this.cells[3].textContent;
                const role = this.cells[4].textContent;
                const status = this.cells[5].textContent;
                const created = this.cells[6].textContent;
                
                // Build the HTML for the modal
                let html = `
                    <div class="member-profile">
                        ${profileCell}
                        <h3>${name}</h3>
                    </div>
                    <div class="member-info">
                        <p><strong>Username:</strong> ${username}</p>
                        <p><strong>Email:</strong> ${email}</p>
                        <p><strong>Role:</strong> ${role}</p>
                        <p><strong>Status:</strong> ${status}</p>
                        <p><strong>Joined:</strong> ${created}</p>
                    </div>
                    <div class="member-actions">
                        <a href="members.php?delete=${memberId}" class="btn btn-danger delete-btn" data-confirm="Are you sure you want to delete this member? This action cannot be undone.">
                            <i class="fas fa-trash"></i> Delete Member
                        </a>
                        ${memberStatus !== 'banned' ? 
                            `<a href="members.php?ban=${memberId}" class="btn btn-warning ban-btn" data-confirm="Are you sure you want to ban this member?">
                                <i class="fas fa-ban"></i> Ban Member
                            </a>` : 
                            `<a href="members.php?unban=${memberId}" class="btn btn-success unban-btn">
                                <i class="fas fa-user-check"></i> Unban Member
                            </a>`
                        }
                    </div>
                `;
                
                modalContent.innerHTML = html;
                
                // Add event listeners to the newly created buttons
                const deleteBtn = modalContent.querySelector('.delete-btn');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', function(e) {
                        if (!confirm(this.getAttribute('data-confirm'))) {
                            e.preventDefault();
                        }
                    });
                }
                
                const banBtn = modalContent.querySelector('.ban-btn');
                if (banBtn) {
                    banBtn.addEventListener('click', function(e) {
                        if (!confirm(this.getAttribute('data-confirm'))) {
                            e.preventDefault();
                        }
                    });
                }
                
                modal.style.display = 'block';
            });
        });
    });
    </script>
    
    <style>
    /* Member-specific styles */
    .member-profile-pic {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .profile-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #4a69bd;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .badge-user {
        background-color: #4a69bd;
        color: white;
    }
    
    .badge-admin {
        background-color: #e63946;
        color: white;
    }
    
    .badge-editor {
        background-color: #2a9d8f;
        color: white;
    }
    
    .badge-comment_moderator {
        background-color: #f4a261;
        color: white;
    }
    
    .badge-active {
        background-color: #2ecc71;
        color: white;
    }
    
    .badge-inactive {
        background-color: #95a5a6;
        color: white;
    }
    
    .badge-banned {
        background-color: #e74c3c;
        color: white;
    }
    
    .table-tools {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    
    .table-filters {
        display: flex;
        gap: 10px;
    }
    
    .table-filters select,
    .table-search input {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .table-search input {
        min-width: 200px;
    }
    
    /* Clickable rows */
    .clickable-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .clickable-row:hover {
        background-color: rgba(74, 105, 189, 0.1);
    }
    
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        position: relative;
    }
    
    .close {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .member-profile {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .member-profile .member-profile-pic,
    .member-profile .profile-placeholder {
        width: 80px;
        height: 80px;
        font-size: 30px;
    }
    
    .member-info p {
        margin: 10px 0;
        line-height: 1.5;
    }
    
    .member-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    
    .btn {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 4px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
    }
    
    .btn:hover {
        transform: translateY(-2px);
    }
    
    .btn:active {
        transform: translateY(0);
    }
    
    .btn i {
        margin-right: 5px;
    }
    
    .btn-danger {
        background-color: #e74c3c;
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #c0392b;
    }
    
    .btn-warning {
        background-color: #f39c12;
        color: white;
    }
    
    .btn-warning:hover {
        background-color: #d35400;
    }
    
    .btn-success {
        background-color: #2ecc71;
        color: white;
    }
    
    .btn-success:hover {
        background-color: #27ae60;
    }
    
    /* Pagination styles */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        gap: 5px;
    }
    
    .page-link {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #4a69bd;
    }
    
    .page-link.current {
        background-color: #4a69bd;
        color: white;
        border-color: #4a69bd;
    }
    
    .page-link:hover:not(.current) {
        background-color: #f5f5f5;
    }
    
    @media (max-width: 768px) {
        .table-tools {
            flex-direction: column;
            gap: 10px;
        }
        
        .table-filters,
        .table-search {
            width: 100%;
        }
        
        .table-filters {
            flex-wrap: wrap;
        }
        
        .table-filters select {
            flex: 1;
            min-width: 120px;
        }
        
        .member-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            text-align: center;
        }
    }
    </style>
    <?php include 'includes/footer.php'; ?>
</body>
</html> 