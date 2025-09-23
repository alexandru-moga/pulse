<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/classes/DiscordBot.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

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

// Validate that user exists and is active
$stmt = $db->prepare("SELECT id, first_name, last_name FROM users WHERE id = ? AND active_member = 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found or inactive']);
    exit;
}

// Validate that project exists
$stmt = $db->prepare("SELECT id, title FROM projects WHERE id = ?");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    echo json_encode(['success' => false, 'error' => 'Project not found']);
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

    // Update or insert assignment
    $stmt = $db->prepare("
        INSERT INTO project_assignments (user_id, project_id, status, pizza_grant) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        status = VALUES(status), 
        pizza_grant = VALUES(pizza_grant), 
        updated_at = CURRENT_TIMESTAMP
    ");

    $result = $stmt->execute([$userId, $projectId, $status, $pizzaGrant]);

    if ($result) {
        // Check if user has Discord linked for role assignment
        $stmt = $db->prepare("SELECT discord_id FROM discord_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        $discordLink = $stmt->fetch();

        $response = [
            'success' => true,
            'message' => 'Assignment updated successfully',
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'project_name' => $project['title'],
            'discord_linked' => $discordLink ? true : false
        ];

        if (!$discordLink && $status === 'accepted') {
            $response['warning'] = 'User does not have Discord linked - cannot assign Discord roles';
        }

        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update assignment']);
    }
} catch (Exception $e) {
    error_log("Assignment update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
