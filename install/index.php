<?php

define('IN_NEIGHBORHOOD_CMS', true);
require_once __DIR__ . '/../core/init.php';

echo 'Including: ' . __DIR__ . '/../core/init.php' . PHP_EOL;
require_once __DIR__ . '/../core/init.php';
echo 'Class User exists: ' . (class_exists('User') ? 'YES' : 'NO') . PHP_EOL;
exit;

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

$configTemplate = '../core/config.php.template';
$configFile = '../core/config.php';

$schemaFile = 'schema.sql';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1: 
            $dbHost = $_POST['db_host'] ?? '';
            $dbName = $_POST['db_name'] ?? '';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';
            
            try {
                $conn = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
                $conn->exec("USE `$dbName`");
                
                $schema = file_get_contents(__DIR__ . '/schema.sql');
                $conn->exec($schema);
                
                $config = file_get_contents($configTemplate);
                $config = str_replace(
                    ['{{DB_HOST}}', '{{DB_NAME}}', '{{DB_USER}}', '{{DB_PASS}}'],
                    [$dbHost, $dbName, $dbUser, $dbPass],
                    $config
                );
                file_put_contents($configFile, $config);
                
                $success = "Database configuration successful!";
                header("Location: index.php?step=2");
                exit;
            } catch (PDOException $e) {
                $error = "Database connection failed: " . $e->getMessage();
            }
            break;
            
        case 2: 
            require_once '../core/init.php';
            
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($email) || empty($password)) {
                $error = "All fields are required";
            } else {
                try {
                    User::create($username, $email, $password, 'admin');
                    $success = "Admin account created successfully!";
                    header("Location: index.php?step=3");
                    exit;
                } catch (Exception $e) {
                    $error = "Failed to create admin account: " . $e->getMessage();
                }
            }
            break;
            
        case 3: 
            require_once '../core/init.php';
            
            $siteTitle = $_POST['site_title'] ?? '';
            $siteUrl = $_POST['site_url'] ?? '';
            $adminEmail = $_POST['admin_email'] ?? '';
            
            $config = file_get_contents($configFile);
            $config = str_replace(
                ["define('SITE_TITLE', 'Neighborhood HQ')", "define('SITE_URL', 'http://127.0.0.1:5500/neighborhood-cms/public')", "define('ADMIN_EMAIL', 'admin@example.com')"],
                ["define('SITE_TITLE', '$siteTitle')", "define('SITE_URL', '$siteUrl')", "define('ADMIN_EMAIL', '$adminEmail')"],
                $config
            );
            file_put_contents($configFile, $config);
            
            $success = "Site configuration saved successfully!";
            header("Location: index.php?step=4");
            exit;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Neighborhood CMS</title>
    <style>
        body {
            font-family: 'Phantom Sans', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #ec3750;
        }
        .step {
            background: #f7f7f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        button {
            background: #ff8c37;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .step-number {
            display: inline-block;
            background: #ec3750;
            color: white;
            width: 30px;
            height: 30px;
            text-align: center;
            line-height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Neighborhood CMS Installation</h1>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($step === 1): ?>
        <div class="step">
            <h2><span class="step-number">1</span> Database Configuration</h2>
            <form method="post" action="index.php?step=1">
                <label for="db_host">Database Host:</label>
                <input type="text" id="db_host" name="db_host" value="localhost" required>
                
                <label for="db_name">Database Name:</label>
                <input type="text" id="db_name" name="db_name" value="neighborhood" required>
                
                <label for="db_user">Database Username:</label>
                <input type="text" id="db_user" name="db_user" value="root" required>
                
                <label for="db_pass">Database Password:</label>
                <input type="password" id="db_pass" name="db_pass">
                
                <button type="submit">Continue</button>
            </form>
        </div>
    <?php elseif ($step === 2): ?>
        <div class="step">
            <h2><span class="step-number">2</span> Admin Account Setup</h2>
            <form method="post" action="index.php?step=2">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit">Continue</button>
            </form>
        </div>
    <?php elseif ($step === 3): ?>
        <div class="step">
            <h2><span class="step-number">3</span> Site Configuration</h2>
            <form method="post" action="index.php?step=3">
                <label for="site_title">Site Title:</label>
                <input type="text" id="site_title" name="site_title" value="Neighborhood HQ" required>
                
                <label for="site_url">Site URL:</label>
                <input type="text" id="site_url" name="site_url" value="http://127.0.0.1:5500/neighborhood-cms/public" required>
                
                <label for="admin_email">Admin Email:</label>
                <input type="email" id="admin_email" name="admin_email" value="admin@example.com" required>
                
                <button type="submit">Continue</button>
            </form>
        </div>
    <?php elseif ($step === 4): ?>
        <div class="step">
            <h2><span class="step-number">4</span> Installation Complete</h2>
            <p>Congratulations! Neighborhood CMS has been successfully installed.</p>
            <p>You can now <a href="../public/index.php">visit your site</a> or <a href="../public/admin/login.php">login to the admin panel</a>.</p>
        </div>
    <?php endif; ?>
</body>
</html>