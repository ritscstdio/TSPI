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

<style>
/* Apply the fixed navbar offset rule */
h1, h2 {
    scroll-margin-top: var(--navbar-scroll-offset);
}

.membership-form-container {
    padding-top: 2rem;
}

/* Form styling based on Figma design */
.form-group {
    margin-bottom: 4px;
}

.form-group label {
    display: block;
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="date"],
.form-group input[type="email"],
.form-group select {
    width: 100%;
    height: 56px;
    padding: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #fff;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    border: 2px solid var(--primary-blue, #1B3FAB);
    outline: none;
}

/* Radio buttons styling */
.radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.radio-item {
    display: flex;
    align-items: center;
    gap: 16px;
}

.radio-item input[type="radio"] {
    appearance: none;
    width: 24px;
    height: 24px;
    border: 1px solid #ccc;
    border-radius: 50%;
    position: relative;
}

.radio-item input[type="radio"]:checked::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 12px;
    height: 12px;
    background-color: var(--primary-blue, #1B3FAB);
    border-radius: 50%;
}

.radio-item label {
    font-size: 16px;
    color: #666;
    margin-bottom: 0;
}

/* Checkbox styling */
.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 16px;
}

.checkbox-item input[type="checkbox"] {
    appearance: none;
    width: 24px;
    height: 24px;
    border: 1px solid #ccc;
    border-radius: 8px;
    position: relative;
}

.checkbox-item input[type="checkbox"]:checked::after {
    content: "";
    position: absolute;
    top: 6px;
    left: 4px;
    width: 16px;
    height: 12px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%231B3FAB'%3E%3Cpath d='M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z'/%3E%3C/svg%3E");
    background-size: contain;
    background-repeat: no-repeat;
}

/* Disclaimer styling */
.disclaimer-box {
    background-color: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 16px;
    margin-top: 24px;
}

#disclaimer_agreement + label {
    font-style: italic;
    color: #555;
}

/* Specific styling for the disclaimer checkbox */
#disclaimer_agreement {
    width: 30px;
    height: 30px;
    min-width: 30px;
    border: 2px solid #1B3FAB;
}

#disclaimer_agreement:checked::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%231B3FAB'%3E%3Cpath d='M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z'/%3E%3C/svg%3E");
    background-size: contain;
    background-repeat: no-repeat;
}

/* Styling for disclaimer checkbox container */
.disclaimer-checkbox-container {
    gap: 15px !important;
}

.disclaimer-checkbox-container label {
    max-width: calc(100% - 45px);
}

/* Disabled button styling */
button[type="submit"]:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}

.checkbox-item label {
    font-size: 16px;
    color: #666;
    margin-bottom: 0;
}

/* Form layout */
.form-row {
    display: flex;
    margin-bottom: 4px;
    gap: 16px;
}

.form-col-2 {
    flex: 1 1 calc(50% - 8px);
}

.form-col-3 {
    flex: 1 1 calc(33.33% - 11px);
}

/* Section divider */
.section-divider {
    height: 1px;
    background-color: #eee;
    margin: 32px 0;
}

/* Beneficiaries table */
.beneficiaries-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
}

.beneficiaries-table th {
    background-color: #f9f9f9;
    padding: 12px;
    text-align: left;
    font-weight: 500;
    border-bottom: 1px solid #eee;
    color: #666;
}

.beneficiaries-table td {
    padding: 8px;
    border-bottom: 1px solid #eee;
}

.beneficiaries-table input,
.beneficiaries-table select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

.beneficiaries-table th:nth-child(3), /* M.I. */
.beneficiaries-table td:nth-child(3) {
    width: 7%; /* Adjusted M.I. width */
    min-width: 50px;
}

.beneficiaries-table th:nth-child(4), /* Date of Birth */
.beneficiaries-table td:nth-child(4) {
    width: 18%; /* Adjusted Date of Birth width */
}

.beneficiaries-table th:nth-child(5), /* Gender */
.beneficiaries-table td:nth-child(5) {
    width: 8%; /* Decreased Gender width */
}

.beneficiaries-table th:nth-child(6), /* Relationship */
.beneficiaries-table td:nth-child(6) {
    width: 12%; /* Adjusted Relationship width (approx 20% reduction from a typical larger share) */
}

.beneficiaries-table th:last-child, /* Dependent */
.beneficiaries-table td:last-child {
    width: 6%; /* Adjusted Dependent width */
    text-align: center;
}

.beneficiaries-table td:last-child input[type="checkbox"] {
    margin: 0 auto; /* Center checkbox in the cell */
    display: block;
}

input[readonly] {
    background-color: #e9ecef; /* Standard grey for disabled/readonly inputs */
    opacity: 1; /* Ensure text is readable */
    cursor: not-allowed;
}

/* Button styling */
.btn {
    display: inline-block;
    border: none;
    border-radius: 8px;
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

/* Primary button (blue) */
.btn-primary {
    background-color: var(--primary-blue, #1B3FAB);
    color: white;
}

.btn-primary:hover {
    background-color: #142b73;
}

/* Secondary button (grey) */
.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* Error and success messages */
.message {
    padding: 16px;
    margin-bottom: 24px;
    border-radius: 8px;
}

.message.success {
    background-color: #e6f7e6;
    border: 1px solid #b8e5b8;
    color: #2e7d32;
}

.message.error {
    background-color: #ffebee;
    border: 1px solid #ffcdd2;
    color: #c62828;
}

.message p {
    margin: 0 0 8px 0;
}

.message ul {
    margin: 0;
    padding-left: 20px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .form-col-2,
    .form-col-3 {
        flex: 1 1 100%;
    }
    
    .checkbox-group,
    .radio-group {
        flex-direction: column;
    }
}

.available-plans-group .checkbox-group {
    padding-top: 8px; /* Add some space between label and checkboxes */
}

.phone-input-group {
    display: flex;
    align-items: center;
}

.phone-input-group .phone-prefix {
    padding: 0 12px;
    height: 56px;
    line-height: 56px;
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    border-right: none;
    border-radius: 8px 0 0 8px;
    white-space: nowrap;
}

.phone-input-group.with-flag .phone-prefix {
    display: flex;
    align-items: center;
}

.phone-input-group .country-flag {
    margin-right: 8px;
    font-size: 1.5em; /* Adjust flag size as needed */
}

.phone-input-group input[type="text"] {
    border-radius: 0 8px 8px 0;
    flex-grow: 1;
}

/* Form pagination buttons */
.form-navigation-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 24px;
    margin-bottom: 24px; /* Added margin for spacing before next section */
}

/* Small button styling */
.btn-sm {
    padding: 8px 12px;
    font-size: 14px;
}

.other-income-source-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.other-income-source-item input {
    flex-grow: 1;
}

.other-income-source-item .remove-income-btn {
    background-color: #ff6b6b;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    line-height: 24px;
    text-align: center;
    cursor: pointer;
    font-weight: bold;
}

.beneficiaries-table th:nth-child(3), /* M.I. */
.beneficiaries-table td:nth-child(3) {
    width: 8%; /* Increased width for M.I. */
    min-width: 60px;
}

.beneficiaries-table th:last-child, /* Dependent */
.beneficiaries-table td:last-child {
    width: 8%; /* Adjust as needed for Dependent */
    text-align: center;
}

.beneficiaries-table td:last-child input[type="checkbox"] {
    margin: 0 auto; /* Center checkbox in the cell */
    display: block;
}

/* Form navigation controls styling */
.form-navigation-controls {
    margin-top: 20px; 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    position: relative; 
    padding: 0 20px; 
    border-top: 1px solid #eee; 
    padding-top: 20px;
}

.form-navigation-controls .btn {
    min-width: 100px;
    text-align: center;
}

.form-navigation-controls .page-indicator {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    font-weight: 500;
}

/* Ensure empty form-col-2 is still visible for spacing */
.form-col-2:empty {
    display: block;
}
</style>

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
                <div class="form-page">
                    <h2>Personal Information</h2>
                    
                    <div class="form-row">
                        <div class="form-col-3">
                            <div class="form-group">
                                <label for="branch">Branch</label>
                                <select id="branch" name="branch" required>
                                    <option value="" disabled selected>Select Branch</option>
                                    <option value="branch1">Branch 1</option>
                                    <option value="branch2">Branch 2</option>
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
                                    <canvas id="member_signature_canvas" width="400" height="200" style="border: 1px solid #ddd; background-color: #fff;"></canvas>
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
                                    <canvas id="beneficiary_signature_canvas" width="400" height="200" style="border: 1px solid #ddd; background-color: #fff;"></canvas>
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
                </div>
            </form>
        <?php endif; ?>
    </div>


</main>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
<script src="https://cdn.jsdelivr.net/npm/philippine-address-selector@latest/dist/philippine-address-selector.bundle.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle submit button based on disclaimer checkbox
    const submitButton = document.querySelector('#submit_application_btn');
    const disclaimerCheckbox = document.querySelector('#disclaimer_agreement');
    
    if (submitButton && disclaimerCheckbox) {
        // Initially the button is disabled (set in HTML)
        
        // Add event listener to enable/disable button based on checkbox
        disclaimerCheckbox.addEventListener('change', function() {
            submitButton.disabled = !this.checked;
        });
    }

    // Auto-calculate age from birthday
    const birthdayField = document.getElementById('birthday');
    const ageField = document.getElementById('age');
    
    if (birthdayField && ageField) {
        // birthdayField.addEventListener('change', function() { // Original event listener
        //     if (this.value) {
        //         const birthDate = new Date(this.value);
        //         const today = new Date();
        //         let age = today.getFullYear() - birthDate.getFullYear();
        //         const monthDiff = today.getMonth() - birthDate.getMonth();
                
        //         if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        //             age--;
        //         }
                
        //         ageField.value = age;
        //     }
        // });
        // Pikaday initialization for birthday will handle this
    }
    
    // Auto-calculate spouse age from spouse birthday
    const spouseBirthdayField = document.getElementById('spouse_birthday');
    const spouseAgeField = document.getElementById('spouse_age');
    
    if (spouseBirthdayField && spouseAgeField) {
        // spouseBirthdayField.addEventListener('change', function() { // Original event listener
        //     if (this.value) {
        //         const birthDate = new Date(this.value);
        //         const today = new Date();
        //         let age = today.getFullYear() - birthDate.getFullYear();
        //         const monthDiff = today.getMonth() - birthDate.getMonth();
                
        //         if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        //             age--;
        //         }
                
        //         spouseAgeField.value = age;
        //     }
        // });
        // Pikaday initialization for spouse_birthday will handle this
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
    
    // Form validation before submit
    const membershipForm = document.getElementById('membership-form');
    
    if (membershipForm) {
        membershipForm.addEventListener('submit', function(event) {
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

        const birthdayPicker = new Pikaday({ // Assign to variable
            field: birthdayField,
            ...pikadayConfig,
            defaultDate: eighteenYearsAgo, // Set default to 18 years ago
            setDefaultDate: true, // Make sure defaultDate is used
            onSelect: function() {
                if (this.getMoment().isValid()) {
                    const birthDate = this.getDate();
                    const today = new Date();
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const monthDiff = today.getMonth() - birthDate.getMonth();
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    if(ageField) ageField.value = age;
                } else {
                    if(ageField) ageField.value = '';
                }
            }
        });
        // Trigger age calculation on initial load if default date is set
        if (birthdayPicker.getDate()) {
            const birthDate = birthdayPicker.getDate();
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            if(ageField) ageField.value = age;
        }
    }

    if (spouseBirthdayField) {
        new Pikaday({
            field: spouseBirthdayField,
            ...pikadayConfig,
            onSelect: function() {
                if (this.getMoment().isValid()) {
                    const birthDate = this.getDate();
                    const today = new Date();
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const monthDiff = today.getMonth() - birthDate.getMonth();
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    if(spouseAgeField) spouseAgeField.value = age;
                } else {
                    if(spouseAgeField) spouseAgeField.value = '';
                }
            }
        });
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
    const otherValidIdsContainer = document.getElementById('other_valid_ids_container');
    let otherValidIdCount = 0;
    
    if (idNumberField && addOtherValidIdBtn) {
        // Create the container if it doesn't exist
        if (!otherValidIdsContainer) {
            const container = document.createElement('div');
            container.id = 'other_valid_ids_container';
            container.style.marginTop = '10px';
            idNumberField.parentNode.insertBefore(container, addOtherValidIdBtn.nextSibling);
        }
        
        addOtherValidIdBtn.addEventListener('click', function() {
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

    // Function to initialize signature pads
    function initializeSignaturePad(canvasId, hiddenInputId, clearButtonId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 0.5,
            maxWidth: 2.5
        });

        // Handle canvas resize
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear(); // Otherwise isEmpty() might return incorrect value
        }

        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        // Save signature data to hidden input when form is submitted
        const form = document.getElementById('membership-form');
        if (form) {
            form.addEventListener('submit', function() {
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
});
</script>

<?php include '../includes/footer.php'; ?> 
