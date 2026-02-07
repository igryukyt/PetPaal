<?php
/**
 * PetPal - Order Confirmation
 */

require_once '../config/config.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    header('Location: ' . SITE_URL);
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT o.*, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . SITE_URL);
    exit;
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - <?php echo SITE_NAME; ?></title>
    <?php include '../includes/head.php'; ?>
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
        }

        .success-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .success-icon {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 20px;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 30px;
        }

        .detail-group h3 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: var(--dark);
        }

        .detail-content {
            color: var(--gray);
            line-height: 1.6;
        }

        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .order-items-table th {
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid var(--light-gray);
            color: var(--gray);
            font-weight: 600;
        }

        .order-items-table td {
            padding: 15px 10px;
            border-bottom: 1px solid var(--light-gray);
            vertical-align: middle;
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .item-info img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
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
            <div class="confirmation-container">
                <div class="success-header">
                    <i class="fas fa-check-circle success-icon"></i>
                    <h1>Order Confirmed!</h1>
                    <p>Thank you for your purchase. Your order has been placed successfully.</p>
                    <div
                        style="background: var(--light-gray); display: inline-block; padding: 8px 16px; border-radius: 20px; margin-top: 15px; font-family: monospace;">
                        Order #<?php echo h($order['order_number']); ?>
                    </div>
                </div>

                <div class="order-details">
                    <div class="detail-group">
                        <h3>Shipping Address</h3>
                        <div class="detail-content">
                            <?php echo nl2br(h($order['shipping_address'])); ?>
                        </div>
                    </div>
                    <div class="detail-group">
                        <h3>Payment Method</h3>
                        <div class="detail-content">
                            <?php echo ucfirst(h($order['payment_method'])); ?><br>
                            Status: <span class="badge badge-success"><?php echo ucfirst(h($order['status'])); ?></span>
                        </div>
                    </div>
                </div>

                <h3>Order Items</h3>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th style="text-align: right;">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="item-info">
                                        <img src="<?php echo h($item['image_url']); ?>"
                                            alt="<?php echo h($item['name']); ?>">
                                        <span><?php echo h($item['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td style="text-align: right;">
                                    ₹<?php echo number_format($item['price'] * $item['quantity'], 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="max-width: 300px; margin-left: auto;">
                    <div class="summary-row total">
                        <span>Total Paid</span>
                        <span>₹<?php echo number_format($order['total_amount'], 0); ?></span>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 40px;">
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>