<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/core/classes/HackClubOAuth.php';

header('Content-Type: text/plain');

echo "=== Hack Club OAuth Configuration Test ===\n\n";

$hackclub = new HackClubOAuth($db);

echo "Is Configured: " . ($hackclub->isConfigured() ? 'YES' : 'NO') . "\n\n";

// Check settings
$stmt = $db->query("SELECT name, value FROM settings WHERE name LIKE 'hackclub_%'");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

echo "Settings:\n";
foreach ($settings as $name => $value) {
    if (strpos($name, 'secret') !== false) {
        echo "  $name: " . (empty($value) ? 'EMPTY' : substr($value, 0, 10) . '...') . "\n";
    } else {
        echo "  $name: " . ($value ?: 'EMPTY') . "\n";
    }
}

echo "\nSession Info:\n";
echo "  user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "  hackclub_is_login: " . ($_SESSION['hackclub_is_login'] ?? 'NOT SET') . "\n";

echo "\nTest Auth URL Generation:\n";
try {
    $authUrl = $hackclub->generateAuthUrl(false);
    echo "  URL: " . substr($authUrl, 0, 100) . "...\n";
    echo "  SUCCESS!\n";
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}
