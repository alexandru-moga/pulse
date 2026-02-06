<?php

class DiscordBot {
    private $db;
    private $botToken;
    private $guildId;
    private $clientId;
    private $clientSecret;
    private $settings;
    
    public function __construct($db) {
        $this->db = $db;
        $this->loadSettings();
    }
    
    private function loadSettings() {
        $stmt = $this->db->prepare("SELECT name, value FROM settings WHERE name LIKE 'discord_%'");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $this->botToken = $settings['discord_bot_token'] ?? '';
        $this->guildId = $settings['discord_guild_id'] ?? '';
        $this->clientId = $settings['discord_client_id'] ?? '';
        $this->clientSecret = $settings['discord_client_secret'] ?? '';
        $this->settings = $settings;
    }
    
    public function handleSlashCommand($interaction) {
        // Handle Discord slash commands
        // This is a placeholder - implement based on your bot's commands
        return [
            'type' => 4,
            'data' => ['content' => 'Command received and processed!']
        ];
    }
    
    public function getRoleSettings($type, $id) {
        if ($type === 'event') {
            $stmt = $this->db->prepare("SELECT discord_participated_role_id FROM events WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            return $result && !empty($result['discord_participated_role_id']);
        } elseif ($type === 'project') {
            $stmt = $this->db->prepare("SELECT discord_accepted_role_id, discord_pizza_role_id FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            return $result && (!empty($result['discord_accepted_role_id']) || !empty($result['discord_pizza_role_id']));
        }
        return false;
    }
    
    public function assignRole($userId, $roleId) {
        if (empty($this->botToken) || empty($this->guildId) || empty($roleId)) {
            return false;
        }
        
        // Get Discord ID from user
        $stmt = $this->db->prepare("SELECT discord_id FROM discord_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        $discordLink = $stmt->fetch();
        
        if (!$discordLink) {
            return false;
        }
        
        $discordUserId = $discordLink['discord_id'];
        
        // Make API call to assign role
        $ch = curl_init("https://discord.com/api/guilds/{$this->guildId}/members/$discordUserId/roles/$roleId");
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bot {$this->botToken}",
                'Content-Length: 0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 204; // 204 No Content means success for Discord API
    }
    
    public function removeRole($userId, $roleId) {
        if (empty($this->botToken) || empty($this->guildId) || empty($roleId)) {
            return false;
        }
        
        // Get Discord ID from user
        $stmt = $this->db->prepare("SELECT discord_id FROM discord_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        $discordLink = $stmt->fetch();
        
        if (!$discordLink) {
            return false;
        }
        
        $discordUserId = $discordLink['discord_id'];
        
        // Make API call to remove role
        $ch = curl_init("https://discord.com/api/guilds/{$this->guildId}/members/$discordUserId/roles/$roleId");
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bot {$this->botToken}"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 204; // 204 No Content means success for Discord API
    }
    
    public function testConnection($guildId = null, $botToken = null) {
        $token = $botToken ?: $this->botToken;
        $guild = $guildId ?: $this->guildId;
        
        if (empty($token) || empty($guild)) {
            return ['success' => false, 'error' => 'Bot token or guild ID not provided'];
        }
        
        // Test API connection by getting guild info
        $ch = curl_init("https://discord.com/api/guilds/$guild");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bot $token"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $guildData = json_decode($response, true);
            return [
                'success' => true,
                'server_name' => $guildData['name'] ?? 'Unknown Server'
            ];
        } else {
            $error = 'Failed to connect to Discord API';
            if ($httpCode === 401) {
                $error = 'Invalid bot token';
            } elseif ($httpCode === 403) {
                $error = 'Bot lacks permissions or is not in the server';
            } elseif ($httpCode === 404) {
                $error = 'Invalid guild ID or bot not in server';
            }
            
            return ['success' => false, 'error' => $error];
        }
    }
    
    public function syncUserRole($userId) {
        // Get user role from database
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ? AND role != 'Guest'");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        // Get appropriate Discord role ID based on user role
        $roleId = null;
        switch ($user['role']) {
            case 'Leader':
                $roleId = $this->settings['discord_leader_role_id'] ?? null;
                break;
            case 'Co-leader':
                $roleId = $this->settings['discord_co_leader_role_id'] ?? null;
                break;
            default:
                $roleId = $this->settings['discord_member_role_id'] ?? null;
                break;
        }
        
        if ($roleId) {
            return $this->assignRole($userId, $roleId);
        }
        
        return false;
    }
    
    public function sendMessage($channelId, $message) {
        if (empty($this->botToken) || empty($channelId)) {
            return false;
        }
        
        $data = json_encode(['content' => $message]);
        
        $ch = curl_init("https://discord.com/api/channels/$channelId/messages");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bot {$this->botToken}",
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    public function getGuilds() {
        if (empty($this->botToken)) {
            return [];
        }
        
        $ch = curl_init("https://discord.com/api/users/@me/guilds");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bot {$this->botToken}"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true) ?: [];
        }
        
        return [];
    }
    
    // Legacy methods for compatibility
    public function setToken($token) {
        $this->botToken = $token;
    }
    
    public function setClientId($clientId) {
        $this->clientId = $clientId;
    }
    
    public function setClientSecret($clientSecret) {
        $this->clientSecret = $clientSecret;
    }
    
    public function getToken() {
        return $this->botToken;
    }
    
    public function getClientId() {
        return $this->clientId;
    }
    
    public function getClientSecret() {
        return $this->clientSecret;
    }
}
?>