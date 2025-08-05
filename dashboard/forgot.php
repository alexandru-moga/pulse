<?php
require_once '../core/init.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

global $db, $settings;

$error = $success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $error = "Please enter your email address.";
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);

            $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
                ->execute([$user['id'], $token, $expires]);

            $resetLink = $settings['site_url'] . "/dashboard/reset.php?token=$token";

            $smtp = [];
            $settingsRows = $db->query("SELECT * FROM settings")->fetchAll();
            foreach ($settingsRows as $row) $smtp[$row['name']] = $row['value'];

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $smtp['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['smtp_user'];
                $mail->Password = $smtp['smtp_pass'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $smtp['smtp_port'];

                $mail->setFrom($smtp['smtp_from'], $smtp['smtp_from_name']);
                $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
                $mail->isHTML(true);
                $mail->Subject = 'Reset Your PULSE Password';
                
                // Modern HTML email template matching the reset page design
                $emailBody = '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Reset Your PULSE Password</title>
                    <style>
                        body {
                            margin: 0;
                            padding: 0;
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                            line-height: 1.6;
                            color: #374151;
                            background-color: #f9fafb;
                        }
                        .container {
                            max-width: 600px;
                            margin: 0 auto;
                            padding: 40px 20px;
                        }
                        .card {
                            background-color: #ffffff;
                            border-radius: 8px;
                            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                            overflow: hidden;
                        }
                        .header {
                            text-align: center;
                            padding: 40px 40px 20px 40px;
                            background-color: #ffffff;
                        }
                        .logo-img {
                            width: 64px;
                            height: 64px;
                            margin: 0 auto 24px auto;
                            display: block;
                            border-radius: 8px;
                        }
                        .logo-fallback {
                            width: 64px;
                            height: 64px;
                            margin: 0 auto 24px auto;
                            background: linear-gradient(135deg, #FF8C37 0%, #EC3750 100%);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-family: system-ui, -apple-system, sans-serif;
                            font-weight: bold;
                            font-size: 24px;
                            color: white;
                            text-decoration: none;
                        }
                        .title {
                            font-size: 24px;
                            font-weight: 800;
                            color: #111827;
                            margin: 0 0 8px 0;
                        }
                        .subtitle {
                            font-size: 14px;
                            color: #6b7280;
                            margin: 0;
                        }
                        .content {
                            padding: 20px 40px 40px 40px;
                        }
                        .greeting {
                            font-size: 16px;
                            margin-bottom: 20px;
                        }
                        .message {
                            font-size: 14px;
                            line-height: 1.5;
                            margin-bottom: 30px;
                            color: #4b5563;
                        }
                        .button {
                            display: inline-block;
                            padding: 12px 24px;
                            background-color: #ec4a0a;
                            color: white;
                            text-decoration: none;
                            border-radius: 6px;
                            font-weight: 500;
                            font-size: 14px;
                            text-align: center;
                            margin: 20px 0;
                        }
                        .button:hover {
                            background-color: #dc2626;
                        }
                        .link-fallback {
                            font-size: 12px;
                            color: #6b7280;
                            margin-top: 20px;
                            word-break: break-all;
                        }
                        .footer {
                            text-align: center;
                            padding: 20px 40px;
                            background-color: #f9fafb;
                            border-top: 1px solid #e5e7eb;
                        }
                        .footer-text {
                            font-size: 12px;
                            color: #6b7280;
                            margin: 0;
                        }
                        .security-notice {
                            background-color: #fef3cd;
                            border: 1px solid #fde68a;
                            border-radius: 6px;
                            padding: 16px;
                            margin: 20px 0;
                        }
                        .security-notice p {
                            margin: 0;
                            font-size: 14px;
                            color: #92400e;
                        }
                        @media only screen and (max-width: 600px) {
                            .container {
                                padding: 20px 10px;
                            }
                            .header, .content, .footer {
                                padding-left: 20px;
                                padding-right: 20px;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="card">
                            <div class="header">
                                <img src="' . $settings['site_url'] . '/images/hackclub-logo.png" 
                                     alt="Hack Club Logo" 
                                     class="logo-img"
                                     style="width: 64px; height: 64px; margin: 0 auto 24px auto; display: block; border-radius: 8px;"
                                     onerror="this.style.display=\'none\'; document.getElementById(\'logo-fallback-reset\').style.display=\'flex\';">
                                <div id="logo-fallback-reset" class="logo-fallback" style="width: 64px; height: 64px; margin: 0 auto 24px auto; background: linear-gradient(135deg, #FF8C37 0%, #EC3750 100%); border-radius: 50%; display: none; align-items: center; justify-content: center; font-family: system-ui, -apple-system, sans-serif; font-weight: bold; font-size: 24px; color: white;">
                                    H
                                </div>
                                <h1 class="title">Reset Your Password</h1>
                                <p class="subtitle">Secure access to your PULSE account</p>
                            </div>
                            
                            <div class="content">
                                <div class="greeting">Hello ' . htmlspecialchars($user['first_name']) . ',</div>
                                
                                <div class="message">
                                    We received a request to reset the password for your PULSE account. If you made this request, click the button below to set a new password.
                                </div>
                                
                                <div style="text-align: center;">
                                    <a href="' . $resetLink . '" class="button">Reset Password</a>
                                </div>
                                
                                <div class="security-notice">
                                    <p><strong>Security Notice:</strong> This link will expire in 1 hour for your security. If you did not request a password reset, you can safely ignore this email.</p>
                                </div>
                                
                                <div class="link-fallback">
                                    If the button above does not work, copy and paste this link into your browser:<br>
                                    <a href="' . $resetLink . '" style="color: #ec4a0a;">' . $resetLink . '</a>
                                </div>
                            </div>
                            
                            <div class="footer">
                                <p class="footer-text">
                                    This email was sent by PULSE. If you have any questions, please contact our support team.
                                </p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>';
                
                $mail->Body = $emailBody;

                $mail->send();
                $success = "A password reset link has been sent to your email address.";
            } catch (Exception $e) {
                $error = "Failed to send email. Please contact support.";
            }
        } else {
            $error = "No user found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PULSE Dashboard</title>
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
    <style>
        /* Prevent element overlap and ensure proper spacing */
        .form-container {
            max-width: 400px;
            margin: 0 auto;
        }
        
        /* Ensure proper spacing between form elements */
        .form-spacing > * + * {
            margin-top: 1.5rem;
        }
        
        /* Prevent button overlap */
        button[type="submit"] {
            margin-top: 1rem;
        }
        
        /* Ensure proper spacing for dividers */
        .divider-section {
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <img src="<?= $settings['site_url'] ?>/images/logo.svg" 
                 alt="PULSE Logo" 
                 class="h-16 w-auto">
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Reset your password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Enter your email address to receive a password reset link
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 form-container">
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
                                Error
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
                                Email Sent
                            </h3>
                            <div class="mt-2 text-sm text-green-700">
                                <?= htmlspecialchars($success) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="space-y-6 form-spacing" method="POST" action="">
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
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                               placeholder="Enter your email address">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Send Reset Link
                    </button>
                </div>
            </form>

            <div class="mt-6 divider-section">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Remember your password?
                        </span>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="login.php" 
                       class="font-medium text-primary hover:text-red-500">
                        Sign in to your account
                    </a>
                </div>
            </div>
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
