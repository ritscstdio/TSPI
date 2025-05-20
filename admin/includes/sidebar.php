<?php
$current_admin = get_admin_user();
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
    <div class="sidebar-logo">
        <a href="index.php">
            <img src="<?php echo SITE_URL; ?>/src/assets/favicon.png" alt="TSPI CMS Logo">
            <span>TSPI CMS</span>
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
            <li class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            </li>
            
            <li class="nav-header"><span>Content Management</span></li>
            
            <?php if ($current_admin && in_array($current_admin['role'], ['admin', 'editor'])): ?>
                <li class="<?php echo in_array($current_page, ['content.php', 'add-content.php', 'edit-content.php']) ? 'active' : ''; ?>">
                    <a href="content.php"><i class="fas fa-newspaper"></i> <span>Content</span></a>
                </li>
                
                <li class="<?php echo in_array($current_page, ['categories.php', 'add-category.php', 'edit-category.php']) ? 'active' : ''; ?>">
                    <a href="categories.php"><i class="fas fa-folder"></i> <span>Categories</span></a>
                </li>

                <li class="<?php echo in_array($current_page, ['media.php', 'add-media.php', 'edit-media.php']) ? 'active' : ''; ?>">
                    <a href="media.php"><i class="fas fa-photo-video"></i> <span>Media</span></a>
                </li>
            <?php endif; ?>
            
            <li class="<?php echo in_array($current_page, ['comments.php', 'edit-comment.php']) ? 'active' : ''; ?>">
                <a href="comments.php"><i class="fas fa-comments"></i> <span>Comments</span></a>
            </li>
            
            <?php if ($current_admin && $current_admin['role'] === 'admin'): ?>
                <li class="nav-header"><span>Administration</span></li>
                
                <li class="<?php echo in_array($current_page, ['users.php', 'add-user.php', 'edit-user.php']) ? 'active' : ''; ?>">
                    <a href="users.php"><i class="fas fa-users"></i> <span>Users</span></a>
                </li>
                
                <li class="<?php echo in_array($current_page, ['applications.php', 'view_application.php']) ? 'active' : ''; ?>">
                    <a href="applications.php"><i class="fas fa-file-alt"></i> <span>Membership Applications</span></a>
                </li>
                
                <li class="<?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                    <a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
