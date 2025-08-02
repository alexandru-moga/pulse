<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

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
        $stmt->execute([$name, $title, $description, $table_name, $menu_enabled, $parent_id, $visibility_db]);
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

        $template_path = '../core/page-template.php';
        $public_path = '../' . $name . '.php';

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
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Page created successfully!'];
                header("Location: page-settings.php");
                exit;
            } else {
                $errors[] = "Page added to database, but file creation failed. Please check permissions.";
            }
        } else {
            $errors[] = "Page added to database, but template file not found at " . htmlspecialchars($template_path);
        }

        if (empty($errors)) {
            $_SESSION['notification'] = ['type' => 'success', 'message' => 'Page created successfully!'];
            header("Location: page-settings.php");
            exit;
        }
    }
}

$pageTitle = 'Create New Page';
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Create New Page</h2>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Add a new page to your website</p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Page Settings
            </a>
        </div>
    </div>
    <?php if ($errors): ?>
        <div class="bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Page Configuration</h3>
        </div>
        
        <form method="post" class="p-6" autocomplete="off">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Page Title</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           required 
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"
                           placeholder="Enter page title">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">URL Slug</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               readonly 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Page URL slug</p>
                    </div>

                    <div>
                        <label for="table_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Database Table</label>
                        <input type="text" 
                               id="table_name" 
                               name="table_name" 
                               readonly 
                               value="<?= htmlspecialchars($_POST['table_name'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Database table for page content</p>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (optional)</label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"
                              placeholder="Brief description of the page"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent Page</label>
                        <select name="parent_id" 
                                id="parent_id"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                            <option value="">-- None (Top Level) --</option>
                            <?php foreach ($pages as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= (isset($_POST['parent_id']) && $_POST['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Menu Options</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" 
                                       name="menu_enabled" 
                                       value="1" 
                                       <?= isset($_POST['menu_enabled']) ? 'checked' : '' ?>
                                       class="rounded border-gray-300 dark:border-gray-600 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-900 dark:text-white">Show in header menu</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Page Visibility</label>
                    <select name="visibility[]" 
                            id="visibility" 
                            multiple 
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                        <?php
                        $all_roles = ['everyone', 'guest', 'Member', 'Co-leader', 'Leader'];
                        $selected_roles = $_POST['visibility'] ?? ['everyone'];
                        foreach ($all_roles as $role): ?>
                            <option value="<?= $role ?>" <?= in_array($role, $selected_roles) ? 'selected' : '' ?>>
                                <?= $role ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hold Ctrl/Cmd to select multiple roles. Select "everyone" for public pages.</p>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Create Page
                </button>
            </div>
        </form>
    </div>
</div>

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

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('title').addEventListener('input', updateAutoFields);
    updateAutoFields();
});
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
