-- Online Food Ordering System Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS food_ordering_system;
USE food_ordering_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu items table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category ENUM('breakfast', 'lunch', 'snacks', 'beverages', 'desserts') NOT NULL,
    image VARCHAR(255) DEFAULT 'default.jpg',
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@campus.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, category, available) VALUES
('Idli Sambar', 'Soft steamed rice cakes with sambar and chutney', 40.00, 'breakfast', TRUE),
('Dosa', 'Crispy rice crepe served with chutney and sambar', 50.00, 'breakfast', TRUE),
('Veg Biryani', 'Aromatic basmati rice with mixed vegetables', 80.00, 'lunch', TRUE),
('Chicken Biryani', 'Flavorful rice with tender chicken pieces', 120.00, 'lunch', TRUE),
('Samosa', 'Crispy fried pastry with spiced potato filling', 20.00, 'snacks', TRUE),
('Vada Pav', 'Spicy potato fritter in a bun', 25.00, 'snacks', TRUE),
('Masala Chai', 'Traditional Indian spiced tea', 15.00, 'beverages', TRUE),
('Cold Coffee', 'Chilled coffee with ice cream', 45.00, 'beverages', TRUE),
('Gulab Jamun', 'Sweet milk dumplings in sugar syrup', 30.00, 'desserts', TRUE),
('Ice Cream', 'Vanilla ice cream scoop', 35.00, 'desserts', TRUE);
