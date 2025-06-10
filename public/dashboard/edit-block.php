<?php
require_once '../../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

$pageName = $_GET['page'] ?? '';
$blockId = intval($_GET['id'] ?? 0);

if (!$pageName || !$blockId) {
    die('Invalid page or block ID.');
}

$page = $db->prepare("SELECT * FROM pages WHERE name = ?");
$page->execute([$pageName]);
$page = $page->fetch();

if (!$page || empty($page['table_name'])) {
    die('Invalid page or table name.');
}

$tableName = $page['table_name'];

$stmt = $db->prepare("SELECT * FROM `$tableName` WHERE id = ?");
$stmt->execute([$blockId]);
$block = $stmt->fetch();

if (!$block) {
    die('Block not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $block_name = trim($_POST['block_name'] ?? '');
    $block_type = trim($_POST['block_type'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $order_num = intval($_POST['order_num'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$block_name) {
        $errors[] = 'Block name is required.';
    }
    if (!$block_type) {
        $errors[] = 'Block type is required.';
    }

    if (empty($errors)) {
        $update = $db->prepare("UPDATE `$tableName` SET block_name = ?, block_type = ?, content = ?, order_num = ?, is_active = ? WHERE id = ?");
        $update->execute([$block_name, $block_type, $content, $order_num, $is_active, $blockId]);
        header("Location: page-settings.php?page=" . urlencode($pageName));
        exit();
    }
}

include '../components/layout/header.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<main class="contact-form-section" style="max-width:600px;margin:2rem auto;">
    <h2>Edit Block</h2>
    <?php if ($errors): ?>
        <div class="form-errors">
            <?php foreach ($errors as $error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="block_name">Block Name</label>
            <input type="text" id="block_name" name="block_name" value="<?= htmlspecialchars($block['block_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="block_type">Block Type</label>
            <input type="text" id="block_type" name="block_type" value="<?= htmlspecialchars($block['block_type']) ?>" required>
        </div>
        <div class="form-group">
            <label for="content">Content (JSON or string)</label>
            <textarea id="content" name="content" rows="6"><?= htmlspecialchars($block['content']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="order_num">Order Number</label>
            <input type="number" id="order_num" name="order_num" value="<?= htmlspecialchars($block['order_num']) ?>" required>
        </div>
        <div class="form-group">
            <label for="is_active">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= $block['is_active'] ? 'checked' : '' ?>> Active
            </label>
        </div>
        <button type="submit" class="cta-button">Save Changes</button>
        <a href="page-settings.php?page=<?= urlencode($pageName) ?>" class="cta-button">Back</a>
    </form>
</main>

<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';

?>
