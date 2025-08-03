<?php
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/classes/DiscordBot.php';

header('Content-Type: application/json');

// Get raw POST data
$rawBody = file_get_contents('php://input');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if body is provided
if (empty($rawBody)) {
    http_response_code(400);
    echo json_encode(['error' => 'No body provided']);
    exit;
}

// Verify Discord signature (required for production)
$signature = $_SERVER['HTTP_X_SIGNATURE_ED25519'] ?? '';
$timestamp = $_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'] ?? '';

// Parse JSON body
$body = json_decode($rawBody, true);
if (!$body) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Handle Discord verification ping
if ($body['type'] === 1) {
    echo json_encode(['type' => 1]);
    exit;
}

try {
    $interaction = $body;
    
    $discordBot = new DiscordBot($db);
    
    // Handle different interaction types
    switch ($interaction['type']) {
        case 1: // PING
            echo json_encode(['type' => 1]);
            break;
            
        case 2: // APPLICATION_COMMAND (slash command)
            $result = $discordBot->handleSlashCommand($interaction);
            if ($result) {
                echo json_encode($result);
            } else {
                echo json_encode([
                    'type' => 4,
                    'data' => ['content' => 'Command executed successfully!']
                ]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown interaction type']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Discord interaction error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
    
} catch (Exception $e) {
    error_log("Discord interaction error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
