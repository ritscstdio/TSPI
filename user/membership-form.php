<?php
$page_title = "Membership Form";
$body_class = "membership-form-page";
require_once '../includes/config.php';

// Function to generate a unique CID
function generateUniqueCID($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $cid = '';
    for ($i = 0; $i < $length; $i++) {
        $cid .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $cid;
}

// Function to check if a CID already exists
function cidExists($cid) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM members_information WHERE cid_no = ?");
    $stmt->execute([$cid]);
    return (int)$stmt->fetchColumn() > 0;
}

// Function to get a guaranteed unique CID
function getUniqueCID($length = 6, $maxAttempts = 10) {
    $attempts = 0;
    do {
        $cid = generateUniqueCID($length);
        $exists = cidExists($cid);
        $attempts++;
    } while ($exists && $attempts < $maxAttempts);
    
    if ($exists) {
        // If we still have a collision after max attempts, use a longer CID
        return getUniqueCID($length + 1, $maxAttempts);
    }
    
    return $cid;
}

// Require user to be logged in
if (!is_logged_in()) {
    $_SESSION['message'] = "You must be logged in to access the membership form.";
    redirect('/user/login.php');
}

// Handle form submission
$errors = [];
$success = false;

// Prevent duplicate application submission - enhanced version
function checkDuplicateApplication($email = null, $user_id = null) {
    global $pdo;
    
    // First check by user_id which is more reliable
    if (!empty($user_id)) {
        // This assumes you have a user_id column in your members_information table
        $stmt = $pdo->prepare("SELECT id, status FROM members_information WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        if ($result) {
            return [
                'has_application' => true,
                'status' => $result['status']
            ];
        }
    }
    
    // If no result from user_id, check by email as fallback
    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT id, status FROM members_information WHERE email = ? AND status IN ('pending', 'approved')");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        if ($result) {
            return [
                'has_application' => true,
                'status' => $result['status']
            ];
        }
    }
    
    // No duplicate found
    return [
        'has_application' => false,
        'status' => null
    ];
}

// Check for duplicate application
$user = get_logged_in_user();
if ($user) {
    $email = isset($user['email']) ? $user['email'] : null;
    $user_id = isset($user['id']) ? $user['id'] : null;
    
    $application_check = checkDuplicateApplication($email, $user_id);
    
    if ($application_check['has_application']) {
        include '../includes/header.php';
        
        $status_message = "Your application is currently being processed.";
        if ($application_check['status'] === 'approved') {
            $status_message = "You already have an approved application.";
        } elseif ($application_check['status'] === 'rejected') {
            $status_message = "Your previous application was rejected. Please contact support for more information.";
        }
        
        echo '<div class="message info" style="margin-top:180px;"><p>' . $status_message . ' You cannot submit another application at this time.</p></div>';
        echo '<script>setTimeout(function(){ window.location.href = "' . SITE_URL . '/homepage.php"; }, 8000);</script>';
        include '../includes/footer.php';
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data
    $user = get_logged_in_user();
    $email = isset($user['email']) ? sanitize($user['email']) : '';
    // Names
    $first_name = sanitize($_POST['first_name']);
    $middle_name = sanitize($_POST['middle_name']);
    $last_name = sanitize($_POST['last_name']);
    
    // Initialize all form fields to prevent undefined variable warnings
    $branch = ""; // Set branch to NULL rather than sanitize
    $cid_no = getUniqueCID(); // Generate a unique CID
    $center_no = sanitize($_POST['center_no'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $civil_status = sanitize($_POST['civil_status'] ?? '');
    $birth_place = sanitize($_POST['birth_place'] ?? '');
    $nationality = sanitize($_POST['nationality'] ?? '');
    $id_number = sanitize($_POST['id_number'] ?? '');
    $other_valid_id = sanitize($_POST['other_valid_id'] ?? '');
    $present_address = sanitize($_POST['present_address'] ?? '');
    $present_brgy_code = sanitize($_POST['present_brgy_code'] ?? '');
    $present_zip_code = sanitize($_POST['present_zip_code'] ?? '');
    $permanent_address = sanitize($_POST['permanent_address'] ?? '');
    $permanent_brgy_code = sanitize($_POST['permanent_brgy_code'] ?? '');
    $permanent_zip_code = sanitize($_POST['permanent_zip_code'] ?? '');
    $home_ownership = sanitize($_POST['home_ownership'] ?? '');
    $length_of_stay = intval($_POST['length_of_stay'] ?? 0);
    $years_in_business = intval($_POST['years_in_business'] ?? 0);
    
    // Helper function to convert date format from MM/DD/YYYY to Y-m-d safely
    function formatMembershipDate($dateStr) {
        if (empty($dateStr)) return null;
        $dateObj = DateTime::createFromFormat('m/d/Y', $dateStr);
        return $dateObj ? $dateObj->format('Y-m-d') : null;
    }
    
    // Birthday and age (input format MM/DD/YYYY)
    $birthdate = formatMembershipDate($_POST['birthday']);
    if ($birthdate) {
        $birthDateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
        $age = $birthDateObj->diff(new DateTime('today'))->y;
    } else {
        $age = 0;
    }
    // Contact
    $phone = sanitize($_POST['cell_phone']);
    // Business info
    $business_name = sanitize($_POST['primary_business']);
    $business_address = sanitize($_POST['business_address_unit'] ?? '');
    // Other sources of income
    $other_income_source_1 = sanitize($_POST['other_income_source_1'] ?? '');
    $other_income_source_2 = sanitize($_POST['other_income_source_2'] ?? '');
    $other_income_source_3 = sanitize($_POST['other_income_source_3'] ?? '');
    $other_income_source_4 = sanitize($_POST['other_income_source_4'] ?? '');
    
    // Spouse (if married)
    $spouse_name = '';
    $spouse_birthdate = null;
    if (isset($_POST['civil_status']) && $_POST['civil_status'] === 'Married') {
        $spouse_name = sanitize(
            trim(
                ($_POST['spouse_first_name'] ?? '') . ' ' .
                ($_POST['spouse_middle_name'] ?? '') . ' ' .
                ($_POST['spouse_last_name'] ?? '')
            )
        );
        $spouse_birthdate = formatMembershipDate($_POST['spouse_birthday']);
    }
    // Beneficiary 1
    $beneficiary_1_firstname = sanitize($_POST['beneficiary_first_name'][0] ?? '');
    $beneficiary_1_lastname  = sanitize($_POST['beneficiary_last_name'][0] ?? '');
    $beneficiary_1_dependent = isset($_POST['beneficiary_dependent'][0]) ? 1 : 0;
    // Beneficiary 2
    $beneficiary_2_firstname = sanitize($_POST['beneficiary_first_name'][1] ?? '');
    $beneficiary_2_lastname  = sanitize($_POST['beneficiary_last_name'][1] ?? '');
    $beneficiary_2_dependent = isset($_POST['beneficiary_dependent'][1]) ? 1 : 0;
    // Beneficiary 3
    $beneficiary_3_firstname = sanitize($_POST['beneficiary_first_name'][2] ?? '');
    $beneficiary_3_lastname  = sanitize($_POST['beneficiary_last_name'][2] ?? '');
    $beneficiary_3_dependent = isset($_POST['beneficiary_dependent'][2]) ? 1 : 0;
    // Beneficiary 4
    $beneficiary_4_firstname = sanitize($_POST['beneficiary_first_name'][3] ?? '');
    $beneficiary_4_lastname  = sanitize($_POST['beneficiary_last_name'][3] ?? '');
    $beneficiary_4_dependent = isset($_POST['beneficiary_dependent'][3]) ? 1 : 0;

    // Trustee
    $trustee_name = sanitize($_POST['trustee_name'] ?? '');
    $trustee_birthdate = formatMembershipDate($_POST['trustee_dob'] ?? '');
    // Signature uploads
    $uploadsDir = UPLOADS_DIR . '/signatures';
    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
    // Member signature
    $memberSignaturePath = null;
    if (!empty($_POST['member_signature']) && $_POST['member_signature'] !== 'data:,') {
        [$meta, $data] = explode(',', $_POST['member_signature']);
        $decoded = base64_decode($data);
        if (!empty($decoded)) {
            $fname = 'member_' . time() . '.png';
            file_put_contents($uploadsDir . '/' . $fname, $decoded);
            $memberSignaturePath = 'uploads/signatures/' . $fname;
        }
    }
    // Beneficiary signature
    $beneficiarySignaturePath = null;
    if (!empty($_POST['beneficiary_signature']) && $_POST['beneficiary_signature'] !== 'data:,') {
        [, $data] = explode(',', $_POST['beneficiary_signature']);
        $decoded = base64_decode($data);
        if (!empty($decoded)) {
            $fname2 = 'beneficiary_' . time() . '.png';
            file_put_contents($uploadsDir . '/' . $fname2, $decoded);
            $beneficiarySignaturePath = 'uploads/signatures/' . $fname2;
        }
    }
    // Insert into database
    global $pdo;
    try {
        // Dynamically build INSERT statement to match parameter count
        $columns = [
            'branch','cid_no','center_no','plans','classification',
            'first_name','middle_name','last_name','gender','civil_status',
            'birthdate','age','birth_place','email','cell_phone','contact_no','nationality',
            'id_number','other_valid_ids','mothers_maiden_last_name','mothers_maiden_first_name','mothers_maiden_middle_name',
            'present_address','present_brgy_code','present_zip_code',
            'permanent_address','permanent_brgy_code','permanent_zip_code',
            'home_ownership','length_of_stay','primary_business','years_in_business','business_address',
            'other_income_source_1','other_income_source_2','other_income_source_3','other_income_source_4',
            'spouse_name','spouse_birthdate','spouse_occupation','spouse_id_number',
            'beneficiary_fn_1','beneficiary_ln_1','beneficiary_mi_1','beneficiary_birthdate_1','beneficiary_gender_1','beneficiary_relationship_1','beneficiary_dependent_1',
            'beneficiary_fn_2','beneficiary_ln_2','beneficiary_mi_2','beneficiary_birthdate_2','beneficiary_gender_2','beneficiary_relationship_2','beneficiary_dependent_2',
            'beneficiary_fn_3','beneficiary_ln_3','beneficiary_mi_3','beneficiary_birthdate_3','beneficiary_gender_3','beneficiary_relationship_3','beneficiary_dependent_3',
            'beneficiary_fn_4','beneficiary_ln_4','beneficiary_mi_4','beneficiary_birthdate_4','beneficiary_gender_4','beneficiary_relationship_4','beneficiary_dependent_4',
            'beneficiary_fn_5','beneficiary_ln_5','beneficiary_mi_5','beneficiary_birthdate_5','beneficiary_gender_5','beneficiary_relationship_5','beneficiary_dependent_5',
            'trustee_name','trustee_birthdate','trustee_relationship',
            'member_name','sig_beneficiary_name','member_signature','beneficiary_signature','disclaimer_agreement','status'
        ];
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = sprintf(
            "INSERT INTO members_information (%s) VALUES (%s)",
            implode(', ', $columns),
            $placeholders
        );
        $stmt = $pdo->prepare($sql);
        
        // Convert plans and classification arrays to JSON
        $plansJson = isset($_POST['plans']) ? json_encode(array_unique($_POST['plans'])) : null;
        $classificationJson = isset($_POST['classification']) ? json_encode($_POST['classification']) : null;
        $otherValidIdsJson = isset($_POST['other_valid_id']) ? json_encode($_POST['other_valid_id']) : null;
        
        // Get values for mother's maiden name that weren't initialized before
        $mothers_maiden_last_name = sanitize($_POST['mothers_maiden_last_name'] ?? '');
        $mothers_maiden_first_name = sanitize($_POST['mothers_maiden_first_name'] ?? '');
        $mothers_maiden_middle_name = sanitize($_POST['mothers_maiden_middle_name'] ?? '');
        
        // Create an array of all values to pass to execute()
        $params = [
            $branch,                                              // branch
            $cid_no,                                              // cid_no
            $center_no,                                           // center_no
            $plansJson,                                           // plans
            $classificationJson,                                  // classification
            $first_name,                                          // first_name
            $middle_name,                                         // middle_name
            $last_name,                                           // last_name
            $gender,                                              // gender
            $civil_status,                                        // civil_status
            $birthdate,                                           // birthdate
            $age,                                                 // age
            $birth_place,                                         // birth_place
            $email,                                               // email
            $phone,                                               // cell_phone
            sanitize($_POST['contact_no'] ?? ''),                 // contact_no
            $nationality,                                         // nationality
            $id_number,                                           // id_number
            $otherValidIdsJson,                                   // other_valid_ids
            $mothers_maiden_last_name,                            // mothers_maiden_last_name
            $mothers_maiden_first_name,                           // mothers_maiden_first_name
            $mothers_maiden_middle_name,                          // mothers_maiden_middle_name
            $present_address,                                     // present_address
            sanitize($_POST['present_brgy_code'] ?? ''),          // present_brgy_code
            $present_zip_code,                                    // present_zip_code
            $permanent_address,                                   // permanent_address
            sanitize($_POST['permanent_brgy_code'] ?? ''),        // permanent_brgy_code
            $permanent_zip_code,                                  // permanent_zip_code
            $home_ownership,                                      // home_ownership
            $length_of_stay,                                      // length_of_stay
            $business_name,                                       // primary_business
            $years_in_business,                                   // years_in_business
            $business_address,                                    // business_address
            $other_income_source_1,                               // other_income_source_1
            $other_income_source_2,                               // other_income_source_2
            $other_income_source_3,                               // other_income_source_3
            $other_income_source_4,                               // other_income_source_4
            $spouse_name,                                         // spouse_name
            $spouse_birthdate,                                    // spouse_birthdate
            sanitize($_POST['spouse_occupation'] ?? ''),          // spouse_occupation
            sanitize($_POST['spouse_id_number'] ?? ''),           // spouse_id_number
            $beneficiary_1_firstname,                             // beneficiary_fn_1
            $beneficiary_1_lastname,                              // beneficiary_ln_1
            sanitize($_POST['beneficiary_mi'][0] ?? ''),          // beneficiary_mi_1
            formatMembershipDate($_POST['beneficiary_dob'][0] ?? ''),          // beneficiary_birthdate_1
            sanitize($_POST['beneficiary_gender'][0] ?? ''),      // beneficiary_gender_1
            sanitize($_POST['beneficiary_relationship'][0] ?? ''), // beneficiary_relationship_1
            $beneficiary_1_dependent,                             // beneficiary_dependent_1
            $beneficiary_2_firstname,                             // beneficiary_fn_2
            $beneficiary_2_lastname,                              // beneficiary_ln_2
            sanitize($_POST['beneficiary_mi'][1] ?? ''),          // beneficiary_mi_2
            formatMembershipDate($_POST['beneficiary_dob'][1] ?? ''),          // beneficiary_birthdate_2
            sanitize($_POST['beneficiary_gender'][1] ?? ''),      // beneficiary_gender_2
            sanitize($_POST['beneficiary_relationship'][1] ?? ''), // beneficiary_relationship_2
            $beneficiary_2_dependent,                             // beneficiary_dependent_2
            $beneficiary_3_firstname,                             // beneficiary_fn_3
            $beneficiary_3_lastname,                              // beneficiary_ln_3
            sanitize($_POST['beneficiary_mi'][2] ?? ''),          // beneficiary_mi_3
            formatMembershipDate($_POST['beneficiary_dob'][2] ?? ''),          // beneficiary_birthdate_3
            sanitize($_POST['beneficiary_gender'][2] ?? ''),      // beneficiary_gender_3
            sanitize($_POST['beneficiary_relationship'][2] ?? ''), // beneficiary_relationship_3
            $beneficiary_3_dependent,                             // beneficiary_dependent_3
            $beneficiary_4_firstname,                             // beneficiary_fn_4
            $beneficiary_4_lastname,                              // beneficiary_ln_4
            sanitize($_POST['beneficiary_mi'][3] ?? ''),          // beneficiary_mi_4
            formatMembershipDate($_POST['beneficiary_dob'][3] ?? ''),          // beneficiary_birthdate_4
            sanitize($_POST['beneficiary_gender'][3] ?? ''),      // beneficiary_gender_4
            sanitize($_POST['beneficiary_relationship'][3] ?? ''), // beneficiary_relationship_4
            $beneficiary_4_dependent,                             // beneficiary_dependent_4
            sanitize($_POST['beneficiary_first_name'][4] ?? ''),  // beneficiary_fn_5
            sanitize($_POST['beneficiary_last_name'][4] ?? ''),   // beneficiary_ln_5
            sanitize($_POST['beneficiary_mi'][4] ?? ''),          // beneficiary_mi_5
            formatMembershipDate($_POST['beneficiary_dob'][4] ?? ''),          // beneficiary_birthdate_5
            sanitize($_POST['beneficiary_gender'][4] ?? ''),      // beneficiary_gender_5
            sanitize($_POST['beneficiary_relationship'][4] ?? ''), // beneficiary_relationship_5
            isset($_POST['beneficiary_dependent'][4]) ? 1 : 0,    // beneficiary_dependent_5
            $trustee_name,                                        // trustee_name
            $trustee_birthdate,                                   // trustee_birthdate
            sanitize($_POST['trustee_relationship'] ?? ''),       // trustee_relationship
            sanitize($_POST['member_name'] ?? ''),                // member_name
            sanitize($_POST['sig_beneficiary_name'] ?? ''),       // sig_beneficiary_name
            $memberSignaturePath,                                 // member_signature
            $beneficiarySignaturePath,                            // beneficiary_signature
            isset($_POST['disclaimer_agreement']) ? 1 : 0,        // disclaimer_agreement
            'pending'                                             // status
        ];

        $stmt->execute($params);
        $success = true;
        
        // Clear localStorage after successful submission
        echo '<script>localStorage.removeItem("membershipFormData");</script>';
    } catch (Exception $e) {
        $errors[] = 'Submission error: ' . $e->getMessage();
    }
}

include '../includes/header.php';
?>
<!-- Add a hidden comment with SQL command for adding UNIQUE constraint to cid_no column -->
<!-- 
To add a UNIQUE constraint to the cid_no column in phpMyAdmin, run the following SQL command:

ALTER TABLE `members_information` ADD UNIQUE KEY `unique_cid` (`cid_no`);

This will ensure that each CID must be unique in the database.
-->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
<link rel="stylesheet" type="text/css" href="../assets/css/forms.css">

<main class="container membership-form-container">
    <div class="auth-box fade-up-on-load">
        <h1>TSPI Membership Form</h1>
        
        <?php if ($success): ?>
            <div class="message success">
                <p>Your membership application has been submitted successfully.</p>
                <p>One of our representatives will contact you soon.</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = '<?php echo SITE_URL; ?>/homepage.php';
                }, 10000);
            </script>
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
            
            <form method="post" action="" id="membership-form">
                <!-- All Form Content -->
                <div class="form-page-content active" id="form-page-1">
                    <h2>Personal Information</h2>
                    
                    <!-- Removed visible inputs for branch, CID no, and center no, but keeping hidden fields for SQL -->
                    <input type="hidden" id="branch" name="branch" value="">
                    <input type="hidden" id="cid_no" name="cid_no" value="">
                    <input type="hidden" id="center_no" name="center_no" value="">
                    

                            <div class="form-group">
                        <label>Member Classification</label>
                        <div class="checkbox-group">
                          
                            <div class="checkbox-item">
                                <input type="checkbox" id="class_tkp" name="classification[]" value="TKP" title="TKP borrower classification - For individual borrowers">
                                <label for="class_tkp">TKP (Borrower) <span class="tooltip-icon" title="TKP borrower classification - For individual borrowers">ⓘ</span></label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="class_tpp" name="classification[]" value="TPP" title="TPP borrower classification - For business or partnership borrowers">
                                <label for="class_tpp">TPP (Borrower) <span class="tooltip-icon" title="TPP borrower classification - For business or partnership borrowers">ⓘ</span></label>
                        </div>

                            <div class="checkbox-item">
                                <input type="checkbox" id="class_borrower" name="classification[]" value="Kapamilya" title="Kapamilya classification - For family members of borrowers">
                                <label for="class_borrower">Kapamilya <span class="tooltip-icon" title="Kapamilya classification - For family members of borrowers">ⓘ</span></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group available-plans-group">
                        <label>Available Plans</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="plan_blip" name="plans[]" value="BLIP" checked onclick="return false;" title="Basic Life Insurance Plan - Provides essential coverage">
                                <label for="plan_blip">Basic Life (BLIP) <span class="tooltip-icon" title="Basic Life Insurance Plan - Provides essential coverage">ⓘ</span></label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="plan_lpip" name="plans[]" value="LPIP" title="Life Plus Insurance Plan - Additional coverage on top of basic">
                                <label for="plan_lpip">Life Plus (LPIP) <span class="tooltip-icon" title="Life Plus Insurance Plan - Additional coverage on top of basic">ⓘ</span></label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="plan_lmip" name="plans[]" value="LMIP" title="Life Max Insurance Plan - Comprehensive coverage with maximum benefits">
                                <label for="plan_lmip">Life Max (LMIP) <span class="tooltip-icon" title="Life Max Insurance Plan - Comprehensive coverage with maximum benefits">ⓘ</span></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" required placeholder="Enter Last Name">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" required placeholder="Enter First Name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" placeholder="Enter Middle Name (optional)">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="civil_status">Civil Status</label>
                                <select id="civil_status" name="civil_status" required>
                                    <option value="" disabled selected>Select Civil Status</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Separated">Separated</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="birthday">Birthday (mm/dd/yyyy)</label>
                                <input type="text" id="birthday" name="birthday" required placeholder="MM/DD/YYYY">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="birth_place">Birth Place</label>
                                <input type="text" id="birth_place" name="birth_place" required placeholder="Enter Place of Birth">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="cell_phone">Phone no./ SIM</label>
                                <div class="phone-input-group with-flag">
                                    <span class="phone-prefix"><span class="country-flag">🇵🇭</span>+63</span>
                                    <input type="text" id="cell_phone" name="cell_phone" required pattern="[0-9]{10}" maxlength="10" title="10-digit mobile number (e.g., 917xxxxxxx)">
                                </div>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="contact_no">Telephone no./ Landline</label>
                                <input type="text" id="contact_no" name="contact_no" pattern="[0-9]{7}" maxlength="7" title="7-digit landline number" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="nationality">Nationality</label>
                                <input type="text" id="nationality" name="nationality" required placeholder="Enter Nationality">
                            </div>
                        </div>
                       
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="id_number">TIN/SSS/GSIS Number</label>
                                <input type="text" id="id_number" name="id_number" required placeholder="Enter Valid ID Number">
                                <button type="button" id="add_other_valid_id_btn" class="btn btn-secondary btn-add btn-sm" style="margin-top: 8px;"><span class="btn-icon">+</span> Do you have other Valid IDs?</button>
                            </div>
                            <div id="other_valid_ids_container" style="margin-top:10px; margin-bottom: 24px;">
                                <!-- Other valid IDs will be added here -->
                            </div>
                        </div>
                    </div>
                    
                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
                    
                    <!-- Mother's Maiden Name - Modified for 3 fields -->
                    <div class="form-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="mothers_maiden_last_name">Mother's Maiden Last Name</label>
                                <input type="text" id="mothers_maiden_last_name" name="mothers_maiden_last_name" required placeholder="Enter Mother's Maiden Last Name">
                            </div>
                        </div>
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="mothers_maiden_first_name">Mother's Maiden First Name</label>
                                <input type="text" id="mothers_maiden_first_name" name="mothers_maiden_first_name" required placeholder="Enter Mother's Maiden First Name">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="mothers_maiden_middle_name">Mother's Maiden Middle Name</label>
                                <input type="text" id="mothers_maiden_middle_name" name="mothers_maiden_middle_name" placeholder="Enter Mother's Maiden Middle Name (optional)">
                            </div>
                        </div>
                        <div class="form-col-2">
                            <!-- Empty div to maintain 2-column layout -->
                        </div>
                    </div>
                    <!-- End Mother's Maiden Name -->

                </div> <!-- End of Page 1 -->

                <div class="form-page-content" id="form-page-2">
                    <h2>Present Address</h2>
                     <div class="form-group">
                        <label for="present_address">Unit / Address</label>
        <input type="text" id="present_address" name="present_address" placeholder="Unit No., Street, Brgy., City" required>
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                <label for="present_brgy_code">Brgy. Code</label>
                <input type="text" id="present_brgy_code" name="present_brgy_code" placeholder="Enter Brgy. Code" required>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="present_zip_code">Zip Code</label>
                                <input type="text" id="present_zip_code" name="present_zip_code" required placeholder="Enter ZIP Code">
                            </div>
                        </div>
                    </div>
     
         <!-- Hidden fields removed -->

                    <div class="section-divider"></div>

                    <!-- Permanent Address -->
                    <h2>Permanent Address</h2>
                    <div class="form-group">
                        <label for="permanent_address">Unit / Address</label>
                        <input type="text" id="permanent_address" name="permanent_address" placeholder="Unit No., Street, Brgy., City" required>
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="permanent_brgy_code">Brgy. Code</label>
                                <input type="text" id="permanent_brgy_code" name="permanent_brgy_code" placeholder="Enter Brgy. Code" required>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="permanent_zip_code">Zip Code</label>
                                <input type="text" id="permanent_zip_code" name="permanent_zip_code" required placeholder="Enter ZIP Code">
                            </div>
                        </div>
                        </div>
                    
                        <!-- Hidden fields removed -->

                    <!-- Home Ownership -->
                    <div class="form-group">
                        <label>Home Ownership</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" id="home_owned" name="home_ownership" value="Owned" required>
                                <label for="home_owned">Owned</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" id="home_rented" name="home_ownership" value="Rented">
                                <label for="home_rented">Rented</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" id="home_living_with_parents" name="home_ownership" value="Living with parents/relatives">
                                <label for="home_living_with_parents">Living with parents/relatives</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="length_of_stay">Length of Stay /yr</label>
                                <input type="number" id="length_of_stay" name="length_of_stay" required min="0">
                            </div>
                        </div>
                        <div class="form-col-2">
                            <!-- Empty div to maintain 2-column layout -->
                        </div>
                    </div>
                    <div class="section-divider"></div>
                    <!-- Business/Source of Funds -->
                    <h2>Business/Source of Funds</h2>
                    <div class="form-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="primary_business">Primary Business</label>
                                <input type="text" id="primary_business" name="primary_business" required placeholder="Enter Primary Business">
                                <button type="button" id="add_other_income_source_btn" class="btn btn-secondary btn-add btn-sm" style="margin-top: 8px;"><span class="btn-icon">+</span> Got other source of income?</button>
                            </div>
                            <div id="other_income_sources_container">
                        <!-- Other income sources will be added here by JS -->
                        </div>
                        </div>
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="years_in_business">Years in Business</label>
                                <input type="number" id="years_in_business" name="years_in_business" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="business_address_unit">Unit / Address</label>
                        <input type="text" id="business_address_unit" name="business_address_unit" placeholder="Unit No., Street, Brgy., City" required>
                        <!-- Hidden fields removed -->
                    </div>
                    <!-- Address fields removed -->
                    <!-- Spouse Information -->
                    <div id="spouse_information_section" style="display: none;">
                        <h2>Spouse Information</h2>
                         <div class="form-row">
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_last_name">Spouse's Last Name</label>
                                    <input type="text" id="spouse_last_name" name="spouse_last_name" placeholder="Enter Spouse's Last Name">
                                </div>
                            </div>
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_first_name">Spouse's First Name</label>
                                    <input type="text" id="spouse_first_name" name="spouse_first_name" placeholder="Enter Spouse's First Name">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_middle_name">Spouse's Middle Name</label>
                                    <input type="text" id="spouse_middle_name" name="spouse_middle_name" placeholder="Enter Spouse's Middle Name (optional)">
                                </div>
                            </div>
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_birthday">Birthday (mm/dd/yyyy)</label>
                                    <input type="text" id="spouse_birthday" name="spouse_birthday" placeholder="MM/DD/YYYY">
                                    <!-- Age is now calculated but not displayed as a field -->
                                    <input type="hidden" id="spouse_age" name="spouse_age">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_occupation">Occupation</label>
                                    <input type="text" id="spouse_occupation" name="spouse_occupation" placeholder="Enter Spouse's Occupation">
                                </div>
                            </div>
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_id_number">TIN/SSS/GSIS/Valid ID</label>
                                    <input type="text" id="spouse_id_number" name="spouse_id_number" placeholder="Enter Spouse's ID Number">
                                </div>
                            </div>
                        </div>
                     
                    </div>
                </div> <!-- End of Page 2 -->

                <div class="form-page-content" id="form-page-3">
                    <h2>Beneficiaries and Dependents</h2>
                    <table class="beneficiaries-table">
                        <thead>
                            <tr>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>M.I.</th>
                                <th>Date of Birth</th>
                                <th>Gender</th>
                                <th>Relationship</th>
                                <th>Dependent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Static 5 beneficiary rows -->
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <tr class="beneficiary-row">
                                <td><input type="text" id="beneficiary_last_name_<?php echo $i; ?>" name="beneficiary_last_name[]" placeholder="Last Name (optional)"></td>
                                <td><input type="text" id="beneficiary_first_name_<?php echo $i; ?>" name="beneficiary_first_name[]" placeholder="First Name (optional)"></td>
                                <td><input type="text" id="beneficiary_mi_<?php echo $i; ?>" name="beneficiary_mi[]" maxlength="1" placeholder="MI"></td>
                                <td><input type="text" id="beneficiary_dob_<?php echo $i; ?>" name="beneficiary_dob[]" class="beneficiary-dob" placeholder="MM/DD/YYYY"></td>
                                <td><select id="beneficiary_gender_<?php echo $i; ?>" name="beneficiary_gender[]"><option value="" selected></option><option value="M">M</option><option value="F">F</option></select></td>
                                <td><input type="text" id="beneficiary_relationship_<?php echo $i; ?>" name="beneficiary_relationship[]" placeholder="Relationship (optional)"></td>
                                <td style="text-align:center;"><input type="checkbox" id="beneficiary_dependent_<?php echo $i; ?>" name="beneficiary_dependent[]" value="1"></td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    
                    <div class="section-divider"></div>
                    
                    <h2>Designation of Trustee</h2>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="trustee_name">Name of Trustee (optional)</label>
                                <input type="text" id="trustee_name" name="trustee_name" placeholder="Enter Trustee's Full Name (optional)">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="trustee_dob">Date of Birth (optional)</label>
                                <input type="text" id="trustee_dob" name="trustee_dob" placeholder="MM/DD/YYYY">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="trustee_relationship">Relationship to Applicant (optional)</label>
                                <input type="text" id="trustee_relationship" name="trustee_relationship" placeholder="Enter Relationship to Beneficiary">
                            </div>
                        </div>
                    </div>
                    
                    <div class="section-divider"></div>
                    
                    <h2>Signature</h2>
                    <div class="form-row signature-section-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="member_signature">Member's Signature</label>
                                <div class="signature-container">
                                    <canvas id="member_signature_canvas" width="400" height="200"></canvas>
                                    <input type="hidden" id="member_signature" name="member_signature">
                                    <div class="signature-buttons" style="margin-top: 10px;">
                                        <button type="button" id="clear_member_signature" class="btn btn-secondary btn-clear btn-sm"><span class="btn-icon">↻</span> Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="beneficiary_signature">Beneficiary's Signature (optional)</label>
                                <div class="signature-container">
                                    <canvas id="beneficiary_signature_canvas" width="400" height="200"></canvas>
                                    <input type="hidden" id="beneficiary_signature" name="beneficiary_signature">
                                    <div class="signature-buttons" style="margin-top: 10px;">
                                        <button type="button" id="clear_beneficiary_signature" class="btn btn-secondary btn-clear btn-sm"><span class="btn-icon">↻</span> Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="member_name">Name of Member (Borrower or Kapamilya)</label>
                                <input type="text" id="member_name" name="member_name" placeholder="Enter Full Name as Signature">
                            </div>
                        </div>
                        <div class="form-col-2">
                            <!-- Empty div to maintain 2-column layout -->
                        </div>
                    </div>
                    
                    <!-- Name of Beneficiary under signatures -->
                    <div class="form-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="sig_beneficiary_name">Name of Beneficiary (optional)</label>
                                <input type="text" id="sig_beneficiary_name" name="sig_beneficiary_name" placeholder="Enter Beneficiary Name (optional)">
                            </div>
                        </div>
                        <div class="form-col-2"></div>
                    </div>
                    
                    <div class="section-divider"></div>
                    
                    <div class="form-group disclaimer-box">
                        <div class="checkbox-item disclaimer-checkbox-container" style="align-items: flex-start;">
                            <input type="checkbox" id="disclaimer_agreement" name="disclaimer_agreement" required>
                            <label for="disclaimer_agreement" style="font-size: 14px; line-height: 1.5; padding-top: 5px;">
                                By proceeding, I acknowledge that I have read and understood the terms and conditions related to the collection, use, and storage of my personal information as provided in this TSPI Membership Form. I voluntarily agree to provide the information requested herein for the purpose of my membership application and related services. I understand that TSPI will handle my personal data in accordance with applicable data privacy laws and their internal policies.
                            </label>
                        </div>
                    </div>
                    
                    <!-- Submit button is now moved to form-navigation-controls -->
                </div> <!-- End of Page 3 -->

                <!-- Form Navigation -->
                <div class="form-navigation-controls">
                    <button type="button" id="prev_page_btn" class="btn btn-previous"><span class="btn-icon">←</span> Previous</button>
                    <div class="page-indicator" id="page_indicator">Page 1 of 3</div>
                    <button type="button" id="next_page_btn" class="btn btn-primary btn-next"><span class="btn-icon">→</span> Next</button>
                    <button type="submit" id="submit_application_btn" class="btn btn-primary btn-submit" style="display: none;" disabled><span class="btn-icon">✓</span> Submit Application</button> 
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Review Modal -->
    <div id="review-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Review Your Application</h2>
            <div id="review-content"></div>
            <div class="modal-actions">
                <button type="button" id="edit-application" class="btn btn-secondary">Edit</button>
                <button type="button" id="confirm-application" class="btn btn-primary">Confirm Submission</button>
            </div>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>

<style>
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
        border-radius: 5px;
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }
    
    .modal-actions {
        margin-top: 20px;
        text-align: right;
    }
    
    #review-content {
        margin: 20px 0;
    }
    
    .review-section {
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .review-section h3 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .review-row {
        display: flex;
        margin-bottom: 5px;
    }
    
    .review-label {
        width: 40%;
        font-weight: bold;
        padding-right: 10px;
    }
    
    .review-value {
        width: 60%;
    }
    
    /* Input with button container */
    .input-with-btn {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .input-with-btn input {
        flex: 1;
    }
    
    .input-with-btn button {
        flex-shrink: 0;
    }
    
    /* Form validation styles - modified to show red outline only after submission attempt */
    form.attempted input:invalid, 
    form.attempted select:invalid {
        border-color: #ff6666;
    }
    
    .date-error {
        color: #ff0000;
        font-size: 12px;
        margin-top: 5px;
    }
    
    /* Plan section styles - simplified */
    .available-plans-group .checkbox-group {
        padding-left: 10px;
    }
    
    /* Make all text inputs uppercase within the membership form only */
    .membership-form-container input[type="text"],
    .membership-form-container textarea,
    .membership-form-container select,
    .membership-form-container .phone-input-group input,
    .membership-form-container .beneficiaries-table input[type="text"],
    .membership-form-container .other-income-source-item input,
    .membership-form-container .other-valid-id-item input {
        text-transform: uppercase;
    }
    
    /* Tooltip styles */
    .checkbox-item {
        position: relative;
    }
    
    .checkbox-item label {
        cursor: pointer;
    }
    
    .tooltip-icon {
        display: inline-block;
        margin-left: 5px;
        color: #007bff;
        font-size: 14px;
        cursor: help;
    }
    
    @media (max-width: 768px) {
        .review-row {
            flex-direction: column;
        }
        
        .review-label, .review-value {
            width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('membership-form');
    const submitButton = document.getElementById('submit_application_btn');
    const disclaimerCheckbox = document.getElementById('disclaimer_agreement');
    
    // Variables for dynamic rows
    let beneficiaryRowCount = 1; // Start with 1 row as default
    const maxBeneficiaryRows = 5; // Maximum number of beneficiary rows (initial + 5 additional)
    let incomeSourceCount = 0;
    let otherValidIdActive = false;
    
    // Set default values for hidden fields
    document.getElementById('branch').value = null; // Set branch to null
    document.getElementById('cid_no').value = ""; // Leave empty for server-side generation
    document.getElementById('center_no').value = "000"; // Default Center No
    
    // Ensure BLIP is checked and cannot be unchecked
    const blipCheckbox = document.getElementById('plan_blip');
    if (blipCheckbox) {
        blipCheckbox.checked = true;
        blipCheckbox.setAttribute('checked', 'checked'); // Add the checked attribute for form submission
        blipCheckbox.onclick = function() {
            return false; // Prevent unchecking
        };
    }

    // Function to mark the form as attempted (for validation styling)
    const markFormAttempted = () => {
        if (form) form.classList.add('attempted');
    };
    
    // Convert all text inputs to uppercase on submit
    const uppercaseTextInputs = () => {
        document.querySelectorAll('input[type="text"], textarea').forEach(input => {
            if (input.value) {
                input.value = input.value.toUpperCase();
            }
        });
        
        // Also uppercase select values
        document.querySelectorAll('select').forEach(select => {
            if (select.value) {
                select.value = select.value.toUpperCase();
                
                // Update the select's selected option text to uppercase for display
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption) {
                    selectedOption.text = selectedOption.text.toUpperCase();
                }
            }
        });
    };
    
    if (form) {
        form.addEventListener('submit', function(event) {
            // If already confirmed (from modal), let the form submit normally
            if (form.dataset.confirmed === 'true') {
                console.log('Form confirmed, submitting...');
                form.dataset.confirmed = 'false';
                return true;
            }
            
            console.log('Form submission intercepted for validation');
            event.preventDefault(); // Prevent default submission
            event.stopImmediatePropagation(); // Prevent duplicate submit handlers
            
            // Mark form as attempted for validation styling
            markFormAttempted();
            
            // Convert to uppercase before validation
            uppercaseTextInputs();
            
            // Validate only the current page
            if (!validateCurrentPageFields()) {
                return; // Stop if validation fails
            }
            
            // Show the review modal
            showReviewModal();
        });
    }
    
    // Handle submit button based on disclaimer checkbox
    if (submitButton && disclaimerCheckbox) {
        disclaimerCheckbox.addEventListener('change', function() {
            submitButton.disabled = !this.checked;
        });
    }

    // Auto-calculate age from birthday
    const birthdayField = document.getElementById('birthday');
    const ageField = document.getElementById('age');
    
    function calculateAge(birthDateValue, targetAgeField) {
        if (birthDateValue && targetAgeField) {
            const birthDate = new Date(birthDateValue);
            if (!isNaN(birthDate.getTime())) { // Check if date is valid
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                targetAgeField.value = age >= 0 ? age : ''; // Ensure age is not negative
            } else {
                targetAgeField.value = '';
            }
        }
    }
    
    // Auto-calculate spouse age from spouse birthday
    const spouseBirthdayField = document.getElementById('spouse_birthday');
    const spouseAgeField = document.getElementById('spouse_age');
    
    if (spouseBirthdayField && spouseAgeField) {
        spouseBirthdayField.addEventListener('change', function() {
            // Create hidden field for spouse age if it doesn't exist
            let hiddenAgeField = document.getElementById('spouse_age');
            if (!hiddenAgeField) {
                hiddenAgeField = document.createElement('input');
                hiddenAgeField.type = 'hidden';
                hiddenAgeField.id = 'spouse_age';
                hiddenAgeField.name = 'spouse_age';
                this.parentNode.appendChild(hiddenAgeField);
            }
            calculateAge(this.value, hiddenAgeField);
        });
    }
    
    // Toggle visibility of spouse fields based on civil status
    const civilStatusSelect = document.getElementById('civil_status');
    const spouseInfoSection = document.getElementById('spouse_information_section');

    if (civilStatusSelect && spouseInfoSection) {
        const toggleSpouseSection = (isMarried) => {
            spouseInfoSection.style.display = isMarried ? 'block' : 'none';
            // Reset spouse fields if not married (optional)
            if (!isMarried) {
                spouseInfoSection.querySelectorAll('input, select').forEach(input => {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            }
        };

        civilStatusSelect.addEventListener('change', function() {
            toggleSpouseSection(this.value === 'Married');
        });

        // Initial check on page load
        toggleSpouseSection(civilStatusSelect.value === 'Married');
    }
    
    // Form validation before submit OR page next
    function validateCurrentPageFields() {
        // Mark form as attempted to show validation styling
        markFormAttempted();
            
        let isValid = true;
        let invalidElements = [];
        const activePage = document.querySelector('.form-page-content.active');
        if (!activePage) return true;
        const inputs = activePage.querySelectorAll('input[required], select[required], textarea[required]');
        inputs.forEach(input => {
            // Skip hidden fields with default values 
            if (input.type === 'hidden') return;
            
            const val = input.value.trim();
            if ((input.type === 'checkbox' || input.type === 'radio')) {
                if (!document.querySelector(`input[name="${input.name}"]:checked`)) {
                isValid = false;
                    invalidElements.push(input);
                }
            } else {
                if (!val) {
                isValid = false;
                    invalidElements.push(input);
                }
            }
        });
        
        if (activePage.id === 'form-page-1') {
            // BLIP is now mandatory and checked by default, so no need to check this
            const classChecked = document.querySelectorAll('input[name="classification[]"]:checked');
            if (classChecked.length === 0) {
                isValid = false;
                invalidElements.push(document.getElementById('class_borrower'));
            }
            const cellPhone = document.getElementById('cell_phone');
            const contactNo = document.getElementById('contact_no');
            if (!cellPhone.value.trim() && !contactNo.value.trim()) {
                isValid = false;
                invalidElements.push(cellPhone);
            }
        }
        
        // Beneficiary rows are now optional (removing validation for beneficiary fields)
        // Removed the specific validation for beneficiary rows that was here
        
        if (!isValid) {
            alert('Please fill out required fields.');
            if (invalidElements.length) {
                invalidElements[0].scrollIntoView({behavior:'smooth', block:'center'});
                invalidElements[0].focus();
            }
            return false;
        }
        return true;
    }

    // Review modal functionality
    const modal = document.getElementById('review-modal');
    const closeBtn = document.querySelector('.close');
    const editBtn = document.getElementById('edit-application');
    const confirmBtn = document.getElementById('confirm-application');
    
    // Close the modal when clicking the × button
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Close modal when clicking Edit button
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Submit the form when clicking Confirm button
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default action
            console.log('Confirm button clicked, preparing submission');
            
            // Convert all inputs to uppercase before final submission
            uppercaseTextInputs();
            
            // Process signature data
            const memberCanvas = document.getElementById('member_signature_canvas');
            const beneficiaryCanvas = document.getElementById('beneficiary_signature_canvas');
            
            if (memberCanvas && memberCanvas._signaturePad) {
                const memberSignature = memberCanvas._signaturePad;
                if (!memberSignature.isEmpty()) {
                    document.getElementById('member_signature').value = memberSignature.toDataURL();
                } else {
                    document.getElementById('member_signature').value = '';
                }
            }
            
            if (beneficiaryCanvas && beneficiaryCanvas._signaturePad) {
                const beneficiarySignature = beneficiaryCanvas._signaturePad;
                if (!beneficiarySignature.isEmpty()) {
                    document.getElementById('beneficiary_signature').value = beneficiarySignature.toDataURL();
                } else {
                    document.getElementById('beneficiary_signature').value = '';
                }
            }
            
            // Ensure BLIP is checked (double check)
            if (blipCheckbox && !blipCheckbox.checked) {
                blipCheckbox.checked = true;
            }
            
            try {
                // Set the confirmed flag and submit the form
                form.dataset.confirmed = 'true';
                
                // Disable the button to prevent double submission
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Submitting...';
                
                console.log('Submitting form...');
                setTimeout(function() {
                    form.submit();
                }, 100); // Small delay to ensure UI updates
            } catch (error) {
                console.error('Error during form submission:', error);
                alert('There was an error submitting the form. Please try again.');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Confirm Submission';
            }
        });
    }
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
    
    function showReviewModal() {
        const reviewContent = document.getElementById('review-content');
        reviewContent.innerHTML = '';
        
        // Personal Information section
        const personalSection = document.createElement('div');
        personalSection.className = 'review-section';
        personalSection.innerHTML = '<h3>Personal Information</h3>';
        
        // Don't show hidden fields in the review
        // addReviewRow(personalSection, 'Branch', document.getElementById('branch').value);
        // addReviewRow(personalSection, 'CID No.', document.getElementById('cid_no').value);
        // addReviewRow(personalSection, 'Center No.', document.getElementById('center_no').value);
        
        // Get selected plans (simplified without categories)
        const selectedPlans = [];
        document.querySelectorAll('input[name="plans[]"]:checked').forEach(checkbox => {
            selectedPlans.push(checkbox.value);
        });
        addReviewRow(personalSection, 'Plans', selectedPlans.join(', '));
        
        // Get selected classifications
        const selectedClassifications = [];
        document.querySelectorAll('input[name="classification[]"]:checked').forEach(checkbox => {
            selectedClassifications.push(checkbox.value);
        });
        addReviewRow(personalSection, 'Classification', selectedClassifications.join(', '));
        
        addReviewRow(personalSection, 'Last Name', document.getElementById('last_name').value);
        addReviewRow(personalSection, 'First Name', document.getElementById('first_name').value);
        addReviewRow(personalSection, 'Middle Name', document.getElementById('middle_name').value);
        addReviewRow(personalSection, 'Gender', document.getElementById('gender').value);
        addReviewRow(personalSection, 'Civil Status', document.getElementById('civil_status').value);
        addReviewRow(personalSection, 'Birthday', document.getElementById('birthday').value);
        addReviewRow(personalSection, 'Birth Place', document.getElementById('birth_place').value);
        addReviewRow(personalSection, 'Phone No.', '+63' + document.getElementById('cell_phone').value);
        addReviewRow(personalSection, 'Telephone No.', document.getElementById('contact_no').value);
        addReviewRow(personalSection, 'Nationality', document.getElementById('nationality').value);
        addReviewRow(personalSection, 'TIN/SSS/GSIS Number', document.getElementById('id_number').value);
        
        // Add mother's maiden name
        addReviewRow(personalSection, 'Mother\'s Maiden Last Name', document.getElementById('mothers_maiden_last_name').value);
        addReviewRow(personalSection, 'Mother\'s Maiden First Name', document.getElementById('mothers_maiden_first_name').value);
        addReviewRow(personalSection, 'Mother\'s Maiden Middle Name', document.getElementById('mothers_maiden_middle_name').value);
        
        reviewContent.appendChild(personalSection);
        
        // Address Information section
        const addressSection = document.createElement('div');
        addressSection.className = 'review-section';
        addressSection.innerHTML = '<h3>Address Information</h3>';
        
        addReviewRow(addressSection, 'Present Address', document.getElementById('present_address').value);
        addReviewRow(addressSection, 'Present Brgy. Code', document.getElementById('present_brgy_code').value);
        addReviewRow(addressSection, 'Present Zip Code', document.getElementById('present_zip_code').value);
        
        addReviewRow(addressSection, 'Permanent Address', document.getElementById('permanent_address').value);
        addReviewRow(addressSection, 'Permanent Brgy. Code', document.getElementById('permanent_brgy_code').value);
        addReviewRow(addressSection, 'Permanent Zip Code', document.getElementById('permanent_zip_code').value);
        
        // Home ownership
        const homeOwnership = document.querySelector('input[name="home_ownership"]:checked');
        addReviewRow(addressSection, 'Home Ownership', homeOwnership ? homeOwnership.value : '');
        addReviewRow(addressSection, 'Length of Stay (years)', document.getElementById('length_of_stay').value);
        
        reviewContent.appendChild(addressSection);
        
        // Business Information section
        const businessSection = document.createElement('div');
        businessSection.className = 'review-section';
        businessSection.innerHTML = '<h3>Business Information</h3>';
        
        addReviewRow(businessSection, 'Primary Business', document.getElementById('primary_business').value);
        addReviewRow(businessSection, 'Years in Business', document.getElementById('years_in_business').value);
        addReviewRow(businessSection, 'Business Address', document.getElementById('business_address_unit').value);
        
        // Other income sources
        const otherIncomeSources = document.querySelectorAll('.other-income-source-item input');
        otherIncomeSources.forEach((input, index) => {
            if (input.value) {
                addReviewRow(businessSection, `Other Income Source ${index + 1}`, input.value);
            }
        });
        
        reviewContent.appendChild(businessSection);
        
        // Spouse Information (if married)
        if (document.getElementById('civil_status').value === 'Married') {
            const spouseSection = document.createElement('div');
            spouseSection.className = 'review-section';
            spouseSection.innerHTML = '<h3>Spouse Information</h3>';
            
            addReviewRow(spouseSection, 'Spouse\'s Last Name', document.getElementById('spouse_last_name').value);
            addReviewRow(spouseSection, 'Spouse\'s First Name', document.getElementById('spouse_first_name').value);
            addReviewRow(spouseSection, 'Spouse\'s Middle Name', document.getElementById('spouse_middle_name').value);
            addReviewRow(spouseSection, 'Spouse\'s Birthday', document.getElementById('spouse_birthday').value);
            addReviewRow(spouseSection, 'Spouse\'s Age', document.getElementById('spouse_age').value);
            addReviewRow(spouseSection, 'Spouse\'s Occupation', document.getElementById('spouse_occupation').value);
            addReviewRow(spouseSection, 'Spouse\'s ID Number', document.getElementById('spouse_id_number').value);
            
            reviewContent.appendChild(spouseSection);
        }
        
        // Beneficiaries Information - as a TABLE
        const beneficiariesSection = document.createElement('div');
        beneficiariesSection.className = 'review-section';
        beneficiariesSection.innerHTML = '<h3>Beneficiaries</h3>';
        
        // Filter out empty beneficiary rows
        const beneficiaryRows = Array.from(document.querySelectorAll('.beneficiary-row')).filter(row => {
            const lastName = row.querySelector('input[name="beneficiary_last_name[]"]').value;
            const firstName = row.querySelector('input[name="beneficiary_first_name[]"]').value;
            return lastName || firstName;
        });
        
        if (beneficiaryRows.length > 0) {
            // Create a table to display beneficiaries
            const table = document.createElement('table');
            table.className = 'review-beneficiaries-table';
            table.style.width = '100%';
            table.style.borderCollapse = 'collapse';
            table.style.marginTop = '10px';
            
            // Create table header
            const thead = document.createElement('thead');
            thead.innerHTML = `
                <tr style="background-color: #f3f3f3; border-bottom: 1px solid #ddd;">
                    <th style="padding: 8px; text-align: left;">Name</th>
                    <th style="padding: 8px; text-align: left;">DOB</th>
                    <th style="padding: 8px; text-align: left;">Gender</th>
                    <th style="padding: 8px; text-align: left;">Relationship</th>
                    <th style="padding: 8px; text-align: center;">Dependent</th>
                </tr>
            `;
            table.appendChild(thead);
            
            // Create table body
            const tbody = document.createElement('tbody');
            beneficiaryRows.forEach((row, index) => {
                const lastName = row.querySelector('input[name="beneficiary_last_name[]"]').value;
                const firstName = row.querySelector('input[name="beneficiary_first_name[]"]').value;
                const mi = row.querySelector('input[name="beneficiary_mi[]"]').value;
                const dob = row.querySelector('input[name="beneficiary_dob[]"]').value;
                const gender = row.querySelector('select[name="beneficiary_gender[]"]').value;
                const relationship = row.querySelector('input[name="beneficiary_relationship[]"]').value;
                const dependent = row.querySelector('input[name="beneficiary_dependent[]"]') && 
                                 row.querySelector('input[name="beneficiary_dependent[]"]').checked ? 'Yes' : 'No';
                
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #eee';
                
                tr.innerHTML = `
                    <td style="padding: 8px;">${lastName}, ${firstName} ${mi}</td>
                    <td style="padding: 8px;">${dob}</td>
                    <td style="padding: 8px;">${gender}</td>
                    <td style="padding: 8px;">${relationship}</td>
                    <td style="padding: 8px; text-align: center;">${dependent}</td>
                `;
                
                tbody.appendChild(tr);
            });
            
            table.appendChild(tbody);
            beneficiariesSection.appendChild(table);
        } else {
            beneficiariesSection.innerHTML += '<p>No beneficiaries added.</p>';
        }
        
        reviewContent.appendChild(beneficiariesSection);
        
        // Trustee Information
        const trusteeSection = document.createElement('div');
        trusteeSection.className = 'review-section';
        trusteeSection.innerHTML = '<h3>Trustee Information</h3>';
        
        addReviewRow(trusteeSection, 'Trustee Name', document.getElementById('trustee_name').value);
        addReviewRow(trusteeSection, 'Trustee Date of Birth', document.getElementById('trustee_dob').value);
        addReviewRow(trusteeSection, 'Relationship to Applicant', document.getElementById('trustee_relationship').value);
        
        reviewContent.appendChild(trusteeSection);
        
        // Signature Information
        const signatureSection = document.createElement('div');
        signatureSection.className = 'review-section';
        signatureSection.innerHTML = '<h3>Signature Information</h3>';
        
        addReviewRow(signatureSection, 'Member Name', document.getElementById('member_name').value);
        addReviewRow(signatureSection, 'Beneficiary Name', document.getElementById('sig_beneficiary_name').value);
        
        reviewContent.appendChild(signatureSection);
        
        // Show the modal
        modal.style.display = 'block';
    }
    
    function addReviewRow(container, label, value) {
        if (!value) return; // Skip empty values
        
        const row = document.createElement('div');
        row.className = 'review-row';
        
        const labelDiv = document.createElement('div');
        labelDiv.className = 'review-label';
        labelDiv.textContent = label;
        
        const valueDiv = document.createElement('div');
        valueDiv.className = 'review-value';
        valueDiv.textContent = value;
        
        row.appendChild(labelDiv);
        row.appendChild(valueDiv);
        container.appendChild(row);
    }

    // Pagination Logic
    const pages = document.querySelectorAll('.form-page-content');
    const prevBtn = document.getElementById('prev_page_btn');
    const nextBtn = document.getElementById('next_page_btn');
    const pageIndicator = document.getElementById('page_indicator');
    const submitApplicationBtn = document.getElementById('submit_application_btn'); // Direct reference


    let currentPage = 0;
    const totalPages = pages.length;

    function updatePageDisplay() {
        pages.forEach((page, index) => {
            page.classList.toggle('active', index === currentPage);
        });
        if (pageIndicator) pageIndicator.textContent = `Page ${currentPage + 1} of ${totalPages}`;
        
        if (prevBtn) {
            prevBtn.style.visibility = currentPage === 0 ? 'hidden' : 'visible';
        }
        if (nextBtn) {
            nextBtn.style.display = currentPage === totalPages - 1 ? 'none' : 'inline-flex'; // use inline-flex to match .btn
        }
        if (submitApplicationBtn) {
            submitApplicationBtn.style.display = currentPage === totalPages - 1 ? 'inline-flex' : 'none'; // use inline-flex
        }
        
        const disclaimerBox = document.querySelector('.disclaimer-box');

        if (currentPage === totalPages - 1) {
            if (disclaimerBox) disclaimerBox.style.display = 'block';

             // Attempt to resize signature canvases if they are on this page and now visible
            const signatureCanvases = pages[currentPage].querySelectorAll('canvas[id*=\'_signature_canvas\']');
            signatureCanvases.forEach(canvas => {
                const pad = canvas._signaturePad; // Assuming we store the pad instance on the canvas element
                if (pad) {
                     // Call the resize function associated with this pad's canvas
                    const resizeFn = window[`resize_${canvas.id}`]; // e.g., window.resize_member_signature_canvas
                    if (typeof resizeFn === 'function') {
                        resizeFn();
                    }
                }
            });
        } else {
            if (disclaimerBox) disclaimerBox.style.display = 'none';
        }
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (validateCurrentPageFields()) {
            if (currentPage < totalPages - 1) {
                    saveFormToLocalStorage(); // Save form data before navigating
                currentPage++;
                updatePageDisplay();
                 window.scrollTo(0, 0); // Scroll to top of page
                }
            }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentPage > 0) {
                saveFormToLocalStorage(); // Save form data before navigating
                currentPage--;
                updatePageDisplay();
                window.scrollTo(0, 0); // Scroll to top of page
            }
        });
    }
    
    // Initial page setup
    updatePageDisplay();

    // Save and Load form data from localStorage
    const formToSave = document.getElementById('membership-form');
    const formElements = formToSave ? formToSave.elements : [];

    function saveFormToLocalStorage() {
        if (!formToSave) return;
        const formData = {};
        
        // Special handling for select elements including branch
        document.querySelectorAll('select').forEach(select => {
            if (select.name) {
                formData[select.name] = select.value;
            }
        });
        
        // Save all regular form fields
        for (const element of formElements) {
            // Skip saving the disclaimer agreement checkbox
            if (element.id === 'disclaimer_agreement') continue;
            
            if (element.name) {
                if (element.type === 'checkbox') {
                    if (element.name.includes('[]')) {
                        // For array checkboxes like plans[] or classification[]
                        const baseName = element.name.replace('[]', '');
                        if (!formData[baseName]) formData[baseName] = [];
                        if (element.checked) {
                            if (!formData[baseName].includes(element.value)) {
                                formData[baseName].push(element.value);
                            }
                        }
                    } else {
                        // Regular checkbox
                        formData[element.name + (element.value ? `_${element.value}` : '')] = element.checked;
                    }
                } else if (element.type === 'radio') {
                    if (element.checked) {
                        formData[element.name] = element.value;
                    }
                } else if (element.tagName === 'SELECT' && element.multiple) {
                    formData[element.name] = Array.from(element.selectedOptions).map(option => option.value);
                } else if (!formData[element.name]) { // Don't overwrite values already set
                    formData[element.name] = element.value;
                }
            }
        }
        
        // Special handling for beneficiary rows
        const beneficiaryRows = document.querySelectorAll('.beneficiary-row');
        formData.beneficiary_rows = [];
        beneficiaryRows.forEach((row, index) => {
            const rowData = {};
            row.querySelectorAll('input, select').forEach(input => {
                const fieldName = input.name.replace('[]', '');
                if (input.type === 'checkbox') {
                    rowData[fieldName] = input.checked;
                } else {
                    rowData[fieldName] = input.value;
                }
            });
            formData.beneficiary_rows.push(rowData);
        });
        
        // Save dynamic rows count
        formData['beneficiary_row_count'] = beneficiaryRowCount;
        
        // Save other income sources
        const incomeSourceInputs = document.querySelectorAll('.other-income-source-item input');
        formData.other_income_sources = [];
        incomeSourceInputs.forEach(input => {
            formData.other_income_sources.push(input.value);
        });
        formData['other_income_source_count'] = incomeSourceCount;
        
        // Save other valid IDs
        const otherValidIdInputs = document.querySelectorAll('.other-valid-id-item input');
        formData.other_valid_ids = [];
        otherValidIdInputs.forEach(input => {
            formData.other_valid_ids.push(input.value);
        });
        formData['other_valid_id_count'] = otherValidIdActive ? 1 : 0;
        
        // Make sure hidden fields are explicitly saved
        formData['branch'] = document.getElementById('branch').value || "Main Branch";
        formData['cid_no'] = document.getElementById('cid_no').value || "000000";
        formData['center_no'] = document.getElementById('center_no').value || "000";
        
        // Make sure barangay codes are explicitly saved
        formData['present_brgy_code'] = document.getElementById('present_brgy_code').value;
        formData['permanent_brgy_code'] = document.getElementById('permanent_brgy_code').value;

        localStorage.setItem('membershipFormData', JSON.stringify(formData));
        console.log('Form data saved:', formData);
    }

    function loadFormFromLocalStorage() {
        const savedData = localStorage.getItem('membershipFormData');
        if (!savedData || !formToSave) return;
        
        try {
            const formData = JSON.parse(savedData);
            console.log('Loading form data:', formData);
            
            // Restore hidden field values
            if (formData.branch) {
                document.getElementById('branch').value = formData.branch;
            }
            
            if (formData.cid_no) {
                document.getElementById('cid_no').value = formData.cid_no;
            }
            
            if (formData.center_no) {
                document.getElementById('center_no').value = formData.center_no;
            }
            
            // Restore barangay codes
            if (formData.present_brgy_code) {
                const presentBrgyCode = document.getElementById('present_brgy_code');
                if (presentBrgyCode) presentBrgyCode.value = formData.present_brgy_code;
            }
            
            if (formData.permanent_brgy_code) {
                const permanentBrgyCode = document.getElementById('permanent_brgy_code');
                if (permanentBrgyCode) permanentBrgyCode.value = formData.permanent_brgy_code;
            }
            
            // Restore simple field values
            for (const element of formElements) {
                if (element.name) {
                    // Skip select fields we've already handled
                    if (element.name === 'branch') continue;
                    
                    // Skip the disclaimer agreement checkbox - should never be remembered
                    if (element.id === 'disclaimer_agreement') continue;
                    
                        if (element.type === 'checkbox') {
                        if (element.name.includes('[]')) {
                            // Handle array checkboxes (plans, classification)
                            const baseName = element.name.replace('[]', '');
                            if (formData[baseName] && Array.isArray(formData[baseName])) {
                                element.checked = formData[baseName].includes(element.value);
                            }
                        } else {
                            // Regular checkbox
                            const key = element.name + (element.value ? `_${element.value}` : '');
                            if (key in formData) {
                                element.checked = formData[key];
                            }
                        }
                        } else if (element.type === 'radio') {
                        if (element.name in formData && element.value === formData[element.name]) {
                                element.checked = true;
                            }
                    } else if (element.name in formData) {
                        element.value = formData[element.name];
                    }
                }
            }
            
            // Handle dynamic content
            // Restore beneficiary rows
            if ('beneficiary_row_count' in formData && formData.beneficiary_row_count > 1) {
                // Add the necessary rows first - up to 5 max
                const rowsToAdd = Math.min(formData.beneficiary_row_count - 1, maxBeneficiaryRows - 1);
                for (let i = 0; i < rowsToAdd; i++) {
                    addBeneficiaryRow(); // Call the function that adds a new row
                }
                
                // Restore beneficiary data if available
                if (formData.beneficiary_rows && Array.isArray(formData.beneficiary_rows)) {
                    const rows = document.querySelectorAll('.beneficiary-row');
                    formData.beneficiary_rows.forEach((rowData, index) => {
                        if (index < rows.length) {
                            const row = rows[index];
                            for (const fieldName in rowData) {
                                const input = row.querySelector(`[name="${fieldName}[]"]`);
                                if (input) {
                                    if (input.type === 'checkbox') {
                                        input.checked = rowData[fieldName];
                        } else {
                                        input.value = rowData[fieldName];
                                    }
                                }
                            }
                        }
                    });
                }
            }
            
            // Restore other income sources
            if (formData.other_income_sources && Array.isArray(formData.other_income_sources)) {
                // Clear any existing income source fields
                document.querySelectorAll('.other-income-source-item').forEach(item => item.remove());
                incomeSourceCount = 0;
                
                // Add new ones with the saved data
                formData.other_income_sources.forEach((value, index) => {
                    if (value && index < 4) { // Limit to 4 sources
                        const newSource = addOtherIncomeSource();
                        if (newSource) {
                            const input = newSource.querySelector('input');
                            if (input) input.value = value;
                        }
                    }
                });
            }
            
            // Restore other valid IDs
            if (formData.other_valid_ids && Array.isArray(formData.other_valid_ids) && formData.other_valid_ids.length > 0) {
                // Clear any existing fields
                document.querySelectorAll('.other-valid-id-item').forEach(item => item.remove());
                otherValidIdActive = false;
                
                // Add new one with the saved data if available
                const idValue = formData.other_valid_ids[0];
                if (idValue) {
                    const newId = addOtherValidId();
                    if (newId) {
                        const input = newId.querySelector('input');
                        if (input) input.value = idValue;
                    }
                }
            }
            
            // Check if spouse fields need to be shown
            if (document.getElementById('civil_status').value === 'Married') {
                const spouseInfoSection = document.getElementById('spouse_information_section');
                if (spouseInfoSection) {
                    spouseInfoSection.style.display = 'block';
                }
            }
            
            console.log('Form data loaded from localStorage');
        } catch (e) {
            console.error('Error loading form data:', e);
        }
    }

    // Call saveFormToLocalStorage on form changes
    if (formToSave) {
        formToSave.addEventListener('input', saveFormToLocalStorage);
        // Also save for select changes
        const selects = formToSave.querySelectorAll('select');
        selects.forEach(select => select.addEventListener('change', saveFormToLocalStorage));
        
        // Load saved data on page load
        loadFormFromLocalStorage();
        // Re-trigger spouse section toggle in case civil status was loaded as Married
        if (civilStatusSelect) {
            civilStatusSelect.dispatchEvent(new Event('change'));
        }
    }

    // Other income sources management
    const addOtherIncomeSourceBtn = document.getElementById('add_other_income_source_btn');
    const otherIncomeSourcesContainer = document.getElementById('other_income_sources_container');
    
    function addOtherIncomeSource() {
        if (incomeSourceCount >= 4) {
            alert('Maximum 4 additional income sources allowed.');
            return;
        }
        
        incomeSourceCount++;
        const sourceNumber = incomeSourceCount;
        
        const sourceContainer = document.createElement('div');
        sourceContainer.className = 'form-group other-income-source-item';
        sourceContainer.style.marginTop = '10px';
        
        sourceContainer.innerHTML = `
            <div class="input-with-btn">
                <input type="text" id="other_income_source_${sourceNumber}" name="other_income_source_${sourceNumber}" placeholder="Enter Other Income Source">
                <button type="button" class="btn btn-danger btn-sm remove-income-source">✕</button>
            </div>
        `;
        
        otherIncomeSourcesContainer.appendChild(sourceContainer);
        
        // Add remove event listener
        const removeBtn = sourceContainer.querySelector('.remove-income-source');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                sourceContainer.remove();
                // We need to re-index and update remaining fields
                updateIncomeSourceIndices();
                saveFormToLocalStorage(); // Update localStorage after removing
            });
        }
        
        saveFormToLocalStorage(); // Save the new state
        return sourceContainer;
    }
    
    // Function to reindex income sources after removal
    function updateIncomeSourceIndices() {
        const sources = document.querySelectorAll('.other-income-source-item');
        
        // Count how many are left
        incomeSourceCount = sources.length;
        
        // Update IDs and names
        sources.forEach((source, index) => {
            const input = source.querySelector('input');
            if (input) {
                input.id = `other_income_source_${index + 1}`;
                input.name = `other_income_source_${index + 1}`;
            }
        });
    }
    
    // Add other income source button click handler
    if (addOtherIncomeSourceBtn) {
        addOtherIncomeSourceBtn.addEventListener('click', addOtherIncomeSource);
    }
    
    // Other valid ID management
    const addOtherValidIdBtn = document.getElementById('add_other_valid_id_btn');
    const otherValidIdsContainer = document.getElementById('other_valid_ids_container');
    
    function addOtherValidId() {
        if (otherValidIdActive) {
            alert('Maximum 1 additional valid ID allowed.');
            return null;
        }
        
        otherValidIdActive = true;
        
        const idContainer = document.createElement('div');
        idContainer.className = 'form-group other-valid-id-item';
        idContainer.style.marginBottom = '10px';
        
        idContainer.innerHTML = `
            <div class="input-with-btn">
                <input type="text" id="other_valid_id_1" name="other_valid_id" placeholder="Enter Other Valid ID">
                <button type="button" class="btn btn-danger btn-sm remove-valid-id">✕</button>
            </div>
        `;
        
        otherValidIdsContainer.appendChild(idContainer);
        
        // Add remove event listener
        const removeBtn = idContainer.querySelector('.remove-valid-id');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                idContainer.remove();
                otherValidIdActive = false;
                saveFormToLocalStorage(); // Update localStorage after removing
            });
        }
        
        saveFormToLocalStorage(); // Save the new state
        return idContainer;
    }
    
    // Add other valid ID button click handler
    if (addOtherValidIdBtn) {
        addOtherValidIdBtn.addEventListener('click', function() {
            addOtherValidId();
        });
    }
    
    // Initialize date pickers for all date fields
    function initDatepickers() {
        if (typeof Pikaday !== 'undefined') {
            // Common date validation function
            const validateDate = function(field) {
                field.addEventListener('blur', function() {
                    const dateValue = this.value.trim();
                    if (dateValue && !/^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/\d{4}$/.test(dateValue)) {
                        this.setCustomValidity('Please enter a valid date in MM/DD/YYYY format');
                        
                        // Add visual error indication
                        this.style.borderColor = 'red';
                        
                        // Show error message near the field
                        let errorMsg = this.nextElementSibling;
                        if (!errorMsg || !errorMsg.classList.contains('date-error')) {
                            errorMsg = document.createElement('div');
                            errorMsg.className = 'date-error';
                            errorMsg.style.color = 'red';
                            errorMsg.style.fontSize = '12px';
                            errorMsg.style.marginTop = '5px';
                            this.parentNode.appendChild(errorMsg);
                        }
                        errorMsg.textContent = 'Please enter a valid date in MM/DD/YYYY format';
                    } else {
                        this.setCustomValidity('');
                        this.style.borderColor = '';
                        
                        // Remove error message if it exists
                        const errorMsg = this.nextElementSibling;
                        if (errorMsg && errorMsg.classList.contains('date-error')) {
                            errorMsg.textContent = '';
                        }
                    }
                });
            };
            
            // Birthday field
            if (document.getElementById('birthday')) {
                const birthdayField = document.getElementById('birthday');
                new Pikaday({
                    field: birthdayField,
                    format: 'MM/DD/YYYY',
                    yearRange: [1900, new Date().getFullYear()],
                    maxDate: new Date(),
                    toString(date, format) {
                        // Format the date as MM/DD/YYYY
                        const month = (date.getMonth() + 1).toString().padStart(2, '0');
                        const day = date.getDate().toString().padStart(2, '0');
                        const year = date.getFullYear();
                        return `${month}/${day}/${year}`;
                    },
                    onSelect: function() {
                        birthdayField.setCustomValidity('');
                        birthdayField.style.borderColor = '';
                        
                        // Remove error message if it exists
                        const errorMsg = birthdayField.nextElementSibling;
                        if (errorMsg && errorMsg.classList.contains('date-error')) {
                            errorMsg.textContent = '';
                        }
                    }
                });
                validateDate(birthdayField);
            }
            
            // Spouse birthday field
            if (document.getElementById('spouse_birthday')) {
                const spouseBirthdayField = document.getElementById('spouse_birthday');
                new Pikaday({
                    field: spouseBirthdayField,
                    format: 'MM/DD/YYYY',
                    yearRange: [1900, new Date().getFullYear()],
                    maxDate: new Date(),
                    toString(date, format) {
                        const month = (date.getMonth() + 1).toString().padStart(2, '0');
                        const day = date.getDate().toString().padStart(2, '0');
                        const year = date.getFullYear();
                        return `${month}/${day}/${year}`;
                    },
                    onSelect: function() {
                        spouseBirthdayField.setCustomValidity('');
                        spouseBirthdayField.style.borderColor = '';
                        const errorMsg = spouseBirthdayField.nextElementSibling;
                        if (errorMsg && errorMsg.classList.contains('date-error')) {
                            errorMsg.textContent = '';
                        }
                    }
                });
                validateDate(spouseBirthdayField);
            }
        }
    }
    
    // Initialize all date pickers
    initDatepickers();
    
    // Initialize signature pads if elements exist
    if (typeof SignaturePad !== 'undefined') {
        // Member signature
        const memberCanvas = document.getElementById('member_signature_canvas');
        const memberSignatureInput = document.getElementById('member_signature');
        
        if (memberCanvas) {
            const memberPad = new SignaturePad(memberCanvas, {
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                penColor: 'black'
            });
            memberCanvas._signaturePad = memberPad; // Store reference for later
            
            // If there's saved signature data, restore it
            if (memberSignatureInput && memberSignatureInput.value) {
                memberPad.fromDataURL(memberSignatureInput.value);
            }
            
            // Clear button
            const clearMemberBtn = document.getElementById('clear_member_signature');
            if (clearMemberBtn) {
                clearMemberBtn.addEventListener('click', function() {
                    memberPad.clear();
                    if (memberSignatureInput) memberSignatureInput.value = '';
                    saveFormToLocalStorage();
                });
            }
            
            // On end event - save signature data
            memberPad.addEventListener('endStroke', function() {
                if (memberSignatureInput) memberSignatureInput.value = memberPad.toDataURL();
                saveFormToLocalStorage();
            });
            
            // Resize function
            window.resize_member_signature_canvas = function() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const savedData = memberPad.toDataURL();
                
                memberCanvas.width = memberCanvas.offsetWidth * ratio;
                memberCanvas.height = memberCanvas.offsetHeight * ratio;
                memberCanvas.getContext('2d').scale(ratio, ratio);
                
                // Restore the signature after resize
                if (savedData) {
                    memberPad.fromDataURL(savedData);
                } else {
                    memberPad.clear();
                }
            };
            
            window.addEventListener('resize', window.resize_member_signature_canvas);
            window.resize_member_signature_canvas(); // Call once on init
        }
        
        // Beneficiary signature
        const beneficiaryCanvas = document.getElementById('beneficiary_signature_canvas');
        const beneficiarySignatureInput = document.getElementById('beneficiary_signature');
        
        if (beneficiaryCanvas) {
            const beneficiaryPad = new SignaturePad(beneficiaryCanvas, {
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                penColor: 'black'
            });
            beneficiaryCanvas._signaturePad = beneficiaryPad; // Store reference for later
            
            // If there's saved signature data, restore it
            if (beneficiarySignatureInput && beneficiarySignatureInput.value) {
                beneficiaryPad.fromDataURL(beneficiarySignatureInput.value);
            }
            
            // Clear button
            const clearBeneficiaryBtn = document.getElementById('clear_beneficiary_signature');
            if (clearBeneficiaryBtn) {
                clearBeneficiaryBtn.addEventListener('click', function() {
                    beneficiaryPad.clear();
                    if (beneficiarySignatureInput) beneficiarySignatureInput.value = '';
                    saveFormToLocalStorage();
                });
            }
            
            // On end event - save signature data
            beneficiaryPad.addEventListener('endStroke', function() {
                if (beneficiarySignatureInput) beneficiarySignatureInput.value = beneficiaryPad.toDataURL();
                saveFormToLocalStorage();
            });
            
            // Resize function
            window.resize_beneficiary_signature_canvas = function() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const savedData = beneficiaryPad.toDataURL();
                
                beneficiaryCanvas.width = beneficiaryCanvas.offsetWidth * ratio;
                beneficiaryCanvas.height = beneficiaryCanvas.offsetHeight * ratio;
                beneficiaryCanvas.getContext('2d').scale(ratio, ratio);
                
                // Restore the signature after resize
                if (savedData) {
                    beneficiaryPad.fromDataURL(savedData);
                } else {
                    beneficiaryPad.clear();
                }
            };
            
            window.addEventListener('resize', window.resize_beneficiary_signature_canvas);
            window.resize_beneficiary_signature_canvas(); // Call once on init
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?> 

