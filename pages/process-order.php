<?php
/**
 * PetPal - Process Order (Demo)
 * Clears cart and redirects to confirmation
 */

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/cart.php');
    exit;
}

// Validate CSRF token (simplified for demo)
if (!isset($_POST['csrf_token'])) {
    die("Invalid request");
}

// Clear cart
$conn = getDBConnection();
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

// Redirect to confirmation
header('Location: ' . SITE_URL . '/pages/order-confirmation.php');
exit;
