<?php

class GoogleOAuth
{
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct($db)
    {
        $this->db = $db;
        $this->loadConfig();
        global $settings;
        $siteUrl = $settings['site_url'] ?? (getRequestScheme() . '://' . $_SERVER['HTTP_HOST']);
        $this->redirectUri = $siteUrl . '/auth/google/';
    }

    private function loadConfig()
    {
        $stmt = $this->db->prepare(
            "SELECT name, value FROM settings WHERE name IN ('google_client_id', 'google_client_secret')"
        );
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $this->clientId = $settings['google_client_id'] ?? null;
        $this->clientSecret = $settings['google_client_secret'] ?? null;
    }

    public function isConfigured()
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    public function generateAuthUrl($isLogin = false)
    {
        if (!$this->isConfigured()) {
            throw new Exception('Google OAuth is not configured');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;
        $_SESSION['google_oauth_action'] = $isLogin ? 'login' : 'link';

        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'openid email profile',
            'response_type' => 'code',
            'state' => $state,
            'access_type' => 'offline'
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function handleCallback($code, $state)
    {
        $savedState = $_SESSION['google_oauth_state'] ?? null;
        $action = $_SESSION['google_oauth_action'] ?? 'link';

        unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_action']);

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

    private function exchangeCodeForToken($code)
    {
        $postData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
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

    private function getUserData($accessToken)
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Failed to fetch user data');
        }

        return json_decode($response, true);
    }

    private function handleLogin($userData)
    {
        if (empty($userData['email'])) {
            return ['success' => false, 'error' => 'No email found in Google account'];
        }

        $stmt = $this->db->prepare(
            "SELECT u.* FROM users u INNER JOIN google_links gl ON u.id = gl.user_id WHERE gl.google_email = ? AND u.role != 'Guest'"
        );
        $stmt->execute([$userData['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'No account found linked to this Google account'];
        }

        $_SESSION['user_id'] = $user['id'];
        return ['success' => true, 'action' => 'login'];
    }

    private function handleLinking($userData)
    {
        if (!function_exists('isLoggedIn') || !isLoggedIn()) {
            return ['success' => false, 'error' => 'You must be logged in to link accounts'];
        }

        global $currentUser;

        // Additional safety check for $currentUser
        if (!$currentUser) {
            return ['success' => false, 'error' => 'User session not found'];
        }

        if (empty($userData['email'])) {
            return ['success' => false, 'error' => 'No email found in Google account'];
        }
        $stmt = $this->db->prepare(
            "SELECT user_id FROM google_links WHERE google_email = ? AND user_id != ?"
        );
        $stmt->execute([$userData['email'], $currentUser->id]);

        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'This Google account is already linked to another user'];
        }
        $stmt = $this->db->prepare(
            "INSERT INTO google_links (user_id, google_id, google_email, google_name, linked_at)
             VALUES (?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                google_email = VALUES(google_email),
                google_name = VALUES(google_name),
                linked_at = NOW()"
        );
        $stmt->execute([
            $currentUser->id,
            $userData['id'],
            $userData['email'],
            $userData['name'] ?? null
        ]);

        return ['success' => true, 'action' => 'link'];
    }

    public function getUserGoogleLink($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM google_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function unlinkGoogleAccount($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM google_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        return true;
    }
}
