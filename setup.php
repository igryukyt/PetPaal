<?php
/**
 * PetPal - Database Setup Script
 * Run this once to initialize the database with proper password hashes
 */

$setup_enabled = getenv('ENABLE_SETUP') === 'true';
$is_cli = php_sapi_name() === 'cli';

if (!$setup_enabled && !$is_cli) {
    http_response_code(403);
    die("Forbidden: Setup is disabled. Set ENABLE_SETUP=true env var to enable.");
}

require_once 'config/config.php';

echo "PetPal Database Setup\n";
echo "=====================\n\n";

try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create database
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->exec("USE " . DB_NAME);
    echo "✓ Database '" . DB_NAME . "' created/selected\n";

    // Create tables
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category ENUM('accessories', 'food') NOT NULL,
            image_url VARCHAR(255),
            stock INT DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS hospitals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            address TEXT NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            image_url VARCHAR(255),
            latitude DECIMAL(10,8),
            longitude DECIMAL(11,8),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            hospital_id INT NOT NULL,
            rating INT NOT NULL,
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_cart_item (user_id, product_id)
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS pet_photos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            pet_name VARCHAR(100),
            photo_url VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS health_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            pet_name VARCHAR(100) NOT NULL,
            checkup_date DATE NOT NULL,
            vet_name VARCHAR(100),
            diagnosis TEXT,
            treatment TEXT,
            next_appointment DATE,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    echo "✓ All tables created\n";

    // Insert default user with proper password hash
    $password = password_hash('Pass123', PASSWORD_DEFAULT);

    // Check if rYuk exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['rYuk']);

    if (!$stmt->fetch()) {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        $stmt->execute(['rYuk', 'ryuk@petpal.com', $password, 'rYuk User']);
        echo "✓ User 'rYuk' created (password: Pass123)\n";
    } else {
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$password, 'rYuk']);
        echo "✓ User 'rYuk' password updated to 'Pass123'\n";
    }

    // Insert sample products if empty
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("
            INSERT INTO products (name, description, price, category, image_url) VALUES
            ('Premium Dog Collar', 'Adjustable leather collar with gold buckle.', 29.99, 'accessories', 'https://images.unsplash.com/photo-1599839575945-a9e5af0c3fa5?w=400'),
            ('Cat Scratching Post', 'Multi-level scratching post with plush platforms.', 49.99, 'accessories', 'https://images.unsplash.com/photo-1545249390-6bdfa286032f?w=400'),
            ('Pet Carrier Bag', 'Airline approved soft-sided pet carrier.', 39.99, 'accessories', 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400'),
            ('Interactive Dog Toy', 'Smart puzzle toy that dispenses treats.', 24.99, 'accessories', 'https://images.unsplash.com/photo-1535294435445-d7249524ef2e?w=400'),
            ('Cozy Pet Bed', 'Ultra-soft orthopedic pet bed.', 59.99, 'accessories', 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=400'),
            ('LED Pet Leash', 'Rechargeable LED light-up leash.', 19.99, 'accessories', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400'),
            ('Premium Dog Food', 'All-natural grain-free dog food, 15lb bag.', 54.99, 'food', 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400'),
            ('Organic Cat Food', 'Gourmet organic cat food, 10lb bag.', 44.99, 'food', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400'),
            ('Puppy Training Treats', 'Soft and chewy training treats.', 12.99, 'food', 'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=400'),
            ('Dental Chew Sticks', 'Veterinarian recommended dental chews.', 18.99, 'food', 'https://images.unsplash.com/photo-1601758124510-52d02ddb7cbd?w=400'),
            ('Kitten Milk Replacer', 'Complete nutrition formula for kittens.', 22.99, 'food', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400'),
            ('Senior Dog Vitamins', 'Daily multivitamin for senior dogs.', 29.99, 'food', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400')
        ");
        echo "✓ Sample products inserted\n";
    }

    // Insert sample hospitals if empty
    $stmt = $conn->query("SELECT COUNT(*) FROM hospitals");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("
            INSERT INTO hospitals (name, address, phone, email, image_url) VALUES
            ('PetCare Animal Hospital', '123 Main Street, Downtown, NY 10001', '(555) 123-4567', 'contact@petcare.com', 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=400'),
            ('Happy Paws Veterinary Clinic', '456 Oak Avenue, Westside, NY 10002', '(555) 234-5678', 'info@happypaws.com', 'https://images.unsplash.com/photo-1628009368231-7bb7cfcb0def?w=400'),
            ('City Pet Emergency Center', '789 Emergency Lane, Midtown, NY 10003', '(555) 345-6789', 'emergency@citypet.com', 'https://images.unsplash.com/photo-1612531386530-97286d97c2d2?w=400'),
            ('Sunshine Animal Care', '321 Sunny Road, Eastside, NY 10004', '(555) 456-7890', 'hello@sunshinecare.com', 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400'),
            ('The Pet Wellness Center', '654 Health Boulevard, Uptown, NY 10005', '(555) 567-8901', 'wellness@petwellness.com', 'https://images.unsplash.com/photo-1601758124096-1fd661873b95?w=400'),
            ('Furry Friends Hospital', '987 Cuddle Street, Northside, NY 10006', '(555) 678-9012', 'care@furryfriends.com', 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400')
        ");
        echo "✓ Sample hospitals inserted\n";
    }

    // Insert sample pet photos
    $stmt = $conn->query("SELECT COUNT(*) FROM pet_photos");
    if ($stmt->fetchColumn() == 0) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute(['rYuk']);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $conn->prepare("
                INSERT INTO pet_photos (user_id, pet_name, photo_url, description) VALUES
                (?, 'Max', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400', 'My golden retriever!'),
                (?, 'Whiskers', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400', 'Whiskers taking a nap')
            ");
            $stmt->execute([$user['id'], $user['id']]);
            echo "✓ Sample pet photos inserted\n";
        }
    }

    echo "\n=====================\n";
    echo "Setup Complete!\n";
    echo "=====================\n";
    echo "\nLogin with:\n";
    echo "  Username: rYuk\n";
    echo "  Password: Pass123\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
