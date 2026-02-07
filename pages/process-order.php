<?php
/**
 * PetPal - Process Order
 * Saves order to database and clears cart
 */

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/cart.php');
    exit;
}

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

// Validate CSRF
if (!isset($_POST['csrf_token'])) {
    die("Invalid request");
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Get cart items
$stmt = $conn->prepare("
    SELECT c.*, p.price 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header('Location: ' . SITE_URL . '/pages/cart.php');
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal > 2000 ? 0 : 99; // ₹2000 threshold
$tax = $subtotal * 0.18; // 18% GST
$total = $subtotal + $shipping + $tax;

// Order Details
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$postcode = $_POST['postcode'] ?? '';
$phone = $_POST['phone'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? 'card';

$shippingAddress = "$firstName $lastName\n$address\n$city, $postcode\n$phone";

try {
    $conn->beginTransaction();

    // Generate Order Number
    $orderNumber = 'ORD-' . strtoupper(uniqid());

    // Insert Order
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, shipping_address)
        VALUES (?, ?, ?, 'pending', ?, ?)
    ");
    $stmt->execute([$userId, $orderNumber, $total, $paymentMethod, $shippingAddress]);
    $orderId = $conn->lastInsertId();

    // Insert Order Items
    $stmtItem = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cartItems as $item) {
        $stmtItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Clear Cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);

    $conn->commit();

    // Redirect to confirmation
    header('Location: ' . SITE_URL . '/pages/order-confirmation.php?id=' . $orderId);
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    // Log error
    error_log("Order Error: " . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to place order. Please try again.'];
    header('Location: ' . SITE_URL . '/pages/checkout.php');
    exit;
}
