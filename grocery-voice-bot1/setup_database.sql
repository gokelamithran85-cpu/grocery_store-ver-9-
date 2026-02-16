-- Create database
CREATE DATABASE IF NOT EXISTS grocery_store;
USE grocery_store;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_250g DECIMAL(10,2) NOT NULL,
    current_stock DECIMAL(10,2) DEFAULT 0,
    image_path VARCHAR(255),
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO products (name, description, price_per_250g, current_stock, category) VALUES
('Carrot', 'Fresh organic carrots', 20.00, 10.00, 'vegetables'),
('Tomato', 'Ripe red tomatoes', 25.00, 8.00, 'vegetables'),
('Potato', 'Fresh potatoes', 15.00, 15.00, 'vegetables'),
('Onion', 'Fresh onions', 18.00, 12.00, 'vegetables'),
('Apple', 'Fresh red apples', 30.00, 10.00, 'fruits'),
('Banana', 'Yellow bananas', 12.00, 20.00, 'fruits');