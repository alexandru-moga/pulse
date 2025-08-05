<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();

global $db, $currentUser, $settings;

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$currentUser->id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($oldPassword, $user['password'])) {
        $error = "Old password is incorrect.";
    } elseif ($newPassword === '') {
        $error = "New password cannot be empty.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New passwords do not match.";
    } elseif ($oldPassword === $newPassword) {
        $error = "New password cannot be the same as the old password.";
    } else {
        // Validate new password
        $passwordValidation = PasswordValidator::validate($newPassword);
        if (!$passwordValidation['valid']) {
            $error = $passwordValidation['message'];
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")
               ->execute([$hashed, $currentUser->id]);
            $success = "Password updated successfully!";
        }
    }
}

$pageTitle = 'Change Password';
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Change Password</h2>
                <p class="text-gray-600 mt-1">Update your account password for security</p>
            </div>
        </div>
    </div>
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Security Settings</h3>
        </div>
        
        <form method="post" class="p-6" autocomplete="off">
            <div class="grid grid-cols-1 gap-6 max-w-lg">
                <div>
                    <label for="old_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                    <input type="password" 
                           id="old_password" 
                           name="old_password" 
                           required 
                           autocomplete="current-password"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" 
                           id="new_password" 
                           name="new_password" 
                           required 
                           minlength="8"
                           autocomplete="new-password"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required 
                           minlength="8"
                           autocomplete="new-password"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>

                <!-- Password Requirements -->
                <div id="password-requirements" class="text-xs text-gray-500 mt-1">
                    <p class="mb-2">Password must contain:</p>
                    <ul class="space-y-1">
                        <li class="requirement-item flex items-center" data-check="minLength">
                            <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                            <span>At least 8 characters</span>
                        </li>
                        <li class="requirement-item flex items-center" data-check="hasUppercase">
                            <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                            <span>At least 1 uppercase letter</span>
                        </li>
                        <li class="requirement-item flex items-center" data-check="hasLowercase">
                            <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                            <span>At least 1 lowercase letter</span>
                        </li>
                        <li class="requirement-item flex items-center" data-check="hasNumber">
                            <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                            <span>At least 1 number</span>
                        </li>
                        <li class="requirement-item flex items-center" data-check="hasSpecialChar">
                            <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                            <span>At least 1 special character</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-start mt-8 pt-6 border-t border-gray-200">
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('new_password');
        const requirements = document.querySelectorAll('.requirement-item');
        
        if (passwordField) {
            passwordField.addEventListener('input', function() {
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
    });
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
