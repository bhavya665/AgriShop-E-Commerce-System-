-- AgriShop Database Schema
-- MySQL Database Setup for Agri E-Commerce

-- Create database
CREATE DATABASE IF NOT EXISTS agri_ecommerce;
USE agri_ecommerce;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2) DEFAULT 0,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    category_id INT,
    sku VARCHAR(50) UNIQUE,
    rating DECIMAL(3,2) DEFAULT 0,
    popularity INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product reviews table
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description, image) VALUES
('Seeds', 'High quality crop seeds for every season and region', 'seeds.jpg'),
('Farming Tools', 'Durable tools for efficient farming and garden maintenance', 'tools.jpg'),
('Fertilizers', 'Improve soil and plant health with our organic and chemical fertilizers', 'fertilizer.jpg'),
('Animal Feed', 'Balanced nutrition for livestock and poultry for better yield', 'animal-feed.jpg'),
('Pesticides', 'Effective pest control solutions for healthy crops', 'pesticides.jpg'),
('Irrigation', 'Water management systems for efficient farming', 'irrigation.jpg');

-- Insert sample products
INSERT INTO products (name, description, price, discount_price, stock, image, category_id, sku, rating, featured) VALUES
('Organic Tomato Seeds', 'High-yield, disease-resistant premium seeds for home and commercial farming', 299.00, 0, 100, 'product1.jpg', 1, 'TOM001', 4.5, TRUE),
('Premium Garden Tool Set', 'Complete set of essential gardening tools including spade, rake, and pruners', 1499.00, 1299.00, 50, 'product2.jpg', 2, 'TOOL001', 4.8, TRUE),
('Organic Vermicompost', '100% natural, nutrient-rich soil enhancer for better plant growth', 599.00, 0, 200, 'product3.jpg', 3, 'FERT001', 4.6, TRUE),
('Advanced Drip Irrigation Kit', 'Water-saving irrigation system suitable for all types of crops', 2999.00, 0, 25, 'product4.jpg', 6, 'IRR001', 4.7, TRUE),
('Monsoon Special Seeds Pack', 'Perfect for rainy season plantation with disease resistance', 999.00, 699.00, 75, 'season1.jpg', 1, 'SEED001', 4.4, FALSE),
('Premium NPK Fertilizer', 'Balanced nutrition formula for all crops with slow-release technology', 800.00, 599.00, 150, 'season2.jpg', 3, 'FERT002', 4.3, FALSE),
('Organic Pest Control Spray', 'Chemical-free pest management solution for organic farming', 1200.00, 720.00, 80, 'season3.jpg', 5, 'PEST001', 4.2, FALSE),
('Solar Water Pump', 'Energy-efficient irrigation solution powered by solar energy', 5000.00, 4000.00, 15, 'season4.jpg', 6, 'PUMP001', 4.9, FALSE);

-- Insert admin user (password: admin123)
INSERT INTO admin (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@agricart.com');

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_featured ON products(featured);
CREATE INDEX idx_products_price ON products(price);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_reviews_product ON product_reviews(product_id);
CREATE INDEX idx_reviews_user ON product_reviews(user_id); 