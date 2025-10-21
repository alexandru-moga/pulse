<?php
require_once '../core/init.php';

// Only allow in development/testing
// Remove this file in production

header('Content-Type: text/plain');

echo "=== Session Debug Info ===\n\n";

echo "Logged in: " . (isLoggedIn() ? "YES" : "NO") . "\n";
if (isLoggedIn()) {
    echo "User ID: " . ($_SESSION['user_id'] ?? 'not set') . "\n";
}

echo "\n=== Session Variables ===\n";
foreach ($_SESSION as $key => $value) {
    if (is_string($value) || is_numeric($value)) {
        echo "$key = $value\n";
    } else {
        echo "$key = " . print_r($value, true) . "\n";
    }
}

echo "\n=== GET Parameters ===\n";
foreach ($_GET as $key => $value) {
    echo "$key = $value\n";
}

echo "\n=== Cookies ===\n";
foreach ($_COOKIE as $key => $value) {
    if ($key !== 'PHPSESSID') {
        echo "$key = $value\n";
    } else {
        echo "$key = [HIDDEN]\n";
    }
}
?>
