<?php
require_once '../core/init.php';

global $db, $settings;

$token = $_GET['token'] ?? '';
$error = $success = null;

if ($token) {
    $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $error = "Invalid or expired reset link.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        if ($newPassword === '') {
            $error = "Password cannot be empty.";
        } elseif ($newPassword !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            // Validate new password
            $passwordValidation = PasswordValidator::validate($newPassword);
            if (!$passwordValidation['valid']) {
                $error = $passwordValidation['message'];
            } else {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $reset['user_id']]);
                $db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$reset['user_id']]);
                $success = "Password has been reset successfully.";
            }
        }
    }
} else {
    $error = "No reset token provided.";
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= htmlspecialchars($settings['site_title']) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= $settings['site_url'] ?>/images/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ef4444'
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-primary">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Set a new password
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Choose a secure password for your account
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?= htmlspecialchars($success) ?>
            </div>
            <div class="text-center">
                <a href="<?= $settings['site_url'] ?>/dashboard/login.php" 
                   class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Go to Login
                </a>
            </div>
        <?php else: ?>
            <form class="space-y-6" method="POST" action="">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        New Password
                    </label>
                    <div class="mt-1 relative">
                        <input id="password" 
                               name="password" 
                               type="password" 
                               required 
                               minlength="8"
                               class="appearance-none block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                               placeholder="Enter new password">
                        <button type="button" 
                                id="togglePassword" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464a10.025 10.025 0 00-5.21 2.506m5.624.872l4.242 4.242M9.878 9.878l4.242 4.242m-4.242-4.242L8.464 8.464m7.07 7.07l-7.07-7.07m7.07 7.07l1.414 1.414a10.025 10.025 0 005.21-2.506m-5.624-.872L9.878 9.878"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="confirm" class="block text-sm font-medium text-gray-700">
                        Confirm Password
                    </label>
                    <div class="mt-1 relative">
                        <input id="confirm" 
                               name="confirm" 
                               type="password" 
                               required 
                               minlength="8"
                               class="appearance-none block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                               placeholder="Confirm new password">
                        <button type="button" 
                                id="toggleConfirm" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg id="confirmEyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="confirmEyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464a10.025 10.025 0 00-5.21 2.506m5.624.872l4.242 4.242M9.878 9.878l4.242 4.242m-4.242-4.242L8.464 8.464m7.07 7.07l-7.07-7.07m7.07 7.07l1.414 1.414a10.025 10.025 0 005.21-2.506m-5.624-.872L9.878 9.878"></path>
                            </svg>
                        </button>
                    </div>
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

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Set New Password
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <div class="text-center">
            <a href="<?= $settings['site_url'] ?>/dashboard/login.php" 
               class="text-sm text-gray-500 hover:text-gray-700">
                ← Back to login
            </a>
        </div>
    </div>

    <!-- Password validation JavaScript -->
    <script src="<?= $settings['site_url'] ?>/css/password-validation.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const confirmField = document.getElementById('confirm');
            const requirements = document.querySelectorAll('.requirement-item');
            
            // Password visibility toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirm = document.getElementById('toggleConfirm');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');
            const confirmEyeIcon = document.getElementById('confirmEyeIcon');
            const confirmEyeSlashIcon = document.getElementById('confirmEyeSlashIcon');
            
            let passwordVisible = false;
            let confirmVisible = false;
            
            // Toggle main password visibility
            togglePassword.addEventListener('click', function() {
                passwordVisible = !passwordVisible;
                
                if (passwordVisible) {
                    passwordField.type = 'text';
                    eyeIcon.classList.add('hidden');
                    eyeSlashIcon.classList.remove('hidden');
                    
                    // Disable confirm password when main password is visible
                    confirmField.disabled = true;
                    confirmField.classList.add('bg-gray-100', 'cursor-not-allowed');
                    confirmField.placeholder = 'Disabled - password is visible above';
                    toggleConfirm.style.display = 'none';
                } else {
                    passwordField.type = 'password';
                    eyeIcon.classList.remove('hidden');
                    eyeSlashIcon.classList.add('hidden');
                    
                    // Re-enable confirm password when main password is hidden
                    confirmField.disabled = false;
                    confirmField.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    confirmField.placeholder = 'Confirm new password';
                    toggleConfirm.style.display = 'flex';
                }
            });
            
            // Toggle confirm password visibility (only works when not disabled)
            toggleConfirm.addEventListener('click', function() {
                if (!confirmField.disabled) {
                    confirmVisible = !confirmVisible;
                    
                    if (confirmVisible) {
                        confirmField.type = 'text';
                        confirmEyeIcon.classList.add('hidden');
                        confirmEyeSlashIcon.classList.remove('hidden');
                    } else {
                        confirmField.type = 'password';
                        confirmEyeIcon.classList.remove('hidden');
                        confirmEyeSlashIcon.classList.add('hidden');
                    }
                }
            });
            
            // Password validation functionality
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
                    
                    // Auto-fill confirm password when main password is visible
                    if (passwordVisible) {
                        confirmField.value = password;
                    }
                });
            }
        });
    </script>
</body>
</html>
