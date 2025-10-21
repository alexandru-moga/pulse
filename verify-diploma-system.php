<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Database Verification</h2>";

// Check diploma_templates table
echo "<h3>Diploma Templates Table</h3>";
try {
    $stmt = $pdo->query("DESCRIBE diploma_templates");
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
    
    $count = $pdo->query("SELECT COUNT(*) FROM diploma_templates")->fetchColumn();
    echo "<p>Total diploma templates: $count</p>";
    
    $templates = $pdo->query("SELECT * FROM diploma_templates")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h4>Diploma Templates:</h4>";
    echo "<pre>";
    print_r($templates);
    echo "</pre>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check certificate_downloads table structure
echo "<h3>Certificate Downloads Table</h3>";
try {
    $stmt = $pdo->query("DESCRIBE certificate_downloads");
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Test CertificateGenerator
echo "<h3>CertificateGenerator Test</h3>";
try {
    require_once __DIR__ . '/core/classes/CertificateGenerator.php';
    
    // Test with user ID 1
    $generator = new CertificateGenerator($pdo);
    $diplomas = $generator->getAvailableDiplomas(1);
    
    echo "<h4>Available diplomas for user 1:</h4>";
    echo "<pre>";
    print_r($diplomas);
    echo "</pre>";
    
    // Test eligibility for event 9 (Daydream Timisoara) with various users
    echo "<h4>Event eligibility test (Event 9 - Daydream Timisoara):</h4>";
    $testUsers = [934, 935, 936, 1]; // Test with some users
    foreach ($testUsers as $userId) {
        $eligible = $generator->isEligibleForEventDiploma($userId, 9);
        echo "<p>User $userId eligible: " . ($eligible ? 'YES' : 'NO') . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>✓ Diploma system setup complete!</h3>";
?>
