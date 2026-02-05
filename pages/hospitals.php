<?php
/**
 * PetPal - Hospitals Page
 * List nearby animal hospitals with ratings
 */

require_once '../config/config.php';

$conn = getDBConnection();

// Fetch all hospitals with ratings
$stmt = $conn->query("
    SELECT hospitals.*, 
           COALESCE(AVG(reviews.rating), 0) as avg_rating,
           COUNT(reviews.id) as review_count
    FROM hospitals
    LEFT JOIN reviews ON hospitals.id = reviews.hospital_id
    GROUP BY hospitals.id
    ORDER BY avg_rating DESC, review_count DESC
");
$hospitals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Find trusted animal hospitals and veterinary clinics near you">
    <title>Animal Hospitals - <?php echo SITE_NAME; ?></title>
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
                <span>Hospitals</span>
            </div>
            <h1>Animal Hospitals</h1>
            <p>Find trusted veterinary care for your beloved pets</p>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                    <?php echo h($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Search Bar (visual only for demo) -->
            <div class="shop-filters">
                <div class="filter-group" style="flex: 1;">
                    <i class="fas fa-search" style="color: var(--gray);"></i>
                    <input type="text" 
                           class="form-control" 
                           placeholder="Search hospitals by name or location..."
                           id="searchInput"
                           style="border: none; box-shadow: none; padding: 10px;">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Sort by:</label>
                    <select class="filter-select" id="sortSelect">
                        <option value="rating">Highest Rated</option>
                        <option value="reviews">Most Reviews</option>
                        <option value="name">Name A-Z</option>
                    </select>
                </div>
            </div>
            
            <!-- Hospitals Grid -->
            <?php if (empty($hospitals)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🏥</div>
                    <h3>No Hospitals Found</h3>
                    <p>Check back soon for hospital listings in your area!</p>
                </div>
            <?php else: ?>
                <div class="grid grid-3" id="hospitalsGrid">
                    <?php foreach ($hospitals as $hospital): ?>
                        <div class="hospital-card" 
                             data-name="<?php echo h(strtolower($hospital['name'])); ?>"
                             data-rating="<?php echo $hospital['avg_rating']; ?>"
                             data-reviews="<?php echo $hospital['review_count']; ?>">
                            <img src="<?php echo h($hospital['image_url']); ?>" 
                                 alt="<?php echo h($hospital['name']); ?>" 
                                 class="hospital-image"
                                 loading="lazy">
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
                                
                                <?php if ($hospital['email']): ?>
                                    <div class="hospital-info">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo h($hospital['email']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="hospital-rating">
                                    <div class="stars">
                                        <?php 
                                        $rating = round($hospital['avg_rating']);
                                        for ($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <i class="fas fa-star" 
                                               style="color: <?php echo $i <= $rating ? '#FDCB6E' : '#DFE6E9'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-count">
                                        <?php echo number_format($hospital['avg_rating'], 1); ?> 
                                        (<?php echo $hospital['review_count']; ?> reviews)
                                    </span>
                                </div>
                                
                                <div class="card-actions" style="margin-top: 15px;">
                                    <a href="<?php echo SITE_URL; ?>/pages/reviews.php?hospital=<?php echo $hospital['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-star"></i>
                                        View Reviews
                                    </a>
                                    <a href="tel:<?php echo h($hospital['phone']); ?>" 
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-phone"></i>
                                        Call
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Emergency Info -->
            <section class="section">
                <div class="card" style="padding: 40px; background: linear-gradient(135deg, #FF6B6B, #E74C3C); color: white;">
                    <div style="display: flex; align-items: center; gap: 30px; flex-wrap: wrap;">
                        <div style="font-size: 4rem;">🚨</div>
                        <div style="flex: 1; min-width: 250px;">
                            <h3 style="color: white; margin-bottom: 10px;">Pet Emergency?</h3>
                            <p style="color: rgba(255,255,255,0.9); margin-bottom: 15px;">
                                If your pet is experiencing a medical emergency, contact the nearest 24-hour veterinary clinic immediately.
                            </p>
                            <a href="tel:555-345-6789" class="btn" style="background: white; color: #E74C3C;">
                                <i class="fas fa-phone-alt"></i>
                                Call Emergency: (555) 345-6789
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.hospital-card').forEach(card => {
                const name = card.dataset.name;
                if (name.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Sort functionality
        document.getElementById('sortSelect').addEventListener('change', function() {
            const grid = document.getElementById('hospitalsGrid');
            const cards = Array.from(grid.querySelectorAll('.hospital-card'));
            
            cards.sort((a, b) => {
                switch (this.value) {
                    case 'rating':
                        return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
                    case 'reviews':
                        return parseInt(b.dataset.reviews) - parseInt(a.dataset.reviews);
                    case 'name':
                        return a.dataset.name.localeCompare(b.dataset.name);
                }
            });
            
            cards.forEach(card => grid.appendChild(card));
        });
    </script>
</body>
</html>
