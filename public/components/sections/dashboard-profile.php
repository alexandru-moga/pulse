<?php
global $currentUser, $db;

if (!$currentUser) {
    echo '<div class="error-message">You must be logged in to access the dashboard.</div>';
    return;
}

$success = $_SESSION['profile_success'] ?? null;
$errors = $_SESSION['profile_errors'] ?? [];
unset($_SESSION['profile_success'], $_SESSION['profile_errors']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dashboard_profile_update'])) {
    $newFirst = trim($_POST['first_name'] ?? '');
    $newLast = trim($_POST['last_name'] ?? '');
    $newDesc = trim($_POST['description'] ?? '');
    $newGithub = trim($_POST['github_username'] ?? '');

    $updateErrors = [];
    if ($newFirst === '') $updateErrors[] = "First name cannot be empty.";
    if ($newLast === '') $updateErrors[] = "Last name cannot be empty.";

    if (empty($updateErrors)) {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, description = ?, github_username = ? WHERE id = ?");
        $stmt->execute([$newFirst, $newLast, $newDesc, $newGithub, $currentUser->id]);
        $_SESSION['profile_success'] = "Profile updated successfully!";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['profile_errors'] = $updateErrors;
        header("Location: dashboard.php");
        exit();
    }
}
?>

<section class="contact-form-section">
    <h2>My Profile</h2>
    <?php if ($success): ?>
        <div class="form-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="form-errors">
            <?php foreach ($errors as $error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form class="dashboard-profile-form" method="POST" action="dashboard.php">
        <div class="form-group">
            <label for="first_name">First Name *</label>
            <input type="text" id="first_name" name="first_name"
                   value="<?= htmlspecialchars($currentUser->first_name ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name *</label>
            <input type="text" id="last_name" name="last_name"
                   value="<?= htmlspecialchars($currentUser->last_name ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="github_username">GitHub Username</label>
            <input type="text" id="github_username" name="github_username"
                   value="<?= htmlspecialchars($currentUser->github_username ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"><?= htmlspecialchars($currentUser->description ?? '') ?></textarea>
        </div>
        <button type="submit" name="dashboard_profile_update" class="cta-button">Update Profile</button>
    </form>
</section>
