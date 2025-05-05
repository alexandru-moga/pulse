<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'neighborhood');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_TITLE', 'Neighborhood HQ');
define('SITE_URL', 'http://127.0.0.1:5500/neighborhood-cms/public');
define('ADMIN_EMAIL', 'admin@example.com');

// File Paths
define('ROOT_PATH', dirname(__DIR__));
define('CORE_PATH', ROOT_PATH . '/core');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('CUSTOM_PATH', ROOT_PATH . '/custom');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// YSWS API Configuration
define('YSWS_API_URL', 'https://api.hackclub.com/v1/ysws');

// Session Configuration
define('SESSION_NAME', 'neighborhood_session');
define('SESSION_LIFETIME', 86400); // 24 hours