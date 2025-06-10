<?php
require_once '../../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

// Get the page name from the URL, e.g. ?page=home
$pageName = $_GET['page'] ?? null;

if ($pageName) {
    // Get the table name for this page
    $page = $db->prepare("SELECT * FROM pages WHERE name = ?");
    $page->execute([$pageName]);
    $page = $page->fetch();

    if (!$page || empty($page['table_name'])) {
        $pageName = null;
    }
}

include '../components/layout/header.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<main class="contact-form-section" style="max-width:900px;margin:2rem auto;">
    <?php if ($pageName && $page): ?>
        <h2><?= htmlspecialchars(ucfirst($pageName)) ?> Page Elements</h2>
        <?php
        $tableName = $page['table_name'];
        $blocks = $db->query("SELECT * FROM `$tableName` ORDER BY order_num ASC")->fetchAll();
        ?>
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
                        <a href="edit-block.php?page=<?= urlencode($pageName) ?>&id=<?= $block['id'] ?>">Edit</a>
                        <a href="delete-block.php?page=<?= urlencode($pageName) ?>&id=<?= $block['id'] ?>" onclick="return confirm('Delete this block?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a href="add-block.php?page=<?= urlencode($pageName) ?>" class="cta-button" style="margin-top:1rem;">Add New Block</a>
        <a href="page-settings.php" class="cta-button">Back</a>
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
                <th>Actions</th>
            </tr>
            <?php foreach ($pages as $pg): ?>
                <?php if (empty($pg['table_name'])) continue; ?>
                <tr>
                    <td><?= htmlspecialchars($pg['name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($pg['title'] ?? '') ?></td>
                    <td><?= htmlspecialchars($pg['table_name'] ?? '') ?></td>
                    <td>
                        <a href="page-settings.php?page=<?= urlencode($pg['name']) ?>" class="cta-button">Edit Elements</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a href="settings.php" class="cta-button">Back</a>
    <?php endif; ?>
</main>

<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';
?>
