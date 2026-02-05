<?php
/**
 * PetPal Configuration File
 * Database connection and session settings
 * Supports both local (XAMPP) and Railway deployment
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration - supports Railway environment variables
$dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');

if ($dbUrl) {
    // Railway MySQL connection
    $parsed = parse_url($dbUrl);
    define('DB_HOST', $parsed['host']);
    define('DB_USER', $parsed['user']);
    define('DB_PASS', $parsed['pass']);
    define('DB_NAME', ltrim($parsed['path'], '/'));
    define('DB_PORT', $parsed['port'] ?? 3306);
} else {
    // Local XAMPP connection
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'petpal');
    define('DB_PORT', 3306);
}

// Site configuration - auto-detect URL
// Check for Railway/Proxy HTTPS
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'https';
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
}
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// For Railway or any production: detect by host or DATABASE_URL presence
if (getenv('DATABASE_URL') || getenv('RAILWAY_ENVIRONMENT') || strpos($host, 'railway.app') !== false) {
    define('SITE_URL', $protocol . '://' . $host);
} else {
    // For local XAMPP: include /PetPal subdirectory
    define('SITE_URL', 'http://localhost/PetPal');
}

define('SITE_NAME', 'PetPal');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Create database connection
function getDBConnection()
{
    static $conn = null;

    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $conn = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $conn;
}

// Helper function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to get current user
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, email, full_name, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Helper function to sanitize output
function h($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Helper function to redirect
function redirect($url)
{
    header("Location: " . $url);
    exit;
}

// Helper function to set flash message
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Helper function to get and clear flash message
function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// CSRF token generation
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token validation
function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
