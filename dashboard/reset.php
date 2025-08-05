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
                    <div class="mt-1">
                        <input id="password" 
                               name="password" 
                               type="password" 
                               required 
                               minlength="8"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                               placeholder="Enter new password">
                    </div>
                    <?= PasswordValidator::getRequirementsHtml() ?>
                </div>

                <div>
                    <label for="confirm" class="block text-sm font-medium text-gray-700">
                        Confirm Password
                    </label>
                    <div class="mt-1">
                        <input id="confirm" 
                               name="confirm" 
                               type="password" 
                               required 
                               minlength="8"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                               placeholder="Confirm new password">
                    </div>
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
</body>
</html>
