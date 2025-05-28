<?php
$page_title = "Sign Up";
$body_class = "signup-page";
require_once '../includes/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/index.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $name = $_POST['name'] ?? '';
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username already exists";
        }
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    // Confirm password
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Validate name
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Generate verification code
        $verification_code = bin2hex(random_bytes(16));
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into database
        try {
            $pdo->beginTransaction();
            
            // Insert user with inactive status
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name, role, status) VALUES (?, ?, ?, ?, 'user', 'inactive')");
            $stmt->execute([$username, $email, $password_hash, $name]);
            $user_id = $pdo->lastInsertId();
            
            // Insert verification code
            $stmt = $pdo->prepare("INSERT INTO email_verifications (user_id, verification_code, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $verification_code, $expires_at]);
            
            $pdo->commit();
            
            // Send verification email
            $verify_url = SITE_URL . "/user/verify.php?code=" . $verification_code;
            $to = $email;
            $subject = "[TSPI] Please Verify Your Email Address";
            
            // Check if logo exists
            $logo_path = "/assets/images/logo.png";
            $logo_url = file_exists($_SERVER['DOCUMENT_ROOT'] . $logo_path) ? 
                        SITE_URL . $logo_path : 
                        "";
            
            // Create HTML version of the email with better formatting
            $html_message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verify Your TSPI Account</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .header {
            background-color: #4a69bd;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 5px 5px;
            border: 1px solid #eeeeee;
        }
        .button {
            display: inline-block;
            background-color: #4a69bd;
            color: white !important;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #999999;
        }
        .verification-link {
            word-break: break-all;
            margin-top: 15px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class='container'>
        " . (!empty($logo_url) ? "<div class='logo'><img src='" . $logo_url . "' alt='TSPI Logo' style='max-height: 60px;'></div>" : "<div style='text-align: center; font-size: 24px; font-weight: bold; margin-bottom: 20px;'>TSPI</div>") . "
        <div class='header'>
            <h1>Verify Your Email Address</h1>
        </div>
        <div class='content'>
            <p>Hello " . htmlspecialchars($username) . ",</p>
            <p>Thank you for creating an account with TSPI. To complete your registration and activate your account, please verify your email address by clicking the button below:</p>
            <div style='text-align: center;'>
                <a href='" . $verify_url . "' class='button'>Verify My Email</a>
            </div>
            <p>This verification link will expire in 24 hours.</p>
            <p>If the button doesn't work, you can copy and paste the following URL into your browser:</p>
            <div class='verification-link'>" . $verify_url . "</div>
            <p>If you didn't create this account, you can safely ignore this email.</p>
            <p>Best regards,<br>The TSPI Team</p>
        </div>
        <div class='footer'>
            <p>&copy; " . date('Y') . " TSPI. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
";

            // Plain text version as fallback
            $message = "Hello $username,\n\n";
            $message .= "Thank you for creating an account with TSPI. To complete your registration, please verify your email address using the link below:\n\n";
            $message .= $verify_url . "\n\n";
            $message .= "This link will expire in 24 hours.\n\n";
            $message .= "If you didn't create this account, you can safely ignore this email.\n\n";
            $message .= "Best regards,\nThe TSPI Team ";
            
            // Attempt to send email - use the configured mailer
            require_once __DIR__ . '/email_config.php';
            $mail_sent = send_email($to, $subject, $html_message);
            
            // For development/testing purposes - always display the verification link
            // Set to false by default, but can be toggled via keyboard shortcut (Alt+D)
            $show_verification_link = false;
            
            $success = true;
            
            // Redirect to homepage after successful registration
            $_SESSION['message'] = "Registration successful! Please check your email to verify your account.";
            redirect('/homepage.php');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<main class="container signup-container">
    <div class="auth-box fade-up-on-load">
        <h1>Create an Account</h1>
        
        <?php if ($success): ?>
            <div class="message success">
                <p>Registration successful! <?php echo $mail_sent ? "Please check your email to verify your account." : "However, there was an issue sending the verification email."; ?></p>
                <p><?php echo $mail_sent ? "A verification link has been sent to " . sanitize($email) : "Please check the verification link below."; ?></p>
                
                <?php if (isset($show_verification_link) && $show_verification_link): ?>
                    <div class="dev-verification-link" <?php echo !$mail_sent ? 'style="border-color: #dc3545;"' : ''; ?>>
                        <p><strong><?php echo $mail_sent ? "Development Testing Only:" : "Email Delivery Failed:"; ?></strong> <?php echo $mail_sent ? "Email sending may not work in local environment." : "Please use this link to verify your account."; ?></p>
                        <p>Use this link to verify your account:</p>
                        <a href="<?php echo $verify_url; ?>" target="_blank"><?php echo $verify_url; ?></a>
                    </div>
                    <style>
                        .dev-verification-link {
                            margin-top: 15px;
                            padding: 10px;
                            background-color: #f8f9fa;
                            border: 1px dashed #ccc;
                            border-radius: 4px;
                        }
                        .dev-verification-link p:last-child {
                            margin-bottom: 5px;
                        }
                    </style>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="message error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" id="signup-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($username) ? sanitize($username) : ''; ?>" required>
                    <div class="form-hint">Username must be at least 3 characters and can only contain letters, numbers, and underscores.</div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? sanitize($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($name) ? sanitize($name) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-strength" id="password-strength"></div>
                    <div class="form-hint">
                        <p>Password must:</p>
                        <ul class="password-requirements">
                            <li id="length-check">Be at least 8 characters</li>
                            <li id="uppercase-check">Include at least one uppercase letter</li>
                            <li id="lowercase-check">Include at least one lowercase letter</li>
                            <li id="number-check">Include at least one number</li>
                            <li id="special-check">Include at least one special character</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <div id="password-match-status"></div>
                </div>
                
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="<?php echo SITE_URL; ?>/user/login.php">Login</a></p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrength = document.getElementById('password-strength');
    const passwordMatchStatus = document.getElementById('password-match-status');
    
    // Password strength requirements
    const lengthCheck = document.getElementById('length-check');
    const uppercaseCheck = document.getElementById('uppercase-check');
    const lowercaseCheck = document.getElementById('lowercase-check');
    const numberCheck = document.getElementById('number-check');
    const specialCheck = document.getElementById('special-check');
    
    // Check password requirements
    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        
        // Check length
        if (password.length >= 8) {
            lengthCheck.classList.add('met');
        } else {
            lengthCheck.classList.remove('met');
        }
        
        // Check uppercase
        if (/[A-Z]/.test(password)) {
            uppercaseCheck.classList.add('met');
        } else {
            uppercaseCheck.classList.remove('met');
        }
        
        // Check lowercase
        if (/[a-z]/.test(password)) {
            lowercaseCheck.classList.add('met');
        } else {
            lowercaseCheck.classList.remove('met');
        }
        
        // Check number
        if (/[0-9]/.test(password)) {
            numberCheck.classList.add('met');
        } else {
            numberCheck.classList.remove('met');
        }
        
        // Check special character
        if (/[^A-Za-z0-9]/.test(password)) {
            specialCheck.classList.add('met');
        } else {
            specialCheck.classList.remove('met');
        }
        
        // Calculate strength
        let strength = 0;
        if (password.length >= 8) strength += 20;
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[a-z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 20;
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;
        
        // Update strength indicator
        passwordStrength.style.width = strength + '%';
        
        if (strength <= 20) {
            passwordStrength.className = 'password-strength very-weak';
        } else if (strength <= 40) {
            passwordStrength.className = 'password-strength weak';
        } else if (strength <= 60) {
            passwordStrength.className = 'password-strength medium';
        } else if (strength <= 80) {
            passwordStrength.className = 'password-strength strong';
        } else {
            passwordStrength.className = 'password-strength very-strong';
        }
    });
    
    // Check password match
    confirmPasswordInput.addEventListener('input', function() {
        if (passwordInput.value === confirmPasswordInput.value) {
            passwordMatchStatus.textContent = "Passwords match";
            passwordMatchStatus.className = "password-match-success";
        } else {
            passwordMatchStatus.textContent = "Passwords do not match";
            passwordMatchStatus.className = "password-match-error";
        }
    });

    // Form submission
    document.getElementById('signup-form').addEventListener('submit', function(e) {
        const strength = parseInt(passwordStrength.style.width);
        
        // Allow form submission if password strength is ≥ 60% (green)
        if (passwordInput.value && strength < 60) {
            e.preventDefault();
            alert('Please create a stronger password (at least green level)');
        }
    });

    // Add a keyboard shortcut to show verification link (Alt+D)
    document.addEventListener('keydown', function(e) {
        // Alt+D to toggle developer tools
        if (e.altKey && e.key === 'd') {
            e.preventDefault();
            const devSection = document.querySelector('.dev-verification-link');
            if (devSection) {
                devSection.style.display = devSection.style.display === 'none' ? 'block' : 'none';
            } else {
                console.log('Developer section not available on this page');
            }
        }
    });
});
</script>

<style>
.signup-container {
    max-width: 600px;
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

.form-hint {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: #666;
}

.password-requirements {
    margin-top: 0.5rem;
    padding-left: 1.5rem;
}

.password-requirements li {
    margin-bottom: 0.25rem;
    font-size: 0.85rem;
    color: #666;
}

.password-requirements li.met {
    color: #28a745;
}

.password-requirements li.met::before {
    content: "✓ ";
    color: #28a745;
}

.password-strength {
    height: 5px;
    margin-top: 0.5rem;
    width: 0%;
    background-color: #dc3545;
    transition: width 0.3s ease, background-color 0.3s ease;
}

.password-strength.very-weak {
    background-color: #dc3545;
}

.password-strength.weak {
    background-color: #ffc107;
}

.password-strength.medium {
    background-color: #fd7e14;
}

.password-strength.strong {
    background-color: #20c997;
}

.password-strength.very-strong {
    background-color: #28a745;
}

.password-match-success {
    color: #28a745;
    font-size: 0.85rem;
    margin-top: 0.5rem;
}

.password-match-error {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.5rem;
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

.message {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
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