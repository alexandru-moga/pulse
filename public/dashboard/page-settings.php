<?php
require_once '../../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

$pageId = isset($_GET['id']) ? intval($_GET['id']) : null;
$page = null;
$tableName = null;
$blocks = [];
$tableExists = false;

if ($pageId) {
    $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$pageId]);
    $page = $stmt->fetch();

    $tableName = isset($page['table_name']) ? trim((string)$page['table_name']) : '';
    if ($page && $tableName !== '') {
        $stmt2 = $db->prepare("SHOW TABLES LIKE ?");
        $stmt2->execute([$tableName]);
        if ($stmt2->fetch()) {
            $tableExists = true;
            $blocks = $db->query("SELECT * FROM `$tableName` ORDER BY order_num ASC")->fetchAll();
        }
    }
}

include '../components/layout/header.php';
?>

<main class="contact-form-section" style="max-width:900px;margin:2rem auto;">
    <?php if ($pageId && $page): ?>
        <h2><?= htmlspecialchars($page['title']) ?> Page Elements</h2>
        <?php if ($tableName === ''): ?>
            <div class="form-errors"><div class="error">No table name set for this page.</div></div>
        <?php elseif (!$tableExists): ?>
            <div class="form-errors"><div class="error">The table <b><?= htmlspecialchars($tableName) ?></b> does not exist in the database.</div></div>
        <?php else: ?>
            <table class="applications-table">
                <tr>
                    <th>ID</th>
                    <th>Block Name</th>
                    <th>Block Type</th>
                    <th>Content</th>
                    <th>Order</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($blocks as $block): ?>
                    <tr>
                        <td><?= htmlspecialchars($block['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($block['block_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($block['block_type'] ?? '') ?></td>
                        <td>
                            <pre style="max-width:300px;overflow:auto;font-size:0.95em;">
<?= htmlspecialchars(mb_strimwidth($block['content'] ?? '', 0, 120, '...')) ?>
                            </pre>
                        </td>
                        <td><?= htmlspecialchars($block['order_num'] ?? '') ?></td>
                        <td><?= ($block['is_active'] ?? 0) ? 'Yes' : 'No' ?></td>
                        <td>
                            <a href="edit-block.php?id=<?= $pageId ?>&block_id=<?= $block['id'] ?>">Edit</a>
                            <a href="delete-block.php?id=<?= $pageId ?>&block_id=<?= $block['id'] ?>" onclick="return confirm('Delete this block?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <a href="add-block.php?id=<?= $pageId ?>" class="cta-button" style="margin-top:1rem;">Add New Block</a>
            <a href="page-settings.php" class="cta-button">Back</a>
        <?php endif; ?>
    <?php else: ?>
        <h2>Available Pages</h2>
        <?php
        $pages = $db->query("SELECT * FROM pages ORDER BY id ASC")->fetchAll();
        ?>
        <table class="applications-table">
            <tr>
                <th>Page Name</th>
                <th>Title</th>
                <th>Table Name</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
            <?php foreach ($pages as $pg): ?>
                <?php if (empty(trim((string)$pg['table_name']))) continue; ?>
                <tr>
                    <td><?= htmlspecialchars($pg['name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($pg['title'] ?? '') ?></td>
                    <td><?= htmlspecialchars($pg['table_name'] ?? '') ?></td>
                    <td>
                        <a href="page-settings.php?id=<?= $pg['id'] ?>" class="cta-button">Edit Elements</a>
                    </td>
                    <td>
                        <a href="delete-page.php?id=<?= $pg['id'] ?>" class="cta-button" onclick="return confirm('Are you sure you want to delete this page, its PHP file, and its table?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a href="create-page.php" class="cta-button">Create Page</a>
        <a href="settings.php" class="cta-button">Back</a>
    <?php endif; ?>
</main>

<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';
?>
