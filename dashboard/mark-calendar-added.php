<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['event_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Event ID required']);
        exit;
    }

    $event_id = (int)$input['event_id'];

    if (!$currentUser) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }

    $user_id = $currentUser->id;

    try {
        // Check if user has already applied for this event
        $stmt = $db->prepare("SELECT id FROM event_applications WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$event_id, $user_id]);
        $application = $stmt->fetch();

        if ($application) {
            // Update existing application to mark calendar as added
            $stmt = $db->prepare("UPDATE event_applications SET calendar_added = 1 WHERE event_id = ? AND user_id = ?");
            $stmt->execute([$event_id, $user_id]);
        } else {
            // Create new application entry with calendar marked as added
            $stmt = $db->prepare("INSERT INTO event_applications (event_id, user_id, status, calendar_added) VALUES (?, ?, 'applied', 1)");
            $stmt->execute([$event_id, $user_id]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
