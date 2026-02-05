<?php
/**
 * PetPal - Cart Page
 * Shopping cart with quantity management and checkout
 */

require_once '../config/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlash('error', 'Please login to view your cart.');
    redirect(SITE_URL . '/pages/login.php');
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Fetch cart items with product details
$stmt = $conn->prepare("
    SELECT cart.*, products.name, products.price, products.image_url, products.category
    FROM cart 
    JOIN products ON cart.product_id = products.id
    WHERE cart.user_id = ?
    ORDER BY cart.created_at DESC
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal > 50 ? 0 : 5.99;
$tax = $subtotal * 0.08; // 8% tax
$total = $subtotal + $shipping + $tax;

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your PetPal shopping cart">
    <title>Shopping Cart -
        <?php echo SITE_NAME; ?>
    </title>
    <?php include '../includes/head.php'; ?>
    <style>
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
        }

        .remove-btn {
            color: var(--danger);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .remove-btn:hover {
            transform: scale(1.2);
        }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>">Home</a>
                <i class="fas fa-chevron-right"></i>
                <a href="<?php echo SITE_URL; ?>/pages/shop.php">Shop</a>
                <i class="fas fa-chevron-right"></i>
                <span>Cart</span>
            </div>
            <h1>Shopping Cart</h1>
            <p>
                <?php echo count($cartItems); ?> item
                <?php echo count($cartItems) !== 1 ? 's' : ''; ?> in your cart
            </p>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <?php $flash = getFlash();
            if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                    <?php echo h($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cartItems)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🛒</div>
                    <h3>Your Cart is Empty</h3>
                    <p>Looks like you haven't added any products yet.</p>
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i>
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <!-- Cart Items -->
                    <div class="cart-items-section">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <?php foreach ($cartItems as $item): ?>
                                    <tr data-item-id="<?php echo $item['id']; ?>">
                                        <td>
                                            <div class="cart-item">
                                                <img src="<?php echo h($item['image_url']); ?>"
                                                    alt="<?php echo h($item['name']); ?>" class="cart-item-image">
                                                <div class="cart-item-info">
                                                    <h4>
                                                        <?php echo h($item['name']); ?>
                                                    </h4>
                                                    <p>
                                                        <?php echo h(ucfirst($item['category'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>$
                                            <?php echo number_format($item['price'], 2); ?>
                                        </td>
                                        <td>
                                            <div class="quantity-control">
                                                <button class="quantity-btn"
                                                    onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrease')">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span class="quantity-display">
                                                    <?php echo $item['quantity']; ?>
                                                </span>
                                                <button class="quantity-btn"
                                                    onclick="updateQuantity(<?php echo $item['id']; ?>, 'increase')">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="item-total">
                                            $
                                            <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                        </td>
                                        <td>
                                            <button class="remove-btn" onclick="removeItem(<?php echo $item['id']; ?>)"
                                                title="Remove">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div style="margin-top: 20px;">
                            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Continue Shopping
                            </a>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="cart-summary" id="cartSummary">
                        <h3>Order Summary</h3>

                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal">$
                                <?php echo number_format($subtotal, 2); ?>
                            </span>
                        </div>

                        <div class="summary-row">
                            <span>Shipping</span>
                            <span id="shipping">
                                <?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'FREE'; ?>
                            </span>
                        </div>

                        <div class="summary-row">
                            <span>Tax (8%)</span>
                            <span id="tax">$
                                <?php echo number_format($tax, 2); ?>
                            </span>
                        </div>

                        <div class="summary-row total">
                            <span>Total</span>
                            <span id="total">$
                                <?php echo number_format($total, 2); ?>
                            </span>
                        </div>

                        <?php if ($shipping > 0): ?>
                            <p class="form-hint" style="margin-top: 15px;">
                                <i class="fas fa-truck"></i>
                                Add $
                                <?php echo number_format(50 - $subtotal, 2); ?> more for free shipping!
                            </p>
                        <?php endif; ?>

                        <button class="btn btn-primary btn-block btn-lg" style="margin-top: 20px;" onclick="checkout()">
                            <i class="fas fa-lock"></i>
                            Proceed to Checkout
                        </button>

                        <div style="margin-top: 20px; text-align: center;">
                            <p style="font-size: 0.85rem; color: var(--gray); margin-bottom: 10px;">
                                Secure checkout powered by
                            </p>
                            <div style="display: flex; justify-content: center; gap: 10px;">
                                <i class="fab fa-cc-visa" style="font-size: 1.5rem; color: var(--gray);"></i>
                                <i class="fab fa-cc-mastercard" style="font-size: 1.5rem; color: var(--gray);"></i>
                                <i class="fab fa-cc-amex" style="font-size: 1.5rem; color: var(--gray);"></i>
                                <i class="fab fa-cc-paypal" style="font-size: 1.5rem; color: var(--gray);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Update quantity
        function updateQuantity(itemId, action) {
            fetch('<?php echo SITE_URL; ?>/api/cart-actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update&item_id=' + itemId + '&direction=' + action
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.removed) {
                            // Item was removed (quantity reached 0)
                            document.querySelector(`tr[data-item-id="${itemId}"]`).remove();
                        } else {
                            // Update quantity display
                            const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                            row.querySelector('.quantity-display').textContent = data.quantity;
                            row.querySelector('.item-total').textContent = '$' + data.item_total;
                        }

                        // Update cart count in navbar
                        const cartCount = document.querySelector('.cart-count');
                        if (data.cart_count > 0) {
                            if (cartCount) {
                                cartCount.textContent = data.cart_count;
                            }
                        } else {
                            location.reload();
                        }

                        // Update summary
                        updateSummary(data.subtotal, data.shipping, data.tax, data.total);
                    } else {
                        alert(data.message || 'Error updating cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        }

        // Remove item
        function removeItem(itemId) {
            if (!confirm('Remove this item from cart?')) return;

            fetch('<?php echo SITE_URL; ?>/api/cart-actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=remove&item_id=' + itemId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`tr[data-item-id="${itemId}"]`).remove();

                        // Update cart count
                        const cartCount = document.querySelector('.cart-count');
                        if (data.cart_count > 0) {
                            if (cartCount) cartCount.textContent = data.cart_count;
                            updateSummary(data.subtotal, data.shipping, data.tax, data.total);
                        } else {
                            location.reload();
                        }
                    } else {
                        alert(data.message || 'Error removing item');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Update summary display
        function updateSummary(subtotal, shipping, tax, total) {
            document.getElementById('subtotal').textContent = '$' + subtotal;
            document.getElementById('shipping').textContent = shipping > 0 ? '$' + shipping : 'FREE';
            document.getElementById('tax').textContent = '$' + tax;
            document.getElementById('total').textContent = '$' + total;
        }

        // Checkout (mock)
        function checkout() {
            alert('Thank you for your order! This is a demo checkout. In a production environment, this would integrate with a payment processor.');
            // In a real app, redirect to payment page
        }
    </script>
</body>

</html>