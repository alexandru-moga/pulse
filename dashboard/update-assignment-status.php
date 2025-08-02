<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/classes/DiscordBot.php';
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

$validStatuses = ['accepted', 'waiting', 'rejected', 'not_sent', 'completed'];
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
    
    // Check if this project is linked to any events with YSWS
    $stmt = $db->prepare("
        SELECT DISTINCT e.id, e.discord_accepted_role_id, e.discord_pizza_role_id 
        FROM events e 
        JOIN event_ysws ey ON e.id = ey.event_id 
        JOIN projects p ON p.requirements LIKE CONCAT('%', ey.ysws_link, '%')
        WHERE p.id = ? AND (e.discord_accepted_role_id IS NOT NULL OR e.discord_pizza_role_id IS NOT NULL)
    ");
    $stmt->execute([$projectId]);
    $linkedEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle Discord role assignment for linked events
    if (!empty($linkedEvents)) {
        $discordBot = new DiscordBot($db);
        
        foreach ($linkedEvents as $event) {
            if ($status === 'accepted') {
                $pizzaGrantReceived = ($pizzaGrant === 'received');
                $discordBot->assignEventRole($userId, $event['id'], $status, $pizzaGrantReceived);
            } else {
                // Remove roles if status is not accepted
                $discordBot->removeEventRole($userId, $event['id']);
            }
        }
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
