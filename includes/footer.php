<?php
/**
 * PetPal Footer Component
 * Site-wide footer with links and contact information
 */
?>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Section -->
            <div class="footer-column">
                <a href="<?php echo SITE_URL; ?>" class="footer-brand">
                    <span class="logo-icon">🐾</span>
                    <span>
                        <?php echo SITE_NAME; ?>
                    </span>
                </a>
                <p class="footer-description">
                    Your trusted companion for all things pet care. We provide the best products,
                    trusted hospital connections, and expert care tips for your furry friends.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h4 class="footer-title">Quick Links</h4>
                <div class="footer-links">
                    <a href="<?php echo SITE_URL; ?>">Home</a>
                    <a href="<?php echo SITE_URL; ?>/pages/care-tips.php">Care Tips</a>
                    <a href="<?php echo SITE_URL; ?>/pages/hospitals.php">Find Hospitals</a>
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php">Shop</a>
                    <a href="<?php echo SITE_URL; ?>/pages/reviews.php">Reviews</a>
                </div>
            </div>

            <!-- Services -->
            <div class="footer-column">
                <h4 class="footer-title">Our Services</h4>
                <div class="footer-links">
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=accessories">Pet Accessories</a>
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=food">Pet Food</a>
                    <a href="<?php echo SITE_URL; ?>/pages/upload-pet.php">Pet Gallery</a>
                    <a href="<?php echo SITE_URL; ?>/pages/health-tracker.php">Health Tracker</a>
                    <a href="<?php echo SITE_URL; ?>/pages/hospitals.php">Veterinary Care</a>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="footer-column">
                <h4 class="footer-title">Contact Us</h4>
                <div class="footer-contact">
                    <p>
                        <i class="fas fa-map-marker-alt"></i>
                        123 Pet Street, Animal City, AC 12345
                    </p>
                    <p>
                        <i class="fas fa-phone"></i>
                        (555) 123-4567
                    </p>
                    <p>
                        <i class="fas fa-envelope"></i>
                        support@petpal.com
                    </p>
                    <p>
                        <i class="fas fa-clock"></i>
                        Mon - Fri: 9:00 AM - 6:00 PM
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy;
                <?php echo date('Y'); ?>
                <?php echo SITE_NAME; ?>. All rights reserved. | Made with ❤️ for pet lovers
            </p>
        </div>
    </div>
</footer>

<!-- Site URL for JavaScript -->
<script>
    window.SITE_URL = '<?php echo SITE_URL; ?>';
</script>

<!-- Main JavaScript -->
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>