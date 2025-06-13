<?php
require_once '../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

$blockId = intval($_GET['id'] ?? 0);
if (!$blockId) die('No block specified.');

$stmt = $db->prepare("SELECT * FROM footer WHERE id = ?");
$stmt->execute([$blockId]);
$block = $stmt->fetch();

if (!$block) die('Block not found.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_type = trim($_POST['section_type'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $order_num = intval($_POST['order_num'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$section_type) $errors[] = 'Section type is required.';
    if (!$content) $errors[] = 'Content is required.';

    if (empty($errors)) {
        $update = $db->prepare("UPDATE footer SET section_type = ?, content = ?, order_num = ?, is_active = ? WHERE id = ?");
        $update->execute([$section_type, $content, $order_num, $is_active, $blockId]);
        header("Location: footer-settings.php");
        exit();
    }
}

include '../components/layout/header.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<main class="contact-form-section" style="max-width:600px;margin:2rem auto;">
    <h2>Edit Footer Block</h2>
    <?php if ($errors): ?>
        <div class="form-errors">
            <?php foreach ($errors as $error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="section_type">Section Type</label>
            <input type="text" id="section_type" name="section_type" value="<?= htmlspecialchars($_POST['section_type'] ?? $block['section_type']) ?>" required>
            <small>Example: logo, links, cta, credits</small>
        </div>
        <div class="form-group">
            <label for="content">Content (JSON or string)</label>
            <textarea id="content" name="content" rows="6" required><?= htmlspecialchars($_POST['content'] ?? $block['content']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="order_num">Order Number</label>
            <input type="number" id="order_num" name="order_num" value="<?= htmlspecialchars($_POST['order_num'] ?? $block['order_num']) ?>" required>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= (isset($_POST['is_active']) ? $_POST['is_active'] : $block['is_active']) ? 'checked' : '' ?>> Active
            </label>
        </div>
        <button type="submit" class="cta-button">Save Changes</button>
        <a href="footer-settings.php" class="cta-button">Back</a>
    </form>
</main>

<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';
?>