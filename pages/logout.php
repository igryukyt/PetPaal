<?php
/**
 * PetPal - Logout Page
 * Destroys user session and redirects to home
 */

require_once '../config/config.php';

// Destroy all session data
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start new session for flash message
session_start();
setFlash('success', 'You have been logged out successfully.');

// Redirect to home page
redirect(SITE_URL);
