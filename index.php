<?php
/**
 * PetPal - Home Page
 * Main landing page with hero section and featured content
 */

require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="PetPal - Your trusted companion for pet care, accessories, and veterinary connections">
    <title><?php echo SITE_NAME; ?> - Your Pet's Best Friend</title>
    <?php include 'includes/head.php'; ?>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="floating-paws">
            <?php for ($i = 0; $i < 10; $i++): ?>
                <span class="paw"
                    style="left: <?php echo rand(0, 100); ?>%; animation-delay: <?php echo rand(0, 15); ?>s;">🐾</span>
            <?php endfor; ?>
        </div>

        <div class="container">
            <div class="hero-content">
                <h1>Give Your Pet the Love They Deserve</h1>
                <p>
                    Discover premium pet products, connect with trusted veterinarians,
                    and get expert care tips all in one place. Join thousands of happy
                    pet parents who trust PetPal.
                </p>
                <div class="hero-buttons">
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i>
                        Shop Now
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/hospitals.php" class="btn btn-outline btn-lg">
                        <i class="fas fa-hospital"></i>
                        Find Vets
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600&h=500&fit=crop"
                    alt="Happy Indian Dog" loading="eager">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose PetPal?</h2>
                <p>Everything your furry friend needs, all in one place</p>
            </div>

            <div class="grid grid-4">
                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h4 class="tip-title">Premium Products</h4>
                    <p class="tip-description">
                        Quality accessories and nutritious food from trusted brands
                    </p>
                </div>

                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <h4 class="tip-title">Trusted Hospitals</h4>
                    <p class="tip-description">
                        Connect with verified veterinary clinics near you
                    </p>
                </div>

                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4 class="tip-title">Expert Care Tips</h4>
                    <p class="tip-description">
                        Professional advice to keep your pets healthy and happy
                    </p>
                </div>

                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="tip-title">Pet Community</h4>
                    <p class="tip-description">
                        Share photos and connect with fellow pet lovers
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Featured Products</h2>
                <p>Top picks for your beloved companions</p>
            </div>

            <?php
            // Fetch featured products
            $products = [];
            try {
                $conn = getDBConnection();
                $stmt = $conn->query("SELECT * FROM products ORDER BY RAND() LIMIT 4");
                $products = $stmt->fetchAll();
            } catch (Exception $e) {
                // Products will be empty, section will show no products
            }
            ?>

            <div class="grid grid-4">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image-wrapper">
                            <img src="<?php echo h($product['image_url']); ?>" alt="<?php echo h($product['name']); ?>"
                                class="product-image" loading="lazy">
                        </div>
                        <div class="product-body">
                            <span class="product-category"><?php echo h($product['category']); ?></span>
                            <h4 class="product-title"><?php echo h($product['name']); ?></h4>
                            <p class="product-description"><?php echo h($product['description']); ?></p>
                            <div class="product-footer">
                                <span class="product-price">₹<?php echo number_format($product['price'], 0); ?></span>
                                <button class="btn btn-sm btn-primary add-to-cart"
                                    data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn btn-primary btn-lg">
                    View All Products
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Pet Gallery Preview -->
    <section class="section bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Pet Gallery</h2>
                <p>Meet our adorable community members</p>
            </div>

            <?php
            // Fetch recent pet photos
            $photos = [];
            try {
                $stmt = $conn->query("SELECT pet_photos.*, users.username FROM pet_photos 
                                      JOIN users ON pet_photos.user_id = users.id 
                                      ORDER BY pet_photos.created_at DESC LIMIT 4");
                $photos = $stmt->fetchAll();
            } catch (Exception $e) {
                // Photos will be empty
            }
            ?>

            <div class="gallery-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="gallery-item">
                        <img src="<?php echo h($photo['photo_url']); ?>" alt="<?php echo h($photo['pet_name']); ?>"
                            loading="lazy">
                        <div class="gallery-overlay">
                            <h4><?php echo h($photo['pet_name']); ?></h4>
                            <p>by <?php echo h($photo['username']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/pages/upload-pet.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-camera"></i>
                    Share Your Pet
                </a>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section" style="background: var(--gradient-hero); color: white;">
        <div class="container">
            <div class="grid grid-4" style="text-align: center;">
                <div>
                    <h2 style="color: white; font-size: 3rem; margin-bottom: 10px;">5000+</h2>
                    <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem;">Happy Pet Parents</p>
                </div>
                <div>
                    <h2 style="color: white; font-size: 3rem; margin-bottom: 10px;">200+</h2>
                    <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem;">Premium Products</p>
                </div>
                <div>
                    <h2 style="color: white; font-size: 3rem; margin-bottom: 10px;">50+</h2>
                    <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem;">Partner Hospitals</p>
                </div>
                <div>
                    <h2 style="color: white; font-size: 3rem; margin-bottom: 10px;">24/7</h2>
                    <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem;">Customer Support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Top Rated Hospitals -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Top Rated Hospitals</h2>
                <p>Trusted veterinary care for your pets</p>
            </div>

            <?php
            // Fetch top rated hospitals
            $hospitals = [];
            try {
                $stmt = $conn->query("
                    SELECT hospitals.*, 
                           COALESCE(AVG(reviews.rating), 0) as avg_rating,
                           COUNT(reviews.id) as review_count
                    FROM hospitals
                    LEFT JOIN reviews ON hospitals.id = reviews.hospital_id
                    GROUP BY hospitals.id
                    ORDER BY avg_rating DESC, review_count DESC
                    LIMIT 3
                ");
                $hospitals = $stmt->fetchAll();
            } catch (Exception $e) {
                // Hospitals will be empty
            }
            ?>

            <div class="grid grid-3">
                <?php foreach ($hospitals as $hospital): ?>
                    <div class="hospital-card">
                        <img src="<?php echo h($hospital['image_url']); ?>" alt="<?php echo h($hospital['name']); ?>"
                            class="hospital-image" loading="lazy">
                        <div class="hospital-body">
                            <h4 class="hospital-name"><?php echo h($hospital['name']); ?></h4>
                            <div class="hospital-info">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo h($hospital['address']); ?></span>
                            </div>
                            <div class="hospital-info">
                                <i class="fas fa-phone"></i>
                                <span><?php echo h($hospital['phone']); ?></span>
                            </div>
                            <div class="hospital-rating">
                                <div class="stars">
                                    <?php
                                    $rating = round($hospital['avg_rating']);
                                    for ($i = 1; $i <= 5; $i++):
                                        ?>
                                        <i class="fas fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"
                                            style="color: <?php echo $i <= $rating ? '#FDCB6E' : '#DFE6E9'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-count">
                                    <?php echo number_format($hospital['avg_rating'], 1); ?>
                                    (<?php echo $hospital['review_count']; ?> reviews)
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/pages/hospitals.php" class="btn btn-primary btn-lg">
                    View All Hospitals
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="card" style="max-width: 700px; margin: 0 auto; text-align: center; padding: 50px;">
                <h3 style="margin-bottom: 15px;">Stay Updated!</h3>
                <p style="margin-bottom: 25px;">Subscribe to get the latest pet care tips and exclusive offers.</p>
                <form style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">
                    <input type="email" class="form-control" placeholder="Enter your email" style="max-width: 350px;">
                    <button type="submit" class="btn btn-primary">
                        Subscribe
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>

</html>