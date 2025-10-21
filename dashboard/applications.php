<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$pageTitle = "Applications";
include __DIR__ . '/components/dashboard-header.php';
$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_update_id'])) {
    $id = intval($_POST['status_update_id']);
    $status = in_array($_POST['status'], ['waiting', 'accepted', 'rejected']) ? $_POST['status'] : 'waiting';
    $db->prepare("UPDATE applications SET status=? WHERE id=?")->execute([$status, $id]);
    $success = "Status updated.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_accept_id'])) {
    $id = intval($_POST['send_accept_id']);
    $app = $db->prepare("SELECT * FROM applications WHERE id=?");
    $app->execute([$id]);
    $app = $app->fetch();
    if ($app) {
        $mail = new PHPMailer(true);
        try {
            $smtp = [];
            foreach ($db->query("SELECT name, value FROM settings") as $row) $smtp[$row['name']] = $row['value'];
            $mail->isSMTP();
            $mail->Host = $smtp['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp['smtp_user'];
            $mail->Password = $smtp['smtp_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtp['smtp_port'];
            $mail->setFrom($smtp['smtp_from'], $smtp['smtp_from_name']);
            $mail->addAddress($app['email'], $app['first_name'] . ' ' . $app['last_name']);
            $mail->isHTML(true);
            $mail->Subject = 'Your Application Has Been Accepted!';
            
            // Modern HTML email template matching the reset page design
            $emailBody = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Application Accepted - Welcome to PULSE</title>
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
                    .celebration {
                        text-align: center;
                        font-size: 48px;
                        margin: 20px 0;
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
                    .success-notice {
                        background-color: #dcfce7;
                        border: 1px solid #86efac;
                        border-radius: 6px;
                        padding: 16px;
                        margin: 20px 0;
                    }
                    .success-notice p {
                        margin: 0;
                        font-size: 14px;
                        color: #166534;
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
                                 onerror="this.style.display=\'none\'; document.getElementById(\'logo-fallback-apps\').style.display=\'flex\';">
                            <div id="logo-fallback-apps" class="logo-fallback" style="width: 64px; height: 64px; margin: 0 auto 24px auto; background: linear-gradient(135deg, #FF8C37 0%, #EC3750 100%); border-radius: 50%; display: none; align-items: center; justify-content: center; font-family: system-ui, -apple-system, sans-serif; font-weight: bold; font-size: 24px; color: white;">
                                H
                            </div>
                            <h1 class="title">Congratulations!</h1>
                            <p class="subtitle">Your application has been accepted</p>
                        </div>
                        
                        <div class="content">
                            <div class="celebration">🎉</div>
                            
                            <div class="greeting">Hello ' . htmlspecialchars($app['first_name']) . ',</div>
                            
                            <div class="message">
                                We are excited to inform you that your application to join ' . htmlspecialchars($settings['site_title']) . ' has been <strong>accepted</strong>! Welcome to our community.
                            </div>
                            
                            <div class="success-notice">
                                <p><strong>Next Steps:</strong> You will receive further instructions about accessing your account and getting started with PULSE. Welcome aboard!</p>
                            </div>
                            
                            <div class="message">
                                We look forward to having you as part of our team and seeing the amazing things you\'ll accomplish with us.
                            </div>
                        </div>
                        
                        <div class="footer">
                            <p class="footer-text">
                                Welcome to ' . htmlspecialchars($settings['site_title']) . '! If you have any questions, please contact our support team.
                            </p>
                        </div>
                    </div>
                </div>
            </body>
            </html>';
            
            $mail->Body = $emailBody;
            $mail->send();
            $success = "Accepted email sent to " . htmlspecialchars($app['email']);
        } catch (Exception $e) {
            $error = "Failed to send email: " . $mail->ErrorInfo;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member_id'])) {
    $appId = intval($_POST['add_member_id']);
    $app = $db->prepare("SELECT * FROM applications WHERE id=?");
    $app->execute([$appId]);
    $app = $app->fetch();
    if ($app) {
        $fields = [
            'first_name'    => $app['first_name'] ?? '',
            'last_name'     => $app['last_name'] ?? '',
            'email'         => $app['email'] ?? '',
            'school'        => $app['school'] ?? '',
            'birthdate'     => $app['birthdate'] ?? '',
            'class'         => $app['class'] ?? '',
            'phone'         => $app['phone'] ?? '',
            'description'   => $app['superpowers'] ?? '',
        ];
        $fields['password'] = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $fields['role'] = 'Member';
        $fields['active_member'] = 1;
        $exists = $db->prepare("SELECT 1 FROM users WHERE email=?");
        $exists->execute([$fields['email']]);
        if ($exists->fetch()) {
            $error = "A user with this email already exists.";
        } else {
            $stmt = $db->prepare("INSERT INTO users
                (first_name, last_name, email, password, school, birthdate, class, phone, role, description, active_member)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $fields['first_name'], $fields['last_name'], $fields['email'], $fields['password'],
                $fields['school'], $fields['birthdate'], $fields['class'],
                $fields['phone'], $fields['role'], $fields['description'], $fields['active_member']
            ]);
            $success = "User added as member!";
        }
    } else {
        $error = "Application not found.";
    }
}

$applications = $db->query("SELECT * FROM applications ORDER BY id DESC")->fetchAll();
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Applications Management</h2>
                <p class="text-gray-600 mt-1">Review and manage membership applications</p>
            </div>
            <div class="text-sm text-gray-500">
                Total Applications: <?= count($applications) ?>
            </div>
        </div>
    </div>
    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Applications List</h3>
        </div>
        
        <?php if (empty($applications)): ?>
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No applications</h3>
                <p class="mt-1 text-sm text-gray-500">No membership applications have been submitted yet.</p>
            </div>
        <?php else: ?>
            <div class="overflow-hidden">
                <div class="overflow-x-auto max-h-96">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <?php foreach (array_keys($applications[0]) as $col): ?>
                                    <?php if ($col !== 'status'): ?>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $col))) ?>
                                        </th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($applications as $app): ?>
                                <tr class="hover:bg-gray-50">
                                    <?php foreach ($app as $col => $val): ?>
                                        <?php if ($col !== 'status'): ?>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?= htmlspecialchars($val ?? '') ?>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <form method="post" class="inline">
                                            <input type="hidden" name="status_update_id" value="<?= $app['id'] ?>">
                                            <select name="status" onchange="this.form.submit()" 
                                                    class="text-sm rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary <?php
                                                        if ($app['status'] === 'waiting') echo 'bg-yellow-50 text-yellow-800';
                                                        elseif ($app['status'] === 'accepted') echo 'bg-green-50 text-green-800';
                                                        elseif ($app['status'] === 'rejected') echo 'bg-red-50 text-red-800';
                                                    ?>">
                                                <option value="waiting" <?= $app['status']=='waiting'?'selected':'' ?>>Waiting</option>
                                                <option value="accepted" <?= $app['status']=='accepted'?'selected':'' ?>>Accepted</option>
                                                <option value="rejected" <?= $app['status']=='rejected'?'selected':'' ?>>Rejected</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($app['status'] == 'accepted'): ?>
                                            <div class="flex space-x-2">
                                                <form method="post" class="inline">
                                                    <input type="hidden" name="send_accept_id" value="<?= $app['id'] ?>">
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        Send Email
                                                    </button>
                                                </form>
                                                <form method="post" class="inline">
                                                    <input type="hidden" name="add_member_id" value="<?= $app['id'] ?>">
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        Add Member
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
