<?php
$settingNames = [
    'site_title',
    'site_url',
    'admin_email',
    'maintenance_mode'
];

require_once '../../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

$success = $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $id => $value) {
        $stmt = $db->prepare("UPDATE settings SET value=? WHERE id=?");
        $stmt->execute([$value, $id]);
    }
    $success = "Settings updated successfully!";
}

$inSql = implode(',', array_fill(0, count($settingNames), '?'));
$stmt = $db->prepare("SELECT * FROM settings WHERE name IN ($inSql) ORDER BY id ASC");
$stmt->execute($settingNames);
$settings = $stmt->fetchAll();

$pageStructure = $pageManager->getPageStructure('dashboard');
include '../components/layout/header.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<main class="contact-form-section">
    <section class="dashboard-card" style="max-width:600px;margin:2rem auto;">
        <h2>Website Settings</h2>
        <?php if ($success): ?>
            <div class="form-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" class="dashboard-form">
            <?php foreach ($settings as $setting): ?>
                <div class="form-group">
                    <label for="setting-<?= $setting['id'] ?>">
                        <?= htmlspecialchars($setting['name']) ?>
                    </label>
                    <input
                        type="text"
                        id="setting-<?= $setting['id'] ?>"
                        name="settings[<?= $setting['id'] ?>]"
                        value="<?= htmlspecialchars($setting['value']) ?>"
                        style="width:100%;"
                    >
                </div>
            <?php endforeach; ?>
            <button type="submit" class="cta-button" style="margin-top:1rem;">Save Settings</button>
            <a href="settings.php" class="cta-button">Back</a>
        </form>
    </section>
</main>

<?php 
include '../components/effects/grid.php';
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
?>