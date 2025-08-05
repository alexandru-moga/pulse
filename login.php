<?php
/**
 * Redirect file for login.php
 * Redirects users from domain/login.php to domain/dashboard/login.php
 * 
 * This file exists because some links or users might try to access
 * the login page at the root level instead of in the dashboard folder.
 */

// Redirect to the correct login page location
header('Location: /dashboard/login.php', true, 301);
exit();
?>
