/**
 * TSPI Membership Form JavaScript
 * This file contains all the functionality for the membership form
 */

document.addEventListener('DOMContentLoaded', function() {
    // Show loader when page starts loading
    const pageLoader = document.querySelector('.page-loader');
    
    // Hide the loader once the page is fully loaded
    window.addEventListener('load', function() {
        if (pageLoader) {
            // Add a slight delay to ensure all assets are loaded
            setTimeout(function() {
                pageLoader.classList.add('fade-out');
                
                // Remove from DOM after animation completes
                setTimeout(function() {
                    pageLoader.style.display = 'none';
                }, 500);
            }, 500);
        }
    });
    
    initializeMembershipForm();
    initializeAgreementModal();
    initializeTooltips(); // Initialize tooltips
    
    // Additional check for disclaimer checkbox and submit button
    const submitButton = document.getElementById('submit_application_btn');
    const disclaimerCheckbox = document.getElementById('disclaimer_agreement');
    
    if (submitButton && disclaimerCheckbox) {
        // Ensure button is properly disabled on page load
        submitButton.disabled = !disclaimerCheckbox.checked;
    }
    
    // Initialize ID preview functionality
    initializeIdPreview();
    
    // Wrap beneficiaries table with scrollable container
    const beneficiariesTable = document.querySelector('.beneficiaries-table');
    if (beneficiariesTable) {
        const wrapper = document.createElement('div');
        wrapper.className = 'beneficiaries-table-wrapper';
        
        // Insert wrapper before table in the DOM
        beneficiariesTable.parentNode.insertBefore(wrapper, beneficiariesTable);
        
        // Move table into wrapper
        wrapper.appendChild(beneficiariesTable);
    }
});

/**
 * Initialize main membership form functionality
 */
function initializeMembershipForm() {
    const form = document.getElementById('membership-form');
    const submitButton = document.getElementById('submit_application_btn');
    const disclaimerCheckbox = document.getElementById('disclaimer_agreement');
    
    // Variables for dynamic rows
    let beneficiaryRowCount = 1; // Start with 1 row as default
    const maxBeneficiaryRows = 5; // Maximum number of beneficiary rows (initial + 5 additional)
    let incomeSourceCount = 0;
    let otherValidIdActive = false;
    
    // Set default values for hidden fields
    if (document.getElementById('branch')) document.getElementById('branch').value = null;
    if (document.getElementById('cid_no')) document.getElementById('cid_no').value = "";
    if (document.getElementById('center_no')) document.getElementById('center_no').value = "000";
    
    // Ensure BLIP is checked and cannot be unchecked
    const blipCheckbox = document.getElementById('plan_blip');
    if (blipCheckbox) {
        blipCheckbox.checked = true;
        blipCheckbox.setAttribute('checked', 'checked');
        blipCheckbox.onclick = function() {
            return false; // Prevent unchecking
        };
    }

    // Initialize form elements and handlers
    initFormValidation();
    initFormNavigation();
    initDatePickers();
    initSignaturePads();
    initDynamicFields();
    initFormStateManager();
}

/**
 * Initialize the user agreement modal functionality
 */
function initializeAgreementModal() {
    // User Agreement Modal Functionality
    const agreementModal = document.getElementById('user-agreement-modal');
    const pageOverlay = document.getElementById('page-overlay');
    const agreeBtn = document.getElementById('agree-button');
    const disagreeBtn = document.getElementById('disagree-button');
    const beneficiaryRadios = document.querySelectorAll('input[name="add_beneficiary"]');
    const beneficiaryCountContainer = document.getElementById('beneficiary-count-container');
    const beneficiaryCountInput = document.getElementById('beneficiary-count');
    
    // Check if we're on a success page
    const successMessage = document.querySelector('.message.success');
    const isSuccessPage = successMessage !== null;
    
    // Helper function to show modal with animation
    const showAgreementModalWithAnimation = () => {
        if (!agreementModal || !pageOverlay) return;
        
        // First display the elements but keep them invisible
        agreementModal.classList.add('active');
        agreementModal.style.opacity = '0';
        pageOverlay.classList.add('active');
        pageOverlay.style.opacity = '0';
        document.body.classList.add('modal-open'); // Disable scrolling
        
        // Then trigger animation after a delay of 1 second
        setTimeout(() => {
            // Fade in the overlay first
            pageOverlay.style.opacity = '1';
            pageOverlay.style.backdropFilter = 'blur(5px)';
            
            // Then fade in the modal with a slight delay
            setTimeout(() => {
                agreementModal.style.opacity = '1';
                const modalContent = agreementModal.querySelector('.agreement-modal-content');
                if (modalContent) {
                    modalContent.style.transform = 'scale(1)';
                    modalContent.style.opacity = '1';
                }
            }, 200);
        }, 1000);
    };
    
    // Helper function to hide modal with animation
    const hideAgreementModalWithAnimation = () => {
        if (!agreementModal || !pageOverlay) return;
        
        // First animate the content
        const modalContent = agreementModal.querySelector('.agreement-modal-content');
        if (modalContent) {
            modalContent.style.transform = 'scale(0.95)';
            modalContent.style.opacity = '0';
        }
        
        // Then hide the modal after animation completes
        setTimeout(() => {
            agreementModal.classList.remove('active');
            pageOverlay.classList.remove('active');
            document.body.classList.remove('modal-open');
        }, 300);
    };
    
    // Show agreement modal immediately if not success page
    if (!isSuccessPage && agreementModal && pageOverlay) {
        showAgreementModalWithAnimation();
    }
    
    // Initialize beneficiary count with default value
    if (beneficiaryCountInput) {
        beneficiaryCountInput.value = "1"; // Default to 1 beneficiary
    }
    
    // Handle beneficiary radio buttons
    if (beneficiaryRadios.length > 0) {
        beneficiaryRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'yes' && beneficiaryCountContainer) {
                    beneficiaryCountContainer.style.display = 'block';
                } else if (beneficiaryCountContainer) {
                    beneficiaryCountContainer.style.display = 'none';
                }
            });
        });
    }
    
    // Handle agree action
    if (agreeBtn) {
        agreeBtn.addEventListener('click', function() {
            hideAgreementModalWithAnimation();
            setTimeout(() => {
                handleAgreementConfirmed();
                showCustomAlert('Agreement accepted. Reload the page if you want to add more beneficiaries.', 'success');
            }, 300);
        });
    }
    
    // Handle disagree action
    if (disagreeBtn) {
        disagreeBtn.addEventListener('click', function() {
            hideAgreementModalWithAnimation();
            showCustomAlert('You must agree to the terms to continue using this application. The page will now redirect.', 'warning');
            
            // Get the SITE_URL from a hidden input or use a default
            const siteUrl = document.querySelector('input[name="site_url"]')?.value || '/';
            
            // Delay redirect to allow alert to be seen
            setTimeout(() => {
                window.location.href = siteUrl + '/homepage.php';
            }, 3000);
        });
    }
}

/**
 * Function to validate beneficiary count input
 */
function validateBeneficiaryCount(input) {
    // Ensure the value is between 1 and 5
    const value = parseInt(input.value, 10);
    if (isNaN(value) || value < 1) {
        input.value = 1;
    } else if (value > 5) {
        input.value = 5;
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const form = document.getElementById('membership-form');
    const submitButton = document.getElementById('submit_application_btn');
    const disclaimerCheckbox = document.getElementById('disclaimer_agreement');
    
    // Handle submit button based on disclaimer checkbox
    if (submitButton && disclaimerCheckbox) {
        // Initially disable the submit button if checkbox is not checked
        submitButton.disabled = !disclaimerCheckbox.checked;
        
        // Update submit button state when checkbox changes
        disclaimerCheckbox.addEventListener('change', function() {
            submitButton.disabled = !this.checked;
        });
        
        // Ensure the button is properly disabled initially
        setTimeout(() => {
            submitButton.disabled = !disclaimerCheckbox.checked;
        }, 100);
    }
    
    // Function to mark the form as attempted (for validation styling)
    const markCurrentPageAttempted = () => {
        if (!form) return;
        
        // First, remove the attempted class from all elements to reset
        document.querySelectorAll('input, select, textarea').forEach(el => {
            el.classList.remove('attempted');
        });
        
        // Then add it only to the current page's elements
        const activePage = document.querySelector('.form-page-content.active');
        if (activePage) {
            activePage.querySelectorAll('input, select, textarea').forEach(el => {
                el.classList.add('attempted');
            });
        }
    };
    
    // Convert all text inputs to uppercase on submit
    const uppercaseTextInputs = () => {
        // Save dropdown values before transforming
        const genderValue = document.getElementById('gender')?.value;
        const civilStatusValue = document.getElementById('civil_status')?.value;
        
        // Process all text inputs
        document.querySelectorAll('input[type="text"], textarea').forEach(input => {
            if (input.value && !input.readOnly) {
                // Force uppercase and set the value back to the input
                input.value = input.value.toUpperCase().trim();
            }
        });
        
        // For select elements, modify only the display text, not the value
        document.querySelectorAll('select').forEach(select => {
            if (select.value && select.options[select.selectedIndex]) {
                // Only uppercase the text content of the option, not the value
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption) {
                    selectedOption.text = selectedOption.text.toUpperCase();
                }
            }
        });
        
        // Restore dropdown values to ensure they weren't changed
        setTimeout(() => {
            if (genderValue) document.getElementById('gender').value = genderValue;
            if (civilStatusValue) document.getElementById('civil_status').value = civilStatusValue;
        }, 10);
    };
    
    // Handle the submitApplicationBtn click to show review modal
    if (submitButton) {
        submitButton.addEventListener('click', function(event) {
            event.preventDefault();
            
            // IMPORTANT: Capture current dropdown values BEFORE any processing
            const currentGenderValue = document.getElementById('gender')?.value;
            const currentCivilStatusValue = document.getElementById('civil_status')?.value;
            
            console.log("Before validation - Gender: ", currentGenderValue);
            console.log("Before validation - Civil Status: ", currentCivilStatusValue);
            
            // Store these in the form state manager
            if (window.formStateManager) {
                window.formStateManager.state.gender = currentGenderValue;
                window.formStateManager.state.civilStatus = currentCivilStatusValue;
            }
            
            // Now do the form validation and processing
            markCurrentPageAttempted();
            uppercaseTextInputs();
            
            // If validation fails, restore dropdown values and return
            if (!validateCurrentPageFields()) {
                // Force restore values from our saved copies
                setTimeout(() => {
                    if (document.getElementById('gender')) 
                        document.getElementById('gender').value = currentGenderValue;
                    if (document.getElementById('civil_status'))
                        document.getElementById('civil_status').value = currentCivilStatusValue;
                    
                    console.log("Restored after failed validation - Gender: ", currentGenderValue);
                    console.log("Restored after failed validation - Civil Status: ", currentCivilStatusValue);
                }, 10);
                return;
            }
            
            // Validation passed, show review modal
            showReviewModal();
            
            // After showing modal, restore dropdown values anyway as a fallback
            setTimeout(() => {
                if (document.getElementById('gender'))
                    document.getElementById('gender').value = currentGenderValue;
                if (document.getElementById('civil_status'))
                    document.getElementById('civil_status').value = currentCivilStatusValue;
                
                // Update state manager values for any additional processing
                if (window.formStateManager) {
                    window.formStateManager.state.gender = currentGenderValue;
                    window.formStateManager.state.civilStatus = currentCivilStatusValue;
                }
                console.log("Restored after showing modal - Gender: ", currentGenderValue);
                console.log("Restored after showing modal - Civil Status: ", currentCivilStatusValue);
            }, 100);
        });
    }
}

/**
 * Validate if user is of legal age (18 or older)
 */
function validateAge(birthdateStr) {
    if (!birthdateStr) return false;
    
    // Parse the birthdate in MM/DD/YYYY format
    const parts = birthdateStr.split('/');
    if (parts.length !== 3) return false;
    
    const birthDate = new Date(
        parseInt(parts[2]),  // Year
        parseInt(parts[0]) - 1,  // Month (0-based)
        parseInt(parts[1])   // Day
    );
    
    // Calculate age
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    // Adjust age if birthday hasn't occurred yet this year
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age >= 18;
}

/**
 * Validate the current form page
 */
function validateCurrentPageFields() {
    // Mark form as attempted to show validation styling
    const form = document.getElementById('membership-form');
    if (!form) return true;
    
    // First, remove the attempted class from all elements to reset
    document.querySelectorAll('input, select, textarea').forEach(el => {
        el.classList.remove('attempted');
    });
    
    // Then add it only to the current page's elements
    const activePage = document.querySelector('.form-page-content.active');
    if (activePage) {
        activePage.querySelectorAll('input, select, textarea').forEach(el => {
            el.classList.add('attempted');
        });
    }
    
    let isValid = true;
    let invalidElements = [];
    if (!activePage) return true;
    
    // Check if we're on page 1 with the birthdate field
    if (activePage.id === 'form-page-1') {
        const birthdateField = document.getElementById('birthday');
        if (birthdateField && birthdateField.value) {
            // Check if user is of legal age
            if (!validateAge(birthdateField.value)) {
                showCustomAlert('You cannot apply for a membership if you are underaged.', 'error');
                isValid = false;
                invalidElements.push(birthdateField);
                birthdateField.classList.add('attempted');
                birthdateField.style.borderColor = 'red';
                
                // Redirect to homepage after 5 seconds
                setTimeout(() => {
                    const siteUrl = document.querySelector('input[name="site_url"]')?.value || '/';
                    window.location.href = siteUrl + '/homepage.php';
                }, 5000);
                
                return false;
            }
        }
    }
    
    // Check if we're on page 2 and has spouse birthday field
    if (activePage.id === 'form-page-2') {
        const spouseBirthdayField = document.getElementById('spouse_birthday');
        const civilStatusSelect = document.getElementById('civil_status');
        const isMarried = civilStatusSelect && civilStatusSelect.value === 'MARRIED';
        
        if (isMarried && spouseBirthdayField && spouseBirthdayField.value) {
            // Check if spouse is of legal age
            if (!validateAge(spouseBirthdayField.value)) {
                showCustomAlert('Your spouse cannot be underaged. You cannot continue with this application.', 'error');
                isValid = false;
                invalidElements.push(spouseBirthdayField);
                spouseBirthdayField.classList.add('attempted');
                spouseBirthdayField.style.borderColor = 'red';
                
                // Redirect to homepage after 5 seconds
                setTimeout(() => {
                    const siteUrl = document.querySelector('input[name="site_url"]')?.value || '/';
                    window.location.href = siteUrl + '/homepage.php';
                }, 5000);
                
                return false;
            }
        }
    }
    
    // Check file inputs for image types only
    const fileInputs = activePage.querySelectorAll('input[type="file"]');
    fileInputs.forEach(fileInput => {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const fileType = file.type;
            
            // Check if it's not an image file (jpg or png)
            if (!fileType.match(/^image\/(jpeg|png)$/)) {
                showCustomAlert('Only image files (JPG, PNG) are allowed for uploads.', 'error');
                isValid = false;
                invalidElements.push(fileInput);
                
                // Clear the file input
                fileInput.value = '';
            }
        }
    });
    
    // Check if we're on page 2 and the user is married
    const civilStatusSelect = document.getElementById('civil_status');
    const isMarried = civilStatusSelect && civilStatusSelect.value === 'MARRIED';
    const isPage2 = activePage.id === 'form-page-2';
    
    if (isPage2 && isMarried) {
        // Make spouse fields required
        const spouseFields = [
            'spouse_last_name', 
            'spouse_first_name',
            'spouse_middle_name',
            'spouse_birthday', 
            'spouse_occupation', 
            'spouse_id_number',
            'spouse_valid_id_upload'
        ];
        
        spouseFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.setAttribute('required', 'required');
            }
        });
    }

    const inputs = activePage.querySelectorAll('input[required], textarea[required]');
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
    
    // Validate select fields separately to check for actual selection (not just first option)
    const selects = activePage.querySelectorAll('select[required]');
    selects.forEach(select => {
        if (select.selectedIndex === 0) {
            isValid = false;
            invalidElements.push(select);
            select.classList.add('attempted');
            select.style.borderColor = 'red';
        }
    });
    
    if (activePage.id === 'form-page-1') {
        // Check classification
        const classChecked = document.querySelector('input[name="classification"]:checked');
        if (!classChecked) {
            isValid = false;
            invalidElements.push(document.getElementById('class_tkp') || document.getElementById('class_borrower'));
        }
        
        // Check for at least one phone number
        const cellPhone = document.getElementById('cell_phone');
        const contactNo = document.getElementById('contact_no');
        if (cellPhone && !cellPhone.value.trim()) {
            isValid = false;
            invalidElements.push(cellPhone);
        }
    }
    
    // Check for signatures on page 3
    if (activePage.id === 'form-page-3') {
        // Always check member signature
        const memberPad = document.getElementById('member_signature_canvas')?._signaturePad;
        const memberSignatureInput = document.getElementById('member_signature');
        
        // Check if signature exists by checking the input value, which is set on endStroke
        if (!memberSignatureInput || !memberSignatureInput.value) {
            isValid = false;
            showCustomAlert('Please provide your signature in the Member Signature field', 'error');
            document.getElementById('member_signature_canvas').scrollIntoView({behavior:'smooth', block:'center'});
            return false;
        }
        
        // Check if user has selected to add beneficiaries
        const hasBeneficiaries = document.getElementById('has_beneficiaries')?.value === 'yes';
        
        // Only check beneficiary signature if beneficiary section is visible and required
        const beneficiarySignatureSection = document.getElementById('beneficiary-signature-section');
        const beneficiarySignatureInput = document.getElementById('beneficiary_signature');
        
        if (hasBeneficiaries && 
            beneficiarySignatureSection && 
            beneficiarySignatureSection.style.display !== 'none') {
            if (!beneficiarySignatureInput || !beneficiarySignatureInput.value) {
                isValid = false;
                showCustomAlert('Please provide a signature in the Beneficiary Signature field', 'error');
                document.getElementById('beneficiary_signature_canvas').scrollIntoView({behavior:'smooth', block:'center'});
                return false;
            }
        }
    }
    
    // Beneficiary rows validation
    if (activePage.id === 'form-page-3') {
        // Check if user has selected to add beneficiaries
        const hasBeneficiaries = document.getElementById('has_beneficiaries')?.value === 'yes';
        
        // Only validate beneficiary section if it's visible and required
        const beneficiariesSection = document.getElementById('beneficiaries-section');
        if (hasBeneficiaries && beneficiariesSection && beneficiariesSection.style.display !== 'none') {
            // Get beneficiary count
            const beneficiaryCount = parseInt(document.getElementById('beneficiary-count')?.value || '1', 10);
            
            // Validate visible beneficiary rows (based on count)
            document.querySelectorAll('.beneficiary-row').forEach((row, index) => {
                // Skip rows that are beyond the selected count
                if (index >= beneficiaryCount) return;
                
                const lastName = row.querySelector('input[name="beneficiary_last_name[]"]');
                const firstName = row.querySelector('input[name="beneficiary_first_name[]"]');
                const dob = row.querySelector('input[name="beneficiary_dob[]"]');
                const gender = row.querySelector('select[name="beneficiary_gender[]"]');
                const relationship = row.querySelector('input[name="beneficiary_relationship[]"]');
                
                // Check each required field
                [lastName, firstName, dob, gender, relationship].forEach(field => {
                    if (field && (!field.value || field.value.trim() === '')) {
                        isValid = false;
                        invalidElements.push(field);
                        field.classList.add('attempted');
                        field.style.borderColor = 'red';
                    }
                });
            });
            
            // Validate trustee fields if section is visible
            const trusteeSection = document.getElementById('trustee-section');
            if (trusteeSection && trusteeSection.style.display !== 'none') {
                const trusteeFields = ['trustee_name', 'trustee_dob', 'trustee_relationship'];
                trusteeFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && (!field.value || field.value.trim() === '')) {
                        isValid = false;
                        invalidElements.push(field);
                        field.classList.add('attempted');
                        field.style.borderColor = 'red';
                    }
                });
            }
        }
    }
    
    if (!isValid) {
        showCustomAlert('Please fill out all required fields.', 'error');
        if (invalidElements.length) {
            invalidElements[0].scrollIntoView({behavior:'smooth', block:'center'});
            invalidElements[0].focus();
        }
        return false;
    }
    return true;
}

/**
 * Initialize form navigation (pagination)
 */
function initFormNavigation() {
    const pages = document.querySelectorAll('.form-page-content');
    const prevBtn = document.getElementById('prev_page_btn');
    const nextBtn = document.getElementById('next_page_btn');
    const pageIndicator = document.getElementById('page_indicator');
    const submitApplicationBtn = document.getElementById('submit_application_btn');

    let currentPage = 0;
    const totalPages = pages.length;

    function updatePageDisplay() {
        // First, save the current state of important dropdowns if we're on page 1
        let savedGenderValue = null;
        let savedCivilStatusValue = null;
        
        if (currentPage === 0) {
            const genderSelect = document.getElementById('gender');
            const civilStatusSelect = document.getElementById('civil_status');
            if (genderSelect) savedGenderValue = genderSelect.value;
            if (civilStatusSelect) savedCivilStatusValue = civilStatusSelect.value;
        }
        
        // Update page display
        pages.forEach((page, index) => {
            page.classList.toggle('active', index === currentPage);
        });
        if (pageIndicator) pageIndicator.textContent = `Page ${currentPage + 1} of ${totalPages}`;
        
        if (prevBtn) {
            prevBtn.style.visibility = currentPage === 0 ? 'hidden' : 'visible';
        }
        if (nextBtn) {
            nextBtn.style.display = currentPage === totalPages - 1 ? 'none' : 'inline-flex';
        }
        if (submitApplicationBtn) {
            submitApplicationBtn.style.display = currentPage === totalPages - 1 ? 'inline-flex' : 'none';
            // Force update the submit button state whenever it becomes visible
            if (currentPage === totalPages - 1) {
                forceUpdateSubmitButtonState();
                
                // Add event listener to disclaimer checkbox on the final page
                const disclaimerCheckbox = document.getElementById('disclaimer_agreement');
                if (disclaimerCheckbox) {
                    // Remove any existing listeners first to prevent duplicates
                    disclaimerCheckbox.removeEventListener('change', forceUpdateSubmitButtonState);
                    // Add the new listener
                    disclaimerCheckbox.addEventListener('change', forceUpdateSubmitButtonState);
                }
            }
        }
        
        // Restore saved dropdown values if navigating back to page 1
        if (currentPage === 0) {
            const genderSelect = document.getElementById('gender');
            const civilStatusSelect = document.getElementById('civil_status');
            
            // Restore values if they were previously set
            if (genderSelect && savedGenderValue) {
                genderSelect.value = savedGenderValue;
            }
            if (civilStatusSelect && savedCivilStatusValue) {
                civilStatusSelect.value = savedCivilStatusValue;
            }
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
    
    // Make updatePageDisplay available globally
    window.updatePageDisplay = updatePageDisplay;
    
    // Set the current page
    window.setCurrentPage = function(pageNum) {
        if (pageNum >= 0 && pageNum < totalPages) {
            currentPage = pageNum;
            updatePageDisplay();
            
            // Always scroll to top when changing pages - improved for mobile
            setTimeout(() => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }, 100);
        }
    };
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (validateCurrentPageFields()) {
                if (currentPage < totalPages - 1) {
                    currentPage++;
                    updatePageDisplay();
                    
                    // Smooth scroll to top with a slight delay
                    setTimeout(() => {
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }, 100);
                }
            }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentPage > 0) {
                currentPage--;
                updatePageDisplay();
                
                // Smooth scroll to top with a slight delay
                setTimeout(() => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }, 100);
            }
        });
    }
    
    // Initial page setup
    updatePageDisplay();
}

/**
 * Initialize date pickers
 */
function initDatePickers() {
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
        
        // Initialize datepickers for beneficiary DOBs
        const beneficiaryDobs = document.querySelectorAll('.beneficiary-dob');
        beneficiaryDobs.forEach(dobField => {
            new Pikaday({
                field: dobField,
                format: 'MM/DD/YYYY',
                yearRange: [1900, new Date().getFullYear()],
                maxDate: new Date(),
                toString(date, format) {
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const day = date.getDate().toString().padStart(2, '0');
                    const year = date.getFullYear();
                    return `${month}/${day}/${year}`;
                }
            });
            validateDate(dobField);
        });
        
        // Trustee birthday field
        if (document.getElementById('trustee_dob')) {
            const trusteeDobField = document.getElementById('trustee_dob');
            new Pikaday({
                field: trusteeDobField,
                format: 'MM/DD/YYYY',
                yearRange: [1900, new Date().getFullYear()],
                maxDate: new Date(),
                toString(date, format) {
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const day = date.getDate().toString().padStart(2, '0');
                    const year = date.getFullYear();
                    return `${month}/${day}/${year}`;
                }
            });
            validateDate(trusteeDobField);
        }
    }
}

/**
 * Initialize signature pads
 */
function initSignaturePads() {
    if (typeof SignaturePad !== 'undefined') {
        // Member signature
        const memberCanvas = document.getElementById('member_signature_canvas');
        const memberSignatureInput = document.getElementById('member_signature');
        
        if (memberCanvas) {
            const memberPad = new SignaturePad(memberCanvas, {
                backgroundColor: 'rgba(0, 0, 0, 0)',
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
                });
            }
            
            // Save on any change to ensure we catch all signature actions
            memberPad.addEventListener('endStroke', function() {
                if (memberSignatureInput) {
                    memberSignatureInput.value = memberPad.toDataURL();
                }
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
                backgroundColor: 'rgba(0, 0, 0, 0)',
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
                });
            }
            
            // Save on any change to ensure we catch all signature actions
            beneficiaryPad.addEventListener('endStroke', function() {
                if (beneficiarySignatureInput) {
                    beneficiarySignatureInput.value = beneficiaryPad.toDataURL();
                }
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
}

/**
 * Initialize dynamic fields like other income sources and other valid IDs
 */
function initDynamicFields() {
    // Convert text inputs to uppercase as the user types
    document.querySelectorAll('input[type="text"], textarea').forEach(input => {
        input.addEventListener('input', function() {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(start, end);
        });
    });
    
    // Phone number and telephone number - restrict to numbers only
    const phoneField = document.getElementById('cell_phone');
    const telephoneField = document.getElementById('contact_no');
    
    // Function to restrict input to numbers only
    const restrictToNumbers = (event) => {
        // Allow: backspace, delete, tab, escape, enter
        if ([46, 8, 9, 27, 13].indexOf(event.keyCode) !== -1 ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (event.keyCode === 65 && event.ctrlKey === true) ||
            (event.keyCode === 67 && event.ctrlKey === true) ||
            (event.keyCode === 86 && event.ctrlKey === true) ||
            (event.keyCode === 88 && event.ctrlKey === true) ||
            // Allow: home, end, left, right
            (event.keyCode >= 35 && event.keyCode <= 39)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress if not
        if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && 
            (event.keyCode < 96 || event.keyCode > 105)) {
            event.preventDefault();
        }
    };
    
    // Function to clean any non-numeric characters on paste or blur
    const cleanNonNumeric = (event) => {
        const input = event.target;
        input.value = input.value.replace(/[^0-9]/g, '');
    };
    
    // Apply restrictions to phone field
    if (phoneField) {
        phoneField.addEventListener('keydown', restrictToNumbers);
        phoneField.addEventListener('paste', cleanNonNumeric);
        phoneField.addEventListener('blur', cleanNonNumeric);
    }
    
    // Apply restrictions to telephone field
    if (telephoneField) {
        telephoneField.addEventListener('keydown', restrictToNumbers);
        telephoneField.addEventListener('paste', cleanNonNumeric);
        telephoneField.addEventListener('blur', cleanNonNumeric);
    }
    
    // Other income sources management
    const addOtherIncomeSourceBtn = document.getElementById('add_other_income_source_btn');
    const otherIncomeSourcesContainer = document.getElementById('other_income_sources_container');
    let incomeSourceCount = 0;
    
    if (addOtherIncomeSourceBtn && otherIncomeSourcesContainer) {
        addOtherIncomeSourceBtn.addEventListener('click', function() {
            addOtherIncomeSource();
        });
    }
    
    function addOtherIncomeSource() {
        if (!otherIncomeSourcesContainer) return null;
        
        if (incomeSourceCount >= 4) {
            alert('Maximum 4 additional income sources allowed.');
            return null;
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
            });
        }
        
        return sourceContainer;
    }
    
    // Function to reindex income sources after removal
    function updateIncomeSourceIndices() {
        if (!otherIncomeSourcesContainer) return;
        
        const sources = otherIncomeSourcesContainer.querySelectorAll('.other-income-source-item');
        
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
    
    // Other valid ID management
    const addOtherValidIdBtn = document.getElementById('add_other_valid_id_btn');
    const otherValidIdsContainer = document.getElementById('other_valid_ids_container');
    let otherValidIdActive = false;
    
    if (addOtherValidIdBtn && otherValidIdsContainer) {
        addOtherValidIdBtn.addEventListener('click', function() {
            addOtherValidId();
        });
    }
    
    function addOtherValidId() {
        if (!otherValidIdsContainer) return null;
        
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
            });
        }
        
        return idContainer;
    }
    
    // Toggle spouse section visibility based on civil status
    const civilStatusSelect = document.getElementById('civil_status');
    const spouseInfoSection = document.getElementById('spouse_information_section');

    if (civilStatusSelect && spouseInfoSection) {
        // Define the toggle function
        const toggleSpouseSection = (isMarried) => {
            console.log('Toggle spouse section - married:', isMarried); // Add debugging
            spouseInfoSection.style.display = isMarried ? 'block' : 'none';
            
            // Update spouse fields required status based on marriage status
            if (!isMarried) {
                spouseInfoSection.querySelectorAll('input, select').forEach(input => {
                    // Remove required attribute
                    input.removeAttribute('required');
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            } else {
                // Make spouse fields required
                const requiredFields = [
                    'spouse_last_name', 
                    'spouse_first_name',
                    'spouse_middle_name',
                    'spouse_birthday', 
                    'spouse_occupation', 
                    'spouse_id_number',
                    'spouse_valid_id_upload'
                ];
                
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.setAttribute('required', 'required');
                    }
                });
            }
        };

        // Add change event listener
        civilStatusSelect.addEventListener('change', function() {
            console.log('Civil status changed to:', this.value); // Add debugging
            toggleSpouseSection(this.value === 'MARRIED');
        });

        // Ensure we run this after the DOM is fully loaded
        setTimeout(() => {
            console.log('Initial civil status check:', civilStatusSelect.value); // Add debugging
            toggleSpouseSection(civilStatusSelect.value === 'MARRIED');
        }, 100);
        
        // Also run the check during initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded civil status check:', civilStatusSelect.value); // Add debugging
            toggleSpouseSection(civilStatusSelect.value === 'MARRIED');
        });
    }
    
    // Auto-calculate spouse age from spouse birthday
    const spouseBirthdayField = document.getElementById('spouse_birthday');
    const spouseAgeField = document.getElementById('spouse_age');
    
    if (spouseBirthdayField && spouseAgeField) {
        spouseBirthdayField.addEventListener('change', function() {
            calculateAge(this.value, spouseAgeField);
        });
    }
    
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
}

/**
 * Handle the agreement confirmation
 */
function handleAgreementConfirmed() {
    // Get modal and overlay elements
    const agreementModal = document.getElementById('user-agreement-modal');
    const pageOverlay = document.getElementById('page-overlay');
    
    // Get beneficiary selection
    const wantsBeneficiaries = document.querySelector('input[name="add_beneficiary"]:checked')?.value === 'yes';
    const beneficiaryCountInput = document.getElementById('beneficiary-count');
    const beneficiaryCount = wantsBeneficiaries && beneficiaryCountInput ? 
                            parseInt(beneficiaryCountInput.value, 10) || 0 : 0;
    
    // Hide modal and overlay
    if (agreementModal) agreementModal.classList.remove('active');
    if (pageOverlay) pageOverlay.classList.remove('active');
    document.body.classList.remove('modal-open'); // Re-enable scrolling
    
    // Get all sections to manage visibility
    const beneficiariesSection = document.getElementById('beneficiaries-section');
    const trusteeSection = document.getElementById('trustee-section');
    const beneficiarySignatureSection = document.getElementById('beneficiary-signature-section');
    const beneficiaryNameField = document.getElementById('beneficiary-name-field');
    
    // Handle beneficiary rows if needed
    if (wantsBeneficiaries) {
        // This just shows the appropriate number of rows since we already have 5 static rows
        const rows = document.querySelectorAll('.beneficiary-row');
        const visibleCount = beneficiaryCount;
        
        rows.forEach((row, index) => {
            if (index < visibleCount) {
                row.style.display = '';
                
                // Make visible beneficiary fields required
                row.querySelectorAll('input:not([type="checkbox"]), select').forEach(field => {
                    field.setAttribute('required', 'required');
                });
            } else {
                row.style.display = 'none';
                
                // Remove required attribute from hidden rows
                row.querySelectorAll('input:not([type="checkbox"]), select').forEach(field => {
                    field.removeAttribute('required');
                });
            }
        });
        
        // Make trustee fields required
        if (trusteeSection) {
            trusteeSection.querySelectorAll('input').forEach(field => {
                field.setAttribute('required', 'required');
            });
        }
        
        // Make beneficiary signature required
        if (beneficiarySignatureSection) {
            const beneficiaryNameInput = document.getElementById('sig_beneficiary_name');
            if (beneficiaryNameInput) {
                beneficiaryNameInput.setAttribute('required', 'required');
            }
        }
        
        // Make sure the beneficiary sections are visible
        if (beneficiariesSection) beneficiariesSection.style.display = '';
        if (trusteeSection) trusteeSection.style.display = '';
        if (beneficiarySignatureSection) beneficiarySignatureSection.style.display = '';
        if (beneficiaryNameField) beneficiaryNameField.style.display = '';
    } else {
        // Hide beneficiary-related sections
        if (beneficiariesSection) beneficiariesSection.style.display = 'none';
        if (trusteeSection) trusteeSection.style.display = 'none';
        if (beneficiarySignatureSection) beneficiarySignatureSection.style.display = 'none';
        if (beneficiaryNameField) beneficiaryNameField.style.display = 'none';
        
        // Remove required attribute from all beneficiary fields
        document.querySelectorAll('.beneficiary-row input, .beneficiary-row select').forEach(field => {
            field.removeAttribute('required');
        });
        
        // Remove required attribute from trustee fields
        if (trusteeSection) {
            trusteeSection.querySelectorAll('input').forEach(field => {
                field.removeAttribute('required');
            });
        }
        
        // Remove required from beneficiary signature
        const beneficiaryNameInput = document.getElementById('sig_beneficiary_name');
        if (beneficiaryNameInput) {
            beneficiaryNameInput.removeAttribute('required');
        }
    }
    
    // Store the user's beneficiary choice in a hidden input for later validation
    const hasBeneficiariesInput = document.createElement('input');
    hasBeneficiariesInput.type = 'hidden';
    hasBeneficiariesInput.id = 'has_beneficiaries';
    hasBeneficiariesInput.value = wantsBeneficiaries ? 'yes' : 'no';
    document.getElementById('membership-form').appendChild(hasBeneficiariesInput);
}

/**
 * Initialize the review modal
 */
function showReviewModal() {
    // Important: Save the current state before showing the modal
    if (window.formStateManager) {
        const genderSelect = document.getElementById('gender');
        const civilStatusSelect = document.getElementById('civil_status');
        
        if (genderSelect && genderSelect.value) {
            window.formStateManager.state.gender = genderSelect.value;
        }
        
        if (civilStatusSelect && civilStatusSelect.value) {
            window.formStateManager.state.civilStatus = civilStatusSelect.value;
        }
    }

    const modal = document.getElementById('review-modal');
    const reviewContent = document.getElementById('review-content');
    
    if (!modal || !reviewContent) return;
    
    reviewContent.innerHTML = '';
    
    // Calculate scrollbar width
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    
    // Add padding to body to prevent content shift when scrollbar disappears
    if (scrollbarWidth > 0) {
        document.body.style.paddingRight = scrollbarWidth + 'px';
    }
    
    // Show modal with fade-in effect
    modal.style.display = 'block';
    modal.style.opacity = '0';
    document.body.classList.add('modal-open'); // Prevent scrolling
    
    // Add slight delay before animation
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.classList.add('active');
        
        // Animate modal content
        setTimeout(() => {
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.transform = 'scale(1)';
                modalContent.style.opacity = '1';
            }
        }, 100);
    }, 50);
    
    // Personal Information section
    const personalSection = document.createElement('div');
    personalSection.className = 'review-section';
    personalSection.innerHTML = '<h3>Personal Information</h3>';
    
    // Get selected plans
    const selectedPlans = [];
    document.querySelectorAll('input[name="plans[]"]:checked').forEach(checkbox => {
        selectedPlans.push(checkbox.value);
    });
    addReviewRow(personalSection, 'Plans', selectedPlans.join(', '));
    
    // Get selected classification
    const selectedClassification = document.querySelector('input[name="classification"]:checked');
    if (selectedClassification) {
        addReviewRow(personalSection, 'Classification', selectedClassification.value);
    }
    
    addReviewRow(personalSection, 'Last Name', document.getElementById('last_name')?.value);
    addReviewRow(personalSection, 'First Name', document.getElementById('first_name')?.value);
    addReviewRow(personalSection, 'Middle Name', document.getElementById('middle_name')?.value);
    
    // Get selected values from dropdowns - IMPROVED HANDLING
    const genderSelect = document.getElementById('gender');
    // Check if a valid option (not the default) is selected
    const genderValue = genderSelect && genderSelect.selectedIndex > 0 ? genderSelect.value : '';
    addReviewRow(personalSection, 'Gender', genderValue);
    
    const civilStatusSelect = document.getElementById('civil_status');
    // Check if a valid option (not the default) is selected
    const civilStatusValue = civilStatusSelect && civilStatusSelect.selectedIndex > 0 ? civilStatusSelect.value : '';
    addReviewRow(personalSection, 'Civil Status', civilStatusValue);
    
    const birthday = document.getElementById('birthday')?.value;
    addReviewRow(personalSection, 'Birthday', birthday);
    
    // Calculate and display age based on birthday
    if (birthday) {
        const age = calculateAgeFromDateString(birthday);
        if (age !== null) {
            addReviewRow(personalSection, 'Age', age.toString());
        }
    }
    
    addReviewRow(personalSection, 'Birth Place', document.getElementById('birth_place')?.value);
    
    const cellPhone = document.getElementById('cell_phone')?.value;
    addReviewRow(personalSection, 'Phone No.', cellPhone ? '+63' + cellPhone : '');
    addReviewRow(personalSection, 'Telephone No.', document.getElementById('contact_no')?.value);
    addReviewRow(personalSection, 'Nationality', document.getElementById('nationality')?.value);
    addReviewRow(personalSection, 'TIN/SSS/GSIS Number', document.getElementById('id_number')?.value);
    
    // Add mother's maiden name
    addReviewRow(personalSection, 'Mother\'s Maiden Last Name', document.getElementById('mothers_maiden_last_name')?.value);
    addReviewRow(personalSection, 'Mother\'s Maiden First Name', document.getElementById('mothers_maiden_first_name')?.value);
    addReviewRow(personalSection, 'Mother\'s Maiden Middle Name', document.getElementById('mothers_maiden_middle_name')?.value);
    
    // Add Valid ID Upload info
    const validIdUpload = document.getElementById('valid_id_upload');
    if (validIdUpload && validIdUpload.files.length > 0) {
        addReviewRow(personalSection, 'Valid ID Upload', validIdUpload.files[0].name);
    }
    
    reviewContent.appendChild(personalSection);
    
    // Add Address section
    const addressSection = document.createElement('div');
    addressSection.className = 'review-section';
    addressSection.innerHTML = '<h3>Address Information</h3>';
    
    addReviewRow(addressSection, 'Present Address', document.getElementById('present_address')?.value);
    addReviewRow(addressSection, 'Present Brgy. Code', document.getElementById('present_brgy_code')?.value);
    addReviewRow(addressSection, 'Present Zip Code', document.getElementById('present_zip_code')?.value);
    
    addReviewRow(addressSection, 'Permanent Address', document.getElementById('permanent_address')?.value);
    addReviewRow(addressSection, 'Permanent Brgy. Code', document.getElementById('permanent_brgy_code')?.value);
    addReviewRow(addressSection, 'Permanent Zip Code', document.getElementById('permanent_zip_code')?.value);
    
    // Home ownership
    const homeOwnership = document.querySelector('input[name="home_ownership"]:checked');
    addReviewRow(addressSection, 'Home Ownership', homeOwnership ? homeOwnership.value : '');
    addReviewRow(addressSection, 'Length of Stay (years)', document.getElementById('length_of_stay')?.value);
    
    reviewContent.appendChild(addressSection);
    
    // Add Business section
    const businessSection = document.createElement('div');
    businessSection.className = 'review-section';
    businessSection.innerHTML = '<h3>Business Information</h3>';
    
    addReviewRow(businessSection, 'Primary Business', document.getElementById('primary_business')?.value);
    addReviewRow(businessSection, 'Years in Business', document.getElementById('years_in_business')?.value);
    addReviewRow(businessSection, 'Business Address', document.getElementById('business_address_unit')?.value);
    
    // Other income sources
    const otherIncomeSources = document.querySelectorAll('.other-income-source-item input');
    otherIncomeSources.forEach((input, index) => {
        if (input.value) {
            addReviewRow(businessSection, `Other Income Source ${index + 1}`, input.value);
        }
    });
    
    reviewContent.appendChild(businessSection);
    
    // Add Spouse section if married
    if (civilStatusSelect && civilStatusSelect.value === 'MARRIED') {
        const spouseSection = document.createElement('div');
        spouseSection.className = 'review-section';
        spouseSection.innerHTML = '<h3>Spouse Information</h3>';
        
        addReviewRow(spouseSection, 'Spouse\'s Last Name', document.getElementById('spouse_last_name')?.value);
        addReviewRow(spouseSection, 'Spouse\'s First Name', document.getElementById('spouse_first_name')?.value);
        addReviewRow(spouseSection, 'Spouse\'s Middle Name', document.getElementById('spouse_middle_name')?.value);
        addReviewRow(spouseSection, 'Spouse\'s Birthday', document.getElementById('spouse_birthday')?.value);
        addReviewRow(spouseSection, 'Spouse\'s Age', document.getElementById('spouse_age')?.value);
        addReviewRow(spouseSection, 'Spouse\'s Occupation', document.getElementById('spouse_occupation')?.value);
        addReviewRow(spouseSection, 'Spouse\'s ID Number', document.getElementById('spouse_id_number')?.value);
        
        // Spouse Valid ID Upload
        const spouseValidIdUpload = document.getElementById('spouse_valid_id_upload');
        if (spouseValidIdUpload && spouseValidIdUpload.files.length > 0) {
            addReviewRow(spouseSection, 'Spouse\'s Valid ID Upload', spouseValidIdUpload.files[0].name);
        }
        
        reviewContent.appendChild(spouseSection);
    }
    
    // Add Beneficiaries section
    const beneficiariesSection = document.getElementById('beneficiaries-section');
    if (beneficiariesSection && beneficiariesSection.style.display !== 'none') {
        const beneficiariesReviewSection = document.createElement('div');
        beneficiariesReviewSection.className = 'review-section';
        beneficiariesReviewSection.innerHTML = '<h3>Beneficiaries</h3>';
        
        // Filter out empty beneficiary rows
        const beneficiaryRows = Array.from(document.querySelectorAll('.beneficiary-row')).filter(row => {
            const lastName = row.querySelector('input[name="beneficiary_last_name[]"]')?.value;
            const firstName = row.querySelector('input[name="beneficiary_first_name[]"]')?.value;
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
                const lastName = row.querySelector('input[name="beneficiary_last_name[]"]')?.value;
                const firstName = row.querySelector('input[name="beneficiary_first_name[]"]')?.value;
                const mi = row.querySelector('input[name="beneficiary_mi[]"]')?.value;
                const dob = row.querySelector('input[name="beneficiary_dob[]"]')?.value;
                const gender = row.querySelector('select[name="beneficiary_gender[]"]')?.value;
                const relationship = row.querySelector('input[name="beneficiary_relationship[]"]')?.value;
                const dependent = row.querySelector('input[name="beneficiary_dependent[]"]') && 
                                row.querySelector('input[name="beneficiary_dependent[]"]').checked ? 'Yes' : 'No';
                
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #eee';
                
                tr.innerHTML = `
                    <td style="padding: 8px;">${lastName || ''}, ${firstName || ''} ${mi || ''}</td>
                    <td style="padding: 8px;">${dob || ''}</td>
                    <td style="padding: 8px;">${gender || ''}</td>
                    <td style="padding: 8px;">${relationship || ''}</td>
                    <td style="padding: 8px; text-align: center;">${dependent}</td>
                `;
                
                tbody.appendChild(tr);
            });
            
            table.appendChild(tbody);
            beneficiariesReviewSection.appendChild(table);
        } else {
            beneficiariesReviewSection.innerHTML += '<p>No beneficiaries added.</p>';
        }
        
        reviewContent.appendChild(beneficiariesReviewSection);
    }
    
    // Add Trustee section
    const trusteeSection = document.getElementById('trustee-section');
    if (trusteeSection && trusteeSection.style.display !== 'none') {
        const trusteeReviewSection = document.createElement('div');
        trusteeReviewSection.className = 'review-section';
        trusteeReviewSection.innerHTML = '<h3>Trustee Information</h3>';
        
        addReviewRow(trusteeReviewSection, 'Trustee Name', document.getElementById('trustee_name')?.value);
        addReviewRow(trusteeReviewSection, 'Trustee Date of Birth', document.getElementById('trustee_dob')?.value);
        addReviewRow(trusteeReviewSection, 'Relationship to Applicant', document.getElementById('trustee_relationship')?.value);
        
        reviewContent.appendChild(trusteeReviewSection);
    }
    
    // Add Signature section
    const signatureSection = document.createElement('div');
    signatureSection.className = 'review-section';
    signatureSection.innerHTML = '<h3>Signature Information</h3>';
    
    addReviewRow(signatureSection, 'Member Name', document.getElementById('member_name')?.value);
    
    // Check if user selected to add beneficiaries
    const hasBeneficiaries = document.getElementById('has_beneficiaries')?.value === 'yes';
    
    // Only show beneficiary name if user selected to add beneficiaries
    if (hasBeneficiaries) {
        addReviewRow(signatureSection, 'Beneficiary Name', document.getElementById('sig_beneficiary_name')?.value);
    }
    
    reviewContent.appendChild(signatureSection);
    
    // Add ID Documents section
    const idPreviewSection = document.createElement('div');
    idPreviewSection.className = 'review-section';
    idPreviewSection.innerHTML = '<h3>ID Documents</h3>';
    
    // Create container for side-by-side display
    const idPreviewContainer = document.createElement('div');
    idPreviewContainer.className = 'review-id-preview-container';
    idPreviewContainer.style.display = 'flex';
    idPreviewContainer.style.flexWrap = 'wrap';
    idPreviewContainer.style.gap = '20px';
    idPreviewContainer.style.marginTop = '15px';
    idPreviewSection.appendChild(idPreviewContainer);
    
    // Valid ID Preview
    if (validIdUpload && validIdUpload.files.length > 0) {
        const validIdPreviewBox = document.createElement('div');
        validIdPreviewBox.className = 'review-id-preview';
        validIdPreviewBox.style.flex = '1 1 calc(50% - 20px)';
        validIdPreviewBox.style.minWidth = '250px';
        validIdPreviewBox.style.backgroundColor = 'white';
        validIdPreviewBox.style.borderRadius = '8px';
        validIdPreviewBox.style.padding = '15px';
        validIdPreviewBox.style.marginBottom = '15px';
        validIdPreviewBox.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.05)';
        
        validIdPreviewBox.innerHTML = `
            <h4 style="color: #1B3FAB; margin-bottom: 10px; font-size: 16px;">Valid ID</h4>
            <p>Filename: ${validIdUpload.files[0].name}</p>
        `;
        
        // Add image preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.style.border = '1px solid #ddd';
            img.style.borderRadius = '4px';
            img.style.marginTop = '10px';
            validIdPreviewBox.appendChild(img);
        };
        reader.readAsDataURL(validIdUpload.files[0]);
        
        idPreviewContainer.appendChild(validIdPreviewBox);
    }
    
    // Spouse Valid ID Preview (only if married)
    if (civilStatusSelect && civilStatusSelect.value === 'MARRIED') {
        const spouseValidIdUpload = document.getElementById('spouse_valid_id_upload');
        if (spouseValidIdUpload && spouseValidIdUpload.files.length > 0) {
            const spouseIdPreviewBox = document.createElement('div');
            spouseIdPreviewBox.className = 'review-id-preview';
            spouseIdPreviewBox.style.flex = '1 1 calc(50% - 20px)';
            spouseIdPreviewBox.style.minWidth = '250px';
            spouseIdPreviewBox.style.backgroundColor = 'white';
            spouseIdPreviewBox.style.borderRadius = '8px';
            spouseIdPreviewBox.style.padding = '15px';
            spouseIdPreviewBox.style.marginBottom = '15px';
            spouseIdPreviewBox.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.05)';
            
            spouseIdPreviewBox.innerHTML = `
                <h4 style="color: #1B3FAB; margin-bottom: 10px; font-size: 16px;">Spouse's Valid ID</h4>
                <p>Filename: ${spouseValidIdUpload.files[0].name}</p>
            `;
            
            // Add image preview
            const spouseReader = new FileReader();
            spouseReader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '100%';
                img.style.maxHeight = '200px';
                img.style.border = '1px solid #ddd';
                img.style.borderRadius = '4px';
                img.style.marginTop = '10px';
                spouseIdPreviewBox.appendChild(img);
            };
            spouseReader.readAsDataURL(spouseValidIdUpload.files[0]);
            
            idPreviewContainer.appendChild(spouseIdPreviewBox);
        }
    }
    
    // Only add the section if there's at least one ID
    if (idPreviewContainer.children.length > 0) {
        reviewContent.appendChild(idPreviewSection);
    }
    
    // Set up modal button events
    setupReviewModalEvents();
}

/**
 * Set up review modal events
 */
function setupReviewModalEvents() {
    const modal = document.getElementById('review-modal');
    const closeBtn = document.querySelector('.close');
    const editBtn = document.getElementById('edit-application');
    const confirmBtn = document.getElementById('confirm-application');
    
    if (!modal) return;
    
    // Helper function to close modal with animation
    const closeModalWithAnimation = () => {
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.transform = 'scale(0.95)';
            modalContent.style.opacity = '0';
        }
        
        // Remove active class
        modal.classList.remove('active');
        
        // Wait for animation to complete before hiding
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }, 300);
    };
    
    // Close the modal when clicking the × button
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            closeModalWithAnimation();
        });
    }
    
    // Close modal when clicking Edit button
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            // Save current dropdown values before closing modal
            const genderValue = document.getElementById('gender')?.value;
            const civilStatusValue = document.getElementById('civil_status')?.value;
            
            closeModalWithAnimation();
            
            // If we need to go back to page 1, restore the dropdown values
            setTimeout(() => {
                const genderSelect = document.getElementById('gender');
                const civilStatusSelect = document.getElementById('civil_status');
                
                if (genderSelect && genderValue) genderSelect.value = genderValue;
                if (civilStatusSelect && civilStatusValue) civilStatusSelect.value = civilStatusValue;
            }, 50);
        });
    }
    
    // Submit the form when clicking Confirm button
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Store the current values for gender and civil status
            const selectedGender = document.getElementById('gender')?.value;
            const selectedCivilStatus = document.getElementById('civil_status')?.value;
            
            // Save to the state manager if it exists
            if (window.formStateManager) {
                window.formStateManager.state.gender = selectedGender;
                window.formStateManager.state.civilStatus = selectedCivilStatus;
            }
            
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
            
            // Ensure BLIP is checked
            const blipCheckbox = document.getElementById('plan_blip');
            if (blipCheckbox && !blipCheckbox.checked) {
                blipCheckbox.checked = true;
            }
            
            // Make sure dropdown values are properly selected before submission
            const genderSelect = document.getElementById('gender');
            const civilStatusSelect = document.getElementById('civil_status');
            
            // Check for empty or default selected values
            if (genderSelect && genderSelect.selectedIndex === 0) {
                showCustomAlert('Please select your gender.', 'error');
                // Close modal and go back to page 1 to fix the issue
                closeModalWithAnimation();
                
                if (window.setCurrentPage) {
                    window.setCurrentPage(0);
                }
                
                // Try to set the values directly
                if (genderSelect && selectedGender) {
                    genderSelect.value = selectedGender;
                }
                
                // Add a small delay to ensure the page is updated before focusing
                setTimeout(() => {
                    if (genderSelect) {
                        if (selectedGender) genderSelect.value = selectedGender;
                        genderSelect.focus();
                        genderSelect.classList.add('attempted');
                        genderSelect.style.borderColor = 'red';
                        genderSelect.scrollIntoView({behavior:'smooth', block:'center'});
                    }
                }, 100);
                
                if (confirmBtn) confirmBtn.disabled = false;
                return false;
            }
            
            if (civilStatusSelect && civilStatusSelect.selectedIndex === 0) {
                showCustomAlert('Please select your civil status.', 'error');
                // Close modal and go back to page 1 to fix the issue
                closeModalWithAnimation();
                
                if (window.setCurrentPage) {
                    window.setCurrentPage(0);
                }
                
                // Try to set the values directly
                if (civilStatusSelect && selectedCivilStatus) {
                    civilStatusSelect.value = selectedCivilStatus;
                }
                
                // Add a small delay to ensure the page is updated before focusing
                setTimeout(() => {
                    if (civilStatusSelect) {
                        if (selectedCivilStatus) civilStatusSelect.value = selectedCivilStatus;
                        civilStatusSelect.focus();
                        civilStatusSelect.classList.add('attempted');
                        civilStatusSelect.style.borderColor = 'red';
                        civilStatusSelect.scrollIntoView({behavior:'smooth', block:'center'});
                    }
                }, 100);
                
                if (confirmBtn) confirmBtn.disabled = false;
                return false;
            }
            
            try {
                const form = document.getElementById('membership-form');
                if (!form) return;
                
                // Set the confirmed flag and submit the form
                form.dataset.confirmed = 'true';
                
                // Disable the button to prevent double submission
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Submitting...';
                
                // Show success message and close modal
                showCustomAlert('Processing your application...', 'info');
                closeModalWithAnimation();
                
                setTimeout(function() {
                    form.submit();
                }, 100); // Small delay to ensure UI updates
            } catch (error) {
                console.error('Error during form submission:', error);
                showCustomAlert('There was an error submitting the form. Please try again.', 'error');
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Confirm Submission';
                }
            }
        });
    }
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            closeModalWithAnimation();
        }
    });
}

/**
 * Helper function to add a row to the review modal
 */
function addReviewRow(container, label, value) {
    if (!container) return;
    
    // Modified to show empty values with a placeholder
    const row = document.createElement('div');
    row.className = 'review-row';
    
    const labelDiv = document.createElement('div');
    labelDiv.className = 'review-label';
    labelDiv.textContent = label;
    
    const valueDiv = document.createElement('div');
    valueDiv.className = 'review-value';
    
    // Handle specific required fields - like Gender and Civil Status
    if (!value && (label === 'Gender' || label === 'Civil Status')) {
        valueDiv.style.color = '#d9534f'; // Bootstrap danger color
        valueDiv.innerHTML = '<span style="font-weight: bold;">⚠️ Required - Please select</span>';
    } else {
        valueDiv.textContent = value || '—';
    }
    
    row.appendChild(labelDiv);
    row.appendChild(valueDiv);
    container.appendChild(row);
}

/**
 * Initialize form state manager to maintain dropdown values
 */
function initFormStateManager() {
    window.formStateManager = {
        state: {
            gender: '',
            civilStatus: ''
        },
        
        init: function() {
            // Set up listeners for key fields
            const genderSelect = document.getElementById('gender');
            const civilStatusSelect = document.getElementById('civil_status');
            
            // Listen for changes on the gender dropdown
            if (genderSelect) {
                genderSelect.addEventListener('change', (e) => {
                    this.state.gender = e.target.value;
                    console.log('Gender stored:', this.state.gender);
                });
            }
            
            // Listen for changes on the civil status dropdown
            if (civilStatusSelect) {
                civilStatusSelect.addEventListener('change', (e) => {
                    this.state.civilStatus = e.target.value;
                    console.log('Civil status stored:', this.state.civilStatus);
                });
            }
            
            // Attach to navigation events
            const nextBtn = document.getElementById('next_page_btn');
            const prevBtn = document.getElementById('prev_page_btn');
            const submitBtn = document.getElementById('submit_application_btn');
            
            if (nextBtn) nextBtn.addEventListener('click', () => this.restoreValuesAfterDelay());
            if (prevBtn) prevBtn.addEventListener('click', () => this.restoreValuesAfterDelay());
            if (submitBtn) submitBtn.addEventListener('click', () => this.restoreValuesAfterDelay());
        },
        
        restoreValuesAfterDelay: function() {
            setTimeout(() => {
                this.restoreValues();
            }, 100);
        },
        
        restoreValues: function() {
            const genderSelect = document.getElementById('gender');
            const civilStatusSelect = document.getElementById('civil_status');
            
            if (genderSelect && this.state.gender) {
                genderSelect.value = this.state.gender;
                console.log('Gender restored to:', this.state.gender);
            }
            
            if (civilStatusSelect && this.state.civilStatus) {
                civilStatusSelect.value = this.state.civilStatus;
                console.log('Civil status restored to:', this.state.civilStatus);
            }
        }
    };
    
    // Initialize form state manager
    window.formStateManager.init();
    
    // Apply initial restore to capture any initial values
    setTimeout(() => {
        window.formStateManager.restoreValues();
    }, 300);
}

/**
 * Force update the submit button state based on the disclaimer checkbox
 * This is crucial for when pages change or elements are dynamically shown/hidden
 */
function forceUpdateSubmitButtonState() {
    const submitButton = document.getElementById('submit_application_btn');
    const disclaimerCheckbox = document.getElementById('disclaimer_agreement');
    
    if (submitButton && disclaimerCheckbox) {
        // Explicitly set disabled state based on checkbox
        submitButton.disabled = !disclaimerCheckbox.checked;
        
        // Apply additional styling if needed
        if (disclaimerCheckbox.checked) {
            submitButton.removeAttribute('style');
        } else {
            submitButton.setAttribute('style', 'background-color: #cccccc !important; cursor: not-allowed !important;');
        }
    }
}

// Add helper function to calculate age from date string in format MM/DD/YYYY
function calculateAgeFromDateString(dateString) {
    if (!dateString) return null;
    
    // Parse the date string (MM/DD/YYYY format)
    const parts = dateString.split('/');
    if (parts.length !== 3) return null;
    
    const month = parseInt(parts[0], 10);
    const day = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);
    
    // Check if valid date parts
    if (isNaN(month) || isNaN(day) || isNaN(year)) return null;
    
    const birthDate = new Date(year, month - 1, day); // Month is 0-based in JS Date
    const today = new Date();
    
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    // Adjust age if birthday hasn't occurred yet this year
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age >= 0 ? age : null; // Return null for negative ages
}

/**
 * Initialize ID preview functionality
 */
function initializeIdPreview() {
    // For main ID upload
    const validIdUpload = document.getElementById('valid_id_upload');
    const validIdPreview = document.getElementById('valid_id_preview');
    const validIdPreviewContainer = document.getElementById('valid_id_preview_container');
    
    if (validIdUpload && validIdPreview && validIdPreviewContainer) {
        validIdUpload.addEventListener('change', function() {
            // Check if file is selected
            if (this.files.length > 0) {
                const file = this.files[0];
                
                // Validate file type (only jpg/png)
                if (!file.type.match(/^image\/(jpeg|png)$/)) {
                    showCustomAlert('Only image files (JPG, PNG) are allowed for uploads.', 'error');
                    this.value = ''; // Clear the input
                    return;
                }
                
                previewImage(this, validIdPreview, validIdPreviewContainer);
            }
        });
    }
    
    // For spouse ID upload
    const spouseValidIdUpload = document.getElementById('spouse_valid_id_upload');
    const spouseValidIdPreview = document.getElementById('spouse_valid_id_preview');
    const spouseValidIdPreviewContainer = document.getElementById('spouse_valid_id_preview_container');
    
    if (spouseValidIdUpload && spouseValidIdPreview && spouseValidIdPreviewContainer) {
        spouseValidIdUpload.addEventListener('change', function() {
            // Check if file is selected
            if (this.files.length > 0) {
                const file = this.files[0];
                
                // Validate file type (only jpg/png)
                if (!file.type.match(/^image\/(jpeg|png)$/)) {
                    showCustomAlert('Only image files (JPG, PNG) are allowed for uploads.', 'error');
                    this.value = ''; // Clear the input
                    return;
                }
                
                previewImage(this, spouseValidIdPreview, spouseValidIdPreviewContainer);
            }
        });
    }
}

/**
 * Preview an image file
 */
function previewImage(input, imgElement, containerElement) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Clear previous content
        containerElement.innerHTML = '';
        
        // Only preview if it's an image
        if (file.type.match('image.*')) {
            // Re-add the image element
            const img = document.createElement('img');
            img.id = imgElement.id;
            img.alt = imgElement.alt;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.style.border = '1px solid #ddd';
            img.style.borderRadius = '4px';
            containerElement.appendChild(img);
            
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                containerElement.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            // For PDFs and other non-image files, show a text indicator
            const docPreview = document.createElement('div');
            docPreview.innerHTML = `
                <div style="border: 1px dashed #ccc; padding: 15px; text-align: center;">
                    <div style="font-size: 24px; color: #666;">📄</div>
                    <div style="margin-top: 5px; font-size: 13px; color: #666;">${file.name}</div>
                </div>
            `;
            containerElement.appendChild(docPreview);
            containerElement.style.display = 'block';
        }
    } else {
        containerElement.style.display = 'none';
    }
}

/**
 * Format phone number to remove leading zero
 */
function formatPhoneNumber(input) {
    // Remove any non-digit characters
    let value = input.value.replace(/\D/g, '');
    
    // Remove leading zero if present
    if (value.startsWith('0')) {
        value = value.substring(1);
    }
    
    // Ensure we don't exceed maxlength
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    
    // Update the input value
    input.value = value;
}

/**
 * Custom styled alert with fade out animation
 */
function showCustomAlert(message, type = 'error') {
    // Remove any existing alerts
    const existingAlerts = document.querySelectorAll('.ui-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create alert element
    const alertEl = document.createElement('div');
    alertEl.className = `ui-alert ${type}`;
    
    // Set title based on type
    let title = 'Warning';
    
    if (type === 'error') {
        title = 'Error';
    } else if (type === 'success') {
        title = 'Success';
    } else if (type === 'info') {
        title = 'Information';
    }
    
    // Create alert content
    alertEl.innerHTML = `
        <div class="alert-content">
            <div class="alert-title">${title}</div>
            <div class="alert-message">${message}</div>
        </div>
    `;
    
    // Add to document
    document.body.appendChild(alertEl);
    
    // The animation is now handled via CSS
    return alertEl;
}

/**
 * Initialize tooltips to use data-title attribute
 */
function initializeTooltips() {
    const tooltipIcons = document.querySelectorAll('.tooltip-icon');
    let activeTooltip = null; // Track currently active tooltip

    tooltipIcons.forEach(icon => {
        // Ensure data-title exists, if not, try to use title and remove it
        if (!icon.hasAttribute('data-title') && icon.hasAttribute('title')) {
            icon.setAttribute('data-title', icon.getAttribute('title'));
            icon.removeAttribute('title');
        }

        // For mobile devices (click/tap to show/hide)
        if (window.innerWidth <= 768) {
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Prevent click from bubbling to document listener immediately

                if (activeTooltip === this) {
                    // If clicking the active tooltip, deactivate it
                    this.classList.remove('active');
                    activeTooltip = null;
                } else {
                    // If another tooltip was active, deactivate it
                    if (activeTooltip) {
                        activeTooltip.classList.remove('active');
                    }
                    // Activate the clicked tooltip
                    this.classList.add('active');
                    activeTooltip = this;
                }
            });
        } else {
            // For desktop - use hover behavior (CSS will handle this)
            // No JS needed for hover if using CSS :hover pseudo-class for visibility
        }
    });

    // Close any active tooltip when clicking elsewhere on the document (for mobile)
    if (window.innerWidth <= 768) {
        document.addEventListener('click', function(e) {
            if (activeTooltip && !activeTooltip.contains(e.target)) {
                activeTooltip.classList.remove('active');
                activeTooltip = null;
            }
        });
    }
}
