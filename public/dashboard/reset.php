<?php
require_once '../../core/init.php';

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
        if ($newPassword === '' || strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($newPassword !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $reset['user_id']]);
            $db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$reset['user_id']]);
            $success = "Password has been reset. You can now <a href='login.php'>log in</a>.";
        }
    }
} else {
    $error = "No reset token provided.";
}

include '../components/layout/header.php';
include '../components/effects/mouse.php';
?>
<head>
    <link rel="stylesheet" href="../css/main.css">
</head>
<main>
    <section class="contact-form-section">
        <h2>Set a New Password</h2>
        <?php if ($error): ?>
            <div class="form-errors"><div class="error"><?= htmlspecialchars($error) ?></div></div>
        <?php elseif ($success): ?>
            <div class="form-success"><?= $success ?></div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm Password</label>
                    <input type="password" name="confirm" id="confirm" required minlength="6">
                </div>
                <button type="submit" class="cta-button">Set New Password</button>
            </form>
        <?php endif; ?>
    </section>
</main>
<?php include '../components/layout/footer.php'; ?>
