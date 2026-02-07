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

    // Insert sample products if empty (prices in Indian Rupees ₹)
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("
            INSERT INTO products (name, description, price, category, image_url) VALUES
            ('Premium Dog Collar', 'Adjustable leather collar with gold buckle.', 1499, 'accessories', 'https://images.unsplash.com/photo-1599839575945-a9e5af0c3fa5?w=400'),
            ('Cat Scratching Post', 'Multi-level scratching post with plush platforms.', 2499, 'accessories', 'https://images.unsplash.com/photo-1545249390-6bdfa286032f?w=400'),
            ('Pet Carrier Bag', 'Airline approved soft-sided pet carrier.', 1999, 'accessories', 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400'),
            ('Interactive Dog Toy', 'Smart puzzle toy that dispenses treats.', 999, 'accessories', 'https://images.unsplash.com/photo-1535294435445-d7249524ef2e?w=400'),
            ('Cozy Pet Bed', 'Ultra-soft orthopedic pet bed.', 2999, 'accessories', 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=400'),
            ('LED Pet Leash', 'Rechargeable LED light-up leash.', 799, 'accessories', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400'),
            ('Premium Dog Food', 'All-natural grain-free dog food, 7kg bag.', 2799, 'food', 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400'),
            ('Organic Cat Food', 'Gourmet organic cat food, 5kg bag.', 1899, 'food', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400'),
            ('Puppy Training Treats', 'Soft and chewy training treats.', 499, 'food', 'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=400'),
            ('Dental Chew Sticks', 'Veterinarian recommended dental chews.', 699, 'food', 'https://images.unsplash.com/photo-1601758124510-52d02ddb7cbd?w=400'),
            ('Kitten Milk Replacer', 'Complete nutrition formula for kittens.', 899, 'food', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400'),
            ('Senior Dog Vitamins', 'Daily multivitamin for senior dogs.', 1299, 'food', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400')
        ");
        echo "✓ Sample products inserted (prices in ₹)\\n";
    }

    // Insert sample hospitals if empty (Indian veterinary hospitals)
    $stmt = $conn->query("SELECT COUNT(*) FROM hospitals");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("
            INSERT INTO hospitals (name, address, phone, email, image_url) VALUES
            ('Crown Vet - Worli', 'Ground Floor, Atur House 87, Dr. Annie Besant Road, Worli Naka, Mumbai 400018', '+91-8062744100', 'contact@crown.vet', 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=400'),
            ('Cessna Lifeline Veterinary Hospital', 'HBCS, 148, KGA Rd, Amarjyoti Layout, Domlur, Bangalore 560071', '+91-7676365365', 'woof@cessnalifeline.com', 'https://images.unsplash.com/photo-1628009368231-7bb7cfcb0def?w=400'),
            ('Sanjay Gandhi Animal Care Centre', 'Raja Garden, New Delhi 110015', '+91-11-25447751', 'info@sgacc.org.in', 'https://images.unsplash.com/photo-1612531386530-97286d97c2d2?w=400'),
            ('Superpets Veterinary Hospital', 'Me-Me Tower, 8th Road, Khar West, Mumbai 400052', '+91-9820012345', 'care@superpets.in', 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400'),
            ('Jeeva Pet Hospital', '1360, 9th Cross Rd, J.P. Nagar 1st Phase, Bangalore 560078', '+91-80-26493939', 'hello@jeevapet.com', 'https://images.unsplash.com/photo-1601758124096-1fd661873b95?w=400'),
            ('Max Petz', 'Mehar Estate, Dr E Moses Road, Mahalaxmi, Mumbai 400011', '+91-22-24934567', 'info@maxpetz.com', 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400')
        ");
        echo "✓ Sample hospitals inserted (Indian locations)\\n";
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
