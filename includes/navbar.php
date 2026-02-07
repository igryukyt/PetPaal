<?php
/**
 * PetPal Navigation Bar Component
 * Sticky responsive navigation with mobile menu
 */

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get cart count if user is logged in
$cartCount = 0;
if (isLoggedIn()) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $cartCount = $result['count'] ?? 0;
    } catch (Exception $e) {
        // Silently fail - cart count will show 0
        $cartCount = 0;
    }
}
?>

<nav class="navbar" id="navbar">
    <div class="container">
        <!-- Brand Logo -->
        <a href="<?php echo SITE_URL; ?>" class="navbar-brand">
            <span class="logo-icon">🐾</span>
            <span>
                <?php echo SITE_NAME; ?>
            </span>
        </a>

        <!-- Navigation Menu -->
        <ul class="nav-menu" id="navMenu">
            <li>
                <a href="<?php echo SITE_URL; ?>"
                    class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                    Home
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/pages/care-tips.php"
                    class="nav-link <?php echo $currentPage === 'care-tips' ? 'active' : ''; ?>">
                    Care Tips
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/pages/hospitals.php"
                    class="nav-link <?php echo $currentPage === 'hospitals' ? 'active' : ''; ?>">
                    Hospitals
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/pages/shop.php"
                    class="nav-link <?php echo $currentPage === 'shop' ? 'active' : ''; ?>">
                    Shop
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/pages/upload-pet.php"
                    class="nav-link <?php echo $currentPage === 'upload-pet' ? 'active' : ''; ?>">
                    Upload Pet
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/pages/reviews.php"
                    class="nav-link <?php echo $currentPage === 'reviews' ? 'active' : ''; ?>">
                    Reviews
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/pages/health-tracker.php"
                    class="nav-link <?php echo $currentPage === 'health-tracker' ? 'active' : ''; ?>">
                    Health Tracker
                </a>
            </li>
        </ul>

        <!-- Navigation Actions -->
        <div class="nav-actions">
            <!-- Cart Icon -->
            <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="cart-icon" title="Shopping Cart">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-count">
                        <?php echo $cartCount; ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Auth Buttons -->
            <?php if (isLoggedIn()): ?>
                <?php $user = getCurrentUser(); ?>
                <div class="user-dropdown">
                    <a href="<?php echo SITE_URL; ?>/pages/profile.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-user"></i>
                        <?php echo h($user['username']); ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn btn-sm btn-secondary">
                    Login
                </a>
                <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn btn-sm btn-primary">
                    Sign Up
                </a>
            <?php endif; ?>

            <!-- Mobile Toggle -->
            <div class="mobile-toggle" id="mobileToggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</nav>

<script>
    // Toggle mobile menu
    function toggleMobileMenu() {
        const navMenu = document.getElementById('navMenu');
        const toggle = document.getElementById('mobileToggle');
        navMenu.classList.toggle('active');
        toggle.classList.toggle('active');
    }

    // Navbar scroll effect
    window.addEventListener('scroll', function () {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function (event) {
        const navMenu = document.getElementById('navMenu');
        const toggle = document.getElementById('mobileToggle');

        if (!event.target.closest('.nav-menu') && !event.target.closest('.mobile-toggle')) {
            navMenu.classList.remove('active');
            toggle.classList.remove('active');
        }
    });
</script>