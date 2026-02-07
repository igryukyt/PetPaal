<?php
/**
 * PetPal - Database Setup Script
 * Run this once to initialize the database with proper password hashes
 * Set FORCE_RESET=true to clear existing data and reload with fresh Indian data
 */

$setup_enabled = getenv('ENABLE_SETUP') === 'true';
$force_reset = getenv('FORCE_RESET') === 'true' || isset($_GET['reset']);
$is_cli = php_sapi_name() === 'cli';

if (!$setup_enabled && !$is_cli) {
    http_response_code(403);
    die("Forbidden: Setup is disabled. Set ENABLE_SETUP=true env var to enable.");
}

require_once 'config/config.php';

echo "PetPal Database Setup\n";
echo "=====================\n\n";

// Debug: Show connection info (hide password)
echo "DEBUG: Host=" . DB_HOST . ", Port=" . DB_PORT . ", DB=" . DB_NAME . ", User=" . DB_USER . "\n";
echo "FORCE_RESET: " . ($force_reset ? "YES - Will clear old data" : "NO") . "\n\n";

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
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_number VARCHAR(20) NOT NULL UNIQUE,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            payment_method VARCHAR(50),
            shipping_address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
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

    // FORCE_RESET: Clear existing data if enabled
    if ($force_reset) {
        echo "🔄 FORCE_RESET enabled - Clearing old data...\n";
        $conn->exec("DELETE FROM pet_photos");
        $conn->exec("DELETE FROM reviews");
        $conn->exec("DELETE FROM order_items");
        $conn->exec("DELETE FROM orders");
        $conn->exec("DELETE FROM cart");
        $conn->exec("DELETE FROM products");
        $conn->exec("DELETE FROM hospitals");
        echo "✓ Old data cleared\n";
    }

    // Insert sample products - Indian Dog Medicines & Treatments (prices in ₹)
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0 || $force_reset) {
        if ($force_reset) {
            $conn->exec("DELETE FROM products");
        }
        $conn->exec("
            INSERT INTO products (name, description, price, category, image_url) VALUES
            ('Drontal Plus Dewormer', 'Bayer broad-spectrum dewormer for dogs. Treats roundworms, hookworms, whipworms & tapeworms.', 450, 'food', 'https://m.media-amazon.com/images/I/61gXnLCbURL._SL1500_.jpg'),
            ('Simparica Trio', 'Monthly chewable tablet for dogs. Protection against fleas, ticks, heartworm & intestinal worms.', 899, 'food', 'https://m.media-amazon.com/images/I/71UgYN9eURL._SL1500_.jpg'),
            ('Megavac 7 Vaccine', 'Indian Immunologicals 7-in-1 vaccine for Distemper, Hepatitis, Parvo, Parainfluenza & Leptospirosis.', 350, 'food', 'https://m.media-amazon.com/images/I/51QhDZxHURL._SL1000_.jpg'),
            ('Kiwof Plus Tablet', 'Savavet dewormer tablet for dogs. Effective against all major intestinal worms.', 180, 'food', 'https://m.media-amazon.com/images/I/61fXnZZnURL._SL1500_.jpg'),
            ('Fiprofort Plus Spot-On', 'Tick and flea treatment spot-on solution for dogs. 1 month protection.', 299, 'food', 'https://m.media-amazon.com/images/I/61lBnZBo7qL._SL1500_.jpg'),
            ('Drools Optimum Performance', 'Premium Indian dog food with chicken & egg. High protein formula, 10kg bag.', 2199, 'food', 'https://m.media-amazon.com/images/I/71Q8pGan5TL._SL1500_.jpg'),
            ('Pedigree Adult Dog Food', 'Complete & balanced nutrition for adult dogs. Chicken & vegetables, 10kg.', 1899, 'food', 'https://m.media-amazon.com/images/I/71aGsg3flhL._SL1500_.jpg'),
            ('Himalaya Erina-EP Shampoo', 'Anti-tick & flea shampoo for dogs. Made in India with natural ingredients.', 245, 'accessories', 'https://m.media-amazon.com/images/I/61vqNXGaURL._SL1500_.jpg'),
            ('Virbac Nutrich Supplement', 'Multivitamin & mineral supplement for dogs. Supports overall health.', 549, 'food', 'https://m.media-amazon.com/images/I/61YqZEb2URL._SL1200_.jpg'),
            ('Adjustable Dog Collar', 'Premium quality nylon collar with quick-release buckle. Made in India.', 349, 'accessories', 'https://m.media-amazon.com/images/I/71FHb4LqURL._SL1500_.jpg'),
            ('Stainless Steel Dog Bowl', 'Non-slip base feeding bowl. Anti-rust stainless steel, 900ml.', 299, 'accessories', 'https://m.media-amazon.com/images/I/61dfRT-1URL._SL1500_.jpg'),
            ('Dog Leash with Padded Handle', 'Heavy duty nylon leash, 5ft length. Comfortable padded grip.', 399, 'accessories', 'https://m.media-amazon.com/images/I/71YqvbNOURL._SL1500_.jpg')
        ");
        echo "✓ Indian dog products & medicines inserted (prices in ₹)\\n";
    }

    // Insert sample hospitals (Indian veterinary hospitals)
    $stmt = $conn->query("SELECT COUNT(*) FROM hospitals");
    if ($stmt->fetchColumn() == 0 || $force_reset) {
        $conn->exec("
            INSERT INTO hospitals (name, address, phone, email, image_url) VALUES
            ('Crown Vet - Worli', 'Ground Floor, Atur House 87, Dr. Annie Besant Road, Worli Naka, Mumbai 400018', '+91-8062744100', 'contact@crown.vet', 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=400'),
            ('Cessna Lifeline Veterinary Hospital', 'HBCS, 148, KGA Rd, Amarjyoti Layout, Domlur, Bangalore 560071', '+91-7676365365', 'woof@cessnalifeline.com', 'https://images.unsplash.com/photo-1576201836106-db1758fd1c97?w=400'),
            ('Sanjay Gandhi Animal Care Centre', 'Raja Garden, New Delhi 110015', '+91-11-25447751', 'info@sgacc.org.in', 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=400'),
            ('Superpets Veterinary Hospital', 'Me-Me Tower, 8th Road, Khar West, Mumbai 400052', '+91-9820012345', 'care@superpets.in', 'https://images.unsplash.com/photo-1599443015574-be5fe8a05783?w=400'),
            ('Jeeva Pet Hospital', '1360, 9th Cross Rd, J.P. Nagar 1st Phase, Bangalore 560078', '+91-80-26493939', 'hello@jeevapet.com', 'https://images.unsplash.com/photo-1629909615184-74f495363b63?w=400'),
            ('Max Petz', 'Mehar Estate, Dr E Moses Road, Mahalaxmi, Mumbai 400011', '+91-22-24934567', 'info@maxpetz.com', 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400')
        ");
        echo "✓ Sample hospitals inserted (Indian locations)\n";
    }

    // Insert sample pet photos (Indian dogs)
    $stmt = $conn->query("SELECT COUNT(*) FROM pet_photos");
    if ($stmt->fetchColumn() == 0 || $force_reset) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute(['rYuk']);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $conn->prepare("
                INSERT INTO pet_photos (user_id, pet_name, photo_url, description) VALUES
                (?, 'Bruno', 'https://m.media-amazon.com/images/I/81cMbF3lURL._SL1500_.jpg', 'My adorable Indie dog Bruno!'),
                (?, 'Simba', 'https://m.media-amazon.com/images/I/71KvYqBiURL._SL1500_.jpg', 'Simba the Labrador enjoying the park')
            ");
            $stmt->execute([$user['id'], $user['id']]);
            echo "✓ Sample pet photos inserted (Indian dogs)\\n";
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
