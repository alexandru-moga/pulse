<?php
require_once '../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

$pageId = isset($_GET['id']) ? intval($_GET['id']) : null;
$blockId = isset($_GET['block_id']) ? intval($_GET['block_id']) : null;
if (!$pageId || !$blockId) die('Invalid page or block ID.');

$page = $db->prepare("SELECT * FROM pages WHERE id = ?");
$page->execute([$pageId]);
$page = $page->fetch();

if (!$page || empty($page['table_name'])) {
    die('Invalid page or table name.');
}
$tableName = $page['table_name'];
$stmt = $db->prepare("SELECT * FROM `$tableName` WHERE id = ?");
$stmt->execute([$blockId]);
$block = $stmt->fetch();
if (!$block) { die('Block not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete = $db->prepare("DELETE FROM `$tableName` WHERE id = ?");
    $delete->execute([$blockId]);
    header("Location: page-settings.php?id=" . $pageId);
    exit();
}

include '../components/layout/header.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<main class="contact-form-section" style="max-width:600px;margin:2rem auto;">
    <h2>Delete Block</h2>
    <p>Are you sure you want to delete the block <strong><?= htmlspecialchars($block['block_name']) ?></strong>?</p>
    <form method="post">
        <button type="submit" class="cta-button" style="background-color:#d9534f;">Delete</button>
        <a href="page-settings.php?page=<?= urlencode($pageName) ?>" class="cta-button">Back</a>
    </form>
</main>

<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';

?>
