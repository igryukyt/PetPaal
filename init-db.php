<?php
/**
 * PetPal - Auto Database Setup
 * This runs automatically on first visit to create tables
 */

require_once __DIR__ . '/config/config.php';

try {
    $conn = getDBConnection();

    // Check if tables exist
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "Database already initialized!";
        exit;
    }

    // Create all tables
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category ENUM('accessories', 'food') NOT NULL,
        image_url VARCHAR(255),
        stock INT DEFAULT 100,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS hospitals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        address TEXT NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100),
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        hospital_id INT NOT NULL,
        rating INT NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS pet_photos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        pet_name VARCHAR(100),
        photo_url VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";

    $conn->exec($sql);
    echo "✓ Tables created!\n";

    // Insert test user
    $password = password_hash('Pass123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    $stmt->execute(['rYuk', 'ryuk@petpal.com', $password, 'rYuk User']);
    echo "✓ User rYuk created!\n";

    // Insert sample products
    $conn->exec("
        INSERT IGNORE INTO products (name, description, price, category, image_url) VALUES
        ('Premium Dog Collar', 'Adjustable leather collar', 29.99, 'accessories', 'https://images.unsplash.com/photo-1599839575945-a9e5af0c3fa5?w=400'),
        ('Cat Scratching Post', 'Multi-level scratching post', 49.99, 'accessories', 'https://images.unsplash.com/photo-1545249390-6bdfa286032f?w=400'),
        ('Pet Carrier Bag', 'Airline approved carrier', 39.99, 'accessories', 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400'),
        ('Interactive Dog Toy', 'Smart puzzle toy', 24.99, 'accessories', 'https://images.unsplash.com/photo-1535294435445-d7249524ef2e?w=400'),
        ('Cozy Pet Bed', 'Ultra-soft orthopedic bed', 59.99, 'accessories', 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=400'),
        ('LED Pet Leash', 'Rechargeable LED leash', 19.99, 'accessories', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400'),
        ('Premium Dog Food', 'Grain-free 15lb bag', 54.99, 'food', 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400'),
        ('Organic Cat Food', 'Gourmet 10lb bag', 44.99, 'food', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400'),
        ('Puppy Training Treats', 'Soft training treats', 12.99, 'food', 'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=400'),
        ('Dental Chew Sticks', 'Vet recommended', 18.99, 'food', 'https://images.unsplash.com/photo-1601758124510-52d02ddb7cbd?w=400')
    ");
    echo "✓ Products added!\n";

    // Insert sample hospitals
    $conn->exec("
        INSERT IGNORE INTO hospitals (name, address, phone, email, image_url) VALUES
        ('PetCare Animal Hospital', '123 Main Street, NY', '(555) 123-4567', 'contact@petcare.com', 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=400'),
        ('Happy Paws Clinic', '456 Oak Avenue, NY', '(555) 234-5678', 'info@happypaws.com', 'https://images.unsplash.com/photo-1628009368231-7bb7cfcb0def?w=400'),
        ('City Pet Emergency', '789 Emergency Lane, NY', '(555) 345-6789', 'emergency@citypet.com', 'https://images.unsplash.com/photo-1612531386530-97286d97c2d2?w=400'),
        ('Sunshine Animal Care', '321 Sunny Road, NY', '(555) 456-7890', 'hello@sunshine.com', 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400')
    ");
    echo "✓ Hospitals added!\n";

    echo "\n✅ Database setup complete!\n";
    echo "Login: rYuk / Pass123\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
