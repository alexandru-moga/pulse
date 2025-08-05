<?php$token = $_GET['token'] ?? '';
$error = $success = null;

if ($token) {
    $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {'../core/init.php';

global $db, $settings;

$token = $_GET['token'] ?? '';
$error = $success = null;

if ($to                            <input id="confirm" 
                                   name="confirm" 
                                   type="password" 
                                   required 
                                   minlength="8"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                   placeholder="Confirm new password">
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - PULSE Dashboard</title>
    <link rel="icon" type="image/x-icon" href="<?= $settings['site_url'] ?>/images/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ec4a0a',
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
                 alt="PULSE Logo" 
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
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($success): ?>
                <div class="rounded-md bg-green-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Success!</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <?= $success ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <a href="login.php" 
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
                                   minlength="6"
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
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="<?= $settings['site_url'] ?>" 
           class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to main site
        </a>
    </div>
</body>
</html>
