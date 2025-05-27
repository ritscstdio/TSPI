<?php
$page_title = "Admin Login";
$body_class = "admin-login-page";
require_once '../includes/config.php';

// Check if already logged in as admin
if (is_admin_logged_in()) {
    $admin = get_admin_user();
    $admin_role = $admin['role'] ?? '';
    
    // Redirect based on user role
    if (in_array($admin_role, ['insurance_officer', 'loan_officer'])) {
        redirect('/admin/applications.php');
    } else {
        redirect('/admin/index.php');
    }
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
            
            // Redirect based on user role
            if (in_array($admin['role'], ['insurance_officer', 'loan_officer'])) {
                redirect('/admin/applications.php');
            } else {
                redirect('/admin/index.php');
            }
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
    <title><?php echo $page_title; ?> - TSPI Management System</title>
    <link rel="icon" type="image/png" href="../src/assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* Background video styling */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .video-background video {
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            object-fit: cover;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }
        
        .video-background:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(20,45,120,0.7));
            z-index: 1;
        }
        
        /* Fade in animation for the login form */
        .admin-login-container {
            animation: fadeIn 1s ease-in-out;
            position: relative;
            z-index: 2;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="<?php echo $body_class; ?>">
    <!-- Background video -->
    <div class="video-background">
        <video autoplay muted loop id="background-video">
            <source src="../src/assets/Special Number.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    
    <div class="admin-login-container">
        <div class="login-form-wrap">
            <div class="login-logo">
          
            </div>
            <h1>TSPI Management System</h1>
            
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
    
    <script>
        // Fade in the video after page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const video = document.getElementById('background-video');
                if (video) {
                    video.style.opacity = '0.6';
                }
            }, 300);
        });
    </script>
</body>
</html>
