<?php

class DiscordBot {
    private $db;
    private $botToken;
    private $guildId;
    
    public function __construct($database) {
        $this->db = $database;
        
        // Get settings from database
        $stmt = $this->db->prepare("SELECT name, value FROM settings WHERE name IN ('discord_bot_token', 'discord_guild_id')");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $this->botToken = $settings['discord_bot_token'] ?? '';
        $this->guildId = $settings['discord_guild_id'] ?? '';
    }
    
    /**
     * Sync roles for a specific project
     */
    public function syncProjectRoles($projectId) {
        try {
            // Get project info
            $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$project) {
                return ['success' => false, 'error' => 'Project not found'];
            }
            
            // Get project assignments for accepted users
            $stmt = $this->db->prepare("
                SELECT pa.*, u.id as user_id, dl.discord_id
                FROM project_assignments pa
                JOIN users u ON pa.user_id = u.id
                LEFT JOIN discord_links dl ON u.id = dl.user_id
                WHERE pa.project_id = ? AND pa.status = 'accepted' AND dl.discord_id IS NOT NULL
            ");
            $stmt->execute([$projectId]);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $assigned_count = 0;
            
            foreach ($assignments as $assignment) {
                $roles_assigned = [];
                
                // Assign accepted role
                if ($project['discord_accepted_role_id']) {
                    if ($this->assignDiscordRole($assignment['discord_id'], $project['discord_accepted_role_id'])) {
                        $roles_assigned[] = $project['discord_accepted_role_id'];
                    }
                }
                
                // Assign pizza role if applicable
                if ($assignment['pizza_grant'] === 'received' && $project['discord_pizza_role_id']) {
                    if ($this->assignDiscordRole($assignment['discord_id'], $project['discord_pizza_role_id'])) {
                        $roles_assigned[] = $project['discord_pizza_role_id'];
                    }
                }
                
                if (!empty($roles_assigned)) {
                    $assigned_count++;
                }
            }
            
            return ['success' => true, 'assigned_count' => $assigned_count];
            
        } catch (Exception $e) {
            error_log("Discord project sync failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Sync roles for a specific event
     */
    public function syncEventRoles($eventId) {
        try {
            // Get event info
            $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event || !$event['discord_participated_role_id']) {
                return ['success' => false, 'error' => 'Event not found or no role configured'];
            }
            
            // Get users linked to this event through YSWS projects
            $stmt = $this->db->prepare("
                SELECT DISTINCT u.id as user_id, dl.discord_id
                FROM event_ysws ey
                JOIN projects p ON p.requirements LIKE CONCAT('%', ey.ysws_link, '%')
                JOIN project_assignments pa ON p.id = pa.project_id AND pa.status = 'accepted'
                JOIN users u ON pa.user_id = u.id
                LEFT JOIN discord_links dl ON u.id = dl.user_id
                WHERE ey.event_id = ? AND dl.discord_id IS NOT NULL
            ");
            $stmt->execute([$eventId]);
            $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $assigned_count = 0;
            
            foreach ($participants as $participant) {
                if ($this->assignDiscordRole($participant['discord_id'], $event['discord_participated_role_id'])) {
                    $assigned_count++;
                }
            }
            
            return ['success' => true, 'assigned_count' => $assigned_count];
            
        } catch (Exception $e) {
            error_log("Discord event sync failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Sync all roles for all projects and events
     */
    public function syncAllRoles() {
        try {
            $projects_synced = 0;
            $events_synced = 0;
            $roles_added = 0;
            $roles_removed = 0;
            
            // First, sync all projects with Discord roles
            $stmt = $this->db->prepare("
                SELECT id, discord_accepted_role_id, discord_pizza_role_id 
                FROM projects 
                WHERE discord_accepted_role_id IS NOT NULL OR discord_pizza_role_id IS NOT NULL
            ");
            $stmt->execute();
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($projects as $project) {
                $result = $this->syncProjectRolesFull($project['id']);
                if ($result['success']) {
                    $projects_synced++;
                    $roles_added += $result['roles_added'];
                    $roles_removed += $result['roles_removed'];
                }
            }
            
            // Then, sync all events with Discord roles
            $stmt = $this->db->prepare("
                SELECT id, discord_participated_role_id 
                FROM events 
                WHERE discord_participated_role_id IS NOT NULL
            ");
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($events as $event) {
                $result = $this->syncEventRolesFull($event['id']);
                if ($result['success']) {
                    $events_synced++;
                    $roles_added += $result['roles_added'];
                    $roles_removed += $result['roles_removed'];
                }
            }
            
            return [
                'success' => true, 
                'projects_synced' => $projects_synced,
                'events_synced' => $events_synced,
                'roles_added' => $roles_added,
                'roles_removed' => $roles_removed
            ];
            
        } catch (Exception $e) {
            error_log("Discord full sync failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Assign Discord role to user
     */
    private function assignDiscordRole($discordUserId, $roleId) {
        if (!$this->botToken || !$this->guildId) {
            return false;
        }
        
        $url = "https://discord.com/api/v10/guilds/{$this->guildId}/members/{$discordUserId}/roles/{$roleId}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bot ' . $this->botToken,
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 204;
    }
    
    /**
     * Test Discord connection
     */
    public function testConnection($guildId = null, $botToken = null) {
        // Use provided parameters or fall back to instance properties
        $token = $botToken ?: $this->botToken;
        $guild = $guildId ?: $this->guildId;
        
        if (!$token || !$guild) {
            return ['success' => false, 'error' => 'Bot token and guild ID are required'];
        }
        
        $url = "https://discord.com/api/v10/guilds/{$guild}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bot ' . $token
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return ['success' => true, 'server_name' => $data['name'] ?? 'Unknown'];
        } else {
            return ['success' => false, 'error' => "HTTP $httpCode"];
        }
    }
    
    /**
     * Generate and store OAuth state token
     */
    public function generateOAuthState($userId = null) {
        try {
            // Clean up expired sessions first
            $this->cleanupExpiredSessions();
            
            $stateToken = bin2hex(random_bytes(32));
            $csrfToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes
            
            error_log("Discord OAuth: Generating state token: $stateToken, expires: $expiresAt");
            
            // Use the existing table structure without user_id
            $stmt = $this->db->prepare("
                INSERT INTO discord_login_sessions (state_token, csrf_token, expires_at) 
                VALUES (?, ?, ?)
            ");
            
            if ($stmt->execute([$stateToken, $csrfToken, $expiresAt])) {
                error_log("Discord OAuth: State token generated successfully");
                return [
                    'success' => true,
                    'state_token' => $stateToken,
                    'csrf_token' => $csrfToken
                ];
            }
            
            error_log("Discord OAuth: Failed to insert state token");
            return ['success' => false, 'error' => 'Failed to generate state token'];
            
        } catch (Exception $e) {
            error_log("OAuth state generation failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Validate OAuth state token
     */
    public function validateOAuthState($stateToken) {
        try {
            error_log("Discord OAuth: Validating state token: $stateToken");
            
            // Check what's in the database first
            $debugStmt = $this->db->prepare("SELECT state_token, expires_at, used, NOW() as current_time FROM discord_login_sessions WHERE state_token = ?");
            $debugStmt->execute([$stateToken]);
            $debugSession = $debugStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Discord OAuth Debug: " . json_encode($debugSession));
            
            $stmt = $this->db->prepare("
                SELECT * FROM discord_login_sessions 
                WHERE state_token = ? 
                AND expires_at > NOW() 
                AND used = FALSE
            ");
            $stmt->execute([$stateToken]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($session) {
                error_log("Discord OAuth: State token valid, marking as used");
                // Mark as used
                $updateStmt = $this->db->prepare("UPDATE discord_login_sessions SET used = TRUE WHERE id = ?");
                $updateStmt->execute([$session['id']]);
                
                return [
                    'success' => true,
                    'session' => $session
                ];
            }
            
            error_log("Discord OAuth: State token validation failed - no valid session found");
            return ['success' => false, 'error' => 'Invalid or expired state token'];
            
        } catch (Exception $e) {
            error_log("OAuth state validation failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Clean up expired OAuth sessions
     */
    public function cleanupExpiredSessions() {
        try {
            $stmt = $this->db->prepare("DELETE FROM discord_login_sessions WHERE expires_at < NOW() OR used = TRUE");
            $deletedRows = $stmt->execute();
            error_log("Discord OAuth: Cleaned up expired sessions, deleted rows: " . $stmt->rowCount());
            return true;
        } catch (Exception $e) {
            error_log("Session cleanup failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exchange OAuth code for access token
     */
    public function exchangeCodeForToken($code) {
        try {
            // Get Discord OAuth settings
            $stmt = $this->db->prepare("
                SELECT name, value FROM settings 
                WHERE name IN ('discord_client_id', 'discord_client_secret', 'discord_redirect_uri')
            ");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            if (empty($settings['discord_client_id']) || empty($settings['discord_client_secret'])) {
                return ['success' => false, 'error' => 'Discord OAuth not configured'];
            }
            
            $data = [
                'client_id' => $settings['discord_client_id'],
                'client_secret' => $settings['discord_client_secret'],
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $settings['discord_redirect_uri']
            ];
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://discord.com/api/v10/oauth2/token',
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $tokenData = json_decode($response, true);
                return ['success' => true, 'token_data' => $tokenData];
            } else {
                $error = json_decode($response, true);
                return ['success' => false, 'error' => $error['error_description'] ?? "HTTP $httpCode"];
            }
            
        } catch (Exception $e) {
            error_log("Discord token exchange failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get Discord user info from access token
     */
    public function getUserInfo($accessToken) {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://discord.com/api/v10/users/@me',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $userData = json_decode($response, true);
                return ['success' => true, 'user_data' => $userData];
            } else {
                return ['success' => false, 'error' => "HTTP $httpCode"];
            }
            
        } catch (Exception $e) {
            error_log("Discord user info fetch failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate Discord OAuth authorization URL
     */
    public function getOAuthUrl($stateToken) {
        try {
            // Get Discord OAuth settings
            $stmt = $this->db->prepare("
                SELECT name, value FROM settings 
                WHERE name IN ('discord_client_id', 'discord_redirect_uri')
            ");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            if (empty($settings['discord_client_id']) || empty($settings['discord_redirect_uri'])) {
                return ['success' => false, 'error' => 'Discord OAuth not configured'];
            }
            
            $params = [
                'client_id' => $settings['discord_client_id'],
                'redirect_uri' => $settings['discord_redirect_uri'],
                'response_type' => 'code',
                'scope' => 'identify email guilds.join',
                'state' => $stateToken
            ];
            
            $url = 'https://discord.com/api/oauth2/authorize?' . http_build_query($params);
            
            return ['success' => true, 'url' => $url];
            
        } catch (Exception $e) {
            error_log("Discord OAuth URL generation failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Clean up Discord roles - remove roles from users who no longer qualify
     */
    public function cleanupDiscordRoles() {
        try {
            $removed_count = 0;
            
            // Clean up project roles
            $removed_count += $this->cleanupProjectRoles();
            
            // Clean up event roles
            $removed_count += $this->cleanupEventRoles();
            
            // Clean up roles from users who are no longer active members
            $removed_count += $this->cleanupInactiveUserRoles();
            
            return ['success' => true, 'removed_count' => $removed_count];
            
        } catch (Exception $e) {
            error_log("Discord role cleanup failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Clean up project-specific roles
     */
    private function cleanupProjectRoles() {
        $removed_count = 0;
        
        try {
            // Get all projects with Discord roles
            $stmt = $this->db->prepare("
                SELECT id, discord_accepted_role_id, discord_pizza_role_id 
                FROM projects 
                WHERE discord_accepted_role_id IS NOT NULL OR discord_pizza_role_id IS NOT NULL
            ");
            $stmt->execute();
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($projects as $project) {
                // Get users who should NOT have the accepted role
                if ($project['discord_accepted_role_id']) {
                    $stmt = $this->db->prepare("
                        SELECT DISTINCT dl.discord_id
                        FROM discord_links dl
                        JOIN users u ON dl.user_id = u.id
                        LEFT JOIN project_assignments pa ON u.id = pa.user_id AND pa.project_id = ?
                        WHERE pa.status IS NULL OR pa.status != 'accepted'
                    ");
                    $stmt->execute([$project['id']]);
                    $usersToRemove = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    foreach ($usersToRemove as $discordId) {
                        if ($this->removeDiscordRole($discordId, $project['discord_accepted_role_id'])) {
                            $removed_count++;
                        }
                    }
                }
                
                // Get users who should NOT have the pizza role
                if ($project['discord_pizza_role_id']) {
                    $stmt = $this->db->prepare("
                        SELECT DISTINCT dl.discord_id
                        FROM discord_links dl
                        JOIN users u ON dl.user_id = u.id
                        LEFT JOIN project_assignments pa ON u.id = pa.user_id AND pa.project_id = ?
                        WHERE pa.pizza_grant IS NULL OR pa.pizza_grant != 'received'
                    ");
                    $stmt->execute([$project['id']]);
                    $usersToRemove = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    foreach ($usersToRemove as $discordId) {
                        if ($this->removeDiscordRole($discordId, $project['discord_pizza_role_id'])) {
                            $removed_count++;
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Project role cleanup failed: " . $e->getMessage());
        }
        
        return $removed_count;
    }
    
    /**
     * Clean up event-specific roles
     */
    private function cleanupEventRoles() {
        $removed_count = 0;
        
        try {
            // Get all events with Discord roles
            $stmt = $this->db->prepare("
                SELECT id, discord_participated_role_id 
                FROM events 
                WHERE discord_participated_role_id IS NOT NULL
            ");
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($events as $event) {
                // Get users who SHOULD have the participated role (qualified participants)
                $stmt = $this->db->prepare("
                    SELECT DISTINCT dl.discord_id
                    FROM event_ysws ey
                    JOIN projects p ON p.requirements LIKE CONCAT('%', ey.ysws_link, '%')
                    JOIN project_assignments pa ON p.id = pa.project_id AND pa.status = 'accepted'
                    JOIN users u ON pa.user_id = u.id
                    JOIN discord_links dl ON u.id = dl.user_id
                    WHERE ey.event_id = ?
                ");
                $stmt->execute([$event['id']]);
                $qualifiedUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Get all guild members who currently have this role
                $roleHolders = $this->getGuildMembersWithRole($event['discord_participated_role_id']);
                
                if ($roleHolders['success']) {
                    foreach ($roleHolders['members'] as $discordId) {
                        // If this user has the role but is not in the qualified list, remove it
                        if (!in_array($discordId, $qualifiedUsers)) {
                            if ($this->removeDiscordRole($discordId, $event['discord_participated_role_id'])) {
                                $removed_count++;
                                error_log("Removed event role {$event['discord_participated_role_id']} from user {$discordId} for event {$event['id']}");
                            }
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Event role cleanup failed: " . $e->getMessage());
        }
        
        return $removed_count;
    }
    
    /**
     * Clean up roles from inactive users or users who unlinked Discord
     */
    private function cleanupInactiveUserRoles() {
        $removed_count = 0;
        
        try {
            // Get all Discord IDs from discord_links table for users who are no longer active
            $stmt = $this->db->prepare("
                SELECT dl.discord_id
                FROM discord_links dl
                LEFT JOIN users u ON dl.user_id = u.id
                WHERE u.id IS NULL OR u.active_member = 0
            ");
            $stmt->execute();
            $inactiveDiscordIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Get all configured role IDs
            $allRoleIds = [];
            
            // Project roles
            $stmt = $this->db->prepare("
                SELECT discord_accepted_role_id, discord_pizza_role_id 
                FROM projects 
                WHERE discord_accepted_role_id IS NOT NULL OR discord_pizza_role_id IS NOT NULL
            ");
            $stmt->execute();
            $projectRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($projectRoles as $role) {
                if ($role['discord_accepted_role_id']) $allRoleIds[] = $role['discord_accepted_role_id'];
                if ($role['discord_pizza_role_id']) $allRoleIds[] = $role['discord_pizza_role_id'];
            }
            
            // Event roles
            $stmt = $this->db->prepare("
                SELECT discord_participated_role_id 
                FROM events 
                WHERE discord_participated_role_id IS NOT NULL
            ");
            $stmt->execute();
            $eventRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $allRoleIds = array_merge($allRoleIds, $eventRoles);
            
            // Remove all configured roles from inactive users
            foreach ($inactiveDiscordIds as $discordId) {
                foreach (array_unique($allRoleIds) as $roleId) {
                    if ($this->removeDiscordRole($discordId, $roleId)) {
                        $removed_count++;
                        error_log("Removed role {$roleId} from inactive user {$discordId}");
                    }
                }
            }
            
            // Clean up discord_links for inactive users
            $stmt = $this->db->prepare("
                DELETE dl FROM discord_links dl
                LEFT JOIN users u ON dl.user_id = u.id
                WHERE u.id IS NULL OR u.active_member = 0
            ");
            $deletedLinks = $stmt->execute() ? $stmt->rowCount() : 0;
            error_log("Cleaned up {$deletedLinks} Discord links for inactive users");
            
        } catch (Exception $e) {
            error_log("Inactive user role cleanup failed: " . $e->getMessage());
        }
        
        return $removed_count;
    }
    
    /**
     * Get all guild members who have a specific role
     */
    private function getGuildMembersWithRole($roleId) {
        if (!$this->botToken || !$this->guildId) {
            return ['success' => false, 'error' => 'Bot token or guild ID not configured'];
        }
        
        try {
            // Get guild members (paginated)
            $members = [];
            $after = null;
            $limit = 1000;
            
            do {
                $url = "https://discord.com/api/v10/guilds/{$this->guildId}/members?limit={$limit}";
                if ($after) {
                    $url .= "&after={$after}";
                }
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bot ' . $this->botToken
                    ]
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode !== 200) {
                    return ['success' => false, 'error' => "Failed to fetch guild members: HTTP {$httpCode}"];
                }
                
                $batch = json_decode($response, true);
                if (empty($batch)) {
                    break;
                }
                
                // Filter members who have the specified role
                foreach ($batch as $member) {
                    if (in_array($roleId, $member['roles'])) {
                        $members[] = $member['user']['id'];
                    }
                }
                
                // Set after parameter for next page
                if (count($batch) === $limit) {
                    $after = end($batch)['user']['id'];
                } else {
                    break;
                }
                
            } while (count($batch) === $limit);
            
            return ['success' => true, 'members' => $members];
            
        } catch (Exception $e) {
            error_log("Failed to get guild members with role: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Remove Discord role from user
     */
    private function removeDiscordRole($discordUserId, $roleId) {
        if (!$this->botToken || !$this->guildId) {
            return false;
        }
        
        $url = "https://discord.com/api/v10/guilds/{$this->guildId}/members/{$discordUserId}/roles/{$roleId}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bot ' . $this->botToken,
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 204;
    }

    /**
     * Register slash commands with Discord
     */
    public function registerSlashCommands() {
        if (!$this->botToken || !$this->guildId) {
            return ['success' => false, 'error' => 'Bot token or guild ID not configured'];
        }

        $commands = [
            [
                'name' => 'sync',
                'description' => 'Synchronize Discord roles with project assignments',
                'default_member_permissions' => '8', // Administrator permission
                'options' => [
                    [
                        'type' => 3, // STRING
                        'name' => 'type',
                        'description' => 'What to sync',
                        'required' => false,
                        'choices' => [
                            ['name' => 'All (projects and events)', 'value' => 'all'],
                            ['name' => 'Projects only', 'value' => 'projects'],
                            ['name' => 'Events only', 'value' => 'events'],
                            ['name' => 'Cleanup invalid roles', 'value' => 'cleanup']
                        ]
                    ]
                ]
            ]
        ];

        foreach ($commands as $command) {
            $result = $this->createSlashCommand($command);
            if (!$result['success']) {
                return $result;
            }
        }

        return ['success' => true, 'message' => 'Slash commands registered successfully'];
    }

    /**
     * Create a single slash command
     */
    private function createSlashCommand($command) {
        $url = "https://discord.com/api/v10/applications/{$this->getApplicationId()}/guilds/{$this->guildId}/commands";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($command),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bot ' . $this->botToken,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            return ['success' => true];
        } else {
            error_log("Failed to create slash command: HTTP $httpCode - $response");
            return ['success' => false, 'error' => "HTTP $httpCode"];
        }
    }

    /**
     * Get application ID from bot token
     */
    private function getApplicationId() {
        // Get application ID from Discord settings or decode from token
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE name = 'discord_client_id'");
        $stmt->execute();
        $clientId = $stmt->fetchColumn();
        
        if ($clientId) {
            return $clientId;
        }

        // Fallback: decode from token (bot tokens contain application ID)
        $tokenParts = explode('.', $this->botToken);
        if (count($tokenParts) >= 1) {
            return base64_decode($tokenParts[0]);
        }

        throw new Exception('Could not determine application ID');
    }

    /**
     * Handle slash command interaction
     */
    public function handleSlashCommand($interaction) {
        $commandName = $interaction['data']['name'] ?? '';
        $options = $interaction['data']['options'] ?? [];
        
        // Parse options into key-value pairs
        $parsedOptions = [];
        foreach ($options as $option) {
            $parsedOptions[$option['name']] = $option['value'];
        }

        switch ($commandName) {
            case 'sync':
                return $this->handleSyncCommand($interaction, $parsedOptions);
            default:
                return $this->createInteractionResponse($interaction, 'Unknown command', true);
        }
    }

    /**
     * Handle /sync command
     */
    private function handleSyncCommand($interaction, $options) {
        $type = $options['type'] ?? 'all';
        
        // Send initial "thinking" response
        $this->sendDeferredResponse($interaction);
        
        try {
            $result = null;
            $message = '';

            switch ($type) {
                case 'projects':
                    $result = $this->syncProjectRolesOnly();
                    $message = $result['success'] ? 
                        "✅ Project roles synced! Synced {$result['projects_synced']} projects." :
                        "❌ Project sync failed: {$result['error']}";
                    break;

                case 'events':
                    $result = $this->syncEventRolesOnly();
                    $message = $result['success'] ? 
                        "✅ Event roles synced! Synced {$result['events_synced']} events." :
                        "❌ Event sync failed: {$result['error']}";
                    break;

                case 'cleanup':
                    $result = $this->cleanupDiscordRoles();
                    $message = $result['success'] ? 
                        "🧹 Discord role cleanup completed! Removed {$result['removed_count']} invalid role assignments." :
                        "❌ Cleanup failed: {$result['error']}";
                    break;

                case 'all':
                default:
                    $result = $this->syncAllRoles();
                    $message = $result['success'] ? 
                        "✅ Discord roles synced successfully! Projects: {$result['projects_synced']}, Events: {$result['events_synced']}" :
                        "❌ Sync failed: {$result['error']}";
                    break;
            }

            return $this->editInteractionResponse($interaction, $message);

        } catch (Exception $e) {
            error_log("Discord sync command error: " . $e->getMessage());
            return $this->editInteractionResponse($interaction, "❌ Sync failed: " . $e->getMessage());
        }
    }

    /**
     * Sync only project roles
     */
    private function syncProjectRolesOnly() {
        try {
            $projects_synced = 0;
            
            $stmt = $this->db->prepare("
                SELECT id FROM projects 
                WHERE discord_accepted_role_id IS NOT NULL OR discord_pizza_role_id IS NOT NULL
            ");
            $stmt->execute();
            $projects = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($projects as $project_id) {
                $result = $this->syncProjectRoles($project_id);
                if ($result['success']) {
                    $projects_synced++;
                }
            }
            
            return ['success' => true, 'projects_synced' => $projects_synced];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sync only event roles
     */
    private function syncEventRolesOnly() {
        try {
            $events_synced = 0;
            
            $stmt = $this->db->prepare("
                SELECT id FROM events 
                WHERE discord_participated_role_id IS NOT NULL
            ");
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($events as $event_id) {
                $result = $this->syncEventRoles($event_id);
                if ($result['success']) {
                    $events_synced++;
                }
            }
            
            return ['success' => true, 'events_synced' => $events_synced];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send deferred response to give more time for processing
     */
    private function sendDeferredResponse($interaction) {
        $url = "https://discord.com/api/v10/interactions/{$interaction['id']}/{$interaction['token']}/callback";
        
        $response = [
            'type' => 5 // DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE
        ];

        $this->sendDiscordRequest($url, $response);
    }

    /**
     * Edit the deferred interaction response
     */
    private function editInteractionResponse($interaction, $content) {
        $applicationId = $this->getApplicationId();
        $url = "https://discord.com/api/v10/webhooks/{$applicationId}/{$interaction['token']}/messages/@original";
        
        $response = [
            'content' => $content
        ];

        return $this->sendDiscordRequest($url, $response, 'PATCH');
    }

    /**
     * Send request to Discord API
     */
    private function sendDiscordRequest($url, $data, $method = 'POST') {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bot ' . $this->botToken,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['success' => $httpCode >= 200 && $httpCode < 300, 'response' => $response, 'code' => $httpCode];
    }

    /**
     * Sync roles for a specific project - comprehensive sync that adds and removes roles
     */
    public function syncProjectRolesFull($projectId) {
        try {
            // Get project info
            $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$project) {
                return ['success' => false, 'error' => 'Project not found'];
            }
            
            $roles_added = 0;
            $roles_removed = 0;
            
            // Handle accepted role sync
            if ($project['discord_accepted_role_id']) {
                $result = $this->syncSpecificRole(
                    $project['discord_accepted_role_id'],
                    "SELECT pa.*, u.id as user_id, dl.discord_id
                     FROM project_assignments pa
                     JOIN users u ON pa.user_id = u.id
                     LEFT JOIN discord_links dl ON u.id = dl.user_id
                     WHERE pa.project_id = ? AND pa.status = 'accepted' AND dl.discord_id IS NOT NULL",
                    [$projectId]
                );
                $roles_added += $result['added'];
                $roles_removed += $result['removed'];
            }
            
            // Handle pizza role sync
            if ($project['discord_pizza_role_id']) {
                $result = $this->syncSpecificRole(
                    $project['discord_pizza_role_id'],
                    "SELECT pa.*, u.id as user_id, dl.discord_id
                     FROM project_assignments pa
                     JOIN users u ON pa.user_id = u.id
                     LEFT JOIN discord_links dl ON u.id = dl.user_id
                     WHERE pa.project_id = ? AND pa.status = 'accepted' AND pa.pizza_grant = 'received' AND dl.discord_id IS NOT NULL",
                    [$projectId]
                );
                $roles_added += $result['added'];
                $roles_removed += $result['removed'];
            }
            
            return [
                'success' => true, 
                'roles_added' => $roles_added,
                'roles_removed' => $roles_removed
            ];
            
        } catch (Exception $e) {
            error_log("Discord project full sync failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Sync roles for a specific event - comprehensive sync that adds and removes roles
     */
    public function syncEventRolesFull($eventId) {
        try {
            // Get event info
            $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event || !$event['discord_participated_role_id']) {
                return ['success' => false, 'error' => 'Event not found or no role configured'];
            }
            
            // Sync participated role
            $result = $this->syncSpecificRole(
                $event['discord_participated_role_id'],
                "SELECT DISTINCT u.id as user_id, dl.discord_id
                 FROM event_ysws ey
                 JOIN projects p ON p.requirements LIKE CONCAT('%', ey.ysws_link, '%')
                 JOIN project_assignments pa ON p.id = pa.project_id AND pa.status = 'accepted'
                 JOIN users u ON pa.user_id = u.id
                 LEFT JOIN discord_links dl ON u.id = dl.user_id
                 WHERE ey.event_id = ? AND dl.discord_id IS NOT NULL",
                [$eventId]
            );
            
            return [
                'success' => true,
                'roles_added' => $result['added'],
                'roles_removed' => $result['removed']
            ];
            
        } catch (Exception $e) {
            error_log("Discord event full sync failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Sync a specific Discord role - adds it to users who should have it, removes from users who shouldn't
     */
    private function syncSpecificRole($roleId, $query, $params) {
        $added = 0;
        $removed = 0;
        
        try {
            // Get all users who should have this role (linked and meet criteria)
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $usersWhoShouldHaveRole = $stmt->fetchAll(PDO::FETCH_COLUMN, 2); // discord_id column
            
            // Get all Discord users who currently have this role
            $currentRoleMembers = $this->getGuildMembersWithRole($roleId);
            
            if (!$currentRoleMembers['success']) {
                error_log("Failed to get current role members: " . $currentRoleMembers['error']);
                return ['added' => 0, 'removed' => 0];
            }
            
            $currentDiscordIds = $currentRoleMembers['members'];
            
            // Add role to users who should have it but don't
            foreach ($usersWhoShouldHaveRole as $discordId) {
                if (!in_array($discordId, $currentDiscordIds)) {
                    if ($this->assignDiscordRole($discordId, $roleId)) {
                        $added++;
                    }
                }
            }
            
            // Remove role from users who have it but shouldn't
            foreach ($currentDiscordIds as $discordId) {
                if (!in_array($discordId, $usersWhoShouldHaveRole)) {
                    if ($this->removeDiscordRole($discordId, $roleId)) {
                        $removed++;
                    }
                }
            }
            
            return ['added' => $added, 'removed' => $removed];
            
        } catch (Exception $e) {
            error_log("Discord specific role sync failed: " . $e->getMessage());
            return ['added' => 0, 'removed' => 0];
        }
    }
