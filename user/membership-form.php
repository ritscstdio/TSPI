<?php
$page_title = "Membership Form";
$body_class = "membership-form-page";
require_once '../includes/config.php';

// Require user to be logged in
if (!is_logged_in()) {
    $_SESSION['message'] = "You must be logged in to access the membership form.";
    redirect('/user/login.php');
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data
    // Form validation will be implemented here
    
    // If validation passes, save data
    // This will be implemented later
}

include '../includes/header.php';
?>
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
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="branch">Branch</label>
                                <select id="branch" name="branch" required>
                                    <option value="" disabled selected>Select Branch</option>
                                    <?php
                                    // Fetch branches from the database
                                    $branch_query = "SELECT id, branch FROM branches ORDER BY branch";
                                    $branch_result = mysqli_query($conn, $branch_query);
                                    
                                    if ($branch_result && mysqli_num_rows($branch_result) > 0) {
                                        while ($branch_row = mysqli_fetch_assoc($branch_result)) {
                                            echo '<option value="' . $branch_row['branch'] . '">' . $branch_row['branch'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="cid_no">CID No.</label>
                                <input type="text" id="cid_no" name="cid_no" required pattern="[0-9]*" inputmode="numeric" placeholder="Enter Client ID Number">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="center_no">Center No. (for fillers)</label>
                                <input type="text" id="center_no" name="center_no" placeholder="Enter Center Number">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group available-plans-group">
                        <label>Available Plans</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="plan_blip" name="plans[]" value="BLIP">
                                <label for="plan_blip">Basic Life (BLIP)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="plan_lpip" name="plans[]" value="LPIP">
                                <label for="plan_lpip">Life Plus (LPIP)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="plan_lmip" name="plans[]" value="LMIP">
                                <label for="plan_lmip">Life Max (LMIP)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="plan_clip" name="plans[]" value="CLIP">
                                <label for="plan_clip">Credit Life (CLIP)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="plan_mri" name="plans[]" value="MRI">
                                <label for="plan_mri">Mortgage Redemption (MRI)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="plan_glip" name="plans[]" value="GLIP">
                                <label for="plan_glip">Golden Life (GLIP)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Member Classification</label>
                        <div class="checkbox-group">
                             <div class="checkbox-item">
                                <input type="checkbox" id="class_borrower" name="classification[]" value="Borrower">
                                <label for="class_borrower">Borrower</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="class_tkp" name="classification[]" value="TKP Kapamilya">
                                <label for="class_tkp">TKP Kapamilya</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="class_tmp" name="classification[]" value="TMP">
                                <label for="class_tmp">TMP</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="class_tpp" name="classification[]" value="TPP">
                                <label for="class_tpp">TPP</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="class_og" name="classification[]" value="OG">
                                <label for="class_og">OG</label>
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
                                <label for="age">Age</label>
                                <input type="number" id="age" name="age" min="18" max="100" required readonly>
                            </div>
                        </div>
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
                                <div class="phone-input-group">
                                    <span class="phone-prefix">+63</span>
                                    <input type="text" id="contact_no" name="contact_no" required pattern="[0-9]{7}" maxlength="7" title="7-digit landline number">
                                </div>
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
                                <button type="button" id="add_other_valid_id_btn" class="btn btn-secondary btn-sm" style="margin-top: 8px;">Do you have other Valid IDs?</button>
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

                    <div class="section-divider"></div>
                </div> <!-- End of Page 1 -->

                <div class="form-page-content" id="form-page-2">
                    <h2>Present Address</h2>
                     <div class="form-group">
                        <label for="present_address">Unit / Address</label>
                        <input type="text" id="present_address" name="present_address" placeholder="Unit No., Building, Street Name" required>
                        <!-- Hidden text fields for address selector -->
                        <input type="hidden" name="present_region_text" id="present_region_text">
                        <input type="hidden" name="present_province_text" id="present_province_text">
                        <input type="hidden" name="present_city_text" id="present_city_text">
                        <input type="hidden" name="present_barangay_text" id="present_barangay_text">
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="present_region">Region</label>
                                <select id="present_region" name="present_region" class="form-control address-region" required></select>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="present_province">Province</label>
                                <select id="present_province" name="present_province" class="form-control address-province" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="present_city">City/Municipality</label>
                                <select id="present_city" name="present_city" class="form-control address-city" required></select>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="present_barangay">Barangay</label>
                                <select id="present_barangay" name="present_barangay" class="form-control address-barangay" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="present_zip_code">Zip Code</label>
                                <input type="text" id="present_zip_code" name="present_zip_code" required placeholder="Enter ZIP Code">
                            </div>
                        </div>
                        <div class="form-col-3">
                            <!-- Empty div to maintain 2-column layout -->
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Permanent Address -->
                    <h2>Permanent Address</h2>
                    <div class="form-group">
                        <label for="permanent_address">Unit / Address</label>
                        <input type="text" id="permanent_address" name="permanent_address" placeholder="Unit No., Building, Street Name" required>
                        <!-- Hidden text fields for address selector -->
                        <input type="hidden" name="permanent_region_text" id="permanent_region_text">
                        <input type="hidden" name="permanent_province_text" id="permanent_province_text">
                        <input type="hidden" name="permanent_city_text" id="permanent_city_text">
                        <input type="hidden" name="permanent_barangay_text" id="permanent_barangay_text">
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="permanent_region">Region</label>
                                <select id="permanent_region" name="permanent_region" class="form-control address-region" required></select>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="permanent_province">Province</label>
                                <select id="permanent_province" name="permanent_province" class="form-control address-province" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="permanent_city">City/Municipality</label>
                                <select id="permanent_city" name="permanent_city" class="form-control address-city" required></select>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="permanent_barangay">Barangay</label>
                                <select id="permanent_barangay" name="permanent_barangay" class="form-control address-barangay" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="permanent_zip_code">Zip Code</label>
                                <input type="text" id="permanent_zip_code" name="permanent_zip_code" required placeholder="Enter ZIP Code">
                            </div>
                        </div>
                        <div class="form-col-2">
                            <!-- Empty div to maintain 2-column layout -->
                        </div>
                    </div>
                    <div class="section-divider"></div>
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
                                <button type="button" id="add_other_income_source_btn" class="btn btn-secondary btn-sm" style="margin-top: 8px;">Got other source of income?</button>
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
                        <input type="text" id="business_address_unit" name="business_address_unit" placeholder="Unit No., Building, Street Name" required>
                        <!-- Hidden text fields for address selector -->
                        <input type="hidden" name="business_region_text" id="business_region_text">
                        <input type="hidden" name="business_province_text" id="business_province_text">
                        <input type="hidden" name="business_city_text" id="business_city_text">
                        <input type="hidden" name="business_barangay_text" id="business_barangay_text">
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="business_region">Region</label>
                                <select id="business_region" name="business_region" class="form-control address-region" required></select>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="business_province">Province</label>
                                <select id="business_province" name="business_province" class="form-control address-province" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="business_city">City/Municipality</label>
                                <select id="business_city" name="business_city" class="form-control address-city" required></select>
                            </div>
                        </div>
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="business_barangay">Barangay</label>
                                <select id="business_barangay" name="business_barangay" class="form-control address-barangay" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="business_zip_code">Zip Code</label>
                                <input type="text" id="business_zip_code" name="business_zip_code" required placeholder="Enter ZIP Code">
                            </div>
                        </div>
                        <div class="form-col-2">
                            <!-- Empty div to maintain 2-column layout -->
                        </div>
                    </div>
                    <div class="section-divider"></div>
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
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_age">Age</label>
                                    <input type="number" id="spouse_age" name="spouse_age" min="18" max="100" readonly>
                                </div>
                            </div>
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_occupation">Occupation</label>
                                    <input type="text" id="spouse_occupation" name="spouse_occupation" placeholder="Enter Spouse's Occupation">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-col-2">
                                <div class="form-group">
                                    <label for="spouse_id_number">TIN/SSS/GSIS/Valid ID</label>
                                    <input type="text" id="spouse_id_number" name="spouse_id_number" placeholder="Enter Spouse's ID Number">
                                </div>
                            </div>
                            <div class="form-col-2">
                                <!-- Empty div to maintain 2-column layout -->
                            </div>
                        </div>
                        <div class="section-divider"></div>
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
                                <th style="width: 5%;"></th> <!-- For remove button -->
                            </tr>
                        </thead>
                        <tbody id="beneficiaries_tbody">
                            <?php // Only render one row initially, rest will be added by JS ?>
                            <tr class="beneficiary-row">
                                <td>
                                    <div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_last_name_1" name="beneficiary_last_name[]" placeholder="Enter Last Name"></div>
                                </td>
                                <td>
                                    <div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_first_name_1" name="beneficiary_first_name[]" placeholder="Enter First Name"></div>
                                </td>
                                <td>
                                    <div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_mi_1" name="beneficiary_mi[]" maxlength="1" placeholder="MI"></div>
                                </td>
                                <td>
                                    <div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_dob_1" name="beneficiary_dob[]" class="beneficiary-dob" placeholder="MM/DD/YYYY"></div>
                                </td>
                                <td>
                                    <div class="form-group" style="margin-bottom:0;"><select id="beneficiary_gender_1" name="beneficiary_gender[]"><option value="" selected></option><option value="M">M</option><option value="F">F</option></select></div>
                                </td>
                                <td>
                                    <div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_relationship_1" name="beneficiary_relationship[]" placeholder="Enter Relationship"></div>
                                </td>
                                <td>
                                    <div class="form-group" style="margin-bottom:0; text-align: center;"><input type="checkbox" id="beneficiary_dependent_1" name="beneficiary_dependent[]" value="1" style="display: inline-block; width: auto;"></div>
                                </td>
                                <td>
                                    <!-- Remove button placeholder for the first row, not typically removable unless it's the only one and empty? Or always not removable? For now, no remove on first static row -->
                                </td>
                            </tr>
                            <?php // End of initial row ?>
                        </tbody>
                    </table>
                    <button type="button" id="add_beneficiary_btn" class="btn btn-secondary btn-sm">Add Beneficiary</button>
                    
                    <div class="section-divider"></div>
                    
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
                    
                    <h2>Signature</h2>
                    <div class="form-row">
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="member_signature">Member's Signature</label>
                                <div class="signature-container">
                                    <canvas id="member_signature_canvas" width="400" height="200"></canvas>
                                    <input type="hidden" id="member_signature" name="member_signature">
                                    <div class="signature-buttons" style="margin-top: 10px;">
                                        <button type="button" id="clear_member_signature" class="btn btn-sm btn-secondary">Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-col-2">
                            <div class="form-group">
                                <label for="beneficiary_signature">Beneficiary's Signature</label>
                                <div class="signature-container">
                                    <canvas id="beneficiary_signature_canvas" width="400" height="200"></canvas>
                                    <input type="hidden" id="beneficiary_signature" name="beneficiary_signature">
                                    <div class="signature-buttons" style="margin-top: 10px;">
                                        <button type="button" id="clear_beneficiary_signature" class="btn btn-sm btn-secondary">Clear</button>
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
                    
                    <div class="section-divider"></div>
                    
                    <div class="form-group disclaimer-box">
                        <div class="checkbox-item disclaimer-checkbox-container" style="align-items: flex-start;">
                            <input type="checkbox" id="disclaimer_agreement" name="disclaimer_agreement" required>
                            <label for="disclaimer_agreement" style="font-size: 14px; line-height: 1.5; padding-top: 5px;">
                                By proceeding, I acknowledge that I have read and understood the terms and conditions related to the collection, use, and storage of my personal information as provided in this TSPI Membership Form. I voluntarily agree to provide the information requested herein for the purpose of my membership application and related services. I understand that TSPI will handle my personal data in accordance with applicable data privacy laws and their internal policies.
                            </label>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" id="submit_application_btn" class="btn btn-primary" disabled>Submit Application</button>
                    </div>
                </div> <!-- End of Page 3 -->

                <!-- Form Navigation -->
                <div class="form-navigation-controls">
                    <button type="button" id="prev_page_btn" class="btn btn-secondary" style="display: none;">Previous</button>
                    <div class="page-indicator" id="page_indicator">Page 1 of 3</div>
                    <button type="button" id="next_page_btn" class="btn btn-primary">Next</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Information Modal -->
    <div id="infoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Important Information</h2>
            </div>
            <div class="modal-body">
                <h3>CERTIFICATION OF HEALTH CONDITION</h3>
                <p>I hereby declare that I am in good health at present to the best of my knowledge. I have no physical disability or any defect. I have not been hospitalized in the last year and I do not expect to be hospitalized in the near future for any illness. I declare that I have not experienced any illness requiring treatment for a week or more, nor have I undergone any surgery, accident, or injury in the past year.</p>
                
                <h3>DECLARATION OF TRUTH</h3>
                <p>I hereby declare and affirm that the information provided in this application is true and correct. I agree that the information I have provided is part of my insurance contract with TSPI MBAI. Any false or incorrect information provided herein may be a cause for cancellation of the insurance and membership in TSPI MBAI. In this event, I understand that TSPI MBAI shall not be liable for any benefits intended for me, except only for the return of the amount paid for the insurance.</p>
                
                <h3>CONSENT UNDER THE DATA PRIVACY ACT</h3>
                <p>Pursuant to the Data Privacy Act, I hereby give my consent to TSPI and TSPI MBAI, to collect, store, use, or process within the country, my recorded personal data. I give my consent for my personal data to be shared by TSPI and TSPI MBAI with its business partners, as well as its service providers, so that they can provide quality services, and for other legitimate purposes appropriate for these services.</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="disagree_btn" class="btn btn-secondary">I Disagree</button>
                <button type="button" id="agree_btn" class="btn btn-primary">I Agree</button>
            </div>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
<script src="https://cdn.jsdelivr.net/npm/philippine-address-selector@latest/dist/philippine-address-selector.bundle.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('membership-form');
    const submitButton = document.getElementById('submit_application_btn');
    const disclaimerCheckbox = document.getElementById('disclaimer_agreement');
    
    // Modal elements
    const infoModal = document.getElementById('infoModal');
    const agreeBtn = document.getElementById('agree_btn');
    const disagreeBtn = document.getElementById('disagree_btn');

    // Show modal on page load
    if (infoModal) {
        infoModal.style.display = 'block';
    }

    if (agreeBtn) {
        agreeBtn.addEventListener('click', function() {
            infoModal.style.display = 'none';
            // Potentially enable form interaction here if it was disabled
        });
    }

    if (disagreeBtn) {
        disagreeBtn.addEventListener('click', function() {
            // Option 1: Redirect (Example: to homepage or an info page)
            // window.location.href = '/'; 
            // Option 2: Disable the form / show a message
            infoModal.style.display = 'none';
            alert('You must agree to the terms to proceed with the application.');
            // Disable all form inputs and buttons if user disagrees
            if (form) {
                const elements = form.elements;
                for (let i = 0, len = elements.length; i < len; ++i) {
                    elements[i].disabled = true;
                }
            }
            if (submitButton) submitButton.disabled = true;
            // Disable pagination buttons as well
            if (document.getElementById('prev_page_btn')) document.getElementById('prev_page_btn').disabled = true;
            if (document.getElementById('next_page_btn')) document.getElementById('next_page_btn').disabled = true;

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
    
    // Form validation before submit
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const errorMessages = [];
            
            // Check if at least one plan is selected
            const planCheckboxes = document.querySelectorAll('input[name="plans[]"]:checked');
            if (planCheckboxes.length === 0) {
                isValid = false;
                errorMessages.push('Please select at least one plan');
            }
            
            // Check if at least one classification is selected
            const classificationCheckboxes = document.querySelectorAll('input[name="classification[]"]:checked');
            if (classificationCheckboxes.length === 0) {
                isValid = false;
                errorMessages.push('Please select at least one member classification');
            }
            
            // Check if at least one beneficiary is added
            let hasBeneficiary = false;
            for (let i = 1; i <= 5; i++) {
                const lastName = document.getElementById(`beneficiary_last_name_${i}`).value;
                const firstName = document.getElementById(`beneficiary_first_name_${i}`).value;
                
                if (lastName || firstName) {
                    hasBeneficiary = true;
                    break;
                }
            }
            
            if (!hasBeneficiary) {
                isValid = false;
                errorMessages.push('Please add at least one beneficiary');
            }
            
            // Display custom error messages if validation fails
            if (!isValid) {
                event.preventDefault();
                alert('Please correct the following errors:\n- ' + errorMessages.join('\n- '));
            }
        });
    }
    
    // Pikaday Initializations
    const pikadayConfig = {
        format: 'MM/DD/YYYY', // Changed format
        toString(date, format) {
            const day = date.getDate();
            const month = date.getMonth() + 1;
            const year = date.getFullYear();
            return `${month < 10 ? '0' + month : month}/${day < 10 ? '0' + day : day}/${year}`; // Changed format
        },
        parse(dateString, format) {
            const parts = dateString.split('/');
            const month = parseInt(parts[0], 10) - 1; // Adjusted for MM/DD/YYYY
            const day = parseInt(parts[1], 10);
            const year = parseInt(parts[2], 10);
            return new Date(year, month, day);
        },
        yearRange: [1900, new Date().getFullYear() + 5] // Adjust year range as needed
    };

    if (birthdayField) {
        const eighteenYearsAgo = new Date();
        eighteenYearsAgo.setFullYear(eighteenYearsAgo.getFullYear() - 18);

        const birthdayPicker = new Pikaday({ 
            field: birthdayField,
            ...pikadayConfig,
            defaultDate: eighteenYearsAgo, 
            setDefaultDate: true, 
            onSelect: function() {
                if (this.getMoment().isValid()) {
                    calculateAge(this.getDate(), ageField);
                } else {
                    if(ageField) ageField.value = '';
                }
            }
        });
        // Trigger age calculation on initial load if default date is set
        if (birthdayField.value) { // Check if field has a value (could be from localStorage later)
             calculateAge(birthdayPicker.getDate(), ageField); // Use picker's date
        } else if (birthdayPicker.getDate()){ // Or if picker set a default
             calculateAge(birthdayPicker.getDate(), ageField);
        }
    }

    if (spouseBirthdayField) {
        new Pikaday({
            field: spouseBirthdayField,
            ...pikadayConfig,
            onSelect: function() {
                if (this.getMoment().isValid()) {
                    calculateAge(this.getDate(), spouseAgeField);
                } else {
                    if(spouseAgeField) spouseAgeField.value = '';
                }
            }
        });
         if (spouseBirthdayField.value) { // For localStorage refill
            calculateAge(spouseBirthdayField.value, spouseAgeField);
        }
    }

    // Initialize for Beneficiary DOBs - Initial Row
    const initialBeneficiaryDobField = document.getElementById('beneficiary_dob_1');
    if (initialBeneficiaryDobField) {
        new Pikaday({
            field: initialBeneficiaryDobField,
            ...pikadayConfig
        });
    }

    // Add Beneficiary Row Logic
    const addBeneficiaryBtn = document.getElementById('add_beneficiary_btn');
    const beneficiariesTbody = document.getElementById('beneficiaries_tbody');
    let beneficiaryRowCount = 1; // Start with 1 because one row is already in HTML
    const maxBeneficiaryRows = 4;

    if (addBeneficiaryBtn && beneficiariesTbody) {
        addBeneficiaryBtn.addEventListener('click', function() {
            if (beneficiaryRowCount < maxBeneficiaryRows) {
                beneficiaryRowCount++;
                const newRow = document.createElement('tr');
                newRow.classList.add('beneficiary-row');
                newRow.innerHTML = `
                    <td><div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_last_name_${beneficiaryRowCount}" name="beneficiary_last_name[]" placeholder="Enter Last Name"></div></td>
                    <td><div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_first_name_${beneficiaryRowCount}" name="beneficiary_first_name[]" placeholder="Enter First Name"></div></td>
                    <td><div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_mi_${beneficiaryRowCount}" name="beneficiary_mi[]" maxlength="1" placeholder="MI"></div></td>
                    <td><div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_dob_${beneficiaryRowCount}" name="beneficiary_dob[]" class="beneficiary-dob" placeholder="MM/DD/YYYY"></div></td>
                    <td><div class="form-group" style="margin-bottom:0;"><select id="beneficiary_gender_${beneficiaryRowCount}" name="beneficiary_gender[]"><option value="" selected></option><option value="M">M</option><option value="F">F</option></select></div></td>
                    <td><div class="form-group" style="margin-bottom:0;"><input type="text" id="beneficiary_relationship_${beneficiaryRowCount}" name="beneficiary_relationship[]" placeholder="Enter Relationship"></div></td>
                    <td><div class="form-group" style="margin-bottom:0; text-align: center;"><input type="checkbox" id="beneficiary_dependent_${beneficiaryRowCount}" name="beneficiary_dependent[]" value="1" style="display: inline-block; width: auto;"></div></td>
                    <td><button type="button" class="remove-beneficiary-btn btn-sm" style="background-color: #ff6b6b; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; line-height: 24px; text-align: center; cursor: pointer; font-weight: bold;">X</button></td>
                `;
                beneficiariesTbody.appendChild(newRow);

                // Initialize Pikaday for the new date field
                const newDobField = newRow.querySelector('.beneficiary-dob');
                if (newDobField) {
                    new Pikaday({
                        field: newDobField,
                        ...pikadayConfig
                    });
                }

                newRow.querySelector('.remove-beneficiary-btn').addEventListener('click', function() {
                    newRow.remove();
                    beneficiaryRowCount--;
                    if (beneficiaryRowCount < maxBeneficiaryRows) {
                        addBeneficiaryBtn.style.display = 'inline-block';
                    }
                });

                if (beneficiaryRowCount >= maxBeneficiaryRows) {
                    addBeneficiaryBtn.style.display = 'none';
                }
            }
        });
        // Initial check in case maxBeneficiaryRows is 1
        if (beneficiaryRowCount >= maxBeneficiaryRows) {
             addBeneficiaryBtn.style.display = 'none';
        }
    }

    const trusteeDobField = document.getElementById('trustee_dob');
    if (trusteeDobField) {
        new Pikaday({
            field: trusteeDobField,
            ...pikadayConfig
        });
    }

    // Philippine Address Selector (wilfredpine/philippine-address-selector)
    const jsonBasePath = '../assets/ph-json/'; // Adjusted path assuming assets is at the root

    function setupAddressDropdowns(prefix) {
        const regionEl = $(`#${prefix}_region`);
        const provinceEl = $(`#${prefix}_province`);
        const cityEl = $(`#${prefix}_city`);
        const barangayEl = $(`#${prefix}_barangay`);

        const regionTextEl = $(`#${prefix}_region_text`);
        const provinceTextEl = $(`#${prefix}_province_text`);
        const cityTextEl = $(`#${prefix}_city_text`);
        const barangayTextEl = $(`#${prefix}_barangay_text`);

        regionEl.empty().append('<option selected=\"true\" disabled>Choose Region</option>').prop('selectedIndex', 0);
        $.getJSON(`${jsonBasePath}region.json`, function (data) {
            data.sort((a, b) => a.region_name.localeCompare(b.region_name));
            $.each(data, function (key, entry) {
                regionEl.append($('<option></option>').attr('value', entry.region_code).text(entry.region_name));
            });
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error(`Error loading ${prefix} regions:`, textStatus, errorThrown);
            console.error("Path used:", `${jsonBasePath}region.json`);
        });

        regionEl.on('change', function() {
            const regionCode = $(this).val();
            regionTextEl.val($(this).find("option:selected").text());
            provinceEl.empty().append('<option selected=\"true\" disabled>Choose Province</option>').prop('selectedIndex', 0);
            cityEl.empty().append('<option selected=\"true\" disabled>Choose City/Municipality</option>').prop('selectedIndex', 0);
            barangayEl.empty().append('<option selected=\"true\" disabled>Choose Barangay</option>').prop('selectedIndex', 0);
            provinceTextEl.val('');
            cityTextEl.val('');
            barangayTextEl.val('');

            $.getJSON(`${jsonBasePath}province.json`, function(data) {
                const result = data.filter(value => value.region_code == regionCode);
                result.sort((a, b) => a.province_name.localeCompare(b.province_name));
                $.each(result, function (key, entry) {
                    provinceEl.append($('<option></option>').attr('value', entry.province_code).text(entry.province_name));
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error(`Error loading ${prefix} provinces:`, textStatus, errorThrown);
            });
        });

        provinceEl.on('change', function() {
            const provinceCode = $(this).val();
            provinceTextEl.val($(this).find("option:selected").text());
            cityEl.empty().append('<option selected=\"true\" disabled>Choose City/Municipality</option>').prop('selectedIndex', 0);
            barangayEl.empty().append('<option selected=\"true\" disabled>Choose Barangay</option>').prop('selectedIndex', 0);
            cityTextEl.val('');
            barangayTextEl.val('');

            $.getJSON(`${jsonBasePath}city.json`, function(data) {
                const result = data.filter(value => value.province_code == provinceCode);
                result.sort((a, b) => a.city_name.localeCompare(b.city_name));
                $.each(result, function (key, entry) {
                    cityEl.append($('<option></option>').attr('value', entry.city_code).text(entry.city_name));
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error(`Error loading ${prefix} cities:`, textStatus, errorThrown);
            });
        });

        cityEl.on('change', function() {
            const cityCode = $(this).val();
            cityTextEl.val($(this).find("option:selected").text());
            barangayEl.empty().append('<option selected=\"true\" disabled>Choose Barangay</option>').prop('selectedIndex', 0);
            barangayTextEl.val('');

            $.getJSON(`${jsonBasePath}barangay.json`, function(data) {
                const result = data.filter(value => value.city_code == cityCode);
                result.sort((a, b) => a.brgy_name.localeCompare(b.brgy_name));
                $.each(result, function (key, entry) {
                    barangayEl.append($('<option></option>').attr('value', entry.brgy_code).text(entry.brgy_name));
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error(`Error loading ${prefix} barangays:`, textStatus, errorThrown);
            });
        });

        barangayEl.on('change', function() {
             barangayTextEl.val($(this).find("option:selected").text());
        });
    }

    setupAddressDropdowns('present');
    setupAddressDropdowns('permanent');
    setupAddressDropdowns('business');

    // Add other income source
    const addIncomeSourceBtn = document.getElementById('add_other_income_source_btn');
    const incomeSourcesContainer = document.getElementById('other_income_sources_container');
    let incomeSourceCount = 0;
    const maxIncomeSources = 4;

    if (addIncomeSourceBtn && incomeSourcesContainer) {
        addIncomeSourceBtn.addEventListener('click', function() {
            if (incomeSourceCount < maxIncomeSources) {
                incomeSourceCount++;
                const newIncomeSourceDiv = document.createElement('div');
                newIncomeSourceDiv.classList.add('other-income-source-item');
                newIncomeSourceDiv.innerHTML = `
                    <div class="form-group" style="flex-grow: 1; margin-bottom: 0;">
                        <label for="other_income_source_${incomeSourceCount}" class="sr-only">Other Source of Income ${incomeSourceCount}</label>
                        <input type="text" id="other_income_source_${incomeSourceCount}" name="other_income_source[]" placeholder="Other Source of Income ${incomeSourceCount}" style="flex-grow: 1;">
                    </div>
                    <button type="button" class="remove-income-source-btn btn-sm" style="background-color: #ff6b6b; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; line-height: 24px; text-align: center; cursor: pointer; font-weight: bold; margin-left: 5px;">X</button>
                `;
                incomeSourcesContainer.appendChild(newIncomeSourceDiv);

                newIncomeSourceDiv.querySelector('.remove-income-source-btn').addEventListener('click', function() {
                    newIncomeSourceDiv.remove();
                    incomeSourceCount--;
                    if (incomeSourceCount < maxIncomeSources) {
                        addIncomeSourceBtn.style.display = 'inline-block';
                    }
                });

                if (incomeSourceCount >= maxIncomeSources) {
                    addIncomeSourceBtn.style.display = 'none';
                }
            }
        });
    }

    // Remove original Other Income Source fields as they are now dynamic
    for (let i = 1; i <= 4; i++) {
        const oldIncomeField = document.getElementById(`other_income_source_${i}`);
        if (oldIncomeField && oldIncomeField.parentElement.classList.contains('form-group')) {
            oldIncomeField.parentElement.remove();
        }
    }

    // Other Valid IDs Logic
    const idNumberField = document.getElementById('id_number');
    const addOtherValidIdBtn = document.getElementById('add_other_valid_id_btn');
    let otherValidIdsContainer = document.getElementById('other_valid_ids_container'); // Ensure it's correctly targeted
    let otherValidIdCount = 0;
    
    if (idNumberField && addOtherValidIdBtn) {
        // Create the container if it doesn't exist and is still null
        if (!otherValidIdsContainer && idNumberField.parentNode) {
            const container = document.createElement('div');
            container.id = 'other_valid_ids_container';
            container.style.marginTop = '10px';
            // Insert after the button's parent div if the button is inside a div, or directly after the button
            const buttonContainer = addOtherValidIdBtn.closest('.form-group') || addOtherValidIdBtn.parentNode;
            if (buttonContainer.nextSibling) {
                 buttonContainer.parentNode.insertBefore(container, buttonContainer.nextSibling);
            } else {
                 buttonContainer.parentNode.appendChild(container);
            }
            otherValidIdsContainer = container; // Update reference
        }
        
        addOtherValidIdBtn.addEventListener('click', function() {
            if (!otherValidIdsContainer) { // Check again in case it was missed
                console.error('other_valid_ids_container not found or created.');
                return;
            }
            otherValidIdCount++;
            const otherIdRow = document.createElement('div');
            otherIdRow.classList.add('other-valid-id-row');
            otherIdRow.style.display = 'flex';
            otherIdRow.style.marginBottom = '5px';
            otherIdRow.style.alignItems = 'center';
            otherIdRow.innerHTML = `
                <input type="text" id="other_valid_id_${otherValidIdCount}" name="other_valid_id[]" placeholder="Other Valid ID" style="flex-grow: 1;">
                <button type="button" class="remove-other-id-btn btn-sm" style="background-color: #ff6b6b; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; line-height: 24px; text-align: center; cursor: pointer; font-weight: bold; margin-left: 5px;">X</button>
            `;
            
            const container = document.getElementById('other_valid_ids_container');
            container.appendChild(otherIdRow);
            
            otherIdRow.querySelector('.remove-other-id-btn').addEventListener('click', function() {
                otherIdRow.remove();
                otherValidIdCount--;
                // Always show the button since we can have multiple IDs
                addOtherValidIdBtn.style.display = 'inline-block';
            });
        });
    }

    // Initialize signature pads
    const memberSignaturePad = initializeSignaturePad('member_signature_canvas', 'member_signature', 'clear_member_signature');
    const beneficiarySignaturePad = initializeSignaturePad('beneficiary_signature_canvas', 'beneficiary_signature', 'clear_beneficiary_signature');

    function initializeSignaturePad(canvasId, hiddenInputId, clearButtonId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn('Signature canvas not found:', canvasId);
            return null;
        }

        // Set explicit width and height for signature pad to work correctly before CSS might resize it.
        // CSS will handle the final display size.
        const context = canvas.getContext('2d');
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        // canvas.width = canvas.offsetWidth * ratio; // This causes issues if offsetWidth is 0 initially
        // canvas.height = canvas.offsetHeight * ratio;
        // Use fixed initial dimensions that match the HTML attributes if present, or default
        canvas.width = (canvas.getAttribute('width') || 400) * ratio;
        canvas.height = (canvas.getAttribute('height') || 200) * ratio;
        context.scale(ratio, ratio);


        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 0.5,
            maxWidth: 2.5
        });

        // Handle canvas resize
        function resizeCanvas() {
            if (!canvas.offsetParent) return; // Don't resize if canvas is not visible (e.g. on non-active page)
            const currentRatio = Math.max(window.devicePixelRatio || 1, 1);
            const W = canvas.offsetWidth; // Get width from CSS-driven layout
            // For height, you might want to maintain an aspect ratio or use a fixed height from CSS
            // Here, we'll try to respect the initial aspect ratio from canvas attributes if possible,
            // otherwise default to a common height or the one from CSS if it's set explicitly.
            const initialCanvasWidth = parseFloat(canvas.getAttribute('data-initial-width') || canvas.width / currentRatio);
            const initialCanvasHeight = parseFloat(canvas.getAttribute('data-initial-height') || canvas.height / currentRatio);

            canvas.width = W * currentRatio;
            // canvas.height = (W * (initialCanvasHeight / initialCanvasWidth)) * currentRatio; // Maintain aspect ratio
            canvas.height = ( (canvas.parentElement.classList.contains('signature-container') ? canvas.parentElement.offsetHeight : 200)  || 200 ) * currentRatio ;


            canvas.getContext("2d").scale(currentRatio, currentRatio);
            const data = signaturePad.toData(); // Save current signature
            signaturePad.clear(); // Clear before redrawing
            signaturePad.fromData(data); // Redraw signature
        }
        
        // Store initial dimensions for aspect ratio
        canvas.setAttribute('data-initial-width', canvas.width / ratio);
        canvas.setAttribute('data-initial-height', canvas.height / ratio);


        window.addEventListener("resize", resizeCanvas);
        // Call resizeCanvas initially if canvas is visible.
        // For paginated forms, this might need to be called when page becomes active.
        // For now, let's call it once. If it's not visible, offsetWidth might be 0.
        // We'll call it specifically when a page with a signature pad becomes active.
        // resizeCanvas(); 

        const currentForm = document.getElementById('membership-form');
        if (currentForm) {
            currentForm.addEventListener('submit', function() {
                if (!signaturePad.isEmpty()) {
                    const signatureData = signaturePad.toDataURL();
                    document.getElementById(hiddenInputId).value = signatureData;
                }
            });
        }

        // Clear button functionality
        const clearButton = document.getElementById(clearButtonId);
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                signaturePad.clear();
                document.getElementById(hiddenInputId).value = '';
            });
        }

        return signaturePad;
    }

    // Pagination Logic
    const pages = document.querySelectorAll('.form-page-content');
    const prevBtn = document.getElementById('prev_page_btn');
    const nextBtn = document.getElementById('next_page_btn');
    const pageIndicator = document.getElementById('page_indicator');
    const submitBtnContainer = document.querySelector('#submit_application_btn').parentElement;


    let currentPage = 0;
    const totalPages = pages.length;

    function updatePageDisplay() {
        pages.forEach((page, index) => {
            page.classList.toggle('active', index === currentPage);
        });
        if (pageIndicator) pageIndicator.textContent = `Page ${currentPage + 1} of ${totalPages}`;
        
        if (prevBtn) prevBtn.style.display = currentPage === 0 ? 'none' : 'inline-block';
        if (nextBtn) nextBtn.style.display = currentPage === totalPages - 1 ? 'none' : 'inline-block';
        
        // Show submit button only on the last page's navigation area
        // The submit button itself is already on page 3's content.
        // We hide the "Next" button on the last page.
        // The submit button is part of page 3 content.
        // Let's ensure the disclaimer and submit button are *only* visible if page 3 is active.
        const disclaimerBox = document.querySelector('.disclaimer-box');
        const submitButtonActual = document.getElementById('submit_application_btn');

        if (currentPage === totalPages - 1) {
            if (disclaimerBox) disclaimerBox.style.display = 'block';
            if (submitButtonActual) submitButtonActual.style.display = 'inline-block';
            if (submitBtnContainer) submitBtnContainer.style.display = 'flex';


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
            if (submitButtonActual) submitButtonActual.style.display = 'none';
             if (submitBtnContainer) submitBtnContainer.style.display = 'none';
        }
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (currentPage < totalPages - 1) {
                currentPage++;
                updatePageDisplay();
                 window.scrollTo(0, 0); // Scroll to top of page
            }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentPage > 0) {
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
        for (const element of formElements) {
            if (element.name) {
                if (element.type === 'checkbox') {
                    formData[element.name + (element.value ? `_${element.value}` : '')] = element.checked; // Store by name_value for multiple checkboxes with same name
                } else if (element.type === 'radio') {
                    if (element.checked) {
                        formData[element.name] = element.value;
                    }
                } else if (element.tagName === 'SELECT' && element.multiple) {
                    formData[element.name] = Array.from(element.selectedOptions).map(option => option.value);
                } else {
                    formData[element.name] = element.value;
                }
            }
        }
        // Save dynamic rows count
        formData['beneficiary_row_count'] = beneficiaryRowCount;
        formData['other_income_source_count'] = incomeSourceCount;
        formData['other_valid_id_count'] = otherValidIdCount;

        localStorage.setItem('membershipFormData', JSON.stringify(formData));
    }

    function loadFormFromLocalStorage() {
        if (!formToSave) return;
        const savedData = localStorage.getItem('membershipFormData');
        if (savedData) {
            const formData = JSON.parse(savedData);
            for (const element of formElements) {
                if (element.name) {
                    const savedValueKey = element.type === 'checkbox' ? element.name + (element.value ? `_${element.value}` : '') : element.name;
                    if (formData.hasOwnProperty(savedValueKey)) {
                        if (element.type === 'checkbox') {
                            element.checked = formData[savedValueKey];
                        } else if (element.type === 'radio') {
                            if (element.value === formData[savedValueKey]) {
                                element.checked = true;
                            }
                        } else if (element.tagName === 'SELECT' && element.multiple) {
                            const values = formData[savedValueKey];
                            if (Array.isArray(values)) {
                                Array.from(element.options).forEach(option => {
                                    option.selected = values.includes(option.value);
                                });
                            }
                        } else {
                             // Skip address dropdowns that are dynamically populated by philippine-address-selector
                            if (!element.classList.contains('address-region') &&
                                !element.classList.contains('address-province') &&
                                !element.classList.contains('address-city') &&
                                !element.classList.contains('address-barangay')) {
                                element.value = formData[savedValueKey];
                            }
                        }
                         // Trigger change event for fields that might have dependent logic (like civil status)
                        if (element.id === 'civil_status' || element.id === 'birthday' || element.id === 'spouse_birthday') {
                            element.dispatchEvent(new Event('change'));
                        }
                    }
                }
            }

            // Restore dynamic rows - this is more complex and might need specific handling for each type
            // For now, just logging the counts. Full restoration would involve recreating rows.
            // console.log("Saved beneficiary rows:", formData['beneficiary_row_count']);
            // console.log("Saved income sources:", formData['other_income_source_count']);
            // console.log("Saved other IDs:", formData['other_valid_id_count']);

            // Special handling for address dropdowns if their text values were saved
            ['present', 'permanent', 'business'].forEach(prefix => {
                if (formData[`${prefix}_region`]) {
                    const regionSelect = document.getElementById(`${prefix}_region`);
                    if (regionSelect) {
                        regionSelect.value = formData[`${prefix}_region`];
                        // Manually trigger change to load provinces, if not automatically handled by value set
                        // This is tricky because the options might not be loaded yet.
                        // It's often better to let the user re-select these or find a way to re-run the address selector logic with saved values.
                        // For now, we're skipping direct value setting for these in the loop above.
                        // We'll rely on saving the text fields and let the user re-select for full dependent dropdown functionality.
                    }
                }
                 // Repopulate the hidden text fields for addresses
                if (document.getElementById(`${prefix}_region_text`)) document.getElementById(`${prefix}_region_text`).value = formData[`${prefix}_region_text`] || '';
                if (document.getElementById(`${prefix}_province_text`)) document.getElementById(`${prefix}_province_text`).value = formData[`${prefix}_province_text`] || '';
                if (document.getElementById(`${prefix}_city_text`)) document.getElementById(`${prefix}_city_text`).value = formData[`${prefix}_city_text`] || '';
                if (document.getElementById(`${prefix}_barangay_text`)) document.getElementById(`${prefix}_barangay_text`).value = formData[`${prefix}_barangay_text`] || '';

            });

            // Re-calculate age for birthday and spouse birthday after loading
            if (birthdayField.value) calculateAge(birthdayField.value, ageField);
            if (spouseBirthdayField.value) calculateAge(spouseBirthdayField.value, spouseAgeField);

            // Update disclaimer checkbox and submit button state
            if (disclaimerCheckbox && formData['disclaimer_agreement']) {
                disclaimerCheckbox.checked = formData['disclaimer_agreement'];
                if (submitButton) submitButton.disabled = !disclaimerCheckbox.checked;
            }


        }
    }

    // Load saved data when page loads
    loadFormFromLocalStorage();

    // Save data when form inputs change
    if (formToSave) {
        formToSave.addEventListener('input', saveFormToLocalStorage);
        // Also save for select changes
        const selects = formToSave.querySelectorAll('select');
        selects.forEach(select => select.addEventListener('change', saveFormToLocalStorage));
    }


    // Ensure signature pad resize functions are globally accessible for updatePageDisplay
    if (memberSignaturePad && memberSignaturePad.canvas) {
        window[`resize_${memberSignaturePad.canvas.id}`] = () => {
            if (!memberSignaturePad.canvas.offsetParent) return;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const W = memberSignaturePad.canvas.offsetWidth;
            memberSignaturePad.canvas.width = W * ratio;
            memberSignaturePad.canvas.height = ( (memberSignaturePad.canvas.parentElement.classList.contains('signature-container') ? memberSignaturePad.canvas.parentElement.offsetHeight : 200)  || 200 ) * ratio ;
            memberSignaturePad.canvas.getContext("2d").scale(ratio, ratio);
            const data = memberSignaturePad.toData();
            memberSignaturePad.clear();
            memberSignaturePad.fromData(data);
        };
    }
    if (beneficiarySignaturePad && beneficiarySignaturePad.canvas) {
         window[`resize_${beneficiarySignaturePad.canvas.id}`] = () => {
            if (!beneficiarySignaturePad.canvas.offsetParent) return;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const W = beneficiarySignaturePad.canvas.offsetWidth;
            beneficiarySignaturePad.canvas.width = W * ratio;
            beneficiarySignaturePad.canvas.height = ( (beneficiarySignaturePad.canvas.parentElement.classList.contains('signature-container') ? beneficiarySignaturePad.canvas.parentElement.offsetHeight : 200)  || 200 ) * ratio ;
            beneficiarySignaturePad.canvas.getContext("2d").scale(ratio, ratio);
            const data = beneficiarySignaturePad.toData();
            beneficiarySignaturePad.clear();
            beneficiarySignaturePad.fromData(data);
        };
    }


});
</script>

<?php include '../includes/footer.php'; ?> 
