<?php
if (file_exists(__DIR__ . '/../config.php')) {
    header('Location: /');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $site_url = trim($_POST['site_url'] ?? '');
    $site_title = trim($_POST['site_title'] ?? '');

    $mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        $error = "Database connection failed: " . $mysqli->connect_error;
    } else {
        $sql = file_get_contents(__DIR__ . '/pulse.sql');
        if (!$mysqli->multi_query($sql)) {
            $error = "SQL import failed: " . $mysqli->error;
        } else {
            $mysqli->query("UPDATE settings SET value='" . $mysqli->real_escape_string($site_url) . "' WHERE name='site_url'");
            $mysqli->query("UPDATE settings SET value='" . $mysqli->real_escape_string($site_title) . "' WHERE name='site_title'");

            $config = "<?php
define('DB_HOST', '" . addslashes($db_host) . "');
define('DB_NAME', '" . addslashes($db_name) . "');
define('DB_USER', '" . addslashes($db_user) . "');
define('DB_PASS', '" . addslashes($db_pass) . "');
";
            file_put_contents(__DIR__ . '/../config.php', $config);

            function rrmdir($dir) {
                foreach(glob($dir . '/*') as $file) {
                    if(is_dir($file)) rrmdir($file); else unlink($file);
                }
                rmdir($dir);
            }
            rrmdir(__DIR__);

            $success = "Installation complete! <a href='/'>Go to your site</a>";
            echo "<!DOCTYPE html><html><body><h2>$success</h2></body></html>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pulse Installer</title>
    <style>
        body { font-family: sans-serif; background: #181a20; color: #fff; }
        .installer { background: #23242a; max-width: 400px; margin: 4rem auto; padding: 2rem; border-radius: 1rem; }
        input, button { width: 100%; padding: 0.8em; margin-bottom: 1em; border-radius: 0.3em; border: 1px solid #444; }
        button { background: #58a6ff; color: #fff; border: none; cursor: pointer; }
        .error { background: #d9534f; color: #fff; padding: 1em; border-radius: 0.4em; margin-bottom: 1em; }
        .success { background: #28a745; color: #fff; padding: 1em; border-radius: 0.4em; margin-bottom: 1em; }
    </style>
</head>
<body>
<div class="installer">
    <h2>Pulse Installer</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <label>Database Host</label>
        <input name="db_host" required value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>">
        <label>Database Name</label>
        <input name="db_name" required value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>">
        <label>Database User</label>
        <input name="db_user" required value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>">
        <label>Database Password</label>
        <input name="db_pass" type="password" value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
        <label>Site URL (with https://)</label>
        <input name="site_url" required value="<?= htmlspecialchars($_POST['site_url'] ?? '') ?>">
        <label>Site Title</label>
        <input name="site_title" required value="<?= htmlspecialchars($_POST['site_title'] ?? 'Pulse') ?>">
        <button type="submit">Install Pulse</button>
    </form>
</div>
</body>
</html>
