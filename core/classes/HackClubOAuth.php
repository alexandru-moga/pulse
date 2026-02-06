<?php

class HackClubOAuth
{
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct($db)
    {
        $this->db = $db;
        $this->loadSettings();
    }

    private function loadSettings()
    {
        $settings = $this->db->query("SELECT name, value FROM settings WHERE name LIKE 'hackclub_%'")->fetchAll(PDO::FETCH_KEY_PAIR);

        $this->clientId = $settings['hackclub_client_id'] ?? '';
        $this->clientSecret = $settings['hackclub_client_secret'] ?? '';
        $this->redirectUri = $settings['hackclub_redirect_uri'] ?? '';
    }

    public function isConfigured()
    {
        return !empty($this->clientId) && !empty($this->clientSecret) && !empty($this->redirectUri);
    }

    public function generateAuthUrl($isLogin = false)
    {
        if (!$this->isConfigured()) {
            throw new Exception('Hack Club OAuth not configured');
        }

        $state = bin2hex(random_bytes(16));
        $csrf = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes

        error_log("Hack Club OAuth: Generating auth URL with state: $state, expires: $expiresAt");

        $stmt = $this->db->prepare("INSERT INTO hackclub_login_sessions (state_token, csrf_token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$state, $csrf, $expiresAt]);

        $_SESSION['hackclub_csrf_token'] = $csrf;
        $_SESSION['hackclub_is_login'] = $isLogin;
        
        error_log("Hack Club OAuth: Set session hackclub_is_login to: " . ($isLogin ? 'true' : 'false'));
        error_log("Hack Club OAuth: Set session hackclub_csrf_token to: $csrf");

        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email name slack_id verification_status'
        ];

        return 'https://auth.hackclub.com/oauth/authorize?' . http_build_query($params) . '&state=' . $state;
    }

    public function handleCallback($code, $state)
    {
        if (!$this->isConfigured()) {
            throw new Exception('Hack Club OAuth not configured');
        }

        error_log("Hack Club OAuth: Handling callback with state: $state");

        $stmt = $this->db->prepare("SELECT csrf_token, used FROM hackclub_login_sessions WHERE state_token = ? AND expires_at > UTC_TIMESTAMP()");
        $stmt->execute([$state]);
        $session = $stmt->fetch();

        error_log("Hack Club OAuth: Session data: " . json_encode($session));
        error_log("Hack Club OAuth: Expected CSRF: " . ($_SESSION['hackclub_csrf_token'] ?? 'none'));

        if (!$session || $session['used'] == 1 || $session['csrf_token'] !== ($_SESSION['hackclub_csrf_token'] ?? '')) {
            error_log("Hack Club OAuth: State validation failed");
            throw new Exception('Invalid or expired state token');
        }

        $this->db->prepare("UPDATE hackclub_login_sessions SET used = 1 WHERE state_token = ?")->execute([$state]);

        $tokenData = $this->exchangeCodeForToken($code);
        $hackclubUser = $this->getHackClubUser($tokenData['access_token']);

        $isLogin = $_SESSION['hackclub_is_login'] ?? false;
        error_log("Hack Club OAuth: In handleCallback, isLogin from session: " . ($isLogin ? 'true' : 'false'));
        error_log("Hack Club OAuth: Hack Club user: " . $hackclubUser['id'] . ' (' . ($hackclubUser['first_name'] ?? '') . ' ' . ($hackclubUser['last_name'] ?? '') . ')');
        
        unset($_SESSION['hackclub_csrf_token'], $_SESSION['hackclub_is_login']);

        if ($isLogin) {
            error_log("Hack Club OAuth: Calling handleHackClubLogin");
            return $this->handleHackClubLogin($hackclubUser, $tokenData);
        } else {
            error_log("Hack Club OAuth: Calling linkHackClubAccount");
            return $this->linkHackClubAccount($hackclubUser, $tokenData);
        }
    }

    private function exchangeCodeForToken($code)
    {
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init('https://auth.hackclub.com/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        error_log("Hack Club OAuth: Token exchange response code: $httpCode");
        error_log("Hack Club OAuth: Token exchange response: " . substr($response, 0, 200));

        if ($httpCode !== 200) {
            throw new Exception('Failed to exchange code for token: ' . $response);
        }

        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            throw new Exception('No access token in response');
        }

        return $tokenData;
    }

    private function getHackClubUser($accessToken)
    {
        $ch = curl_init('https://auth.hackclub.com/api/v1/me');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        error_log("Hack Club OAuth: User info response code: $httpCode");
        error_log("Hack Club OAuth: User info response: " . substr($response, 0, 500));

        if ($httpCode !== 200) {
            throw new Exception('Failed to fetch user info: ' . $response);
        }

        $userData = json_decode($response, true);
        if (!isset($userData['identity'])) {
            throw new Exception('Invalid user data response');
        }

        return $userData['identity'];
    }

    private function handleHackClubLogin($hackclubUser, $tokenData)
    {
        error_log("Hack Club OAuth: Attempting login with Hack Club ID: " . $hackclubUser['id']);

        // Check if this Hack Club account is linked to a user
        $stmt = $this->db->prepare("SELECT u.* FROM users u INNER JOIN hackclub_links hl ON u.id = hl.user_id WHERE hl.hackclub_id = ? AND u.role != 'Guest'");
        $stmt->execute([$hackclubUser['id']]);
        $user = $stmt->fetch();

        if (!$user) {
            error_log("Hack Club OAuth: No linked user found for Hack Club ID: " . $hackclubUser['id']);
            $_SESSION['hackclub_error'] = 'No account linked to this Hack Club account. Please link your account from the dashboard first.';
            return ['success' => false, 'error' => 'No account linked to this Hack Club account', 'action' => 'login'];
        }

        error_log("Hack Club OAuth: Login successful for user: " . $user['id']);

        // Update the link with latest info
        $this->updateHackClubLink($user['id'], $hackclubUser, $tokenData);

        // Log the user in
        $_SESSION['user_id'] = $user['id'];
        return ['success' => true, 'user' => $user, 'action' => 'login'];
    }

    private function linkHackClubAccount($hackclubUser, $tokenData)
    {
        error_log("Hack Club OAuth: linkHackClubAccount called");
        error_log("Hack Club OAuth: Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
        
        if (!isset($_SESSION['user_id'])) {
            error_log("Hack Club OAuth: ERROR - No user_id in session!");
            throw new Exception('Must be logged in to link Hack Club account');
        }

        $userId = $_SESSION['user_id'];
        error_log("Hack Club OAuth: Linking Hack Club ID " . $hackclubUser['id'] . " to user " . $userId);

        // Check if this Hack Club account is already linked to another user
        $stmt = $this->db->prepare("SELECT user_id FROM hackclub_links WHERE hackclub_id = ?");
        $stmt->execute([$hackclubUser['id']]);
        $existingLink = $stmt->fetch();

        if ($existingLink && $existingLink['user_id'] != $userId) {
            throw new Exception('This Hack Club account is already linked to another user');
        }

        // Create or update the link
        error_log("Hack Club OAuth: Calling updateHackClubLink");
        $this->updateHackClubLink($userId, $hackclubUser, $tokenData);
        error_log("Hack Club OAuth: Link created/updated successfully");

        return [
            'success' => true,
            'user' => $hackclubUser,
            'action' => 'link'
        ];
    }

    private function updateHackClubLink($userId, $hackclubUser, $tokenData)
    {
        $stmt = $this->db->prepare("SELECT id FROM hackclub_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        $existing = $stmt->fetch();

        $data = [
            'hackclub_id' => $hackclubUser['id'],
            'first_name' => $hackclubUser['first_name'] ?? '',
            'last_name' => $hackclubUser['last_name'] ?? '',
            'email' => $hackclubUser['primary_email'] ?? '',
            'slack_id' => $hackclubUser['slack_id'] ?? null,
            'verification_status' => $hackclubUser['verification_status'] ?? null,
            'ysws_eligible' => $hackclubUser['ysws_eligible'] ?? false,
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'token_expires_at' => date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 15778800))
        ];

        if ($existing) {
            $stmt = $this->db->prepare("UPDATE hackclub_links SET 
                hackclub_id = ?, first_name = ?, last_name = ?, email = ?, slack_id = ?,
                verification_status = ?, ysws_eligible = ?, access_token = ?, refresh_token = ?,
                token_expires_at = ?, updated_at = NOW()
                WHERE user_id = ?");
            $stmt->execute([
                $data['hackclub_id'],
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['slack_id'],
                $data['verification_status'],
                $data['ysws_eligible'] ? 1 : 0,
                $data['access_token'],
                $data['refresh_token'],
                $data['token_expires_at'],
                $userId
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO hackclub_links 
                (user_id, hackclub_id, first_name, last_name, email, slack_id, verification_status, ysws_eligible, access_token, refresh_token, token_expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $data['hackclub_id'],
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['slack_id'],
                $data['verification_status'],
                $data['ysws_eligible'] ? 1 : 0,
                $data['access_token'],
                $data['refresh_token'],
                $data['token_expires_at']
            ]);
        }

        error_log("Hack Club OAuth: Link updated for user " . $userId);
    }

    public function unlinkAccount($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM hackclub_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        return true;
    }

    public function getLinkedAccount($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM hackclub_links WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
