<?php

class SlackOAuth
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
        $this->redirectUri = $siteUrl . '/auth/slack/';
    }

    private function loadConfig()
    {
        $stmt = $this->db->prepare("SELECT name, value FROM settings WHERE name IN ('slack_client_id', 'slack_client_secret')");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $this->clientId = $settings['slack_client_id'] ?? null;
        $this->clientSecret = $settings['slack_client_secret'] ?? null;
    }

    public function isConfigured()
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    public function getUserSlackLink($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM slack_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function unlinkSlackAccount($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM slack_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        return true;
    }

    public function generateAuthUrl($isLogin = false)
    {
        if (!$this->isConfigured()) {
            throw new Exception('Slack OAuth is not configured');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['slack_oauth_state'] = $state;
        $_SESSION['slack_oauth_action'] = $isLogin ? 'login' : 'link';

        $params = [
            'client_id' => $this->clientId,
            'scope' => 'identity.basic identity.email',
            'redirect_uri' => $this->redirectUri,
            'state' => $state
        ];

        return 'https://slack.com/oauth/authorize?' . http_build_query($params);
    }

    public function handleCallback($code, $state)
    {
        $savedState = $_SESSION['slack_oauth_state'] ?? null;
        $action = $_SESSION['slack_oauth_action'] ?? 'link';

        unset($_SESSION['slack_oauth_state'], $_SESSION['slack_oauth_action']);

        if ($state !== $savedState) {
            return ['success' => false, 'error' => 'Invalid state parameter'];
        }

        try {
            $tokenData = $this->exchangeCodeForToken($code);
            $userData = $this->getUserData($tokenData['access_token']);

            if ($action === 'login') {
                return $this->handleLogin($userData, $tokenData);
            } else {
                return $this->handleLinking($userData, $tokenData);
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
            'redirect_uri' => $this->redirectUri
        ];

        $ch = curl_init('https://slack.com/api/oauth.access');
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
        if (!$data['ok']) {
            throw new Exception('Slack OAuth error: ' . ($data['error'] ?? 'Unknown error'));
        }

        return $data;
    }

    private function getUserData($accessToken)
    {
        $ch = curl_init('https://slack.com/api/users.identity');
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

        $data = json_decode($response, true);
        if (!$data['ok']) {
            throw new Exception('Slack API error: ' . ($data['error'] ?? 'Unknown error'));
        }

        return $data;
    }

    private function handleLogin($userData, $tokenData)
    {
        $email = $userData['user']['email'] ?? null;
        if (empty($email)) {
            return ['success' => false, 'error' => 'No email found in Slack account'];
        }

        $stmt = $this->db->prepare("SELECT u.* FROM users u INNER JOIN slack_links sl ON u.id = sl.user_id WHERE sl.slack_id = ? AND u.active_member = 1");
        $stmt->execute([$userData['user']['id']]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'No account found linked to this Slack account'];
        }

        $_SESSION['user_id'] = $user['id'];
        return ['success' => true, 'action' => 'login'];
    }

    private function handleLinking($userData, $tokenData)
    {
        if (!isLoggedIn()) {
            return ['success' => false, 'error' => 'You must be logged in to link accounts'];
        }

        global $currentUser;

        // Additional safety check for $currentUser
        if (!$currentUser) {
            return ['success' => false, 'error' => 'User session not found'];
        }

        $slackUserId = $userData['user']['id'] ?? null;
        if (empty($slackUserId)) {
            return ['success' => false, 'error' => 'No Slack user ID found'];
        }
        $stmt = $this->db->prepare("SELECT user_id FROM slack_links WHERE slack_id = ? AND user_id != ?");
        $stmt->execute([$slackUserId, $currentUser->id]);

        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'This Slack account is already linked to another user'];
        }
        $stmt = $this->db->prepare("INSERT INTO slack_links (user_id, slack_id, slack_username, team_id, team_name, access_token, linked_at) VALUES (?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE slack_username = VALUES(slack_username), team_id = VALUES(team_id), team_name = VALUES(team_name), access_token = VALUES(access_token), linked_at = NOW()");
        $stmt->execute([
            $currentUser->id,
            $slackUserId,
            $userData['user']['name'] ?? ($userData['user']['real_name'] ?? null),
            $tokenData['team']['id'] ?? null,
            $tokenData['team']['name'] ?? null,
            $tokenData['access_token'] ?? null
        ]);

        return ['success' => true, 'action' => 'link'];
    }
}
