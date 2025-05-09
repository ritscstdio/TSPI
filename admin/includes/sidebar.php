<?php
$current_user = get_logged_in_user();
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
            <span class="user-name"><?php echo $current_user ? sanitize($current_user['name']) : 'Guest'; ?></span>
            <span class="user-role"><?php echo $current_user ? ucfirst(sanitize($current_user['role'])) : ''; ?></span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            
            <li class="nav-header">Content Management</li>
            
            <?php if ($current_user && in_array($current_user['role'], ['admin', 'editor'])): ?>
                <li class="<?php echo in_array($current_page, ['articles.php', 'add-article.php', 'edit-article.php']) ? 'active' : ''; ?>">
                    <a href="articles.php"><i class="fas fa-newspaper"></i> Articles</a>
                </li>
                
                <li class="<?php echo in_array($current_page, ['pages.php', 'add-page.php', 'edit-page.php']) ? 'active' : ''; ?>">
                    <a href="pages.php"><i class="fas fa-file-alt"></i> Pages</a>
                </li>
                
                <li class="<?php echo in_array($current_page, ['categories.php', 'add-category.php', 'edit-category.php']) ? 'active' : ''; ?>">
                    <a href="categories.php"><i class="fas fa-folder"></i> Categories</a>
                </li>

                <li class="<?php echo in_array($current_page, ['media.php', 'add-media.php', 'edit-media.php']) ? 'active' : ''; ?>">
                    <a href="media.php"><i class="fas fa-photo-video"></i> Media</a>
                </li>
            <?php endif; ?>
            
            <li class="<?php echo in_array($current_page, ['comments.php', 'edit-comment.php']) ? 'active' : ''; ?>">
                <a href="comments.php"><i class="fas fa-comments"></i> Comments</a>
            </li>
            
            <?php if ($current_user && $current_user['role'] === 'admin'): ?>
                <li class="nav-header">Administration</li>
                
                <li class="<?php echo in_array($current_page, ['users.php', 'add-user.php', 'edit-user.php']) ? 'active' : ''; ?>">
                    <a href="users.php"><i class="fas fa-users"></i> Users</a>
                </li>
                
                <li class="<?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                    <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
