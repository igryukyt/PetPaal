<?php
/**
 * PetPal - Care Tips Page
 * Display pet health and care tips
 */

require_once '../config/config.php';

// Pet care tips data
$tips = [
    [
        'icon' => 'fas fa-heartbeat',
        'title' => 'Regular Vet Checkups',
        'description' => 'Schedule annual wellness exams for your pet. Regular checkups help detect health issues early and keep vaccinations up to date. Puppies and kittens may need more frequent visits during their first year.',
        'category' => 'Health'
    ],
    [
        'icon' => 'fas fa-bone',
        'title' => 'Balanced Nutrition',
        'description' => 'Feed your pet a balanced diet appropriate for their age, size, and activity level. Avoid feeding table scraps, as many human foods can be harmful to pets. Always provide fresh, clean water.',
        'category' => 'Nutrition'
    ],
    [
        'icon' => 'fas fa-running',
        'title' => 'Daily Exercise',
        'description' => 'Dogs need daily walks and playtime. Even cats benefit from interactive play sessions. Regular exercise helps maintain a healthy weight and provides mental stimulation.',
        'category' => 'Exercise'
    ],
    [
        'icon' => 'fas fa-tooth',
        'title' => 'Dental Care',
        'description' => 'Brush your pet\'s teeth regularly and provide dental chews. Poor dental health can lead to serious health issues. Schedule professional cleanings as recommended by your vet.',
        'category' => 'Hygiene'
    ],
    [
        'icon' => 'fas fa-cut',
        'title' => 'Grooming Routine',
        'description' => 'Regular grooming keeps your pet\'s coat healthy and reduces shedding. It\'s also a great opportunity to check for lumps, bumps, or skin issues. Trim nails regularly to prevent discomfort.',
        'category' => 'Hygiene'
    ],
    [
        'icon' => 'fas fa-shield-alt',
        'title' => 'Parasite Prevention',
        'description' => 'Use year-round flea, tick, and heartworm prevention. Parasites can cause serious health problems. Consult your vet for the best prevention options for your pet.',
        'category' => 'Health'
    ],
    [
        'icon' => 'fas fa-brain',
        'title' => 'Mental Stimulation',
        'description' => 'Provide puzzle toys, training sessions, and new experiences. Mental enrichment is just as important as physical exercise. It helps prevent boredom and destructive behaviors.',
        'category' => 'Behavior'
    ],
    [
        'icon' => 'fas fa-home',
        'title' => 'Safe Environment',
        'description' => 'Pet-proof your home by securing toxic substances, small objects, and electrical cords. Ensure your pet has a comfortable, quiet space to rest and feel safe.',
        'category' => 'Safety'
    ],
    [
        'icon' => 'fas fa-id-card',
        'title' => 'Identification',
        'description' => 'Keep your pet\'s ID tags updated and consider microchipping. If your pet gets lost, proper identification greatly increases the chances of being reunited.',
        'category' => 'Safety'
    ],
    [
        'icon' => 'fas fa-sun',
        'title' => 'Weather Awareness',
        'description' => 'Protect pets from extreme temperatures. Never leave pets in hot cars. Provide shade and water in summer, and warm shelter in winter. Watch for signs of heatstroke or hypothermia.',
        'category' => 'Safety'
    ],
    [
        'icon' => 'fas fa-users',
        'title' => 'Socialization',
        'description' => 'Expose your pet to different people, animals, and environments from a young age. Proper socialization helps prevent fear and aggression. Continue positive social experiences throughout their life.',
        'category' => 'Behavior'
    ],
    [
        'icon' => 'fas fa-weight',
        'title' => 'Weight Management',
        'description' => 'Maintain a healthy weight for your pet. Obesity can lead to diabetes, joint problems, and other health issues. Monitor food portions and limit treats to 10% of daily calories.',
        'category' => 'Nutrition'
    ]
];

// Categories for filtering
$categories = ['All', 'Health', 'Nutrition', 'Exercise', 'Hygiene', 'Behavior', 'Safety'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Expert pet care tips and advice to keep your furry friends healthy and happy">
    <title>Pet Care Tips -
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
                <span>Care Tips</span>
            </div>
            <h1>Pet Care Tips</h1>
            <p>Expert advice to keep your furry friends healthy, happy, and thriving</p>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <!-- Category Filters -->
            <div class="category-tabs" id="categoryTabs">
                <?php foreach ($categories as $category): ?>
                    <button class="category-tab <?php echo $category === 'All' ? 'active' : ''; ?>"
                        data-category="<?php echo $category; ?>">
                        <?php echo $category; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Tips Grid -->
            <div class="grid grid-3" id="tipsGrid">
                <?php foreach ($tips as $tip): ?>
                    <div class="tip-card" data-category="<?php echo $tip['category']; ?>">
                        <div class="tip-icon">
                            <i class="<?php echo $tip['icon']; ?>"></i>
                        </div>
                        <span class="product-category" style="margin-bottom: 10px; display: inline-block;">
                            <?php echo h($tip['category']); ?>
                        </span>
                        <h4 class="tip-title">
                            <?php echo h($tip['title']); ?>
                        </h4>
                        <p class="tip-description">
                            <?php echo h($tip['description']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Additional Resources -->
            <section class="section" style="padding-bottom: 0;">
                <div class="card" style="padding: 40px; text-align: center;">
                    <h3 style="margin-bottom: 15px;">Need Professional Advice?</h3>
                    <p style="margin-bottom: 25px; max-width: 600px; margin-left: auto; margin-right: auto;">
                        Connect with verified veterinary professionals in your area for personalized care
                        recommendations.
                    </p>
                    <a href="<?php echo SITE_URL; ?>/pages/hospitals.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-hospital"></i>
                        Find a Veterinarian
                    </a>
                </div>
            </section>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Category filtering
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                // Update active tab
                document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const category = this.dataset.category;

                // Filter tips
                document.querySelectorAll('.tip-card').forEach(card => {
                    if (category === 'All' || card.dataset.category === category) {
                        card.style.display = 'block';
                        card.style.animation = 'fadeInUp 0.5s ease';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>