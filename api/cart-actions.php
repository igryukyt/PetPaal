<?php
/**
 * PetPal - Cart Actions API
 * Handles add, update, and remove cart operations
 */

require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.']);
    exit;
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Validate CSRF token for 'add' action (update and remove may come from JS without form)
// For add from shop.php which uses main.js, we skip CSRF since it's a simple add
$action = $_POST['action'] ?? '';

// For actions that modify data significantly, validate CSRF if provided
if (isset($_POST['csrf_token']) && !validateCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Please refresh and try again.']);
    exit;
}

switch ($action) {
    case 'add':
        $productId = intval($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        // Check if already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
            $stmt->execute([$existing['id']]);
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$userId, $productId]);
        }

        // Get updated cart count
        $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'message' => 'Added to cart!',
            'cart_count' => intval($result['count'])
        ]);
        break;

    case 'update':
        $itemId = intval($_POST['item_id'] ?? 0);
        $direction = $_POST['direction'] ?? '';

        if ($itemId <= 0 || !in_array($direction, ['increase', 'decrease'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit;
        }

        // Get current item
        $stmt = $conn->prepare("
            SELECT cart.*, products.price 
            FROM cart 
            JOIN products ON cart.product_id = products.id
            WHERE cart.id = ? AND cart.user_id = ?
        ");
        $stmt->execute([$itemId, $userId]);
        $item = $stmt->fetch();

        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
            exit;
        }

        $newQuantity = $direction === 'increase' ? $item['quantity'] + 1 : $item['quantity'] - 1;
        $removed = false;

        if ($newQuantity <= 0) {
            // Remove item
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
            $stmt->execute([$itemId]);
            $removed = true;
            $newQuantity = 0;
        } else {
            // Update quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $itemId]);
        }

        // Calculate totals
        $stmt = $conn->prepare("
            SELECT cart.quantity, products.price 
            FROM cart 
            JOIN products ON cart.product_id = products.id
            WHERE cart.user_id = ?
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll();

        $subtotal = 0;
        $cartCount = 0;
        foreach ($cartItems as $cartItem) {
            $subtotal += $cartItem['price'] * $cartItem['quantity'];
            $cartCount += $cartItem['quantity'];
        }

        $shipping = $subtotal > 50 ? 0 : 5.99;
        $tax = $subtotal * 0.08;
        $total = $subtotal + $shipping + $tax;

        echo json_encode([
            'success' => true,
            'removed' => $removed,
            'quantity' => $newQuantity,
            'item_total' => number_format($item['price'] * $newQuantity, 2),
            'cart_count' => $cartCount,
            'subtotal' => number_format($subtotal, 2),
            'shipping' => number_format($shipping, 2),
            'tax' => number_format($tax, 2),
            'total' => number_format($total, 2)
        ]);
        break;

    case 'remove':
        $itemId = intval($_POST['item_id'] ?? 0);

        if ($itemId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit;
        }

        // Delete item
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$itemId, $userId]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
            exit;
        }

        // Calculate totals
        $stmt = $conn->prepare("
            SELECT cart.quantity, products.price 
            FROM cart 
            JOIN products ON cart.product_id = products.id
            WHERE cart.user_id = ?
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll();

        $subtotal = 0;
        $cartCount = 0;
        foreach ($cartItems as $cartItem) {
            $subtotal += $cartItem['price'] * $cartItem['quantity'];
            $cartCount += $cartItem['quantity'];
        }

        $shipping = $subtotal > 50 ? 0 : 5.99;
        $tax = $subtotal * 0.08;
        $total = $subtotal + $shipping + $tax;

        echo json_encode([
            'success' => true,
            'cart_count' => $cartCount,
            'subtotal' => number_format($subtotal, 2),
            'shipping' => number_format($shipping, 2),
            'tax' => number_format($tax, 2),
            'total' => number_format($total, 2)
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
