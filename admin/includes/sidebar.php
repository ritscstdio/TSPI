<?php
$current_admin = get_admin_user();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $current_admin ? $current_admin['role'] : '';
?>

<aside class="admin-sidebar">
    <div class="sidebar-logo">
        <a href="<?php echo in_array($user_role, ['insurance_officer', 'loan_officer']) ? 'applications.php' : 'index.php'; ?>">
            <img src="<?php echo SITE_URL; ?>/src/assets/favicon.png" alt="TSPI CMS Logo">
            <span>TSPI Manager</span>
        </a>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="sidebar-user">
        <div class="user-info">
            <span class="user-name"><?php echo $current_admin ? sanitize($current_admin['name']) : 'Guest'; ?></span>
            <span class="user-role"><?php echo $current_admin ? ucfirst(sanitize($current_admin['role'])) : ''; ?></span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <?php if (!in_array($user_role, ['insurance_officer', 'loan_officer'])): ?>
            <li class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            </li>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['admin', 'moderator', 'secretary'])): ?>
            <li class="nav-header"><span>Content Management</span></li>
            
            <?php if (in_array($user_role, ['admin', 'moderator', 'secretary'])): ?>
                <li class="<?php echo in_array($current_page, ['content.php', 'add-content.php', 'edit-content.php']) ? 'active' : ''; ?>">
                    <a href="content.php"><i class="fas fa-newspaper"></i> <span>Content</span></a>
                </li>
                
                <?php if (in_array($user_role, ['admin', 'moderator'])): ?>
                <li class="<?php echo in_array($current_page, ['categories.php', 'add-category.php', 'edit-category.php']) ? 'active' : ''; ?>">
                    <a href="categories.php"><i class="fas fa-folder"></i> <span>Categories</span></a>
                </li>

                <li class="<?php echo in_array($current_page, ['media.php', 'add-media.php', 'edit-media.php']) ? 'active' : ''; ?>">
                    <a href="media.php"><i class="fas fa-photo-video"></i> <span>Media</span></a>
                </li>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['admin', 'moderator', 'secretary'])): ?>
            <li class="<?php echo in_array($current_page, ['comments.php', 'edit-comment.php']) ? 'active' : ''; ?>">
                <a href="comments.php"><i class="fas fa-comments"></i> <span>Comments</span></a>
            </li>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['admin', 'moderator', 'insurance_officer', 'loan_officer', 'secretary'])): ?>
                <li class="nav-header"><span>Form Management</span></li>
                <li class="<?php echo ($current_page === 'applications.php') ? 'active' : ''; ?>">
                    <a href="applications.php"><i class="fas fa-file-alt"></i> <span>Membership Applications</span></a>
                </li>
                <li class="<?php echo $current_page === 'approved_records.php' ? 'active' : ''; ?>">
                    <a href="approved_records.php"><i class="fas fa-check-circle"></i> <span>Approved Records</span></a>
                </li>
            <?php endif; ?>
            
            <?php if ($user_role === 'admin'): ?>
                <li class="nav-header"><span>Administration</span></li>
                
                <li class="<?php echo in_array($current_page, ['users.php', 'add-user.php', 'edit-user.php']) ? 'active' : ''; ?>">
                    <a href="users.php"><i class="fas fa-users"></i> <span>Users</span></a>
                </li>
                
                <li class="<?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                    <a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
