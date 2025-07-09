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
            $mail->Body = "Hello " . htmlspecialchars($app['first_name']) . ",<br><br>Your application has been <b>accepted</b>!<br>Welcome to the club!<br><br>PULSE Team";
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
            'discord_id'    => $app['discord_id'] ?? '',
            'school'        => $app['school'] ?? '',
            'birthdate'     => $app['birthdate'] ?? '',
            'class'         => $app['class'] ?? '',
            'phone'         => $app['phone'] ?? '',
            'description'   => $app['superpowers'] ?? '',
        ];
        $fields['password'] = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $fields['role'] = 'Member';
        $fields['active_member'] = 1;
        $fields['hcb_member'] = 0;
        $exists = $db->prepare("SELECT 1 FROM users WHERE email=?");
        $exists->execute([$fields['email']]);
        if ($exists->fetch()) {
            $error = "A user with this email already exists.";
        } else {
            $stmt = $db->prepare("INSERT INTO users
                (first_name, last_name, email, password, discord_id, school, birthdate, class, phone, role, description, active_member, hcb_member)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $fields['first_name'], $fields['last_name'], $fields['email'], $fields['password'],
                $fields['discord_id'], $fields['school'], $fields['birthdate'], $fields['class'],
                $fields['phone'], $fields['role'], $fields['description'], $fields['active_member'], $fields['hcb_member']
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
    <!-- Page Header -->
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

    <!-- Notifications -->
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

    <!-- Applications Table -->
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
            <div class="overflow-x-auto">
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
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
