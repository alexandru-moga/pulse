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

                // Debug logging (remove in production)
                error_log("Reset password: hashing '$newPassword' for user " . $reset['user_id']);
                error_log("Generated hash: " . $hashed);

                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateResult = $stmt->execute([$hashed, $reset['user_id']]);

                if ($updateResult) {
                    $db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$reset['user_id']]);
                    $success = "Password has been reset successfully. You can now login with your new password.";
                    error_log("Password updated successfully for user " . $reset['user_id']);
                } else {
                    $error = "Failed to update password. Please try again.";
                    error_log("Failed to update password for user " . $reset['user_id']);
                }
            }
        }
    }
} else {
    $error = "No reset token provided.";
}
?>
<!DOCTYPE html>
<html lang="en">

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
                        primary: '#ec4a0a'
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <img src="<?= $settings['site_url'] ?>/images/logo.svg"
                alt="<?= htmlspecialchars($settings['site_title']) ?> Logo"
                class="h-16 w-auto">
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Set a new password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Choose a secure password for your account
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">

            <?php if ($error): ?>
                <div class="rounded-md bg-red-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Reset Failed
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="rounded-md bg-green-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">
                                Success!
                            </h3>
                            <div class="mt-2 text-sm text-green-700">
                                <?= htmlspecialchars($success) ?>
                            </div>
                        </div>
                    </div>
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
                        <!-- Password Strength Indicator -->
                        <div id="passwordStrength" class="mt-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                            <div id="passwordStrengthBar" class="h-full bg-red-500 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <div id="confirmPasswordSection">
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
                        <!-- Confirm Password Strength Indicator -->
                        <div id="confirmStrength" class="mt-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                            <div id="confirmStrengthBar" class="h-full bg-red-500 rounded-full transition-all duration-300" style="width: 0%"></div>
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
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="<?= $settings['site_url'] ?>/dashboard/login.php"
            class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to login
        </a>
    </div>

    <!-- Password validation JavaScript -->
    <script src="<?= $settings['site_url'] ?>/js/password-validation.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setupPasswordValidation({
                passwordFieldId: 'password',
                confirmFieldId: 'confirm',
                confirmSectionId: 'confirmPasswordSection',
                requirementsSelector: '#password-requirements',
                strengthBarId: 'passwordStrengthBar',
                confirmStrengthBarId: 'confirmStrengthBar',
                togglePasswordId: 'togglePassword',
                toggleConfirmId: 'toggleConfirm',
                eyeIconId: 'eyeIcon',
                eyeSlashIconId: 'eyeSlashIcon',
                confirmEyeIconId: 'confirmEyeIcon',
                confirmEyeSlashIconId: 'confirmEyeSlashIcon'
            });
        });
    </script>
</body>

</html>