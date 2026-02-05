<?php
/**
 * PetPal - Reviews Page
 * Display and submit hospital reviews
 */

require_once '../config/config.php';

$conn = getDBConnection();

// Get selected hospital ID
$selectedHospitalId = isset($_GET['hospital']) ? intval($_GET['hospital']) : null;

// Fetch all hospitals for dropdown
$hospitalsStmt = $conn->query("SELECT id, name FROM hospitals ORDER BY name");
$hospitals = $hospitalsStmt->fetchAll();

// Fetch reviews with hospital and user info
$reviewQuery = "
    SELECT reviews.*, users.username, users.full_name, hospitals.name as hospital_name
    FROM reviews
    JOIN users ON reviews.user_id = users.id
    JOIN hospitals ON reviews.hospital_id = hospitals.id
";

if ($selectedHospitalId) {
    $reviewQuery .= " WHERE reviews.hospital_id = " . $selectedHospitalId;
}

$reviewQuery .= " ORDER BY reviews.created_at DESC";
$reviewsStmt = $conn->query($reviewQuery);
$reviews = $reviewsStmt->fetchAll();

// Calculate average rating
$avgRating = 0;
$totalRatings = count($reviews);
if ($totalRatings > 0) {
    $avgRating = array_sum(array_column($reviews, 'rating')) / $totalRatings;
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Read and write reviews for animal hospitals">
    <title>Hospital Reviews - <?php echo SITE_NAME; ?></title>
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
                <a href="<?php echo SITE_URL; ?>/pages/hospitals.php">Hospitals</a>
                <i class="fas fa-chevron-right"></i>
                <span>Reviews</span>
            </div>
            <h1>Hospital Reviews</h1>
            <p>See what pet parents are saying about veterinary clinics</p>
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
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
                <!-- Reviews List -->
                <div>
                    <!-- Filter -->
                    <div class="shop-filters" style="margin-bottom: 30px;">
                        <div class="filter-group">
                            <label class="filter-label">Filter by Hospital:</label>
                            <select class="filter-select" id="hospitalFilter" onchange="filterHospital()">
                                <option value="">All Hospitals</option>
                                <?php foreach ($hospitals as $hospital): ?>
                                    <option value="<?php echo $hospital['id']; ?>"
                                            <?php echo $selectedHospitalId == $hospital['id'] ? 'selected' : ''; ?>>
                                        <?php echo h($hospital['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <?php if ($totalRatings > 0): ?>
                            <div class="filter-group" style="margin-left: auto;">
                                <span style="font-size: 1.5rem; font-weight: 600; color: var(--primary);">
                                    <?php echo number_format($avgRating, 1); ?>
                                </span>
                                <div class="stars" style="margin-left: 5px;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color: <?php echo $i <= round($avgRating) ? '#FDCB6E' : '#DFE6E9'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-count" style="margin-left: 5px;">
                                    (<?php echo $totalRatings; ?> reviews)
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reviews -->
                    <?php if (empty($reviews)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📝</div>
                            <h3>No Reviews Yet</h3>
                            <p>Be the first to leave a review!</p>
                        </div>
                    <?php else: ?>
                        <div id="reviewsList">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card" data-hospital="<?php echo $review['hospital_id']; ?>">
                                    <div class="review-header">
                                        <div class="review-avatar">
                                            <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                                        </div>
                                        <div class="review-user">
                                            <div class="review-name"><?php echo h($review['full_name']); ?></div>
                                            <div class="review-date">
                                                <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star" style="color: <?php echo $i <= $review['rating'] ? '#FDCB6E' : '#DFE6E9'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <div style="margin-bottom: 10px;">
                                        <span class="product-category"><?php echo h($review['hospital_name']); ?></span>
                                    </div>
                                    
                                    <p class="review-content"><?php echo nl2br(h($review['comment'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Write Review Form -->
                <div>
                    <div class="card" style="padding: 25px; position: sticky; top: 100px;">
                        <h3 style="margin-bottom: 20px;">Write a Review</h3>
                        
                        <?php if (!isLoggedIn()): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <span>Please <a href="<?php echo SITE_URL; ?>/pages/login.php" style="font-weight: 600;">login</a> to write a review.</span>
                            </div>
                        <?php else: ?>
                            <form id="reviewForm" method="POST" action="<?php echo SITE_URL; ?>/api/submit-review.php">
                                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                                
                                <div class="form-group">
                                    <label class="form-label">Select Hospital</label>
                                    <select name="hospital_id" class="form-control" required>
                                        <option value="">Choose a hospital...</option>
                                        <?php foreach ($hospitals as $hospital): ?>
                                            <option value="<?php echo $hospital['id']; ?>"
                                                    <?php echo $selectedHospitalId == $hospital['id'] ? 'selected' : ''; ?>>
                                                <?php echo h($hospital['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Your Rating</label>
                                    <div class="rating-input">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                                            <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars">★</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Your Review</label>
                                    <textarea name="comment" 
                                              class="form-control" 
                                              rows="5" 
                                              placeholder="Share your experience..."
                                              required
                                              minlength="10"></textarea>
                                    <span class="form-hint">Minimum 10 characters</span>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-paper-plane"></i>
                                    Submit Review
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Filter by hospital
        function filterHospital() {
            const hospitalId = document.getElementById('hospitalFilter').value;
            if (hospitalId) {
                window.location.href = '<?php echo SITE_URL; ?>/pages/reviews.php?hospital=' + hospitalId;
            } else {
                window.location.href = '<?php echo SITE_URL; ?>/pages/reviews.php';
            }
        }
        
        // Form submission
        document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thank you for your review!');
                    location.reload();
                } else {
                    alert(data.message || 'Error submitting review');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
            });
        });
    </script>
    
    <style>
        @media (max-width: 992px) {
            .main-content > .container > div {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</body>
</html>
