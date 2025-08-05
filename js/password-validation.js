// Password Validation and UI Enhancement JavaScript
// Handles password strength, visibility toggles, and validation across all forms

// Function to calculate password strength
function calculateStrength(password) {
    let score = 0;
    const checks = {
        minLength: password.length >= 8,
        hasUppercase: /[A-Z]/.test(password),
        hasLowercase: /[a-z]/.test(password),
        hasNumber: /[0-9]/.test(password),
        hasSpecialChar: /[^A-Za-z0-9]/.test(password)
    };

    // Calculate score based on requirements met
    Object.values(checks).forEach(check => {
        if (check) score += 20;
    });

    return { score, checks };
}

// Function to update strength indicator
function updateStrengthIndicator(strengthBar, password, isConfirm = false, mainPassword = '') {
    if (!strengthBar) return;

    if (!password) {
        strengthBar.style.width = '0%';
        strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-gray-300';
        return;
    }

    let strength;
    if (isConfirm) {
        // For confirm field, check if it matches the main password
        if (password === mainPassword && mainPassword.length > 0) {
            strength = calculateStrength(password);
        } else {
            strengthBar.style.width = '100%';
            strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-red-500';
            return;
        }
    } else {
        strength = calculateStrength(password);
    }

    const { score } = strength;
    strengthBar.style.width = score + '%';

    // Update color based on strength
    if (score < 40) {
        strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-red-500';
    } else if (score < 80) {
        strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-yellow-500';
    } else {
        strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-green-500';
    }
}

// Enhanced password validation setup for any form
function setupPasswordValidation(config) {
    const {
        passwordFieldId,
        confirmFieldId,
        confirmSectionId,
        requirementsSelector,
        strengthBarId,
        confirmStrengthBarId,
        togglePasswordId,
        toggleConfirmId,
        eyeIconId,
        eyeSlashIconId,
        confirmEyeIconId,
        confirmEyeSlashIconId
    } = config;

    const passwordField = document.getElementById(passwordFieldId);
    const confirmField = document.getElementById(confirmFieldId);
    const confirmSection = document.getElementById(confirmSectionId);
    const requirements = document.querySelectorAll(requirementsSelector + ' .requirement-item');
    const strengthBar = document.getElementById(strengthBarId);
    const confirmStrengthBar = document.getElementById(confirmStrengthBarId);

    // Visibility toggle elements
    const togglePassword = document.getElementById(togglePasswordId);
    const toggleConfirm = document.getElementById(toggleConfirmId);
    const eyeIcon = document.getElementById(eyeIconId);
    const eyeSlashIcon = document.getElementById(eyeSlashIconId);
    const confirmEyeIcon = document.getElementById(confirmEyeIconId);
    const confirmEyeSlashIcon = document.getElementById(confirmEyeSlashIconId);

    let passwordVisible = false;
    let confirmVisible = false;

    // Function to hide confirm password section
    function hideConfirmPasswordSection() {
        if (confirmSection) {
            confirmSection.style.display = 'none';
        }
        if (confirmField && passwordField) {
            confirmField.value = passwordField.value;
            // Reset confirm field to password type when section is hidden
            confirmField.type = 'password';
        }
        // Reset confirm visibility state and icons when section is hidden
        confirmVisible = false;
        if (confirmEyeIcon) confirmEyeIcon.classList.remove('hidden');
        if (confirmEyeSlashIcon) confirmEyeSlashIcon.classList.add('hidden');
    }

    // Function to show confirm password section
    function showConfirmPasswordSection() {
        if (confirmSection) {
            confirmSection.style.display = 'block';
        }
    }

    // Function to update visibility states and UI
    function updatePasswordVisibility() {
        // Hide confirm section if either password field is visible
        // Show confirm section only when both password fields are hidden
        if (passwordVisible || confirmVisible) {
            hideConfirmPasswordSection();
        } else {
            showConfirmPasswordSection();
        }
    }

    // Toggle main password visibility
    if (togglePassword) {
        togglePassword.addEventListener('click', function () {
            passwordVisible = !passwordVisible;

            if (passwordVisible) {
                passwordField.type = 'text';
                if (eyeIcon) eyeIcon.classList.add('hidden');
                if (eyeSlashIcon) eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordField.type = 'password';
                if (eyeIcon) eyeIcon.classList.remove('hidden');
                if (eyeSlashIcon) eyeSlashIcon.classList.add('hidden');
            }

            updatePasswordVisibility();
        });
    }

    // Toggle confirm password visibility
    if (toggleConfirm) {
        toggleConfirm.addEventListener('click', function () {
            confirmVisible = !confirmVisible;

            if (confirmVisible) {
                if (confirmField) confirmField.type = 'text';
                if (confirmEyeIcon) confirmEyeIcon.classList.add('hidden');
                if (confirmEyeSlashIcon) confirmEyeSlashIcon.classList.remove('hidden');
            } else {
                if (confirmField) confirmField.type = 'password';
                if (confirmEyeIcon) confirmEyeIcon.classList.remove('hidden');
                if (confirmEyeSlashIcon) confirmEyeSlashIcon.classList.add('hidden');
            }

            updatePasswordVisibility();
        });
    }

    // Password validation and strength functionality
    if (passwordField) {
        passwordField.addEventListener('input', function () {
            const password = this.value;

            // Update strength indicator
            updateStrengthIndicator(strengthBar, password);

            // Check each requirement
            const checks = {
                minLength: password.length >= 8,
                hasUppercase: /[A-Z]/.test(password),
                hasLowercase: /[a-z]/.test(password),
                hasNumber: /[0-9]/.test(password),
                hasSpecialChar: /[^A-Za-z0-9]/.test(password)
            };

            // Update requirement indicators
            requirements.forEach(item => {
                const check = item.getAttribute('data-check');
                const dot = item.querySelector('.requirement-dot');
                const text = item.querySelector('span:last-child');

                if (checks[check]) {
                    // Requirement met - green dot and text
                    dot.classList.remove('bg-gray-300');
                    dot.classList.add('bg-green-500');
                    text.classList.remove('text-gray-500');
                    text.classList.add('text-green-600');
                } else {
                    // Requirement not met - gray dot and text
                    dot.classList.remove('bg-green-500');
                    dot.classList.add('bg-gray-300');
                    text.classList.remove('text-green-600');
                    text.classList.add('text-gray-500');
                }
            });

            // Update password field border color
            const allValid = Object.values(checks).every(check => check);
            if (password.length > 0) {
                if (allValid) {
                    passwordField.classList.remove('border-red-300', 'focus:border-red-300');
                    passwordField.classList.add('border-green-300', 'focus:border-green-300');
                } else {
                    passwordField.classList.remove('border-green-300', 'focus:border-green-300');
                    passwordField.classList.add('border-red-300', 'focus:border-red-300');
                }
            } else {
                passwordField.classList.remove('border-red-300', 'focus:border-red-300', 'border-green-300', 'focus:border-green-300');
            }

            // Auto-fill confirm password when any password is visible
            if ((passwordVisible || confirmVisible) && confirmField) {
                confirmField.value = password;
            }

            // Update confirm field strength indicator if it has content
            if (confirmField && confirmField.value && confirmStrengthBar) {
                updateStrengthIndicator(confirmStrengthBar, confirmField.value, true, password);
            }
        });
    }

    // Confirm password validation
    if (confirmField) {
        confirmField.addEventListener('input', function () {
            const confirmPassword = this.value;
            const mainPassword = passwordField ? passwordField.value : '';

            updateStrengthIndicator(confirmStrengthBar, confirmPassword, true, mainPassword);

            // Update confirm field border color based on match
            if (confirmPassword.length > 0) {
                if (confirmPassword === mainPassword && mainPassword.length > 0) {
                    confirmField.classList.remove('border-red-300', 'focus:border-red-300');
                    confirmField.classList.add('border-green-300', 'focus:border-green-300');
                } else {
                    confirmField.classList.remove('border-green-300', 'focus:border-green-300');
                    confirmField.classList.add('border-red-300', 'focus:border-red-300');
                }
            } else {
                confirmField.classList.remove('border-red-300', 'focus:border-red-300', 'border-green-300', 'focus:border-green-300');
            }
        });
    }
}

// Simplified setup for basic password validation without confirm field
function setupBasicPasswordValidation(passwordFieldId, requirementsSelector) {
    const passwordField = document.getElementById(passwordFieldId);
    const requirements = document.querySelectorAll(requirementsSelector + ' .requirement-item');

    if (passwordField) {
        passwordField.addEventListener('input', function () {
            const password = this.value;

            // Check each requirement
            const checks = {
                minLength: password.length >= 8,
                hasUppercase: /[A-Z]/.test(password),
                hasLowercase: /[a-z]/.test(password),
                hasNumber: /[0-9]/.test(password),
                hasSpecialChar: /[^A-Za-z0-9]/.test(password)
            };

            // Update requirement indicators
            requirements.forEach(item => {
                const check = item.getAttribute('data-check');
                const dot = item.querySelector('.requirement-dot');
                const text = item.querySelector('span:last-child');

                if (checks[check]) {
                    // Requirement met - green dot and text
                    dot.classList.remove('bg-gray-300');
                    dot.classList.add('bg-green-500');
                    text.classList.remove('text-gray-500');
                    text.classList.add('text-green-600');
                } else {
                    // Requirement not met - gray dot and text
                    dot.classList.remove('bg-green-500');
                    dot.classList.add('bg-gray-300');
                    text.classList.remove('text-green-600');
                    text.classList.add('text-gray-500');
                }
            });

            // Update password field border color
            const allValid = Object.values(checks).every(check => check);
            if (password.length > 0) {
                if (allValid) {
                    passwordField.classList.remove('border-red-300', 'focus:border-red-300');
                    passwordField.classList.add('border-green-300', 'focus:border-green-300');
                } else {
                    passwordField.classList.remove('border-green-300', 'focus:border-green-300');
                    passwordField.classList.add('border-red-300', 'focus:border-red-300');
                }
            } else {
                passwordField.classList.remove('border-red-300', 'focus:border-red-300', 'border-green-300', 'focus:border-green-300');
            }
        });
    }
}
