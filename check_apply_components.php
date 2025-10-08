<?php
require 'core/init.php';

echo "<h2>Apply Page Components</h2>";
$stmt = $db->query('SELECT * FROM page_apply WHERE is_active = 1 ORDER BY position ASC');
$components = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Position</th><th>Component Type</th><th>Settings Preview</th></tr>";
foreach ($components as $comp) {
    $settingsPreview = substr($comp['settings'], 0, 100);
    echo "<tr>";
    echo "<td>" . $comp['position'] . "</td>";
    echo "<td><strong>" . $comp['component_type'] . "</strong></td>";
    echo "<td>" . htmlspecialchars($settingsPreview) . "...</td>";
    echo "</tr>";
}
echo "</table>";
?>
