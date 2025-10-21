<?php
echo "PHP is working!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "File exists: " . (file_exists(__DIR__ . '/../core/init.php') ? 'Yes' : 'No') . "<br>";

// Try to include init.php
try {
    require_once __DIR__ . '/../core/init.php';
    echo "init.php loaded successfully<br>";
    echo "Database connected: " . (isset($db) ? 'Yes' : 'No') . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
