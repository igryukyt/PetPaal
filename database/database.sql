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

-- Sample Products - Accessories
INSERT INTO products (name, description, price, category, image_url) VALUES
('Premium Dog Collar', 'Adjustable leather collar with gold buckle, comfortable and stylish for all dog breeds.', 29.99, 'accessories', 'https://images.unsplash.com/photo-1599839575945-a9e5af0c3fa5?w=400'),
('Cat Scratching Post', 'Multi-level scratching post with plush platforms and dangling toys.', 49.99, 'accessories', 'https://images.unsplash.com/photo-1545249390-6bdfa286032f?w=400'),
('Pet Carrier Bag', 'Airline approved soft-sided pet carrier with breathable mesh panels.', 39.99, 'accessories', 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400'),
('Interactive Dog Toy', 'Smart puzzle toy that dispenses treats and keeps your dog entertained.', 24.99, 'accessories', 'https://images.unsplash.com/photo-1535294435445-d7249524ef2e?w=400'),
('Cozy Pet Bed', 'Ultra-soft orthopedic pet bed with removable washable cover.', 59.99, 'accessories', 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=400'),
('LED Pet Leash', 'Rechargeable LED light-up leash for safe nighttime walks.', 19.99, 'accessories', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400');

-- Sample Products - Food
INSERT INTO products (name, description, price, category, image_url) VALUES
('Premium Dog Food', 'All-natural grain-free dog food with real chicken, 15lb bag.', 54.99, 'food', 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400'),
('Organic Cat Food', 'Gourmet organic cat food with salmon and vegetables, 10lb bag.', 44.99, 'food', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400'),
('Puppy Training Treats', 'Soft and chewy training treats, perfect for positive reinforcement.', 12.99, 'food', 'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=400'),
('Dental Chew Sticks', 'Veterinarian recommended dental chews for fresh breath and clean teeth.', 18.99, 'food', 'https://images.unsplash.com/photo-1601758124510-52d02ddb7cbd?w=400'),
('Kitten Milk Replacer', 'Complete nutrition formula for orphaned or weaning kittens.', 22.99, 'food', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400'),
('Senior Dog Vitamins', 'Daily multivitamin supplement for senior dogs with joint support.', 29.99, 'food', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400');

-- Sample Hospitals
INSERT INTO hospitals (name, address, phone, email, image_url) VALUES
('PetCare Animal Hospital', '123 Main Street, Downtown, NY 10001', '(555) 123-4567', 'contact@petcare.com', 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=400'),
('Happy Paws Veterinary Clinic', '456 Oak Avenue, Westside, NY 10002', '(555) 234-5678', 'info@happypaws.com', 'https://images.unsplash.com/photo-1628009368231-7bb7cfcb0def?w=400'),
('City Pet Emergency Center', '789 Emergency Lane, Midtown, NY 10003', '(555) 345-6789', 'emergency@citypet.com', 'https://images.unsplash.com/photo-1612531386530-97286d97c2d2?w=400'),
('Sunshine Animal Care', '321 Sunny Road, Eastside, NY 10004', '(555) 456-7890', 'hello@sunshinecare.com', 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400'),
('The Pet Wellness Center', '654 Health Boulevard, Uptown, NY 10005', '(555) 567-8901', 'wellness@petwellness.com', 'https://images.unsplash.com/photo-1601758124096-1fd661873b95?w=400'),
('Furry Friends Hospital', '987 Cuddle Street, Northside, NY 10006', '(555) 678-9012', 'care@furryfriends.com', 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400');

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

-- Sample Pet Photos
INSERT INTO pet_photos (user_id, pet_name, photo_url, description) VALUES
(1, 'Max', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400', 'My golden retriever enjoying the park!'),
(2, 'Whiskers', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400', 'Whiskers taking a nap in the sun'),
(1, 'Buddy', 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400', 'Buddy after his first grooming session'),
(3, 'Luna', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400', 'Luna playing with her favorite toy');

-- Sample Health Records
INSERT INTO health_records (user_id, pet_name, checkup_date, vet_name, diagnosis, treatment, next_appointment, notes) VALUES
(1, 'Max', '2024-01-15', 'Dr. Johnson', 'Annual checkup - healthy', 'Vaccinations updated', '2025-01-15', 'Weight: 65lbs, all vitals normal'),
(1, 'Max', '2024-06-20', 'Dr. Johnson', 'Minor ear infection', 'Ear drops prescribed for 7 days', '2024-07-05', 'Follow up in 2 weeks'),
(2, 'Whiskers', '2024-02-10', 'Dr. Smith', 'Dental cleaning', 'Professional cleaning performed', '2025-02-10', 'Teeth in good condition'),
(3, 'Luna', '2024-03-05', 'Dr. Williams', 'Spay surgery', 'Surgery successful, pain meds for 5 days', '2024-03-12', 'Recovery going well');
