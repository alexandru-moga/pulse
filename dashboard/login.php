<?php
require_once '../core/init.php';
require_once '../core/classes/DiscordOAuth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

global $db, $settings;

$error = $_SESSION['discord_error'] ?? $_SESSION['error'] ?? null;
$info = $_SESSION['info'] ?? null;
unset($_SESSION['discord_error'], $_SESSION['error'], $_SESSION['info']);

$discord = new DiscordOAuth($db);
$discordConfigured = $discord->isConfigured();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($settings['site_title']) ?> Dashboard</title>
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
                alt="<?= htmlspecialchars($settings['site_title']) ?> Logo"
                class="h-16 w-auto">
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Sign in to Dashboard
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Access <?= htmlspecialchars($settings['site_title']) ?> dashboard
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <?php if ($info): ?>
                <div class="rounded-md bg-blue-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <?= htmlspecialchars($info) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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
                                Login Failed
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Success Message Container (for AJAX response) -->
            <div id="successMessage" class="hidden rounded-md bg-green-50 p-4 mb-6">
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
                        <div class="mt-2 text-sm text-green-700" id="successMessageText">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login Form -->
            <div class="space-y-4">
                <!-- Email Login Form -->
                <form id="emailLoginForm" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email address
                        </label>
                        <div class="mt-1">
                            <input id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                autofocus
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                placeholder="Enter your email">
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            id="emailLoginButton"
                            class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span id="emailLoginButtonText">Login with Email</span>
                        </button>
                    </div>
                </form>

                <?php if ($discordConfigured): ?>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">
                                Or
                            </span>
                        </div>
                    </div>

                    <!-- Discord Login Button -->
                    <a href="<?= htmlspecialchars($discord->generateAuthUrl(true)) ?>"
                        class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z" />
                        </svg>
                        Login with Discord
                    </a>
                <?php endif; ?>

                <?php
                // Check if Hack Club is configured
                require_once __DIR__ . '/../core/classes/HackClubOAuth.php';
                $hackclub = new HackClubOAuth($db);
                $hackclubConfigured = $hackclub->isConfigured();
                ?>
                <?php if ($hackclubConfigured): ?>
                    <!-- Hack Club Login Button -->
                    <a href="<?= htmlspecialchars($hackclub->generateAuthUrl(true)) ?>"
                        class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                        </svg>
                        Login with Hack Club
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="<?= $settings['site_url'] ?>"
            class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to main site
        </a>
    </div>

    <!-- Cookie Consent -->
    <?php include __DIR__ . '/../components/cookie-consent.php'; ?>

    <script>
        document.getElementById('emailLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const button = document.getElementById('emailLoginButton');
            const buttonText = document.getElementById('emailLoginButtonText');
            const email = document.getElementById('email').value;
            const successMessage = document.getElementById('successMessage');
            const successMessageText = document.getElementById('successMessageText');

            // Disable button and show loading state
            button.disabled = true;
            buttonText.textContent = 'Sending...';
            button.classList.add('opacity-75', 'cursor-not-allowed');

            // Hide previous messages
            successMessage.classList.add('hidden');

            try {
                const formData = new FormData();
                formData.append('email', email);

                const response = await fetch('send-login-link.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    successMessageText.textContent = data.message;
                    successMessage.classList.remove('hidden');

                    // Clear the email field
                    document.getElementById('email').value = '';
                } else {
                    // Show error message
                    alert(data.error || 'Failed to send login link. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                // Re-enable button
                button.disabled = false;
                buttonText.textContent = 'Login with Email';
                button.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        });
    </script>
</body>

</html>