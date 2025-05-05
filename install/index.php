<?php
session_start();

// Define paths
$configFile = __DIR__ . '/../core/config.php';
$configTemplate = __DIR__ . '/../core/config.php.template';
$schemaFile = __DIR__ . '/schema.sql';

// Handle step progression
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($step) {
            case 1: // Database setup
                $dbHost = $_POST['db_host'] ?? '';
                $dbName = $_POST['db_name'] ?? '';
                $dbUser = $_POST['db_user'] ?? '';
                $dbPass = $_POST['db_pass'] ?? '';

                // Test and create database
                $conn = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->exec("DROP DATABASE IF EXISTS `$dbName`");
                $conn->exec("CREATE DATABASE `$dbName`");
                $conn->exec("USE `$dbName`");
                
                // Import schema
                $schema = file_get_contents($schemaFile);
                $conn->exec($schema);

                // Create/overwrite config
                $configContent = str_replace(
                    ['{{DB_HOST}}', '{{DB_NAME}}', '{{DB_USER}}', '{{DB_PASS}}'],
                    [$dbHost, $dbName, $dbUser, $dbPass],
                    file_get_contents($configTemplate)
                );
                file_put_contents($configFile, $configContent);

                header('Location: ?step=2');
                exit;

            case 2: // Admin account
                require_once __DIR__ . '/../core/init.php';
                
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';

                if (empty($username) || empty($email) || empty($password)) {
                    throw new Exception('All fields are required');
                }

                // Create admin user
                User::create($username, $email, $password, 'admin');
                header('Location: ?step=3');
                exit;

            case 3: // Site configuration
                require_once __DIR__ . '/../core/init.php';
                
                $siteTitle = $_POST['site_title'] ?? '';
                $siteUrl = $_POST['site_url'] ?? '';
                $adminEmail = $_POST['admin_email'] ?? '';

                // Update config
                $configContent = file_get_contents($configFile);
                $configContent = preg_replace([
                    "/define\('SITE_TITLE', '.*?'\)/",
                    "/define\('SITE_URL', '.*?'\)/",
                    "/define\('ADMIN_EMAIL', '.*?'\)/"
                ], [
                    "define('SITE_TITLE', '$siteTitle')",
                    "define('SITE_URL', '$siteUrl')",
                    "define('ADMIN_EMAIL', '$adminEmail')"
                ], $configContent);
                
                file_put_contents($configFile, $configContent);
                header('Location: ?step=complete');
                exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> 
    <title>Install Club Website</title>
</head>
<body>
    <div class="card">
        <h1>Install Club Website</h1>
        
        <?php if ($error): ?>
            <div class="error">Error: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Database Host:</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Database Name:</label>
                    <input type="text" name="db_name" value="" required>
                </div>
                <div class="form-group">
                    <label>Database User:</label>
                    <input type="text" name="db_user" value="" required>
                </div>
                <div class="form-group">
                    <label>Database Password:</label>
                    <input type="password" name="db_pass">
                </div>
                <button type="submit">Continue</button>
            </form>

        <?php elseif ($step === 2): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Admin Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Admin Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Admin Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">Create Admin Account</button>
            </form>

        <?php elseif ($step === 3): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Site Title:</label>
                    <input type="text" name="site_title" value="Hack Club" required>
                </div>
                <div class="form-group">
                    <label>Site URL:</label>
                    <input type="url" name="site_url" value="" required>
                </div>
                <div class="form-group">
                    <label>Admin Email:</label>
                    <input type="email" name="admin_email" value="" required>
                </div>
                <button type="submit">Complete Installation</button>
            </form>

        <?php elseif ($step === 'complete'): ?>
            <div class="success">
                <h2>Installation Complete! 🎉</h2>
                <p>Your Club Website is ready to use.</p>
                <p>
                    <a href="../public/">Visit Site</a> | 
                    <a href="../public/admin/">Admin Dashboard</a>
                </p>
                <p><strong>For security, please delete the install directory:</strong></p>
                <code>rm -rf install/</code>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
