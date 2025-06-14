<?php
require_once '../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser;

require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$createSuccess = $createError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $fields = [
        'first_name', 'last_name', 'email', 'discord_id', 'slack_id', 'github_username',
        'school', 'hcb_member', 'birthdate', 'class', 'phone', 'role', 'description'
    ];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '');
    $data['active_member'] = isset($_POST['active_member']) ? 1 : 0;
    $data['hcb_member'] = isset($_POST['hcb_member']) ? 1 : 0;
    $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $exists = $db->prepare("SELECT 1 FROM users WHERE email=?");
    $exists->execute([$data['email']]);
    if ($exists->fetch()) {
        $createError = "A user with this email already exists.";
    } else {
        $stmt = $db->prepare("INSERT INTO users
            (first_name, last_name, email, password, discord_id, slack_id, github_username, school, hcb_member, birthdate, class, phone, role, description, active_member)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['first_name'], $data['last_name'], $data['email'], $data['password'],
            $data['discord_id'], $data['slack_id'], $data['github_username'], $data['school'],
            $data['hcb_member'], $data['birthdate'], $data['class'],
            $data['phone'], $data['role'], $data['description'], $data['active_member']
        ]);
        header("Location: users.php?created=1");
        exit();
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM users WHERE id=?")->execute([$_GET['delete']]);
    header("Location: users.php?deleted=1");
    exit();
}

$editSuccess = $editError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = intval($_POST['user_id']);
    $fields = [
        'first_name', 'last_name', 'email', 'discord_id', 'slack_id', 'github_username',
        'school', 'hcb_member', 'birthdate', 'class', 'phone', 'role', 'description'
    ];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '');
    $data['active_member'] = isset($_POST['active_member']) ? 1 : 0;
    $data['hcb_member'] = isset($_POST['hcb_member']) ? 1 : 0;

    $updatePassword = !empty($_POST['password']);
    if ($updatePassword) {
        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET
            first_name=?, last_name=?, email=?, password=?, discord_id=?, slack_id=?, github_username=?, school=?, hcb_member=?, birthdate=?, class=?, phone=?, role=?, description=?, active_member=?
            WHERE id=?");
        $params = array_values($data);
        $params[] = $id;
    } else {
        $stmt = $db->prepare("UPDATE users SET
            first_name=?, last_name=?, email=?, discord_id=?, slack_id=?, github_username=?, school=?, hcb_member=?, birthdate=?, class=?, phone=?, role=?, description=?, active_member=?
            WHERE id=?");
        $params = array_values($data);
        $params[] = $id;
    }
    $stmt->execute($params);
    $editSuccess = "User updated successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    $id = intval($_POST['user_id']);
    $active = isset($_POST['active_member']) ? 1 : 0;
    $db->prepare("UPDATE users SET active_member=? WHERE id=?")->execute([$active, $id]);
    exit();
}

$resetSuccess = $resetError = null;
if (isset($_GET['reset']) && is_numeric($_GET['reset'])) {
    $userId = $_GET['reset'];
    $user = $db->prepare("SELECT * FROM users WHERE id=?");
    $user->execute([$userId]);
    $user = $user->fetch();
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
            ->execute([$userId, $token, $expires]);
        $resetLink = $settings['site_url'] . "/dashboard/reset.php?token=$token";
        $smtp = [];
        foreach ($db->query("SELECT name, value FROM settings") as $row) $smtp[$row['name']] = $row['value'];
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
                $mail->Body = "Hello,<br><br>
                    We received a request to reset your password. 
                    <b><a href=\"$resetLink\">Click here to reset your password</a></b>.<br><br>
                    This link will expire in 1 hour. If you did not request this, you can ignore this email.<br><br>
                    PULSE Team";

                $mail->send();
            $resetSuccess = "Password reset email sent to " . htmlspecialchars($user['email']);
        } catch (Exception $e) {
            $resetError = "Failed to send email: " . $mail->ErrorInfo;
        }
    }
}

$users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

$pageTitle = "Manage Users";
include '../components/layout/header.php';
include '../components/effects/grid.php';
?>

<head>
        <link rel="stylesheet" href="../css/main.css">
</head>

<main class="contact-form-section">
    <h2>Manage Users</h2>
    <?php if ($createSuccess): ?><div class="form-success"><?= htmlspecialchars($createSuccess) ?></div><?php endif; ?>
    <?php if ($createError): ?><div class="form-errors"><div class="error"><?= htmlspecialchars($createError) ?></div></div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="form-success">User deleted.</div><?php endif; ?>
    <?php if ($editSuccess): ?><div class="form-success"><?= htmlspecialchars($editSuccess) ?></div><?php endif; ?>
    <?php if ($editError): ?><div class="form-errors"><div class="error"><?= htmlspecialchars($editError) ?></div></div><?php endif; ?>
    <?php if ($resetSuccess): ?><div class="form-success"><?= htmlspecialchars($resetSuccess) ?></div><?php endif; ?>
    <?php if ($resetError): ?><div class="form-errors"><div class="error"><?= htmlspecialchars($resetError) ?></div></div><?php endif; ?>

    <?php
    if (isset($_GET['add'])): ?>
        <a href="users.php" class="cta-button" style="margin-bottom:1.5rem;display:inline-block;">&larr; Back to all users</a>
        <form method="post" class="manage-user-form">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name"></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name"></div>
            <div class="form-group"><label>Email</label><input type="email" name="email"></div>
            <div class="form-group"><label>Password</label><input type="password" name="password"></div>
            <div class="form-group"><label>Discord ID</label><input type="text" name="discord_id"></div>
            <div class="form-group"><label>Slack ID</label><input type="text" name="slack_id"></div>
            <div class="form-group"><label>GitHub Username</label><input type="text" name="github_username"></div>
            <div class="form-group"><label>School</label><input type="text" name="school"></div>
            <div class="toggles-row" style="gap:2rem;">
                <div>
                    <span class="switch-label">Hcb Member</span>
                    <label class="switch">
                        <input type="checkbox" name="hcb_member" value="1">
                        <span class="slider"></span>
                    </label>
                </div>
                <div>
                    <span class="switch-label">Active Member</span>
                    <label class="switch">
                        <input type="checkbox" name="active_member" value="1">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            <div class="form-group"><label>Birth Date</label><input type="date" name="birthdate"></div>
            <div class="form-group"><label>Class</label><input type="text" name="class"></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
            <div class="form-group"><label>Role</label><input type="text" name="role"></div>
            <div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
            <button type="submit" name="create_user" class="cta-button">Create User</button>
        </form>
    <?php
    elseif (isset($_GET['edit']) && is_numeric($_GET['edit'])):
        $editId = intval($_GET['edit']);
        $editUser = null;
        foreach ($users as $u) {
            if ($u['id'] == $editId) {
                $editUser = $u;
                break;
            }
        }
        if ($editUser): ?>
            <a href="users.php" class="cta-button" style="margin-bottom:1.5rem;display:inline-block;">&larr; Back to all users</a>
            <form method="post" class="manage-user-form">
                <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?= htmlspecialchars($editUser['first_name']) ?>"></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?= htmlspecialchars($editUser['last_name']) ?>"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>"></div>
                <div class="form-group"><label>Password <span style="font-weight:400;color:#bfc9d1">(leave blank to keep unchanged)</span></label><input type="password" name="password"></div>
                <div class="form-group"><label>Discord ID</label><input type="text" name="discord_id" value="<?= htmlspecialchars($editUser['discord_id']) ?>"></div>
                <div class="form-group"><label>Slack ID</label><input type="text" name="slack_id" value="<?= htmlspecialchars($editUser['slack_id']) ?>"></div>
                <div class="form-group"><label>GitHub Username</label><input type="text" name="github_username" value="<?= htmlspecialchars($editUser['github_username']) ?>"></div>
                <div class="form-group"><label>School</label><input type="text" name="school" value="<?= htmlspecialchars($editUser['school']) ?>"></div>
                <div class="toggles-row" style="gap:2rem;">
                    <div>
                        <span class="switch-label">Hcb Member</span>
                        <label class="switch">
                            <input type="checkbox" name="hcb_member" value="1" <?= $editUser['hcb_member']?'checked':'' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div>
                        <span class="switch-label">Active Member</span>
                        <label class="switch">
                            <input type="checkbox" name="active_member" value="1" <?= $editUser['active_member']?'checked':'' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                <div class="form-group"><label>Birth Date</label><input type="date" name="birthdate" value="<?= htmlspecialchars($editUser['birthdate']) ?>"></div>
                <div class="form-group"><label>Class</label><input type="text" name="class" value="<?= htmlspecialchars($editUser['class']) ?>"></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($editUser['phone']) ?>"></div>
                <div class="form-group"><label>Role</label><input type="text" name="role" value="<?= htmlspecialchars($editUser['role']) ?>"></div>
                <div class="form-group"><label>Description</label><textarea name="description"><?= htmlspecialchars($editUser['description']) ?></textarea></div>
                <button type="submit" name="edit_user" class="cta-button">Save Changes</button>
            </form>
        <?php else: ?>
            <div class="form-errors"><div class="error">User not found.</div></div>
            <a href="users.php" class="cta-button">&larr; Back to all users</a>
        <?php endif; ?>
    <?php
    else: ?>
        <h3>All Users</h3>
        <table>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Role</th>
                <th colspan="1" style="text-align:center;">Active</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <div class="toggles-row">
                            <form method="post" class="inline-form" onsubmit="return false;">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <label class="switch">
                                    <input type="checkbox" name="active_member" value="1"
                                        <?= $u['active_member'] ? 'checked' : '' ?>
                                        onchange="toggleActive(this, <?= $u['id'] ?>)">
                                    <span class="slider"></span>
                                </label>
                            </form>
                        </div>
                    </td>
                    <td>
                        <a href="?edit=<?= $u['id'] ?>">Edit</a>
                        <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete user?')">Delete</a>
                        <a href="?reset=<?= $u['id'] ?>" onclick="return confirm('Send password reset email?')">Send Reset</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <a href="users.php?add=1" class="add-user-btn cta-button">Add User</a>
    <?php endif; ?>
</main>

<script>
function toggleActive(checkbox, userId) {
    var formData = new FormData();
    formData.append('toggle_active', 1);
    formData.append('user_id', userId);
    if (checkbox.checked) formData.append('active_member', 1);
    fetch(window.location.href, { method: 'POST', body: formData });
}
</script>

<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
?>
