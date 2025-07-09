<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$userId = $_POST['user_id'] ?? null;
$projectId = $_POST['project_id'] ?? null;
$status = $_POST['status'] ?? null;
$pizzaGrant = $_POST['pizza_grant'] ?? 'none';

if (!$userId || !$projectId || !$status) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$validStatuses = ['accepted', 'waiting', 'rejected', 'not_participating', 'not_sent', 'completed'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

$validPizzaGrants = ['none', 'received'];
if (!in_array($pizzaGrant, $validPizzaGrants)) {
    echo json_encode(['success' => false, 'error' => 'Invalid pizza grant value']);
    exit;
}

try {
    global $db;
    
    $stmt = $db->prepare("SELECT id FROM project_assignments WHERE user_id = ? AND project_id = ?");
    $stmt->execute([$userId, $projectId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $db->prepare("UPDATE project_assignments SET status = ?, pizza_grant = ? WHERE user_id = ? AND project_id = ?");
        $stmt->execute([$status, $pizzaGrant, $userId, $projectId]);
    } else {
        $stmt = $db->prepare("INSERT INTO project_assignments (user_id, project_id, status, pizza_grant) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $projectId, $status, $pizzaGrant]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
