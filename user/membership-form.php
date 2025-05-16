<?php
$page_title = "Membership Form";
$body_class = "membership-form-page";
require_once '../includes/config.php';

// Verify that the user is logged in, or is coming from the join us button
$from_join = isset($_GET['join']) && $_GET['join'] == 'true';
$from_verification = isset($_GET['verified']) && $_GET['verified'] == 'true';

// Check if user is logged in or coming from appropriate sources
if (!is_logged_in() && !$from_join && !$from_verification) {
    // Redirect to login
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

<style>
/* Apply the fixed navbar offset rule */
h1, h2 {
    scroll-margin-top: var(--navbar-scroll-offset);
}

.membership-form-container {
    padding-top: 2rem;
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
                <!-- Personal Information Section -->
                <h2>Personal Information</h2>
                
                <div class="form-row">
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="branch">Branch</label>
                            <input type="text" id="branch" name="branch" required>
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="cid_no">CID No.</label>
                            <input type="text" id="cid_no" name="cid_no" required>
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="center_no">Center No. (for fillers)</label>
                            <input type="text" id="center_no" name="center_no">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
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
                            <label for="last_name">Last Name/Apelyido</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="first_name">First Name/Pangalan</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="middle_name">Middle Name/Gitnang Pangalan</label>
                            <input type="text" id="middle_name" name="middle_name">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col-2">
                        <div class="form-group">
                            <label>Gender/Kasarian</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" id="gender_male" name="gender" value="Male" required>
                                    <label for="gender_male">Male</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="gender_female" name="gender" value="Female">
                                    <label for="gender_female">Female</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label>Civil Status</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" id="civil_single" name="civil_status" value="Single" required>
                                    <label for="civil_single">Single</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="civil_married" name="civil_status" value="Married">
                                    <label for="civil_married">Married</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="civil_widowed" name="civil_status" value="Widowed">
                                    <label for="civil_widowed">Widowed</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="civil_separated" name="civil_status" value="Separated">
                                    <label for="civil_separated">Separated</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="birthday">Birthday (mm/dd/yyyy)</label>
                            <input type="date" id="birthday" name="birthday" required>
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="age">Age/Edad</label>
                            <input type="number" id="age" name="age" min="18" max="100" required>
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="birth_place">Birth Place/Lugar ng Kapanganakan</label>
                            <input type="text" id="birth_place" name="birth_place" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="nationality">Nationality</label>
                            <input type="text" id="nationality" name="nationality" required>
                        </div>
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="contact_no">Contact No./Telepono</label>
                            <input type="text" id="contact_no" name="contact_no" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="cell_phone">Cell Phone</label>
                            <input type="text" id="cell_phone" name="cell_phone" required>
                        </div>
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="id_number">TIN/SSS/GSIS Number</label>
                            <input type="text" id="id_number" name="id_number" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="other_id">Other ID</label>
                    <input type="text" id="other_id" name="other_id">
                </div>
                
                <div class="form-group">
                    <label for="mothers_maiden_name">Mother's Maiden Name (Last, First, Middle)</label>
                    <input type="text" id="mothers_maiden_name" name="mothers_maiden_name" required>
                </div>
                
                <div class="form-group">
                    <label for="present_address">Present Address/Residential Address</label>
                    <input type="text" id="present_address" name="present_address" required>
                </div>
                
                <div class="form-row">
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="present_brgy_code">Brgy. Code</label>
                            <input type="text" id="present_brgy_code" name="present_brgy_code">
                        </div>
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="present_zip_code">Zip Code</label>
                            <input type="text" id="present_zip_code" name="present_zip_code" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="permanent_address">Permanent Address/Residential Address</label>
                    <input type="text" id="permanent_address" name="permanent_address" required>
                </div>
                
                <div class="form-row">
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="permanent_brgy_code">Brgy. Code</label>
                            <input type="text" id="permanent_brgy_code" name="permanent_brgy_code">
                        </div>
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="permanent_zip_code">Zip Code</label>
                            <input type="text" id="permanent_zip_code" name="permanent_zip_code" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col-2">
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
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="length_of_stay">Length of Stay/Tagal ng Paninirahan</label>
                            <input type="text" id="length_of_stay" name="length_of_stay" required>
                        </div>
                    </div>
                </div>
                
                <div class="section-divider"></div>
                
                <!-- Business Information Section -->
                <h2>Business/Source of Funds</h2>
                
                <div class="form-group">
                    <label for="primary_business">Primary Business/Pangunahing Negosyo</label>
                    <input type="text" id="primary_business" name="primary_business" required>
                </div>
                
                <div class="form-group">
                    <label for="business_address">Business Address</label>
                    <input type="text" id="business_address" name="business_address" required>
                </div>
                
                <div class="form-group">
                    <label for="years_in_business">Years in Business/Taon ng Negosyo</label>
                    <input type="number" id="years_in_business" name="years_in_business" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="other_income_source_1">Other Sources of Income (1)</label>
                    <input type="text" id="other_income_source_1" name="other_income_source_1">
                </div>
                
                <div class="form-group">
                    <label for="other_income_source_2">Other Sources of Income (2)</label>
                    <input type="text" id="other_income_source_2" name="other_income_source_2">
                </div>
                
                <div class="form-group">
                    <label for="other_income_source_3">Other Sources of Income (3)</label>
                    <input type="text" id="other_income_source_3" name="other_income_source_3">
                </div>
                
                <div class="form-group">
                    <label for="other_income_source_4">Other Sources of Income (4)</label>
                    <input type="text" id="other_income_source_4" name="other_income_source_4">
                </div>
                
                <div class="section-divider"></div>
                
                <!-- Spouse Information Section -->
                <h2>Spouse Information</h2>
                
                <div class="form-row">
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="spouse_name">Spouse's Name (Last, First, Middle)</label>
                            <input type="text" id="spouse_name" name="spouse_name">
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="spouse_occupation">Occupation/Trabaho</label>
                            <input type="text" id="spouse_occupation" name="spouse_occupation">
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="spouse_birthday">Birthday (mm/dd/yyyy)</label>
                            <input type="date" id="spouse_birthday" name="spouse_birthday">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="spouse_id_number">TIN/SSS/GSIS/Valid ID</label>
                            <input type="text" id="spouse_id_number" name="spouse_id_number">
                        </div>
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="spouse_age">Age/Edad</label>
                            <input type="number" id="spouse_age" name="spouse_age" min="18" max="100">
                        </div>
                    </div>
                </div>
                
                <div class="section-divider"></div>
                
                <!-- Beneficiaries Section -->
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
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                        <tr>
                            <td>
                                <div class="form-group">
                                    <input type="text" id="beneficiary_last_name_<?php echo $i; ?>" name="beneficiary_last_name_<?php echo $i; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <input type="text" id="beneficiary_first_name_<?php echo $i; ?>" name="beneficiary_first_name_<?php echo $i; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <input type="text" id="beneficiary_mi_<?php echo $i; ?>" name="beneficiary_mi_<?php echo $i; ?>" maxlength="1">
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <input type="date" id="beneficiary_dob_<?php echo $i; ?>" name="beneficiary_dob_<?php echo $i; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <select id="beneficiary_gender_<?php echo $i; ?>" name="beneficiary_gender_<?php echo $i; ?>">
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <input type="text" id="beneficiary_relationship_<?php echo $i; ?>" name="beneficiary_relationship_<?php echo $i; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <input type="checkbox" id="beneficiary_dependent_<?php echo $i; ?>" name="beneficiary_dependent_<?php echo $i; ?>" value="1">
                                </div>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                
                <div class="section-divider"></div>
                
                <!-- Trustee Section -->
                <h2>Designation of Trustee</h2>
                
                <div class="form-row">
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="trustee_name">Name of Trustee</label>
                            <input type="text" id="trustee_name" name="trustee_name">
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="trustee_dob">Date of Birth</label>
                            <input type="date" id="trustee_dob" name="trustee_dob">
                        </div>
                    </div>
                    <div class="form-col-3">
                        <div class="form-group">
                            <label for="trustee_relationship">Relationship to Applicant</label>
                            <input type="text" id="trustee_relationship" name="trustee_relationship">
                        </div>
                    </div>
                </div>
                
                <div class="section-divider"></div>
                
                <!-- Signatures Section -->
                <h2>Signatures and Dates</h2>
                
                <div class="form-row">
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="signing_date">Date of Signing</label>
                            <input type="date" id="signing_date" name="signing_date" required>
                        </div>
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="client_cid_no">CID No.</label>
                            <input type="text" id="client_cid_no" name="client_cid_no">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="proxy_name">Signature over Printed Name (for proxy)</label>
                    <input type="text" id="proxy_name" name="proxy_name">
                </div>
                
                <div class="form-group">
                    <label for="proxy_address">Address/Tirahan</label>
                    <input type="text" id="proxy_address" name="proxy_address">
                </div>
                
                <div class="form-row">
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="member_name">Name of Member (Borrower or Kapamilya)</label>
                            <input type="text" id="member_name" name="member_name">
                        </div>
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="member_signature_date">Date of Signature</label>
                            <input type="date" id="member_signature_date" name="member_signature_date">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="beneficiary_witness_name">Name of Beneficiary/Witness</label>
                            <input type="text" id="beneficiary_witness_name" name="beneficiary_witness_name">
                        </div>
                    </div>
                    <div class="form-col-2">
                        <div class="form-group">
                            <label for="beneficiary_signature_date">Date of Signature</label>
                            <input type="date" id="beneficiary_signature_date" name="beneficiary_signature_date">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </form>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate age from birthday
    const birthdayField = document.getElementById('birthday');
    const ageField = document.getElementById('age');
    
    if (birthdayField && ageField) {
        birthdayField.addEventListener('change', function() {
            if (this.value) {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                ageField.value = age;
            }
        });
    }
    
    // Auto-calculate spouse age from spouse birthday
    const spouseBirthdayField = document.getElementById('spouse_birthday');
    const spouseAgeField = document.getElementById('spouse_age');
    
    if (spouseBirthdayField && spouseAgeField) {
        spouseBirthdayField.addEventListener('change', function() {
            if (this.value) {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                spouseAgeField.value = age;
            }
        });
    }
    
    // Copy CID No. from personal info to signature section
    const cidNoField = document.getElementById('cid_no');
    const clientCidNoField = document.getElementById('client_cid_no');
    
    if (cidNoField && clientCidNoField) {
        cidNoField.addEventListener('input', function() {
            clientCidNoField.value = this.value;
        });
    }
    
    // Toggle visibility of spouse fields based on civil status
    const civilStatusRadios = document.querySelectorAll('input[name="civil_status"]');
    
    // Find the spouse section elements
    let spouseSection = null;
    document.querySelectorAll('h2').forEach(heading => {
        if (heading.textContent === 'Spouse Information') {
            spouseSection = {
                heading: heading,
                divider: heading.previousElementSibling,
                content: heading.nextElementSibling
            };
        }
    });
    
    if (civilStatusRadios.length && spouseSection) {
        // Function to toggle spouse section visibility
        const toggleSpouseSection = (isMarried) => {
            const displayValue = isMarried ? 'block' : 'none';
            spouseSection.heading.style.display = displayValue;
            spouseSection.divider.style.display = displayValue;
            
            // Find all form rows in the spouse section
            let currentElement = spouseSection.heading.nextElementSibling;
            while (currentElement && !currentElement.classList.contains('section-divider')) {
                currentElement.style.display = displayValue;
                currentElement = currentElement.nextElementSibling;
            }
        };
        
        // Add event listeners to civil status radio buttons
        civilStatusRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                toggleSpouseSection(this.value === 'Married');
            });
        });
        
        // Initial check on page load
        const marriedRadio = document.getElementById('civil_married');
        toggleSpouseSection(marriedRadio && marriedRadio.checked);
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
    
    // Set default date for signing date
    const signingDateField = document.getElementById('signing_date');
    if (signingDateField && !signingDateField.value) {
        const today = new Date();
        const year = today.getFullYear();
        let month = today.getMonth() + 1;
        let day = today.getDate();
        
        month = month < 10 ? '0' + month : month;
        day = day < 10 ? '0' + day : day;
        
        signingDateField.value = `${year}-${month}-${day}`;
    }
});
</script>

<?php include '../includes/footer.php'; ?> 