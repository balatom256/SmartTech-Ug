-- =====================================================
-- SmartTech-UG Database
-- Import this file in phpMyAdmin
-- =====================================================

CREATE DATABASE IF NOT EXISTS smarttech_ug CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smarttech_ug;

-- ── USERS ──
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  address TEXT,
  role ENUM('customer','admin') DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── CATEGORIES ──
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  icon VARCHAR(100) DEFAULT 'fa-solid fa-box',
  slug VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── PRODUCTS ──
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  brand VARCHAR(100),
  description TEXT,
  price DECIMAL(12,2) NOT NULL,
  old_price DECIMAL(12,2) DEFAULT NULL,
  stock INT DEFAULT 0,
  image VARCHAR(255) DEFAULT '',
  emoji VARCHAR(10) DEFAULT '📦',
  badge VARCHAR(50) DEFAULT '',
  featured TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ── ORDERS ──
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  payment_method VARCHAR(50) DEFAULT 'Mobile Money',
  delivery_address TEXT,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── ORDER ITEMS ──
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ── CART ──
CREATE TABLE cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- ── WISHLIST ──
CREATE TABLE wishlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY unique_wish (user_id, product_id)
);

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Admin user (password: admin123)
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin SmartTech', 'admin@smarttech-ug.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256700000001', 'admin');

-- Customer (password: password)
INSERT INTO users (name, email, password, phone, role) VALUES
('Aisha Kato', 'aisha@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256772000001', 'customer');

-- Categories
INSERT INTO categories (name, icon, slug) VALUES
('Smartphones',   'fa-solid fa-mobile-screen', 'smartphones'),
('Laptops',       'fa-solid fa-laptop',         'laptops'),
('Audio & Sound', 'fa-solid fa-headphones',     'audio'),
('TVs & Displays','fa-solid fa-tv',             'tvs'),
('Smartwatches',  'fa-solid fa-clock',          'smartwatches'),
('Cameras',       'fa-solid fa-camera',         'cameras'),
('Accessories',   'fa-solid fa-plug',           'accessories'),
('Gaming',        'fa-solid fa-gamepad',        'gaming');

-- Products
INSERT INTO products (category_id, name, brand, description, price, old_price, stock, emoji, badge, featured) VALUES
(1, 'Samsung Galaxy S24 Ultra', 'Samsung', 'The ultimate Galaxy experience with AI features, 200MP camera and titanium frame.', 3800000, 4200000, 25, '📱', 'Best Seller', 1),
(1, 'iPhone 15 Pro Max 256GB', 'Apple', 'Titanium design, A17 Pro chip, 48MP ProCamera system.', 5300000, 6200000, 18, '📱', 'Hot', 1),
(1, 'Tecno Spark 20 Pro', 'Tecno', 'Affordable smartphone with great camera and long battery life.', 680000, NULL, 50, '📱', '', 0),
(1, 'Samsung Galaxy A55 5G', 'Samsung', '5G connectivity, 50MP camera, 5000mAh battery.', 1200000, 1400000, 30, '📱', 'Sale', 1),

(2, 'MacBook Air M3 13"', 'Apple', '18-hour battery, M3 chip, Liquid Retina display, fanless design.', 5800000, NULL, 12, '💻', 'New', 1),
(2, 'Dell XPS 15 OLED i9', 'Dell', 'OLED touch display, Intel Core i9, 32GB RAM, 1TB SSD.', 6200000, 7500000, 8, '💻', '20% Off', 1),
(2, 'HP Spectre x360 14', 'HP', 'Intel Core Ultra, OLED touch, 360-degree hinge.', 3600000, 4500000, 15, '💻', '20% Off', 1),
(2, 'Lenovo ThinkPad E14', 'Lenovo', 'Business laptop, AMD Ryzen 5, 16GB RAM, 512GB SSD.', 2100000, NULL, 20, '💻', '', 0),

(3, 'Sony WH-1000XM5', 'Sony', 'Industry-leading noise cancellation, 30-hour battery, premium audio.', 780000, 1200000, 40, '🎧', '35% Off', 1),
(3, 'Apple AirPods Pro 2nd Gen', 'Apple', 'Active Noise Cancellation, Transparency mode, Adaptive Audio.', 980000, NULL, 35, '🎧', 'New', 1),
(3, 'JBL Charge 5', 'JBL', 'Waterproof Bluetooth speaker, 20 hours playtime, built-in powerbank.', 380000, NULL, 60, '🔊', '', 0),
(3, 'Samsung Galaxy Buds2 Pro', 'Samsung', 'Hi-Fi 24-bit audio, intelligent ANC, ergonomic fit.', 420000, 550000, 45, '🎵', 'Sale', 0),

(4, 'Samsung 55" QLED 4K TV', 'Samsung', 'Quantum Dot technology, smart TV, HDR10+.', 2850000, 3800000, 10, '📺', '25% Off', 1),
(4, 'LG 43" UHD Smart TV', 'LG', '4K UHD, WebOS, built-in Google Assistant.', 1400000, NULL, 15, '📺', '', 0),

(5, 'Apple Watch Series 9 GPS', 'Apple', 'Advanced health sensors, crash detection, 18-hour battery.', 1800000, NULL, 22, '⌚', 'New', 1),
(5, 'Samsung Galaxy Watch 6 Classic', 'Samsung', 'Rotating bezel, advanced health tracking, 40-hour battery.', 720000, 950000, 28, '⌚', 'Sale', 1),

(6, 'Sony Alpha A7 IV', 'Sony', 'Full-frame mirrorless, 33MP, 4K 60fps video.', 5200000, NULL, 6, '📷', '', 0),
(6, 'Canon EOS M50 Mark II', 'Canon', '24.1MP APS-C sensor, 4K video, vlogging-friendly flip screen.', 2100000, 2600000, 10, '📸', 'Sale', 1),

(7, 'Anker 65W GaN Charger', 'Anker', 'Compact 3-port USB-C charger, charges 3 devices simultaneously.', 95000, NULL, 100, '🔌', '', 0),
(7, 'Baseus USB-C Hub 7-in-1', 'Baseus', 'HDMI 4K, 3x USB-A, SD card, PD charging.', 120000, 160000, 80, '🔌', 'Sale', 0),

(8, 'PlayStation 5 Slim', 'Sony', 'Next-gen gaming, 4K 120fps, ultra-high speed SSD.', 3200000, NULL, 7, '🎮', 'Hot', 1),
(8, 'Xbox Series X', 'Microsoft', '12 teraflops, 4K gaming, Quick Resume.', 3100000, NULL, 5, '🎮', '', 0);

-- Sample order
INSERT INTO orders (user_id, total, status, payment_method, delivery_address) VALUES
(2, 5300000, 'delivered', 'MTN Mobile Money', 'Nakasero, Kampala');
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (1, 2, 1, 5300000);
