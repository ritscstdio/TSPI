
<header class="admin-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="header-right">
        <div class="dropdown">
            <button class="header-user-btn">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $current_user ? sanitize($current_user['name']) : 'Guest'; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu">
                <a href="#" id="editProfileLink" 
                   data-user-name="<?php echo $current_user ? sanitize($current_user['name']) : ''; ?>" 
                   data-user-email="<?php echo $current_user ? sanitize($current_user['email']) : ''; ?>">
                   <i class="fas fa-user-edit"></i> Edit Profile
                </a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</header>
