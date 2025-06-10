<?php
require_once '../../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

$tableName = 'footer';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM `$tableName` WHERE id=?");
    $stmt->execute([$deleteId]);
    header("Location: footer-settings.php");
    exit();
}

$blocks = $db->query("SELECT * FROM `$tableName` ORDER BY order_num ASC")->fetchAll();

include '../components/layout/header.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<main class="contact-form-section" style="max-width:900px;margin:2rem auto;">
    <h2>Footer Elements</h2>
    <table class="applications-table">
        <tr>
            <th>ID</th>
            <th>Section Type</th>
            <th>Content</th>
            <th>Order</th>
            <th>Active</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($blocks as $block): ?>
            <tr>
                <td><?= htmlspecialchars($block['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($block['section_type'] ?? '') ?></td>
                <td>
                    <pre style="max-width:300px;overflow:auto;font-size:0.95em;"><?= htmlspecialchars(mb_strimwidth($block['content'] ?? '', 0, 120, '...')) ?></pre>
                </td>
                <td><?= htmlspecialchars($block['order_num'] ?? '') ?></td>
                <td><?= ($block['is_active'] ?? 0) ? 'Yes' : 'No' ?></td>
                <td><?= htmlspecialchars($block['created_at'] ?? '') ?></td>
                <td>
                    <a href="edit-footer-block.php?id=<?= $block['id'] ?>" class="cta-button">Edit</a>
                    <a href="footer-settings.php?delete=<?= $block['id'] ?>" class="cta-button" style="background:#d9534f;" onclick="return confirm('Delete this block?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="add-footer-block.php" class="cta-button" style="margin-top:1rem;">Add New Footer Block</a>
    <a href="settings.php" class="cta-button">Back</a>
</main>

<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';
?>
