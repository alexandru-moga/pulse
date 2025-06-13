<?php
require_once '../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    die("Invalid page ID.");
}

$stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$id]);
$page = $stmt->fetch();

if (!$page) {
    die("Page not found.");
}

$page_name = $page['name'];
$table_name = $page['table_name'];

$file_path = "../../public/{$page_name}.php";
if (file_exists($file_path)) {
    if (!unlink($file_path)) {
        die("Could not delete PHP file: {$file_path}");
    }
}

try {
    $db->query("DROP TABLE IF EXISTS `$table_name`");
} catch (Exception $e) {
    die("Could not drop table `$table_name`: " . $e->getMessage());
}

$stmt = $db->prepare("DELETE FROM pages WHERE id = ?");
$stmt->execute([$id]);

header("Location: page-settings.php");
exit();
?>
