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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed -
        <?php echo SITE_NAME; ?>
    </title>
    <?php include '../includes/head.php'; ?>
    <style>
        .confirmation-container {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            max-width: 600px;
            margin: 0 auto;
        }

        .success-icon {
            font-size: 5rem;
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

        .order-id {
            background: var(--light-gray);
            padding: 10px 20px;
            border-radius: 50px;
            font-family: monospace;
            font-size: 1.1rem;
            margin: 20px 0;
            display: inline-block;
        }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="confirmation-container">
                <i class="fas fa-check-circle success-icon"></i>
                <h1>Order Confirmed!</h1>
                <p>Thank you for your purchase. Your order has been placed successfully.</p>

                <div class="order-id">
                    Order #
                    <?php echo strtoupper(uniqid('ORD-')); ?>
                </div>

                <p style="color: var(--gray); margin-bottom: 30px;">
                    We've sent a confirmation email to <strong>
                        <?php echo h(getCurrentUser()['email']); ?>
                    </strong> with your order details.
                </p>

                <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg">
                    Continue Shopping
                </a>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>