<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'website');
define('DB_USER', 'user');
define('DB_PASS', 'password');

// Session Configuration
define('SESSION_NAME', 'hackclub_session');
define('SESSION_LIFETIME', 86400);

// Site Configuration
define('SITE_TITLE', ' Hack Club');
define('SITE_URL', 'http://localhost/club-website/public');
define('ADMIN_EMAIL', 'admin@example.com');

// File Paths
define('ROOT_PATH', dirname(__DIR__));
define('CORE_PATH', ROOT_PATH . '/core');
define('CUSTOM_PATH', ROOT_PATH . '/custom');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('INSTALL_PATH', ROOT_PATH . '/install');

// YSWS API Configuration
define('YSWS_API_URL', 'https://api.hackclub.com/v1/ysws');