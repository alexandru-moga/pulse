class PasswordValidationUI {
    constructor(passwordFieldId, requirementsElementId = null) {
        this.passwordField = document.getElementById(passwordFieldId);
        this.requirementsElement = requirementsElementId ? document.getElementById(requirementsElementId) : null;

        if (this.passwordField) {
            this.setupValidation();
        }
    }

    setupValidation() {
        this.passwordField.addEventListener('input', () => {
            this.validatePassword();
        });

        if (this.requirementsElement) {
            this.createRequirementsIndicator();
        }
    }

    validatePassword() {
        const password = this.passwordField.value;
        const validation = this.checkPassword(password);

        if (password.length > 0) {
            if (validation.valid) {
                this.passwordField.classList.remove('border-red-300', 'focus:border-red-300');
                this.passwordField.classList.add('border-green-300', 'focus:border-green-300');
            } else {
                this.passwordField.classList.remove('border-green-300', 'focus:border-green-300');
                this.passwordField.classList.add('border-red-300', 'focus:border-red-300');
            }
        } else {
            this.passwordField.classList.remove('border-red-300', 'focus:border-red-300', 'border-green-300', 'focus:border-green-300');
        }

        this.updateRequirementsIndicator(validation);

        return validation;
    }

    checkPassword(password) {
        const checks = {
            minLength: password.length >= 8,
            hasUppercase: /[A-Z]/.test(password),
            hasLowercase: /[a-z]/.test(password),
            hasNumber: /[0-9]/.test(password),
            hasSpecialChar: /[^A-Za-z0-9]/.test(password)
        };

        const allValid = Object.values(checks).every(check => check);

        return {
            valid: allValid,
            checks: checks
        };
    }

    createRequirementsIndicator() {
        if (!this.requirementsElement) return;

        const indicators = `
            <div class="password-requirements mt-2 text-xs">
                <p class="text-gray-600 mb-1">Password requirements:</p>
                <ul class="space-y-1">
                    <li class="requirement-item flex items-center" data-check="minLength">
                        <span class="requirement-icon w-3 h-3 mr-2">×</span>
                        <span>At least 8 characters</span>
                    </li>
                    <li class="requirement-item flex items-center" data-check="hasUppercase">
                        <span class="requirement-icon w-3 h-3 mr-2">×</span>
                        <span>At least 1 uppercase letter</span>
                    </li>
                    <li class="requirement-item flex items-center" data-check="hasLowercase">
                        <span class="requirement-icon w-3 h-3 mr-2">×</span>
                        <span>At least 1 lowercase letter</span>
                    </li>
                    <li class="requirement-item flex items-center" data-check="hasNumber">
                        <span class="requirement-icon w-3 h-3 mr-2">×</span>
                        <span>At least 1 number</span>
                    </li>
                    <li class="requirement-item flex items-center" data-check="hasSpecialChar">
                        <span class="requirement-icon w-3 h-3 mr-2">×</span>
                        <span>At least 1 special character</span>
                    </li>
                </ul>
            </div>
        `;

        this.requirementsElement.innerHTML = indicators;
    }

    updateRequirementsIndicator(validation) {
        if (!this.requirementsElement) return;

        const items = this.requirementsElement.querySelectorAll('.requirement-item');
        items.forEach(item => {
            const check = item.getAttribute('data-check');
            const icon = item.querySelector('.requirement-icon');

            if (validation.checks[check]) {
                item.classList.remove('text-red-600');
                item.classList.add('text-green-600');
                icon.textContent = '✓';
                icon.classList.remove('text-red-500');
                icon.classList.add('text-green-500');
            } else {
                item.classList.remove('text-green-600');
                item.classList.add('text-red-600');
                icon.textContent = '×';
                icon.classList.remove('text-green-500');
                icon.classList.add('text-red-500');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const passwordFields = ['password', 'new_password'];

    passwordFields.forEach(fieldId => {
        if (document.getElementById(fieldId)) {
            new PasswordValidationUI(fieldId);
        }
    });
});
