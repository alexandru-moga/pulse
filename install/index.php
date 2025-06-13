<?php
if (file_exists(__DIR__ . '/../config.php')) {
    header('Location: /');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');
    $site_url = trim($_POST['site_url'] ?? '');
    $site_title = trim($_POST['site_title'] ?? '');

    $mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        $error = "Database connection failed: " . $mysqli->connect_error;
    } else {
        $sqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'pulse.sql';
        if (!file_exists($sqlFile)) {
            $error = "Error: Missing <b>pulse.sql</b> in /install directory.";
        } else {
            $sql = file_get_contents($sqlFile);
            if ($sql === false || trim($sql) === '') {
                $error = "Error: Could not read or empty SQL file.";
            } else {
                if (!$mysqli->multi_query($sql)) {
                    $error = "SQL import failed: " . $mysqli->error;
                } else {
                    do {
                        if ($result = $mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($mysqli->more_results() && $mysqli->next_result());

                    $mysqli->query("UPDATE settings SET value='" . $mysqli->real_escape_string($site_url) . "' WHERE name='site_url'");
                    $mysqli->query("UPDATE settings SET value='" . $mysqli->real_escape_string($site_title) . "' WHERE name='site_title'");

                    $initPath = __DIR__ . '/../core/config.php';
                    $config = "<?php
define('DB_HOST', '" . addslashes($db_host) . "');
define('DB_NAME', '" . addslashes($db_name) . "');
define('DB_USER', '" . addslashes($db_user) . "');
define('DB_PASS', '" . addslashes($db_pass) . "');
";
                    file_put_contents($initPath, $config);

                    function rrmdir($dir) {
                        foreach(glob($dir . '/*') as $file) {
                            if(is_dir($file)) rrmdir($file); else @unlink($file);
                        }
                        @rmdir($dir);
                    }
                    $installDir = __DIR__;
                    $success = "Installation complete! <a href='" . htmlspecialchars($site_url) . "'>Go to your site</a>";
                    echo "<!DOCTYPE html><html><body style='font-family:sans-serif;background:#181a20;color:#fff;text-align:center;padding:3em;'><h2>$success</h2></body></html>";
                    rrmdir($installDir);
                    exit;
                }
            }
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
        label { display: block; margin-bottom: 0.2em; font-weight: 600; }
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
        <label>Site URL (with http:// or https://)</label>
        <input name="site_url" required value="<?= htmlspecialchars($_POST['site_url'] ?? '') ?>">
        <label>Site Title</label>
        <input name="site_title" required value="<?= htmlspecialchars($_POST['site_title'] ?? 'Pulse') ?>">
        <button type="submit">Install Pulse</button>
    </form>
</div>
</body>
</html>
