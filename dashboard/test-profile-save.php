<?php
require_once __DIR__ . '/../core/init.php';
checkActiveOrLimitedAccess();

global $db, $currentUser, $settings;

$testResult = [];

// Test 1: Check if we have a valid user
$testResult['user_check'] = $currentUser ? "✅ User loaded: " . $currentUser->first_name : "❌ No user";

// Test 2: Check database connection
try {
    $stmt = $db->prepare("SELECT 1");
    $stmt->execute();
    $testResult['db_check'] = "✅ Database connection working";
} catch (Exception $e) {
    $testResult['db_check'] = "❌ Database error: " . $e->getMessage();
}

// Test 3: Check if this is a POST request
$testResult['request_method'] = "Request method: " . $_SERVER['REQUEST_METHOD'];

// Test 4: Check if POST data exists
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testResult['post_data'] = "POST data received: " . json_encode($_POST);
    
    // Test 5: Try a simple database update
    if ($currentUser) {
        try {
            $stmt = $db->prepare("UPDATE users SET last_profile_update = NOW() WHERE id = ?");
            $result = $stmt->execute([$currentUser->id]);
            $testResult['db_update'] = $result ? "✅ Database update successful" : "❌ Database update failed";
        } catch (Exception $e) {
            $testResult['db_update'] = "❌ Database update error: " . $e->getMessage();
        }
    } else {
        $testResult['db_update'] = "❌ No user to update";
    }
} else {
    $testResult['post_data'] = "No POST data (GET request)";
}

// Test 6: Check session
$testResult['session'] = "Session ID: " . session_id();

// Test 7: Check if columns exist
try {
    $stmt = $db->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['first_name', 'last_name', 'description', 'bio', 'school', 'phone', 'profile_image', 'profile_public'];
    $missingColumns = array_diff($requiredColumns, $columns);
    $testResult['columns'] = empty($missingColumns) ? "✅ All required columns exist" : "❌ Missing columns: " . implode(', ', $missingColumns);
} catch (Exception $e) {
    $testResult['columns'] = "❌ Column check error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($testResult, JSON_PRETTY_PRINT);
?>