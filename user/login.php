<?php
$page_title = "Login";
$body_class = "login-page";
require_once '../includes/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/homepage.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate credentials
    if (empty($username)) {
        $errors[] = "Username or email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        // Check if username is actually an email
        $is_email = filter_var($username, FILTER_VALIDATE_EMAIL);
        
        if ($is_email) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        }
        
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Check if account is verified
            if ($user['status'] === 'inactive') {
                $errors[] = "Your account has not been verified. Please check your email for the verification link.";
            } else {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect to homepage or requested page
                $redirect_to = $_SESSION['redirect_after_login'] ?? '/homepage.php';
                unset($_SESSION['redirect_after_login']);
                redirect($redirect_to);
            }
        } else {
            $errors[] = "Invalid username/email or password";
        }
    }
}

include '../includes/header.php';
?>

<main class="container login-container">
    <div class="auth-box fade-up-on-load">
        <h1>Login to Your Account</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="message error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" value="<?php echo isset($username) ? sanitize($username) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        
        <div class="auth-links">
            <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/user/signup.php">Sign Up</a></p>
            <p><a href="<?php echo SITE_URL; ?>/user/forgot-password.php">Forgot Password?</a></p>
        </div>
    </div>
</main>

<style>
.login-container {
    max-width: 500px;
    margin: 2rem auto;
    padding-left: 1rem;
    padding-right: 1rem;
}

.auth-box {
    background-color: #fff; /* Added back white background */
    /* background-color: #fff; */ /* Removed as per user request */
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Kept for dropshadow */
    padding: 2rem;
}

.auth-box h1 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    text-align: center;
    color: #333;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.btn {
    display: block;
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-primary {
    background-color: #0056b3;
    color: white;
}

.btn-primary:hover {
    background-color: #004494;
}

.auth-links {
    margin-top: 1.5rem;
    text-align: center;
}

.auth-links a {
    color: #0056b3;
    text-decoration: none;
}

.auth-links a:hover {
    text-decoration: underline;
}

.auth-links p {
    margin: 0.5rem 0;
}

.message {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.message ul {
    margin: 0;
    padding-left: 1.5rem;
}
</style>

<?php
include '../includes/footer.php';
?> 