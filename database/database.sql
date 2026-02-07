-- PetPal Database Schema
-- Compatible with MySQL / phpMyAdmin

CREATE DATABASE IF NOT EXISTS petpal;
USE petpal;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table (accessories and food)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category ENUM('accessories', 'food') NOT NULL,
    image_url VARCHAR(255),
    stock INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hospitals table
CREATE TABLE hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    image_url VARCHAR(255),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hospital_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Pet photos table
CREATE TABLE pet_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_name VARCHAR(100),
    photo_url VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Health records table
CREATE TABLE health_records (
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
);

-- =====================
-- SAMPLE DATA
-- =====================

-- Sample Users (password is 'Pass123' - run setup.php to create with proper hash)
-- Or register new account through the website
INSERT INTO users (username, email, password, full_name) VALUES
('rYuk', 'ryuk@petpal.com', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MQDGGZRLzVpGn7qGzkzXU0xUlVJPk3K', 'rYuk User'),
('jane_doe', 'jane@petpal.com', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MQDGGZRLzVpGn7qGzkzXU0xUlVJPk3K', 'Jane Doe'),
('john_smith', 'john@petpal.com', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MQDGGZRLzVpGn7qGzkzXU0xUlVJPk3K', 'John Smith');

-- Sample Products - Indian Dog Medicines, Treatments & Food
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
('Dog Leash with Padded Handle', 'Heavy duty nylon leash, 5ft length. Comfortable padded grip.', 399, 'accessories', 'https://m.media-amazon.com/images/I/71YqvbNOURL._SL1500_.jpg');

-- Sample Hospitals - Indian Veterinary Hospitals
INSERT INTO hospitals (name, address, phone, email, image_url) VALUES
('Crown Vet - Worli', 'Ground Floor, Atur House 87, Dr. Annie Besant Road, Worli Naka, Mumbai 400018', '+91-8062744100', 'contact@crown.vet', 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=400'),
('Cessna Lifeline Veterinary Hospital', 'HBCS, 148, KGA Rd, Amarjyoti Layout, Domlur, Bangalore 560071', '+91-7676365365', 'woof@cessnalifeline.com', 'https://images.unsplash.com/photo-1628009368231-7bb7cfcb0def?w=400'),
('Sanjay Gandhi Animal Care Centre', 'Raja Garden, New Delhi 110015', '+91-11-25447751', 'info@sgacc.org.in', 'https://images.unsplash.com/photo-1612531386530-97286d97c2d2?w=400'),
('Superpets Veterinary Hospital', 'Me-Me Tower, 8th Road, Khar West, Mumbai 400052', '+91-9820012345', 'care@superpets.in', 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400'),
('Jeeva Pet Hospital', '1360, 9th Cross Rd, J.P. Nagar 1st Phase, Bangalore 560078', '+91-80-26493939', 'hello@jeevapet.com', 'https://images.unsplash.com/photo-1601758124096-1fd661873b95?w=400'),
('Max Petz', 'Mehar Estate, Dr E Moses Road, Mahalaxmi, Mumbai 400011', '+91-22-24934567', 'info@maxpetz.com', 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400');

-- Sample Reviews
INSERT INTO reviews (user_id, hospital_id, rating, comment) VALUES
(1, 1, 5, 'Excellent care for my dog! The staff was very professional and caring.'),
(2, 1, 4, 'Great service, though wait times can be a bit long during peak hours.'),
(1, 2, 5, 'Dr. Smith is amazing with cats! Highly recommend this clinic.'),
(3, 2, 5, 'Best veterinary experience ever. Very clean facility.'),
(2, 3, 4, 'Quick emergency response. Saved my cats life!'),
(3, 4, 5, 'Wonderful staff and reasonable prices. My pets love it here!'),
(1, 5, 4, 'Comprehensive wellness checkups. They really care about prevention.'),
(2, 6, 5, 'The most friendly and knowledgeable vets in town!');

-- Sample Pet Photos - Indian Dogs
INSERT INTO pet_photos (user_id, pet_name, photo_url, description) VALUES
(1, 'Bruno', 'https://m.media-amazon.com/images/I/81cMbF3lURL._SL1500_.jpg', 'My adorable Indie dog Bruno!'),
(2, 'Simba', 'https://m.media-amazon.com/images/I/71KvYqBiURL._SL1500_.jpg', 'Simba the Labrador enjoying the park'),
(1, 'Buddy', 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400', 'Buddy after his first grooming session'),
(3, 'Luna', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400', 'Luna playing with her favorite toy');

-- Sample Health Records
INSERT INTO health_records (user_id, pet_name, checkup_date, vet_name, diagnosis, treatment, next_appointment, notes) VALUES
(1, 'Bruno', '2024-01-15', 'Dr. Sharma', 'Annual checkup - healthy', 'Megavac 7 vaccination', '2025-01-15', 'Weight: 22kg, all vitals normal'),
(1, 'Bruno', '2024-06-20', 'Dr. Sharma', 'Tick fever check', 'Testing negative, prescribed Simparica', '2024-07-05', 'Preventative care'),
(2, 'Simba', '2024-02-10', 'Dr. Patel', 'Dental cleaning', 'Professional cleaning performed', '2025-02-10', 'Teeth in good condition'),
(3, 'Luna', '2024-03-05', 'Dr. Rao', 'Spay surgery', 'Surgery successful, pain meds for 5 days', '2024-03-12', 'Recovery going well');
