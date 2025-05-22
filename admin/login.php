<?php
$page_title = "Admin Login";
$body_class = "admin-login-page";
require_once '../includes/config.php';

// Check if already logged in as admin
if (is_admin_logged_in()) {
    redirect('/admin/index.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        $_SESSION['message'] = "Username and password are required";
    } else {
        // Explicitly use the administrators table
        $stmt = $pdo->prepare("SELECT * FROM administrators WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['message'] = "Welcome back, {$admin['name']}!";
            redirect('/admin/index.php');
        } else {
            // Login failed
            $_SESSION['message'] = "Invalid username or password";
        }
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
    <div class="admin-login-container">
        <div class="login-form-wrap">
            <div class="login-logo">
                <img src="../assets/logo.jpg" alt="TSPI Logo">
            </div>
            <h1>TSPI CMS Admin</h1>
            
            <?php if ($message = get_flash_message()): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form action="" method="post" class="login-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="login-btn">Log In <i class="fas fa-sign-in-alt"></i></button>
                </div>
            </form>
            
            <div class="login-footer">
                <p><a href="../index.php">Back to Site</a></p>
            </div>
        </div>
    </div>
</body>
</html>
