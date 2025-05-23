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
        $stmt = $pdo->prepare("SELECT id, status FROM members_information WHERE fk_user_id = ?");
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
    
    // Explicitly get the email from the logged-in user session and ensure it's set
    $email = '';
    if ($user && isset($user['email'])) {
        $email = sanitize($user['email']);
        // Add a log entry for debugging
        error_log("User email retrieved for database insertion: " . $email);
    } else {
        // Log if we couldn't find the email
        error_log("Warning: Could not retrieve user email for membership form submission");
    }
    
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
    $spouse_age = null;
    if (isset($_POST['civil_status']) && $_POST['civil_status'] === 'Married') {
        $spouse_name = sanitize(
            trim(
                ($_POST['spouse_first_name'] ?? '') . ' ' .
                ($_POST['spouse_middle_name'] ?? '') . ' ' .
                ($_POST['spouse_last_name'] ?? '')
            )
        );
        $spouse_birthdate = formatMembershipDate($_POST['spouse_birthday']);
        
        // Calculate spouse age if spouse birthdate is provided
        if ($spouse_birthdate) {
            $spouseBirthdateObj = DateTime::createFromFormat('Y-m-d', $spouse_birthdate);
            $spouse_age = $spouseBirthdateObj->diff(new DateTime('today'))->y;
        }
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
            'fk_user_id','branch','cid_no','center_no','plans','classification',
            'first_name','middle_name','last_name','gender','civil_status',
            'birthdate','age','birth_place','email','cell_phone','contact_no','nationality',
            'id_number','other_valid_ids','mothers_maiden_last_name','mothers_maiden_first_name','mothers_maiden_middle_name',
            'present_address','present_brgy_code','present_zip_code',
            'permanent_address','permanent_brgy_code','permanent_zip_code',
            'home_ownership','length_of_stay','primary_business','years_in_business','business_address',
            'other_income_source_1','other_income_source_2','other_income_source_3','other_income_source_4',
            'spouse_name','spouse_birthdate','spouse_occupation','spouse_id_number','spouse_age',
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
        $classificationJson = isset($_POST['classification']) ? json_encode([$_POST['classification']]) : null;
        $otherValidIdsJson = isset($_POST['other_valid_id']) ? json_encode($_POST['other_valid_id']) : null;
        
        // Get values for mother's maiden name that weren't initialized before
        $mothers_maiden_last_name = sanitize($_POST['mothers_maiden_last_name'] ?? '');
        $mothers_maiden_first_name = sanitize($_POST['mothers_maiden_first_name'] ?? '');
        $mothers_maiden_middle_name = sanitize($_POST['mothers_maiden_middle_name'] ?? '');
        
        // Create an array of all values to pass to execute()
        $params = [
            $user_id,                                             // fk_user_id
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
            $email,                                               // email - ensure this is the logged-in user's email
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
            $spouse_age,                                          // spouse_age
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

        // Double-check that the email parameter is set correctly before executing the query
        if (empty($params[13])) { // Index 13 corresponds to the email parameter
            // If somehow the email was lost in the parameters, try to get it again
            $currentUser = get_logged_in_user();
            if ($currentUser && isset($currentUser['email'])) {
                $params[13] = sanitize($currentUser['email']);
                error_log("Email parameter was empty, reset to: " . $params[13]);
            }
        }

        // Log the email that will be inserted
        error_log("Email being inserted into database: " . $params[13]);
            
        $stmt->execute($params);
        $success = true;
    } catch (Exception $e) {
        $errors[] = 'Submission error: ' . $e->getMessage();
    }
}

include '../includes/header.php';
?>
<!-- User Agreement Modal -->
<div id="user-agreement-modal" class="agreement-modal">
    <div class="agreement-modal-content">
        <h2>User Agreement</h2>
        
        <div class="agreement-section">
            <h3>PROOF OF HEALTH CONDITION</h3>
            <p>I declare that I am currently in good health to the best of my knowledge. I have no physical disabilities or any defects. I have not been hospitalized in the last year and do not expect to be hospitalized in the near future for any illness. I declare that I have not suffered from any illness requiring treatment for one week or more, and I have not undergone any surgery, accident, or injury in the past year.</p>
        </div>
        
        <div class="agreement-section">
            <h3>DECLARATION OF TRUTH</h3>
            <p>I declare and affirm that the information provided in this application is true and correct. I agree that the information I have entered is part of my insurance contract with TSPI MBAI. Any false and incorrect information entered here may lead to the cancellation of insurance and membership with TSPI MBAI. In this event, I understand that TSPI MBAI will not be liable for any benefits due to me, except for the return of the amount paid for the insurance.</p>
        </div>
        
        <div class="agreement-section">
            <h3>CONSENT UNDER THE DATA PRIVACY ACT</h3>
            <p>In accordance with the Data Privacy Act, I hereby give consent to TSPI and TSPI MBAI to collect, store, use, or process within the country my recorded personal data. I give consent for my personal data to be shared by TSPI and TSPI MBAI with its business partners, as well as its service providers, so that they can provide quality services, and for other legitimate purposes consistent with these services.</p>
        </div>
        
        <div class="agreement-section">
            <h3>PROOF OF MEMBER CONSENT AND AGREEMENT TO TSPI MBAI POLICIES</h3>
            <p>As a member, I understand and agree to the following:</p>
            <p><strong>A-</strong> TSPI MBAI has explained to me the importance of having Micro Insurance with contributions/premiums of:</p>
            <ul>
                <li>P5.00 per week or P240.00 per year for the <strong>BASIC LIFE INSURANCE PLAN (BLIP)</strong>;</li>
                <li>P1.00 per thousand per week of my borrowed amount for the <strong>CREDIT LIFE INSURANCE PLAN (CLIP)</strong>; or</li>
                <li>P10 per thousand per year of my borrowed amount for <strong>MORTGAGE REDEMPTION INSURANCE (MRI)</strong>.</li>
            </ul>
            <p><strong>B-</strong> That if the cause of death is a serious illness or Pre-Existing Condition (PEC) in the first year of membership, only the amount paid for Micro Insurance will be refunded.</p>
            <p><strong>C-</strong> It has also been explained to me that microinsurance at TSPI MBAI is for members aged 18 to 60 years old, renewable until age 65. However, the BLIP benefit will be halved when the member's age is 61 to 65 years old.</p>
            <p><strong>D-</strong> That I and my legitimate relatives are covered by a <strong>one (1) year contestability period</strong>. If I leave the program and decide to return to TSPI, I and my legitimate relatives will again be covered by the one (1) year contestability period.</p>
            <p><strong>E-</strong> That my primary beneficiary for CLIP / MRI is TSPI (A Microfinance NGO) and the secondary beneficiaries are those stated in this form.</p>
        </div>
        
        <div class="agreement-section">
            <h3>1. Proof of Consent for Premium Collection (for Borrower only)</h3>
            <p>This is to authorize TSPI MBAI to charge TULAY SA PAG-UNLAD, INC. (TSPI) the corresponding premium amount for BLIP and CLIP by deducting it from my borrowed amount (loan proceeds) or from my Capital Build Up (CBU) if the premium is not included in my regular amortization. I understand that when I make payments to TSPI (weekly, bi-monthly, or monthly), my Micro Insurance premium is paid first before the interest and the principal loan amount.</p>
        </div>
        
        <div class="agreement-section">
            <h3>2. Proof of Agreement to Grace Period for Contribution/Premium Payment</h3>
            <p>I understand the following policies for the 45-day grace period for insurance payments:</p>
            <p><strong>a. BLIP</strong></p>
            <ul>
                <li>In case I am unable to pay my contribution for BLIP, I have a <strong>45-day grace period</strong> for my insurance coverage to continue.</li>
                <li>If I fail to pay my contribution for BLIP or renew after the 45-day grace period, my life plus and life max, if any, will become void.</li>
            </ul>
            <p><strong>b. CLIP/MRI</strong></p>
            <ul>
                <li>In case I am unable to pay my contribution for CLIP/MRI until the loan maturity, I have a <strong>45-day grace period</strong> for my insurance coverage to continue.</li>
                <li>The end or maturity date of my loan is the end of my CLIP/MRI coverage. I understand that there is no grace period for this.</li>
            </ul>
            <p>I understand that the insurance coverage for CLIP or MRI will start on the day I obtain a loan from TSPI and will end on the maturity date of my loan. Therefore, if I decide to take a break from borrowing or undergo a resting period, I will no longer have insurance coverage for CLIP or MRI, and I will no longer have to pay contributions for it.</p>
        </div>
        
        <div class="agreement-section">
            <h3>3. Proof of Consent for Return of Equity Value</h3>
            <p>In case my coverage ends or lapses on any date, I authorize TSPI MBAI to:</p>
            <ol>
                <li>Transfer my <strong>Equity Value</strong> to my CBU or to the CBU of my recruiter at TSPI (A Microfinance NGO) to pay for my remaining debt or the debt of my Recruiter, ________________________________, at TSPI.</li>
                <li>If neither I nor my Recruiter has any debt, transfer my EV to my CBU or to my Recruiter's CBU to be added to my/his/her savings.</li>
            </ol>
        </div>
        
        <div class="agreement-beneficiary-question">
            <h3>Would you like to add beneficiaries/trustees?</h3>
            <div class="radio-options">
                <label><input type="radio" name="add_beneficiary" value="yes"> Yes</label>
                <label><input type="radio" name="add_beneficiary" value="no" checked> No</label>
            </div>
            
            <div id="beneficiary-count-container" class="beneficiary-count-wrapper">
                <label for="beneficiary-count">How many beneficiaries would you like to add? (1-5)</label>
                <input type="number" id="beneficiary-count" min="1" max="5" value="1" oninput="validateBeneficiaryCount(this)">
            </div>
        </div>
        
        <div class="agreement-actions">
            <button id="agree-button" class="btn btn-primary">I Agree</button>
            <button id="disagree-button" class="btn btn-secondary">I Disagree</button>
        </div>
    </div>
</div>

<!-- Page Overlay to prevent interaction -->
<div id="page-overlay" class="page-overlay"></div>

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
                                <input type="radio" id="class_tkp" name="classification" value="TKP" title="TKP borrower classification - For individual borrowers">
                                <label for="class_tkp">TKP (Borrower) <span class="tooltip-icon" title="TKP borrower classification - For individual borrowers">ⓘ</span></label>
                            </div>
                            <div class="checkbox-item">
                                <input type="radio" id="class_tpp" name="classification" value="TPP" title="TPP borrower classification - For business or partnership borrowers">
                                <label for="class_tpp">TPP (Borrower) <span class="tooltip-icon" title="TPP borrower classification - For business or partnership borrowers">ⓘ</span></label>
                        </div>

                            <div class="checkbox-item">
                                <input type="radio" id="class_borrower" name="classification" value="Kapamilya" title="Kapamilya classification - For family members of borrowers">
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
                                <input type="text" id="last_name" name="last_name" required placeholder="Enter Last Name" autocomplete="family-name">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" required placeholder="Enter First Name" autocomplete="given-name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" placeholder="Enter Middle Name" required autocomplete="additional-name">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" required autocomplete="sex">
                                    <option value="" selected>Select Gender</option>
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
                                    <option value="" selected>Select Civil Status</option>
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
                                <input type="text" id="birthday" name="birthday" required placeholder="MM/DD/YYYY" autocomplete="bday">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="birth_place">Birth Place</label>
                                <input type="text" id="birth_place" name="birth_place" required placeholder="Enter Place of Birth" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="cell_phone">Phone no./ SIM</label>
                                <div class="phone-input-group with-flag">
                                    <span class="phone-prefix"><span class="country-flag">🇵🇭</span>+63</span>
                                    <input type="text" id="cell_phone" name="cell_phone" required pattern="[0-9]{10}" maxlength="10" title="10-digit mobile number (e.g., 917xxxxxxx)" autocomplete="tel-national">
                                </div>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="contact_no">Telephone no./ Landline</label>
                                <input type="text" id="contact_no" name="contact_no" pattern="[0-9]{7}" maxlength="8" title="8-digit landline number" placeholder="Enter Landline Number" autocomplete="tel-local">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="nationality">Nationality</label>
                                <input type="text" id="nationality" name="nationality" required placeholder="Enter Nationality" autocomplete="country-name">
                            </div>
                        </div>
                       
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="id_number">TIN/SSS/GSIS Number</label>
                                <input type="text" id="id_number" name="id_number" required placeholder="Enter Valid ID Number" autocomplete="off">
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
                                <input type="text" id="mothers_maiden_last_name" name="mothers_maiden_last_name" required placeholder="Enter Mother's Maiden Last Name" autocomplete="family-name">
                            </div>
                        </div>
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="mothers_maiden_first_name">Mother's Maiden First Name</label>
                                <input type="text" id="mothers_maiden_first_name" name="mothers_maiden_first_name" required placeholder="Enter Mother's Maiden First Name" autocomplete="given-name">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="mothers_maiden_middle_name">Mother's Maiden Middle Name</label>
                                <input type="text" id="mothers_maiden_middle_name" name="mothers_maiden_middle_name" placeholder="Enter Mother's Maiden Middle Name" required autocomplete="additional-name">
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
        <input type="text" id="present_address" name="present_address" placeholder="Unit No., Street, Brgy., City" required autocomplete="address-line1">
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                <label for="present_brgy_code">Brgy. Code</label>
                <input type="text" id="present_brgy_code" name="present_brgy_code" placeholder="Enter Brgy. Code" required autocomplete="off">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="present_zip_code">Zip Code</label>
                                <input type="text" id="present_zip_code" name="present_zip_code" required placeholder="Enter ZIP Code" autocomplete="postal-code">
                            </div>
                        </div>
                    </div>
     
         <!-- Hidden fields removed -->

                    <div class="section-divider"></div>

                    <!-- Permanent Address -->
                    <h2>Permanent Address</h2>
                    <div class="form-group">
                        <label for="permanent_address">Unit / Address</label>
                        <input type="text" id="permanent_address" name="permanent_address" placeholder="Unit No., Street, Brgy., City" required autocomplete="address-line1">
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="permanent_brgy_code">Brgy. Code</label>
                                <input type="text" id="permanent_brgy_code" name="permanent_brgy_code" placeholder="Enter Brgy. Code" required autocomplete="off">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="permanent_zip_code">Zip Code</label>
                                <input type="text" id="permanent_zip_code" name="permanent_zip_code" required placeholder="Enter ZIP Code" autocomplete="postal-code">
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
                        <input type="text" id="business_address_unit" name="business_address_unit" placeholder="Unit No., Street, Brgy., City" required autocomplete="street-address">
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
                                    <input type="text" id="spouse_last_name" name="spouse_last_name" placeholder="Enter Spouse's Last Name" autocomplete="family-name">
                                </div>
                            </div>
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_first_name">Spouse's First Name</label>
                                    <input type="text" id="spouse_first_name" name="spouse_first_name" placeholder="Enter Spouse's First Name" autocomplete="given-name">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_middle_name">Spouse's Middle Name</label>
                                    <input type="text" id="spouse_middle_name" name="spouse_middle_name" placeholder="Enter Spouse's Middle Name" autocomplete="additional-name">
                                </div>
                            </div>
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_birthday">Birthday (mm/dd/yyyy)</label>
                                    <input type="text" id="spouse_birthday" name="spouse_birthday" placeholder="MM/DD/YYYY" autocomplete="bday">
                                    <!-- Age is now calculated but not displayed as a field -->
                                    <input type="hidden" id="spouse_age" name="spouse_age">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_occupation">Occupation</label>
                                    <input type="text" id="spouse_occupation" name="spouse_occupation" placeholder="Enter Spouse's Occupation" autocomplete="organization-title">
                                </div>
                            </div>
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_id_number">TIN/SSS/GSIS/Valid ID</label>
                                    <input type="text" id="spouse_id_number" name="spouse_id_number" placeholder="Enter Spouse's ID Number" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden spouse age field to store calculated age -->
                        <input type="hidden" id="spouse_age" name="spouse_age">
                     
                    </div>
                </div> <!-- End of Page 2 -->

                <div class="form-page-content" id="form-page-3">
                    <div id="beneficiaries-section">
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
                                <td><input type="text" id="beneficiary_last_name_<?php echo $i; ?>" name="beneficiary_last_name[]" placeholder="Last Name"></td>
                                <td><input type="text" id="beneficiary_first_name_<?php echo $i; ?>" name="beneficiary_first_name[]" placeholder="First Name"></td>
                                <td><input type="text" id="beneficiary_mi_<?php echo $i; ?>" name="beneficiary_mi[]" maxlength="1" placeholder="MI"></td>
                                <td><input type="text" id="beneficiary_dob_<?php echo $i; ?>" name="beneficiary_dob[]" class="beneficiary-dob" placeholder="MM/DD/YYYY"></td>
                                <td><select id="beneficiary_gender_<?php echo $i; ?>" name="beneficiary_gender[]">
                                    <option value="" selected>Select</option>
                                    <option value="M">M</option>
                                    <option value="F">F</option>
                                </select></td>
                                <td><input type="text" id="beneficiary_relationship_<?php echo $i; ?>" name="beneficiary_relationship[]" placeholder="Relationship"></td>
                                <td style="text-align:center;"><input type="checkbox" id="beneficiary_dependent_<?php echo $i; ?>" name="beneficiary_dependent[]" value="1"></td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    
                    <div class="section-divider"></div>
                    </div>
                    
                    <div id="trustee-section">
                    <h2>Designation of Trustee</h2>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="trustee_name">Name of Trustee</label>
                                <input type="text" id="trustee_name" name="trustee_name" placeholder="Enter Trustee's Full Name">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="trustee_dob">Date of Birth</label>
                                <input type="text" id="trustee_dob" name="trustee_dob" placeholder="MM/DD/YYYY">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="trustee_relationship">Relationship to Applicant</label>
                                <input type="text" id="trustee_relationship" name="trustee_relationship" placeholder="Enter Relationship to Beneficiary">
                            </div>
                        </div>
                    </div>
                    
                    <div class="section-divider"></div>
                    </div>
                    
                    <h2>Signature</h2>
                    <div class="form-row signature-section-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="member_signature">Member's Signature</label>
                                <div class="signature-container">
                                    <canvas id="member_signature_canvas" width="400" height="200"></canvas>
                                    <input type="hidden" id="member_signature" name="member_signature">
                                    <div class="signature-buttons">
                                        <button type="button" id="clear_member_signature" class="btn btn-secondary btn-clear btn-sm"><span class="btn-icon">↻</span> Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-col-2">
                            <div id="beneficiary-signature-section" class="form-group">
                                <label for="beneficiary_signature">Beneficiary's Signature</label>
                                <div class="signature-container">
                                    <canvas id="beneficiary_signature_canvas" width="400" height="200"></canvas>
                                    <input type="hidden" id="beneficiary_signature" name="beneficiary_signature">
                                    <div class="signature-buttons">
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
                                <input type="text" id="member_name" name="member_name" placeholder="Enter Full Name as Signature" required>
                            </div>
                        </div>
                        <div class="form-col-2"></div>
                    </div>
                    
                    <!-- Name of Beneficiary under signatures -->
                    <div id="beneficiary-name-field" class="form-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="sig_beneficiary_name">Name of Beneficiary</label>
                                <input type="text" id="sig_beneficiary_name" name="sig_beneficiary_name" placeholder="Enter Beneficiary Name">
                            </div>
                        </div>
                        <div class="form-col-2"></div>
                    </div>
                    
                    <div class="section-divider"></div>
                    
                    <div class="form-group disclaimer-box">
                        <div class="checkbox-item disclaimer-checkbox-container">
                            <input type="checkbox" id="disclaimer_agreement" name="disclaimer_agreement" required>
                            <label for="disclaimer_agreement" class="disclaimer-text">
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
                    <button type="button" id="submit_application_btn" class="btn btn-primary btn-submit" style="display: none;" disabled data-preserve-form="true"><span class="btn-icon">✓</span> Submit Application</button> 
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

<!-- Add membership form JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
<script src="../assets/js/membership-form.js"></script>

<!-- Hidden field for SITE_URL -->
<input type="hidden" name="site_url" value="<?php echo SITE_URL; ?>">

<?php include '../includes/footer.php'; ?> 

