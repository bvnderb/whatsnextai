<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Time zone
date_default_timezone_set('UTC');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'procrastinator_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application URLs
define('BASE_URL', 'http://localhost/procrastinator/public');
?>