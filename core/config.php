<?php
define('DB_HOST', getenv('DB_HOST') ?: 'mysql.railway.internal:3306');
define('DB_NAME', getenv('DB_NAME') ?: 'railway');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'password');