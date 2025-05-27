<?php
$page_title = "My Profile";
$body_class = "profile-page";
require_once '../includes/config.php';

// Require login
if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = '/user/profile.php';
    redirect('/user/login.php');
}

// Get user data
$user = get_logged_in_user();
if (!$user) {
    $_SESSION['message'] = "Error retrieving user profile";
    redirect('/index.php');
}

// Check if user has a membership application
$membership_query = $pdo->prepare("SELECT * FROM members_information WHERE email = ?");
$membership_query->execute([$user['email']]);
$membership = $membership_query->fetch();

// store original email for comparison
$original_email = $user['email'];

$errors = [];
$info_success_message = '';
$password_success_message = '';

// Handle form submissions separately
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Profile info & picture update ---
    if (isset($_POST['update_info'])) {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $remove_picture = isset($_POST['remove_picture']);

        // validate name
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        // validate email
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        } elseif ($email !== $original_email) {
            // ensure uniqueness
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Email already exists";
            }
        }

        // handle picture removal or upload
        if ($remove_picture) {
            // flag removal
        } elseif (!empty($_FILES['profile_picture']['name'])) {
            if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg','image/png','image/gif'];
                if (!in_array($_FILES['profile_picture']['type'], $allowed)) {
                    $errors[] = "Only JPG/PNG/GIF allowed for profile picture.";
                } else {
                    $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'user_'.$user['id'].'_'.time().'.'.$ext;
                }
            } else {
                $errors[] = "Error uploading profile picture.";
            }
        }

        if (empty($errors)) {
            $pdo->beginTransaction();

            // update name
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$name, $user['id']]);

            // remove old picture
            if ($remove_picture && $user['profile_picture']) {
                @unlink(PROFILE_PICS_DIR.'/'.$user['profile_picture']);
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);
            }

            // save new picture
            if (isset($new_filename)) {
                if ($user['profile_picture']) {
                    @unlink(PROFILE_PICS_DIR.'/'.$user['profile_picture']);
                }
                move_uploaded_file(
                    $_FILES['profile_picture']['tmp_name'],
                    PROFILE_PICS_DIR.'/'.$new_filename
                );
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->execute([$new_filename, $user['id']]);
            }

            // if email changed, insert a new verification record (don't update users.email yet)
            if ($email !== $original_email) {
                $vcode     = bin2hex(random_bytes(16));
                $expires   = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // First delete any existing verification record for this user
                $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                
                // Then insert the new verification record
                $stmt = $pdo->prepare("
                    INSERT INTO email_verifications
                      (user_id, verification_code, expires_at, new_email)
                    VALUES (?,?,?,?)
                ");
                $stmt->execute([$user['id'], $vcode, $expires, $email]);

                // send the verify-new-email mail
                $verify_url = SITE_URL . "/user/verify.php?code=$vcode";
                $to      = $email;
                $subject = "Verify your new email address";
                $body    = "Hello $name,\n\nClick to verify your new email:\n$verify_url\n\nThis link will expire in 24 hours.\n\nRegards,\nTSPI Team";
                $hdrs    = "From: " . ADMIN_EMAIL;
                
                // Send email via configured mailer
                require_once __DIR__ . '/email_config.php';
                if (function_exists('dev_send_email')) {
                    $mail_sent = dev_send_email($to, $subject, $body, $hdrs);
                } else {
                    $mail_sent = send_email($to, $subject, $body, $hdrs);
                }
                
                if ($mail_sent) {
                    $info_success_message = "Profile updated. Please check your new email to verify the change.";
                } else {
                    $errors[] = "Profile updated but there was an error sending the verification email. Please contact support.";
                    // Roll back the email change since we couldn't send the verification
                    $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                }
            } else {
                $info_success_message = "Profile updated successfully.";
            }

            $pdo->commit();
            // reload user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user = $stmt->fetch();
        }
    }

    // --- Password change only ---
    if (isset($_POST['change_password'])) {
        $cp = $_POST['current_password'] ?? '';
        $np = $_POST['new_password']     ?? '';
        $cf = $_POST['confirm_password'] ?? '';

        if (empty($cp) || !password_verify($cp, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
        if (empty($np) || strlen($np) < 8
            || !preg_match('/[A-Z]/',$np)
            || !preg_match('/[a-z]/',$np)
            || !preg_match('/[0-9]/',$np)
            || !preg_match('/[^A-Za-z0-9]/',$np)
        ) {
            $errors[] = "New password does not meet requirements";
        }
        if ($np !== $cf) {
            $errors[] = "New passwords do not match";
        }

        if (empty($errors)) {
            $hash = password_hash($np, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $user['id']]);
            $password_success_message = "Password changed successfully.";
        }
    }
}

include '../includes/header.php';
?>

<main class="container profile-container">
    <div class="profile-header">
        <h1>My Profile</h1>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="message error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($info_success_message)): ?>
        <div class="message success">
            <?php echo $info_success_message; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($password_success_message)): ?>
        <div class="message success">
            <?php echo $password_success_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="profile-content">
        <div class="profile-card fade-up-on-load">
            <div class="profile-info">
                <div class="profile-avatar">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo SITE_URL . '/uploads/profile_pics/' . sanitize($user['profile_picture']); ?>"
                             alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                    <div class="user-role"><?php echo ucfirst(sanitize($user['role'])); ?></div>
                </div>
                <div class="profile-details">
                    <h2><?php echo sanitize($user['name'] ?: $user['username']); ?></h2>
                    <p class="username">@<?php echo sanitize($user['username']); ?></p>
                    <p class="email"><?php echo sanitize($user['email']); ?></p>
                    <p class="member-since">Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    
                    <?php if (!$membership): ?>
                        <div class="membership-badge non-member">Non-member</div>
                        <p class="membership-info">You haven't applied for TSPI membership yet.</p>
                        <a href="<?php echo SITE_URL; ?>/user/membership-form.php" class="btn btn-sm btn-primary">Apply Now</a>
                    <?php elseif ($membership['status'] === 'pending'): ?>
                        <div class="membership-badge pending">Approval Pending</div>
                        <p class="membership-info">Your application is being processed.</p>
                        <p class="membership-detail">
                            <strong>Application Date:</strong> <?php echo date('F j, Y', strtotime($membership['created_at'])); ?>
                        </p>
                    <?php elseif ($membership['status'] === 'approved'): ?>
                        <div class="membership-badge approved">Approved</div>
                        <h3 class="membership-details-heading">Membership Details</h3>
                        <div class="membership-details">
                            <p class="membership-detail">
                                <strong>Name:</strong> <?php echo sanitize($membership['first_name'] . ' ' . $membership['middle_name'] . ' ' . $membership['last_name']); ?>
                            </p>
                            
                            <p class="membership-detail">
                                <strong>Classification:</strong><?php 
                                $classification = json_decode($membership['classification'], true);
                                echo is_array($classification) ? sanitize(implode(', ', $classification)) : 'None';
                                ?>
                            </p>
                            
                            <p class="membership-detail">
                                <strong>CID No.:</strong><?php echo sanitize($membership['cid_no']); ?>
                            </p>
                            
                            <p class="membership-detail">
                                <strong>Branch:</strong><?php echo sanitize($membership['branch']); ?>
                            </p>
                            
                            <p class="membership-detail">
                                <strong>Center No.:</strong><?php echo !empty($membership['center_no']) ? sanitize($membership['center_no']) : 'N/A'; ?>
                            </p>
                            
                            <p class="membership-detail">
                                <strong>Present Address:</strong><?php echo sanitize($membership['present_address']); ?>
                            </p>
                            
                            <p class="membership-detail">
                                <strong>Permanent Address:</strong><?php echo sanitize($membership['permanent_address']); ?>
                            </p>
                            
                            <p class="membership-detail">
                                <strong>Primary Business:</strong><?php echo sanitize($membership['primary_business']); ?>
                            </p>
                            
                            <?php if (!empty($membership['business_address'])): ?>
                            <p class="membership-detail">
                                <strong>Business Address:</strong><?php echo sanitize($membership['business_address']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <p class="membership-detail">
                                <strong>Approved Date:</strong><?php echo !empty($membership['secretary_approval_date']) 
                                    ? date('F j, Y', strtotime($membership['secretary_approval_date'])) 
                                    : 'N/A'; ?>
                            </p>
                        </div>
                    <?php elseif ($membership['status'] === 'rejected'): ?>
                        <div class="membership-badge rejected">Application Rejected</div>
                        <p class="membership-info">Your application was not approved.</p>
                        <p class="membership-detail">
                            <strong>Date:</strong> <?php echo date('F j, Y', strtotime($membership['updated_at'])); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="profile-edit-card fade-up-on-load">
            <h2>Edit Profile</h2>
            <!-- Profile Info & Picture Update -->
            <form method="post" action="" enctype="multipart/form-data" id="profile-info-form">
                <input type="hidden" name="update_info" value="1">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo sanitize($user['name']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo sanitize($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    <?php if ($user['profile_picture']): ?>
                      <div class="checkbox-container">
                        <label class="checkbox-label">
                          <input type="checkbox" name="remove_picture" value="1">
                          <span class="checkbox-text">Remove current picture</span>
                        </label>
                      </div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Save Profile</button>
            </form>

            <!-- Password Change -->
            <form method="post" action="" id="password-change-form">
                <input type="hidden" name="change_password" value="1">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password">
                    <div class="password-strength" id="password-strength"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                    <div id="password-match-status"></div>
                </div>
                <button type="submit" class="btn btn-secondary">Change Password</button>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('new_password');
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
        
        // If password is empty, reset strength
        if (password === '') {
            passwordStrength.style.width = '0%';
            passwordStrength.className = 'password-strength';
            lengthCheck.classList.remove('met');
            uppercaseCheck.classList.remove('met');
            lowercaseCheck.classList.remove('met');
            numberCheck.classList.remove('met');
            specialCheck.classList.remove('met');
            return;
        }
        
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
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword === '') {
            passwordMatchStatus.textContent = '';
            passwordMatchStatus.className = '';
        } else if (password === confirmPassword) {
            passwordMatchStatus.textContent = "Passwords match";
            passwordMatchStatus.className = "password-match-success";
        } else {
            passwordMatchStatus.textContent = "Passwords do not match";
            passwordMatchStatus.className = "password-match-error";
        }
    }
    
    passwordInput.addEventListener('input', checkPasswordMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
});
</script>

<style>
.profile-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding-left: 1rem;
    padding-right: 1rem;
}

.profile-header {
    margin-bottom: 1.5rem;
}

.profile-header h1 {
    margin: 0;
    color: #333;
}

.profile-content {
    display: grid;
    grid-template-columns: minmax(550px, 1fr) 2fr;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .profile-content {
        grid-template-columns: 1fr;
    }
    
    .profile-card {
        width: 100%;
        max-width: 600px;
        margin: 0 auto 1.5rem;
    }
}

@media (max-width: 768px) {
    .profile-content {
        grid-template-columns: 1fr;
    }
    
    .profile-card, 
    .profile-edit-card {
        margin-bottom: 1.5rem;
    }
    
    .profile-avatar {
        margin-bottom: 1rem;
    }
    
    .profile-details {
        padding: 0 0.5rem;
    }
    
    .membership-details {
        padding: 10px;
    }
    
    .form-group input,
    .btn {
        font-size: 16px; /* Prevent zoom on mobile */
    }
}

@media (max-width: 480px) {
    .profile-container {
        padding: 0.5rem;
        margin: 5rem auto 1rem;
    }
    
    .profile-header h1 {
        font-size: 1.5rem;
    }
    
    body {
        margin-top: 2rem;
    }
    
    .profile-card {
        margin-top: 2rem;
    }
    
    .membership-detail {
        word-break: break-word;
    }
}

.profile-card, 
.profile-edit-card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
}

.profile-card {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

.profile-info {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.profile-avatar {
    position: relative;
    display: inline-block;
    cursor: pointer;
    margin-bottom: 1rem;
}

.profile-avatar i {
    width: 5rem;
    height: 5rem;
    font-size: 5rem;
    color: #0056b3;
    background-color: #fff;
    border: 2px solid #ddd;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, border-color 0.2s ease;
}

.profile-avatar img {
    width: 5rem;
    height: 5rem;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ddd;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, border-color 0.2s ease;
}

.profile-avatar:hover i,
.profile-avatar:hover img {
    transform: scale(1.05);
    border-color: #0056b3;
}

.user-role {
    position: absolute;
    bottom: 0;
    right: 0;
    background-color: #0056b3;
    color: white;
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    text-transform: uppercase;
}

.profile-details {
    text-align: center;
    width: 100%;
}

.profile-details h2 {
    margin: 0;
    margin-bottom: 0.5rem;
    color: #333;
}

.profile-details .username {
    color: #555;
    margin-bottom: 0.5rem;
}

.profile-details .email {
    color: #666;
    margin-bottom: 0.5rem;
}

.profile-details .member-since {
    color: #777;
    font-size: 0.85rem;
    margin-bottom: 1rem;
}

.profile-edit-card h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #333;
}

.profile-edit-card h3 {
    margin: 1.5rem 0 0.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
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

.form-group input:disabled {
    background-color: #f5f5f5;
    cursor: not-allowed;
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

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
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

/* Checkbox styling */
.checkbox-container {
    display: flex;
    align-items: center;
    margin-top: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 8px;
    appearance: none;
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    border: 2px solid #ccc;
    border-radius: 3px;
    outline: none;
    transition: all 0.2s;
    position: relative;
    cursor: pointer;
    vertical-align: middle;
}

.checkbox-label input[type="checkbox"]:checked {
    background-color: #0056b3;
    border-color: #0056b3;
}

/* Remove the checkmark, keep only the color change */
.checkbox-label input[type="checkbox"]:checked::after {
    content: none;
}

.checkbox-text {
    font-size: 0.9rem;
    color: #555;
    line-height: 1;
    display: inline-block;
    vertical-align: middle;
}

/* Membership status styling */
.membership-status-container {
    margin-top: 1.5rem;
    border-top: 1px solid #eee;
    padding-top: 1.5rem;
    text-align: left;
    width: 100%;
}

.membership-status-container h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    font-size: 1.2rem;
    color: #333;
}

.membership-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: bold;
    margin-bottom: 12px;
}

.membership-badge.non-member {
    background-color: #f0f0f0;
    color: #666;
}

.membership-badge.pending {
    background-color: #fff3cd;
    color: #856404;
}

.membership-badge.approved {
    background-color: #d4edda;
    color: #155724;
}

.membership-badge.rejected {
    background-color: #f8d7da;
    color: #721c24;
}

.membership-info {
    margin-bottom: 10px;
    color: #555;
}

.membership-details {
    background-color: transparent;
    border-radius: 8px;
    padding: 20px;
    margin-top: 15px;
    box-shadow: none;
    text-align: left;
}

.membership-detail {
    margin: 12px 0;
    font-size: 0.95rem;
    color: #555;
    line-height: 1.5;
}

.membership-detail strong {
    color: #333;
    margin-right: 0;
    min-width: 150px;
    display: inline-block;
    font-weight: 600;
}

.membership-details-heading {
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    color: #333;
    text-align: left;
}

.btn-sm {
    display: inline-block;
    width: auto;
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
    margin-top: 5px;
}
</style>

<?php
include '../includes/footer.php';
?> 