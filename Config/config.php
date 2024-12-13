<?php
// Application configuration
define('APP_NAME', 'Yoxplore');
define('APP_URL', 'http://localhost/yoxplore');
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Session configuration
ini_set('session.cookie_httponly', 1);
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>