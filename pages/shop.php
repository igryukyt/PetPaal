<?php
/**
 * PetPal - Shop Page
 * Display products with category filtering and add to cart
 */

require_once '../config/config.php';

$conn = getDBConnection();

// Get category filter
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$validCategories = ['all', 'accessories', 'food'];
if (!in_array($category, $validCategories)) {
    $category = 'all';
}

// Fetch products
if ($category === 'all') {
    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
    $stmt->execute([$category]);
}
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Shop premium pet accessories and food at PetPal">
    <title>Shop -
        <?php echo SITE_NAME; ?>
    </title>
    <?php include '../includes/head.php'; ?>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>">Home</a>
                <i class="fas fa-chevron-right"></i>
                <span>Shop</span>
            </div>
            <h1>Pet Shop</h1>
            <p>Premium products for your beloved companions</p>
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

            <!-- Category Tabs -->
            <div class="category-tabs">
                <a href="<?php echo SITE_URL; ?>/pages/shop.php"
                    class="category-tab <?php echo $category === 'all' ? 'active' : ''; ?>">
                    All Products
                </a>
                <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=accessories"
                    class="category-tab <?php echo $category === 'accessories' ? 'active' : ''; ?>">
                    <i class="fas fa-bone"></i> Accessories
                </a>
                <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=food"
                    class="category-tab <?php echo $category === 'food' ? 'active' : ''; ?>">
                    <i class="fas fa-drumstick-bite"></i> Food
                </a>
            </div>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🛒</div>
                    <h3>No Products Found</h3>
                    <p>Check back soon for new products!</p>
                </div>
            <?php else: ?>
                <div class="grid grid-4" id="productsGrid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image-wrapper">
                                <img src="<?php echo h($product['image_url']); ?>" alt="<?php echo h($product['name']); ?>"
                                    class="product-image" loading="lazy">
                            </div>
                            <div class="product-body">
                                <span class="product-category">
                                    <?php echo h(ucfirst($product['category'])); ?>
                                </span>
                                <h4 class="product-title">
                                    <?php echo h($product['name']); ?>
                                </h4>
                                <p class="product-description">
                                    <?php echo h($product['description']); ?>
                                </p>
                                <div class="product-footer">
                                    <span class="product-price">
                                        $
                                        <?php echo number_format($product['price'], 2); ?>
                                    </span>
                                    <button class="btn btn-sm btn-primary add-to-cart"
                                        data-product-id="<?php echo $product['id']; ?>" title="Add to Cart">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Shop Info -->
            <section class="section">
                <div class="grid grid-3">
                    <div class="card" style="text-align: center; padding: 30px;">
                        <i class="fas fa-truck"
                            style="font-size: 2.5rem; color: var(--primary); margin-bottom: 15px;"></i>
                        <h4>Free Shipping</h4>
                        <p style="margin: 0;">On orders over $50</p>
                    </div>
                    <div class="card" style="text-align: center; padding: 30px;">
                        <i class="fas fa-undo"
                            style="font-size: 2.5rem; color: var(--primary); margin-bottom: 15px;"></i>
                        <h4>Easy Returns</h4>
                        <p style="margin: 0;">30-day return policy</p>
                    </div>
                    <div class="card" style="text-align: center; padding: 30px;">
                        <i class="fas fa-headset"
                            style="font-size: 2.5rem; color: var(--primary); margin-bottom: 15px;"></i>
                        <h4>24/7 Support</h4>
                        <p style="margin: 0;">Always here to help</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.dataset.productId;
                const btn = this;
                
                <?php if (!isLoggedIn()): ?>
                        if (confirm('Please login to add items to cart. Go to login page?')) {
                        window.location.href = '<?php echo SITE_URL; ?>/pages/login.php';
                    }
                    return;
                <?php endif; ?>

                    // Disable button and show loading
                    btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                // Send AJAX request
                fetch('<?php echo SITE_URL; ?>/api/cart-actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=add&product_id=' + productId
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update cart count
                            const cartCount = document.querySelector('.cart-count');
                            if (cartCount) {
                                cartCount.textContent = data.cart_count;
                            } else {
                                const cartIcon = document.querySelector('.cart-icon');
                                const newCount = document.createElement('span');
                                newCount.className = 'cart-count';
                                newCount.textContent = data.cart_count;
                                cartIcon.appendChild(newCount);
                            }

                            // Show success
                            btn.innerHTML = '<i class="fas fa-check"></i>';
                            btn.classList.remove('btn-primary');
                            btn.classList.add('btn-success');

                            setTimeout(() => {
                                btn.innerHTML = '<i class="fas fa-cart-plus"></i>';
                                btn.classList.remove('btn-success');
                                btn.classList.add('btn-primary');
                                btn.disabled = false;
                            }, 1500);
                        } else {
                            alert(data.message || 'Error adding to cart');
                            btn.innerHTML = '<i class="fas fa-cart-plus"></i>';
                            btn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                        btn.innerHTML = '<i class="fas fa-cart-plus"></i>';
                        btn.disabled = false;
                    });
            });
        });
    </script>
</body>

</html>