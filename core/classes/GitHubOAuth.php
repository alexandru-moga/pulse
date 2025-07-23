<?php

class GitHubOAuth {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    
    public function __construct($db) {
        $this->db = $db;
        $this->loadConfig();
        global $settings;
        $siteUrl = $settings['site_url'] ?? (getRequestScheme() . '://' . $_SERVER['HTTP_HOST']);
        $this->redirectUri = $siteUrl . '/auth/github/';
    }
    
    private function loadConfig() {
        $stmt = $this->db->prepare("SELECT name, value FROM settings WHERE name IN ('github_client_id', 'github_client_secret')");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $this->clientId = $settings['github_client_id'] ?? null;
        $this->clientSecret = $settings['github_client_secret'] ?? null;
    }
    
    public function isConfigured() {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }
    
    public function generateAuthUrl($isLogin = false) {
        if (!$this->isConfigured()) {
            throw new Exception('GitHub OAuth is not configured');
        }
        
        $state = bin2hex(random_bytes(16));
        $_SESSION['github_oauth_state'] = $state;
        $_SESSION['github_oauth_action'] = $isLogin ? 'login' : 'link';
        
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'user:email',
            'state' => $state,
            'allow_signup' => 'false'
        ];
        
        return 'https://github.com/login/oauth/authorize?' . http_build_query($params);
    }
    
    public function handleCallback($code, $state) {
        $savedState = $_SESSION['github_oauth_state'] ?? null;
        $action = $_SESSION['github_oauth_action'] ?? 'link';
        
        unset($_SESSION['github_oauth_state'], $_SESSION['github_oauth_action']);
        
        if ($state !== $savedState) {
            return ['success' => false, 'error' => 'Invalid state parameter'];
        }
        
        try {
            $tokenData = $this->exchangeCodeForToken($code);
            $userData = $this->getUserData($tokenData['access_token']);
            
            if ($action === 'login') {
                return $this->handleLogin($userData);
            } else {
                return $this->handleLinking($userData);
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function exchangeCodeForToken($code) {
        $postData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri
        ];
        
        $ch = curl_init('https://github.com/login/oauth/access_token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: Pulse-App'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to exchange code for token');
        }
        
        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            throw new Exception('No access token received');
        }
        
        return $data;
    }
    
    private function getUserData($accessToken) {
        $ch = curl_init('https://api.github.com/user');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'User-Agent: Pulse-App',
                'Accept: application/vnd.github.v3+json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to fetch user data');
        }
        
        $userData = json_decode($response, true);
        $ch = curl_init('https://api.github.com/user/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'User-Agent: Pulse-App',
                'Accept: application/vnd.github.v3+json'
            ]
        ]);
        
        $emailResponse = curl_exec($ch);
        curl_close($ch);
        
        $emails = json_decode($emailResponse, true);
        $primaryEmail = null;
        
        if (is_array($emails)) {
            foreach ($emails as $email) {
                if ($email['primary'] && $email['verified']) {
                    $primaryEmail = $email['email'];
                    break;
                }
            }
        }
        
        $userData['email'] = $primaryEmail;
        return $userData;
    }
    
    private function handleLogin($userData) {
        if (empty($userData['login'])) {
            return ['success' => false, 'error' => 'No GitHub username found'];
        }
        
        $stmt = $this->db->prepare("SELECT u.* FROM users u JOIN github_links gl ON u.id = gl.user_id WHERE gl.github_username = ? AND u.active_member = 1");
        $stmt->execute([$userData['login']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'error' => 'No account found with this GitHub username'];
        }
        
        $_SESSION['user_id'] = $user['id'];
        return ['success' => true, 'action' => 'login'];
    }
    
    public function getUserGitHubLink($userId) {
        $stmt = $this->db->prepare("SELECT * FROM github_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function unlinkGitHubAccount($userId) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM github_links WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    private function handleLinking($userData) {
        if (!isLoggedIn()) {
            return ['success' => false, 'error' => 'You must be logged in to link accounts'];
        }
        
        global $currentUser;
        
        if (empty($userData['login'])) {
            return ['success' => false, 'error' => 'No GitHub username found'];
        }
        $stmt = $this->db->prepare("SELECT user_id FROM github_links WHERE github_username = ? AND user_id != ?");
        $stmt->execute([$userData['login'], $currentUser->id]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'This GitHub account is already linked to another user'];
        }
        
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO github_links (user_id, github_id, github_username, github_email, linked_at) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE github_username = VALUES(github_username), github_email = VALUES(github_email), linked_at = NOW()");
            $stmt->execute([
                $currentUser->id,
                $userData['id'],
                $userData['login'],
                $userData['email'] ?? null
            ]);
            
            $this->db->commit();
            return ['success' => true, 'action' => 'link'];
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
