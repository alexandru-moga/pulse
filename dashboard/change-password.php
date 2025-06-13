<?php
require_once '../core/init.php';
checkLoggedIn();

global $db, $currentUser;

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$currentUser->id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($oldPassword, $user['password'])) {
        $error = "Old password is incorrect.";
    } elseif ($newPassword === '') {
        $error = "New password cannot be empty.";
    } elseif (strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New passwords do not match.";
    } elseif ($oldPassword === $newPassword) {
        $error = "New password cannot be the same as the old password.";
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")
           ->execute([$hashed, $currentUser->id]);
        $success = "Password updated successfully!";
    }
}

include '../components/layout/header.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';
?>

<head>
        <link rel="stylesheet" href="../css/main.css">
</head>

<main class="contact-form-section">
    <h2>Change Password</h2>
    <?php if ($success): ?>
        <div class="form-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="form-errors"><div class="error"><?= htmlspecialchars($error) ?></div></div>
    <?php endif; ?>
    <form method="post" class="change-password-form" autocomplete="off">
        <div class="form-group">
            <label for="old_password">Old Password</label>
            <input type="password" name="old_password" id="old_password" required autocomplete="current-password">
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" required minlength="6" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Repeat New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required minlength="6" autocomplete="new-password">
        </div>
        <button type="submit" class="cta-button">Change Password</button>
    </form>
</main>

<?php include '../components/layout/footer.php'; ?>
