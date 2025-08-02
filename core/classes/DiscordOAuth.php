<?php

class DiscordOAuth {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $botToken;
    private $guildId;
    
    public function __construct($db) {
        $this->db = $db;
        $this->loadSettings();
    }
    
    private function loadSettings() {
        $settings = $this->db->query("SELECT name, value FROM settings WHERE name LIKE 'discord_%'")->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $this->clientId = $settings['discord_client_id'] ?? '';
        $this->clientSecret = $settings['discord_client_secret'] ?? '';
        $this->redirectUri = $settings['discord_redirect_uri'] ?? '';
        $this->botToken = $settings['discord_bot_token'] ?? '';
        $this->guildId = $settings['discord_guild_id'] ?? '';
    }
    
    public function isConfigured() {
        return !empty($this->clientId) && !empty($this->clientSecret) && !empty($this->redirectUri);
    }
    
    public function generateAuthUrl($isLogin = false) {
        if (!$this->isConfigured()) {
            throw new Exception('Discord OAuth not configured');
        }
        
        $state = bin2hex(random_bytes(16));
        $csrf = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes
        
        error_log("Discord OAuth: Generating auth URL with state: $state, expires: $expiresAt");
        
        $stmt = $this->db->prepare("INSERT INTO discord_login_sessions (state_token, csrf_token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$state, $csrf, $expiresAt]);
        
        $_SESSION['discord_csrf_token'] = $csrf;
        $_SESSION['discord_is_login'] = $isLogin;
        
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'identify email guilds.join',
            'state' => $state
        ];
        
        return 'https://discord.com/api/oauth2/authorize?' . http_build_query($params);
    }
    
    public function handleCallback($code, $state) {
        if (!$this->isConfigured()) {
            throw new Exception('Discord OAuth not configured');
        }
        
        error_log("Discord OAuth: Handling callback with state: $state");
        
        $stmt = $this->db->prepare("SELECT csrf_token, used FROM discord_login_sessions WHERE state_token = ? AND expires_at > NOW()");
        $stmt->execute([$state]);
        $session = $stmt->fetch();
        
        error_log("Discord OAuth: Session data: " . json_encode($session));
        error_log("Discord OAuth: Expected CSRF: " . ($_SESSION['discord_csrf_token'] ?? 'none'));
        
        if (!$session || $session['used'] == 1 || $session['csrf_token'] !== ($_SESSION['discord_csrf_token'] ?? '')) {
            error_log("Discord OAuth: State validation failed");
            throw new Exception('Invalid or expired state token');
        }
        
        $this->db->prepare("UPDATE discord_login_sessions SET used = 1 WHERE state_token = ?")->execute([$state]);
        
        $tokenData = $this->exchangeCodeForToken($code);
        $discordUser = $this->getDiscordUser($tokenData['access_token']);
        
        $isLogin = $_SESSION['discord_is_login'] ?? false;
        unset($_SESSION['discord_csrf_token'], $_SESSION['discord_is_login']);
        
        if ($isLogin) {
            return $this->handleDiscordLogin($discordUser, $tokenData);
        } else {
            return $this->linkDiscordAccount($discordUser, $tokenData);
        }
    }
    
    private function exchangeCodeForToken($code) {
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri
        ];
        
        $ch = curl_init('https://discord.com/api/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to exchange code for token');
        }
        
        return json_decode($response, true);
    }
    
    private function getDiscordUser($accessToken) {
        $ch = curl_init('https://discord.com/api/users/@me');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $accessToken"]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get Discord user info');
        }
        
        return json_decode($response, true);
    }
    
    private function handleDiscordLogin($discordUser, $tokenData) {
        $stmt = $this->db->prepare("SELECT u.* FROM users u JOIN discord_links udl ON u.id = udl.user_id WHERE udl.discord_id = ?");
        $stmt->execute([$discordUser['id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $this->updateDiscordLink($user['id'], $discordUser, $tokenData);
            $_SESSION['user_id'] = $user['id'];
            return ['success' => true, 'user' => $user, 'action' => 'login'];
        } else {
            return ['success' => false, 'error' => 'No account linked to this Discord user', 'discord_user' => $discordUser];
        }
    }
    
    private function linkDiscordAccount($discordUser, $tokenData) {
        if (!isLoggedIn()) {
            throw new Exception('Must be logged in to link Discord account');
        }
        
        global $currentUser;
        $stmt = $this->db->prepare("SELECT user_id FROM discord_links WHERE discord_id = ?");
        $stmt->execute([$discordUser['id']]);
        $existingLink = $stmt->fetch();
        
        if ($existingLink && $existingLink['user_id'] != $currentUser->id) {
            throw new Exception('This Discord account is already linked to another user');
        }
        $this->db->prepare("DELETE FROM discord_links WHERE user_id = ?")->execute([$currentUser->id]);
        $stmt = $this->db->prepare("
            INSERT INTO discord_links 
            (user_id, discord_id, discord_username, discord_discriminator, discord_avatar, access_token, refresh_token, expires_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
        $stmt->execute([
            $currentUser->id,
            $discordUser['id'],
            $discordUser['username'],
            $discordUser['discriminator'] ?? '0000',
            $discordUser['avatar'],
            $tokenData['access_token'],
            $tokenData['refresh_token'] ?? null,
            $expiresAt
        ]);
        $this->addUserToGuild($discordUser['id'], $tokenData['access_token']);
        
        return ['success' => true, 'user' => $discordUser, 'action' => 'link'];
    }
    
    private function updateDiscordLink($userId, $discordUser, $tokenData) {
        $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
        
        $stmt = $this->db->prepare("
            UPDATE discord_links 
            SET discord_username = ?, discord_discriminator = ?, discord_avatar = ?, 
                access_token = ?, refresh_token = ?, expires_at = ?
            WHERE user_id = ?
        ");
        
        $stmt->execute([
            $discordUser['username'],
            $discordUser['discriminator'] ?? '0000',
            $discordUser['avatar'],
            $tokenData['access_token'],
            $tokenData['refresh_token'] ?? null,
            $expiresAt,
            $userId
        ]);
    }
    
    private function addUserToGuild($discordUserId, $accessToken) {
        if (empty($this->botToken) || empty($this->guildId)) {
            return;
        }
        
        $data = json_encode(['access_token' => $accessToken]);
        
        $ch = curl_init("https://discord.com/api/guilds/{$this->guildId}/members/$discordUserId");
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bot {$this->botToken}",
                'Content-Type: application/json'
            ]
        ]);
        
        curl_exec($ch);
        curl_close($ch);
    }
    
    public function getUserDiscordLink($userId) {
        $stmt = $this->db->prepare("SELECT * FROM discord_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function unlinkDiscordAccount($userId) {
        $this->db->prepare("DELETE FROM discord_links WHERE user_id = ?")->execute([$userId]);
    }
}
