<?php
// Prevent direct access
defined('IN_NEIGHBORHOOD_CMS') or die('Direct access not allowed');

// Only define constants if they don't exist yet
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'neighborhood');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

if (!defined('SESSION_NAME')) define('SESSION_NAME', 'neighborhood_session');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 86400);

if (!defined('SITE_TITLE')) define('SITE_TITLE', 'Neighborhood HQ');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/neighborhood-cms/public');
if (!defined('ADMIN_EMAIL')) define('ADMIN_EMAIL', 'admin@example.com');

// File Paths (absolute, works from any include)
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
if (!defined('CORE_PATH')) define('CORE_PATH', ROOT_PATH . '/core');
if (!defined('CUSTOM_PATH')) define('CUSTOM_PATH', ROOT_PATH . '/custom');
if (!defined('MODULES_PATH')) define('MODULES_PATH', ROOT_PATH . '/modules');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', ROOT_PATH . '/public');
if (!defined('INSTALL_PATH')) define('INSTALL_PATH', ROOT_PATH . '/install');

// YSWS API Configuration
if (!defined('YSWS_API_URL')) define('YSWS_API_URL', 'https://api.hackclub.com/v1/ysws');
