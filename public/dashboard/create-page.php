<?php
require_once '../../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

$pages = $db->query("SELECT id, title FROM pages ORDER BY title ASC")->fetchAll();

$errors = [];
$success = null;

function slugify($text) {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($text));
    return trim($text, '_');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $menu_enabled = isset($_POST['menu_enabled']) ? 1 : 0;
    $visibility = $_POST['visibility'] ?? [];
    if (count($visibility) === 1 && $visibility[0] === 'everyone') {
        $visibility_db = null;
    } else {
        $visibility_db = implode(', ', $visibility);
    }

    $name = slugify($title);
    $table_name = 'page_' . $name;

    if ($title === '') $errors[] = "Page title is required.";
    if ($name === '') $errors[] = "Page name could not be generated from title.";
    if ($table_name === '' || strpos($table_name, 'page_') !== 0) $errors[] = "Table name is required and must start with 'page_'.";
    if (empty($visibility)) $errors[] = "At least one visibility role must be selected.";

    $stmt = $db->prepare("SELECT COUNT(*) FROM pages WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "A page with this name already exists.";
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO pages (name, title, description, table_name, menu_enabled, parent_id, visibility) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $title, $description, $table_name, $menu_enabled, $parent_id, $visibility_str]);
        $page_id = $db->lastInsertId();

        $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `block_name` varchar(100) NOT NULL,
            `block_type` varchar(50) NOT NULL,
            `content` text DEFAULT NULL,
            `order_num` int(11) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        try {
            $db->query($sql);
        } catch (Exception $e) {
            $errors[] = "Page added, but table creation failed: " . $e->getMessage();
        }

        $template_path = '../../core/page-template.php';
        $public_path = '../../public/' . $name . '.php';

        if (file_exists($template_path)) {
            $template_content = file_get_contents($template_path);
            $replacements = [
                '{{PAGE_TITLE}}' => $title,
                '{{PAGE_NAME}}' => $name,
                '{{PAGE_ID}}' => $page_id,
                '{{TABLE_NAME}}' => $table_name,
                '{{DESCRIPTION}}' => $description
            ];
            $new_file_content = str_replace(array_keys($replacements), array_values($replacements), $template_content);

            if (file_put_contents($public_path, $new_file_content) !== false) {
                $success = "Page created successfully! File created at " . htmlspecialchars($public_path);
            } else {
                $errors[] = "Page added to database, but file creation failed. Please check permissions.";
            }
        } else {
            $errors[] = "Page added to database, but template file not found at " . htmlspecialchars($template_path);
        }

        if (empty($errors)) {
            header("Location: page-settings.php");
            exit();
        }
    }
}

include '../components/layout/header.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
    <script>
    function slugify(text) {
        text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        text = text.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        return text;
    }
    function updateAutoFields() {
        var title = document.getElementById('title').value;
        var pageName = slugify(title);
        document.getElementById('name').value = pageName;
        document.getElementById('table_name').value = 'page_' + pageName;
    }
    window.addEventListener('DOMContentLoaded', function() {
        document.getElementById('title').addEventListener('input', updateAutoFields);
        updateAutoFields();
    });
    </script>
</head>

<main class="contact-form-section" style="max-width:600px;margin:2rem auto;">
    <h2>Create New Page</h2>
    <?php if ($errors): ?>
        <div class="form-errors">
            <?php foreach ($errors as $error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="form-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" class="dashboard-form" autocomplete="off">
        <div class="form-group">
            <label for="title">Name</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="name">URL (view-only)</label>
            <input type="text" id="name" name="name" class="readonly-field" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" readonly required>
        </div>
        <div class="form-group">
            <label for="table_name">Database table (view-only)</label>
            <input type="text" id="table_name" name="table_name" class="readonly-field" value="<?= htmlspecialchars($_POST['table_name'] ?? '') ?>" readonly required>
        </div>
        <div class="form-group">
            <label for="description">Description (optional)</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Show in Header Menu</label>
            <label class="switch">
                <input type="checkbox" name="menu_enabled" value="1" <?= isset($_POST['menu_enabled']) ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
        <div class="form-group">
            <label for="parent_id">Parent Page (optional)</label>
            <select name="parent_id" id="parent_id">
                <option value="">-- None --</option>
                <?php foreach ($pages as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (isset($_POST['parent_id']) && $_POST['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="visibility">Visibility (hold Ctrl/Cmd to select multiple)</label>
            <select name="visibility[]" id="visibility" multiple required>
                <?php
                $all_roles = ['everyone', 'guest', 'Member', 'Co-leader', 'Leader'];
                $selected_roles = $_POST['visibility'] ?? [];
                foreach ($all_roles as $role): ?>
                    <option value="<?= $role ?>" <?= in_array($role, $selected_roles) ? 'selected' : '' ?>><?= $role ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="cta-button">Create Page</button>
        <a href="page-settings.php" class="cta-button">Back</a>
    </form>
</main>

<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';
?>
