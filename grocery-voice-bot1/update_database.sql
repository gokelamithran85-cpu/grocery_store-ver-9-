-- Add to existing database

-- Create receipts directory
CREATE TABLE IF NOT EXISTS email_receipts (
    receipt_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    user_id INT,
    email_sent_to VARCHAR(255),
    receipt_html TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Add email to users table if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(100) UNIQUE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(15);
ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT;

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(10)
);

-- Insert default categories
INSERT INTO categories (name, description, icon) VALUES
('vegetables', 'Fresh vegetables and greens', '🥦'),
('fruits', 'Seasonal fruits', '🍎'),
('snacks', 'Chips, chocolates, biscuits', '🍿'),
('dairy', 'Milk, cheese, eggs, yogurt', '🥛'),
('beverages', 'Juices, soft drinks, tea, coffee', '🥤'),
('household', 'Cleaning supplies, utensils', '🏠'),
('personal-care', 'Shampoo, soap, toothpaste', '🧴'),
('frozen-foods', 'Frozen vegetables, ice cream', '🧊');

-- Update products with categories
UPDATE products SET category = 'vegetables' WHERE name LIKE '%carrot%' OR name LIKE '%tomato%' OR name LIKE '%potato%' OR name LIKE '%onion%';
UPDATE products SET category = 'fruits' WHERE name LIKE '%apple%' OR name LIKE '%banana%';

-- Create orders table (if not exists)
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create order_items table (if not exists)
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity DECIMAL(10,2) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Add wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    UNIQUE KEY unique_wishlist (user_id, product_id)
);