<?php
echo "<!-- Loading config.php -->\n";
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session settings before starting session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 0 for localhost
    session_start();
}

// Handle language selection
if (isset($_COOKIE['lang'])) {
    $_SESSION['lang'] = $_COOKIE['lang'];
} else {
    $_SESSION['lang'] = 'zh-hk'; // Default to Traditional Chinese (HK)
}

require_once dirname(__DIR__) . '/lang/translations.php';

// Database configuration
define('DB_HOST', 'sql201.infinityfree.com');
define('DB_USER', 'if0_38232604');
define('DB_PASS', 'K4ORKAUbrICO');
define('DB_NAME', 'if0_38232604_db');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Debug database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
} else {
    error_log("Database connected successfully");
}

// Test query
$test = $conn->query("SELECT 1");
if (!$test) {
    error_log("Database test query failed: " . $conn->error);
}
?> 