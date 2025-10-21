<?php
require_once '../core/init.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

global $db, $settings;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

$email = trim($_POST['email'] ?? '');

if ($email === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please enter your email address.']);
    exit();
}

// Check if user exists
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    // For security, don't reveal if email exists or not
    // But still send success message
    echo json_encode([
        'success' => true,
        'message' => 'If an account exists with this email, a login link has been sent.'
    ]);
    exit();
}

// Generate secure token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', time() + 900); // 15 minutes expiry

// Store token in database
try {
    $db->prepare("INSERT INTO email_login_tokens (user_id, token, expires_at) VALUES (?, ?, ?)")
        ->execute([$user['id'], $token, $expires]);
} catch (Exception $e) {
    error_log("Failed to create login token: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to generate login link. Please try again.']);
    exit();
}

// Create login link
$loginLink = $settings['site_url'] . "/dashboard/verify-login-token.php?token=$token";

// Get SMTP settings
$smtp = [];
$settingsRows = $db->query("SELECT * FROM settings")->fetchAll();
foreach ($settingsRows as $row) {
    $smtp[$row['name']] = $row['value'];
}

// Send email with PHPMailer
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
    $mail->Subject = 'Login to ' . htmlspecialchars($settings['site_title']) . ' Dashboard';

    // Modern HTML email template
    $emailBody = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login to ' . htmlspecialchars($settings['site_title']) . '</title>
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
                         alt="Logo" 
                         class="logo-img">
                    <h1 class="title">Login to Your Account</h1>
                    <p class="subtitle">Secure access to your ' . htmlspecialchars($settings['site_title']) . ' dashboard</p>
                </div>
                
                <div class="content">
                    <p class="greeting">Hi ' . htmlspecialchars($user['first_name']) . ',</p>
                    
                    <p class="message">
                        We received a request to log in to your account. Click the button below to securely access your dashboard:
                    </p>
                    
                    <div style="text-align: center;">
                        <a href="' . $loginLink . '" class="button">
                            Login to Dashboard
                        </a>
                    </div>
                    
                    <div class="security-notice">
                        <p><strong>⚠️ Security Note:</strong> This login link will expire in 15 minutes and can only be used once.</p>
                    </div>
                    
                    <p class="message">
                        If the button doesn\'t work, copy and paste this link into your browser:
                    </p>
                    
                    <p class="link-fallback">
                        ' . $loginLink . '
                    </p>
                    
                    <p class="message" style="margin-top: 30px;">
                        <strong>Didn\'t request this?</strong><br>
                        If you didn\'t try to log in, you can safely ignore this email. Someone may have entered your email address by mistake.
                    </p>
                </div>
                
                <div class="footer">
                    <p class="footer-text">
                        © ' . date('Y') . ' ' . htmlspecialchars($settings['site_title']) . '. All rights reserved.
                    </p>
                    <p class="footer-text" style="margin-top: 8px;">
                        This is an automated message. Please do not reply to this email.
                    </p>
                </div>
            </div>
        </div>
    </body>
    </html>';

    $mail->Body = $emailBody;
    $mail->AltBody = "Hi {$user['first_name']},\n\nClick this link to log in to your {$settings['site_title']} account:\n\n{$loginLink}\n\nThis link will expire in 15 minutes.\n\nIf you didn't request this, you can safely ignore this email.";

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'A login link has been sent to your email. Please check your inbox.'
    ]);
} catch (Exception $e) {
    error_log("Email sending failed: " . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send login link. Please try again later.'
    ]);
}
