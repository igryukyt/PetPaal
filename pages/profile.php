<?php
/**
 * PetPal - User Profile Page
 * Display user information and activity
 */

require_once '../config/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlash('error', 'Please login to view your profile.');
    redirect(SITE_URL . '/pages/login.php');
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Get user statistics
$stats = [
    'reviews' => 0,
    'photos' => 0,
    'health_records' => 0,
    'orders' => 0
];

// Count reviews
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE user_id = ?");
$stmt->execute([$userId]);
$stats['reviews'] = $stmt->fetch()['count'];

// Count photos
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM pet_photos WHERE user_id = ?");
$stmt->execute([$userId]);
$stats['photos'] = $stmt->fetch()['count'];

// Count health records
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM health_records WHERE user_id = ?");
$stmt->execute([$userId]);
$stats['health_records'] = $stmt->fetch()['count'];

// Get recent activity
$stmt = $conn->prepare("
    (SELECT 'review' as type, created_at, CONCAT('Reviewed ', (SELECT name FROM hospitals WHERE id = hospital_id)) as description 
     FROM reviews WHERE user_id = ? ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'photo' as type, created_at, CONCAT('Uploaded photo of ', pet_name) as description 
     FROM pet_photos WHERE user_id = ? ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'health' as type, created_at, CONCAT('Added health record for ', pet_name) as description 
     FROM health_records WHERE user_id = ? ORDER BY created_at DESC LIMIT 3)
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$userId, $userId, $userId]);
$activities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your PetPal profile">
    <title>My Profile -
        <?php echo SITE_NAME; ?>
    </title>
    <?php include '../includes/head.php'; ?>
    <style>
        .profile-header {
            background: var(--gradient-hero);
            padding: 120px 0 80px;
            color: white;
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3rem;
            color: var(--primary);
            box-shadow: var(--shadow-xl);
        }

        .profile-name {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .profile-username {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: -40px;
            margin-bottom: 40px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            text-align: center;
            box-shadow: var(--shadow-lg);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            color: var(--gray-dark);
            margin-top: 5px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .activity-icon.review {
            background: var(--warning);
        }

        .activity-icon.photo {
            background: var(--success);
        }

        .activity-icon.health {
            background: var(--primary);
        }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Profile Header -->
    <header class="profile-header">
        <div class="container">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <h1 class="profile-name">
                <?php echo h($user['full_name']); ?>
            </h1>
            <p class="profile-username">@
                <?php echo h($user['username']); ?>
            </p>
            <p style="color: rgba(255,255,255,0.7); margin-top: 10px;">
                <i class="fas fa-calendar"></i>
                Member since
                <?php echo date('F Y', strtotime($user['created_at'])); ?>
            </p>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo $stats['reviews']; ?>
                    </div>
                    <div class="stat-label">Reviews</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo $stats['photos']; ?>
                    </div>
                    <div class="stat-label">Pet Photos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo $stats['health_records']; ?>
                    </div>
                    <div class="stat-label">Health Records</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo $stats['orders']; ?>
                    </div>
                    <div class="stat-label">Orders</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Account Info -->
                <div class="card" style="padding: 25px;">
                    <h3 style="margin-bottom: 20px;">
                        <i class="fas fa-user" style="color: var(--primary);"></i>
                        Account Information
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo h($user['full_name']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo h($user['username']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo h($user['email']); ?>" readonly>
                    </div>

                    <p class="form-hint">Contact support to update your account information.</p>
                </div>

                <!-- Recent Activity -->
                <div class="card" style="padding: 25px;">
                    <h3 style="margin-bottom: 20px;">
                        <i class="fas fa-history" style="color: var(--secondary);"></i>
                        Recent Activity
                    </h3>

                    <?php if (empty($activities)): ?>
                        <div class="empty-state" style="padding: 30px 0;">
                            <p style="color: var(--gray);">No recent activity</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon <?php echo $activity['type']; ?>">
                                    <i class="fas fa-<?php
                                    echo $activity['type'] === 'review' ? 'star' :
                                        ($activity['type'] === 'photo' ? 'camera' : 'heartbeat');
                                    ?>"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 500;">
                                        <?php echo h($activity['description']); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray);">
                                        <?php echo date('M j, Y', strtotime($activity['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card" style="padding: 25px; margin-top: 30px;">
                <h3 style="margin-bottom: 20px;">Quick Actions</h3>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="<?php echo SITE_URL; ?>/pages/upload-pet.php" class="btn btn-primary">
                        <i class="fas fa-camera"></i>
                        Upload Pet Photo
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/health-tracker.php" class="btn btn-secondary">
                        <i class="fas fa-heartbeat"></i>
                        Health Tracker
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/reviews.php" class="btn btn-secondary">
                        <i class="fas fa-star"></i>
                        Write Review
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn btn-secondary">
                        <i class="fas fa-shopping-bag"></i>
                        Browse Shop
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>