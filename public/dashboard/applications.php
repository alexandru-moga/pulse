<?php
require_once '../../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser;

require_once __DIR__ . '/../../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$pageTitle = "All Applications";
include '../components/layout/header.php';
include '../components/effects/grid.php';
$success = $error = null;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_update_id'])) {
    $id = intval($_POST['status_update_id']);
    $status = in_array($_POST['status'], ['waiting', 'accepted', 'rejected']) ? $_POST['status'] : 'waiting';
    $db->prepare("UPDATE applications SET status=? WHERE id=?")->execute([$status, $id]);
    $success = "Status updated.";
}

// Handle send accepted email
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

// Handle add as member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member_id'])) {
    $appId = intval($_POST['add_member_id']);
    $app = $db->prepare("SELECT * FROM applications WHERE id=?");
    $app->execute([$appId]);
    $app = $app->fetch();
    if ($app) {
        // Map application fields to users fields as needed
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

<head>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/applications.css">
</head>
<main class="applications-section">
    <h2>All Applications</h2>
    <?php if ($success): ?><div class="form-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="form-errors"><div class="error"><?= htmlspecialchars($error) ?></div></div><?php endif; ?>
    <?php if (empty($applications)): ?>
        <div class="form-errors"><div class="error">No applications found.</div></div>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="applications-table">
            <tr>
                <?php foreach (array_keys($applications[0]) as $col): ?>
                    <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $col))) ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
<?php foreach ($applications as $app): ?>
    <tr>
        <?php foreach ($app as $col => $val): ?>
            <?php if ($col !== 'status'): ?>
                <td><?= htmlspecialchars($val) ?></td>
            <?php endif; ?>
        <?php endforeach; ?>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="status_update_id" value="<?= $app['id'] ?>">
                <select name="status" onchange="this.form.submit()" style="padding:2px 8px;">
                    <option value="waiting" <?= $app['status']=='waiting'?'selected':'' ?>>Waiting</option>
                    <option value="accepted" <?= $app['status']=='accepted'?'selected':'' ?>>Accepted</option>
                    <option value="rejected" <?= $app['status']=='rejected'?'selected':'' ?>>Rejected</option>
                </select>
            </form>
        </td>
                    <td>
                        <?php if ($app['status'] == 'accepted'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="send_accept_id" value="<?= $app['id'] ?>">
                            <button type="submit" class="add-member-btn">Send Accepted Email</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="add_member_id" value="<?= $app['id'] ?>">
                            <button type="submit" class="add-member-btn">Add as Member</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>
    <?php endif; ?>
</main>
<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
?>
