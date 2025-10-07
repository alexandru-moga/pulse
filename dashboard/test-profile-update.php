<?php
require_once __DIR__ . '/../core/init.php';
checkActiveOrLimitedAccess();

global $db, $currentUser;

// Log everything for debugging
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'user_id' => $currentUser ? $currentUser->id : 'No user',
    'post_data' => $_POST,
    'files_data' => $_FILES,
    'session_id' => session_id()
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logData['processing'] = 'Starting form processing';
    
    try {
        // Try the exact same logic as profile-edit.php
        $newFirst = trim($_POST['first_name'] ?? '');
        $newLast = trim($_POST['last_name'] ?? '');
        $newDesc = trim($_POST['description'] ?? '');
        $newSchool = trim($_POST['school'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');

        $logData['extracted_data'] = [
            'first_name' => $newFirst,
            'last_name' => $newLast,
            'description' => $newDesc,
            'school' => $newSchool,
            'phone' => $newPhone
        ];

        $updateErrors = [];
        if ($newFirst === '') $updateErrors[] = "First name cannot be empty.";
        if ($newLast === '') $updateErrors[] = "Last name cannot be empty.";

        $logData['validation_errors'] = $updateErrors;

        if (empty($updateErrors) && $currentUser) {
            $logData['attempting_update'] = true;
            
            $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, description = ?, school = ?, phone = ? WHERE id = ?");
            $result = $stmt->execute([$newFirst, $newLast, $newDesc, $newSchool, $newPhone, $currentUser->id]);
            
            $logData['update_result'] = $result;
            $logData['affected_rows'] = $stmt->rowCount();
            
            if ($result) {
                $logData['status'] = 'SUCCESS';
            } else {
                $logData['status'] = 'FAILED';
                $logData['error_info'] = $stmt->errorInfo();
            }
        } else {
            $logData['status'] = 'VALIDATION_FAILED';
        }
        
    } catch (Exception $e) {
        $logData['status'] = 'EXCEPTION';
        $logData['exception'] = $e->getMessage();
    }
} else {
    $logData['status'] = 'GET_REQUEST';
}

// Return results
header('Content-Type: application/json');
echo json_encode($logData, JSON_PRETTY_PRINT);
?>