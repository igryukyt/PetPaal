<?php
/**
 * PetPal - Checkout Page
 * Demo checkout process
 */

require_once '../config/config.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please login to checkout.'];
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

// Get cart items
$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.image_url, p.category 
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
$shipping = $subtotal > 2000 ? 0 : 99;
$tax = $subtotal * 0.18; // 18% GST
$total = $subtotal + $shipping + $tax;

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout -
        <?php echo SITE_NAME; ?>
    </title>
    <?php include '../includes/head.php'; ?>
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }

        .checkout-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 20px;
        }

        .checkout-header {
            border-bottom: 2px solid var(--light-gray);
            padding-bottom: 15px;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .payment-methods {
            display: grid;
            gap: 15px;
        }

        .payment-method {
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .payment-method.active {
            border-color: var(--primary);
            background-color: var(--light-gray);
        }

        .order-summary-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--light-gray);
        }

        .order-summary-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        .summary-totals {
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: var(--gray);
        }

        .summary-row.total {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            border-top: 2px solid var(--light-gray);
            padding-top: 15px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/pages/cart.php">Cart</a>
                <i class="fas fa-chevron-right"></i>
                <span>Checkout</span>
            </div>

            <div class="checkout-container">
                <!-- Checkout Form -->
                <form action="process-order.php" method="POST" id="checkoutForm">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">

                    <div class="checkout-section">
                        <div class="checkout-header">
                            <i class="fas fa-map-marker-alt"></i> Shipping Address
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" required
                                    value="<?php echo h(explode(' ', getCurrentUser()['full_name'])[0]); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" required
                                placeholder="House No, Street Name">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Postcode</label>
                                <input type="text" class="form-control" name="postcode" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" required placeholder="+91 98765 43210">
                        </div>
                    </div>

                    <div class="checkout-section">
                        <div class="checkout-header">
                            <i class="fas fa-credit-card"></i> Payment Method
                        </div>

                        <div class="payment-methods">
                            <label class="payment-method active">
                                <input type="radio" name="payment_method" value="card" checked>
                                <i class="fas fa-credit-card fa-lg"></i>
                                <div>
                                    <strong>Credit / Debit Card</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Safe money transfer using your
                                        bank account</div>
                                </div>
                            </label>

                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="upi">
                                <i class="fas fa-mobile-alt fa-lg"></i>
                                <div>
                                    <strong>UPI / GPay / PhonePe</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Pay instantly via UPI App</div>
                                </div>
                            </label>

                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cod">
                                <i class="fas fa-money-bill-wave fa-lg"></i>
                                <div>
                                    <strong>Cash on Delivery</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Pay correctly at your doorstep
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Card Details (Fake) -->
                        <div id="cardDetails" style="margin-top: 20px;">
                            <div class="form-group">
                                <label class="form-label">Card Number</label>
                                <input type="text" class="form-control" placeholder="0000 0000 0000 0000"
                                    maxlength="19">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Expiry</label>
                                    <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">CVV</label>
                                    <input type="password" class="form-control" placeholder="123" maxlength="3">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Pay ₹
                        <?php echo number_format($total, 0); ?>
                    </button>
                    <p class="text-center" style="margin-top: 15px; font-size: 0.9rem; color: var(--gray);">
                        <i class="fas fa-lock"></i> Payments are secure and encrypted
                    </p>
                </form>

                <!-- Order Summary -->
                <div class="checkout-sidebar">
                    <div class="checkout-section">
                        <div class="checkout-header">Order Summary</div>

                        <div class="order-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="order-summary-item">
                                    <img src="<?php echo h($item['image_url']); ?>" alt="<?php echo h($item['name']); ?>">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 4px;">
                                            <?php echo h($item['name']); ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--gray);">
                                            Qty:
                                            <?php echo $item['quantity']; ?>
                                        </div>
                                    </div>
                                    <div style="font-weight: 600;">
                                        ₹
                                        <?php echo number_format($item['price'] * $item['quantity'], 0); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-totals">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>₹
                                    <?php echo number_format($subtotal, 0); ?>
                                </span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span>
                                    <?php echo $shipping > 0 ? '₹' . number_format($shipping, 0) : 'FREE'; ?>
                                </span>
                            </div>
                            <div class="summary-row">
                                <span>GST (18%)</span>
                                <span>₹
                                    <?php echo number_format($tax, 0); ?>
                                </span>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>₹
                                    <?php echo number_format($total, 0); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Simple toggle for payment methods
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const cardDetails = document.getElementById('cardDetails');

        paymentMethods.forEach(method => {
            method.addEventListener('change', (e) => {
                document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
                e.target.closest('.payment-method').classList.add('active');

                if (e.target.value === 'card') {
                    cardDetails.style.display = 'block';
                } else {
                    cardDetails.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>