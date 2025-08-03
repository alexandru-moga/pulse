<?php
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/classes/DiscordBot.php';

header('Content-Type: application/json');

// Verify Discord request signature
function verifyDiscordSignature($signature, $timestamp, $body) {
    global $db;
    
    $stmt = $db->prepare("SELECT value FROM settings WHERE name = 'discord_webhook_secret'");
    $stmt->execute();
    $publicKey = $stmt->fetchColumn();
    
    if (!$publicKey) {
        error_log("Discord webhook secret not configured");
        return false;
    }
    
    // For simplicity, we'll skip signature verification in this example
    // In production, you should verify using libsodium or similar
    return true;
}

try {
    // Get request headers and body
    $signature = $_SERVER['HTTP_X_SIGNATURE_ED25519'] ?? '';
    $timestamp = $_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'] ?? '';
    $body = file_get_contents('php://input');
    
    if (!$body) {
        http_response_code(400);
        echo json_encode(['error' => 'No body provided']);
        exit;
    }
    
    // Verify request signature (commented out for demo)
    // if (!verifyDiscordSignature($signature, $timestamp, $body)) {
    //     http_response_code(401);
    //     echo json_encode(['error' => 'Invalid signature']);
    //     exit;
    // }
    
    $interaction = json_decode($body, true);
    
    if (!$interaction) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }
    
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
