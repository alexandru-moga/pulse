<?php
require_once '../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser;

$pageTitle = "Contact Messages";
include '../components/layout/header.php';
include '../components/effects/grid.php';

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_update_id'])) {
    $id = intval($_POST['status_update_id']);
    $status = ($_POST['status'] === 'solved') ? 'solved' : 'waiting';
    $db->prepare("UPDATE contact_messages SET status=? WHERE id=?")->execute([$status, $id]);
    $success = "Status updated.";
}

$messages = $db->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>

<head>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/applications.css">
</head>
<main class="applications-section">
    <h2>Contact Messages</h2>
    <?php if ($success): ?><div class="form-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="form-errors"><div class="error"><?= htmlspecialchars($error) ?></div></div><?php endif; ?>
    <?php if (empty($messages)): ?>
        <div class="form-errors"><div class="error">No messages found.</div></div>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="applications-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Submitted At</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($messages as $msg): ?>
                <tr>
                    <td><?= htmlspecialchars($msg['id']) ?></td>
                    <td><?= htmlspecialchars($msg['name']) ?></td>
                    <td><?= htmlspecialchars($msg['email']) ?></td>
                    <td><?= htmlspecialchars($msg['message']) ?></td>
                    <td><?= htmlspecialchars($msg['submitted_at']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="status_update_id" value="<?= $msg['id'] ?>">
                            <select name="status" onchange="this.form.submit()" style="padding:2px 8px;">
                                <option value="waiting" <?= $msg['status']=='waiting'?'selected':'' ?>>Waiting</option>
                                <option value="solved" <?= $msg['status']=='solved'?'selected':'' ?>>Solved</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <?php if ($msg['status'] != 'solved'): ?>
                            <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Reply to your contact message" class="add-member-btn">Reply</a>
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