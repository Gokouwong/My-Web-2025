-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS food_website;

-- Use the database
USE food_website;

-- Drop existing tables
DROP TABLE IF EXISTS banners;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS website_content;
DROP TABLE IF EXISTS jrtso_listings;
DROP TABLE IF EXISTS user_uploads;

-- Create banners table
CREATE TABLE banners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    image_path VARCHAR(255),
    title VARCHAR(255),
    subtitle TEXT,
    active BOOLEAN DEFAULT true
);

-- Create products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_number VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    intro TEXT,
    description TEXT,
    image_path VARCHAR(255),
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    display_order INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00
);

-- Create pages table
CREATE TABLE pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    page_name VARCHAR(50),
    title VARCHAR(255),
    content TEXT
);

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(20)
);

-- Create website_content table
CREATE TABLE website_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255),
    content TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create jrtso_listings table
CREATE TABLE jrtso_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user_uploads table
CREATE TABLE user_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    image_path VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    description TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_name VARCHAR(255),
    user_email VARCHAR(255)
);

-- Insert initial content
INSERT INTO website_content (page, title, content) VALUES 
('about', 'About Us', 'About us content here'),
('contact', 'Contact Us', 'Contact information here'),
('home_intro', 'Welcome', 'Welcome to our restaurant. We serve delicious food with great service.');

-- Insert sample products with product numbers
INSERT INTO products (product_number, name, intro, description, active) VALUES
('P001', 'Classic Burger', 'Juicy beef patty with fresh vegetables', 'Our signature burger made with 100% premium beef, topped with crisp lettuce, tomatoes, and special sauce.', true),
('P002', 'Margherita Pizza', 'Traditional Italian pizza with fresh basil', 'Hand-tossed pizza with San Marzano tomatoes, fresh mozzarella, basil, and extra virgin olive oil.', true),
('P003', 'Caesar Salad', 'Crisp romaine lettuce with parmesan', 'Fresh romaine hearts, house-made croutons, aged parmesan, and our special Caesar dressing.', true),
('P004', 'Grilled Salmon', 'Fresh Atlantic salmon with herbs', 'Premium salmon fillet grilled to perfection, served with seasonal vegetables and lemon butter sauce.', true),
('P005', 'Chocolate Lava Cake', 'Warm chocolate cake with molten center', 'Rich chocolate cake with a gooey center, served with vanilla ice cream and fresh berries.', true);

-- Insert some initial data
INSERT INTO jrtso_listings (number, title, description) VALUES
(1, 'JRTSO Title 1', 'Description for JRTSO item 1'),
(2, 'JRTSO Title 2', 'Description for JRTSO item 2'),
(3, 'JRTSO Title 3', 'Description for JRTSO item 3'),
(4, 'JRTSO Title 4', 'Description for JRTSO item 4'),
(5, 'JRTSO Title 5', 'Description for JRTSO item 5'),
(6, 'JRTSO Title 6', 'Description for JRTSO item 6'),
(7, 'JRTSO Title 7', 'Description for JRTSO item 7'),
(8, 'JRTSO Title 8', 'Description for JRTSO item 8'),
(9, 'JRTSO Title 9', 'Description for JRTSO item 9'),
(10, 'JRTSO Title 10', 'Description for JRTSO item 10');

-- Set initial display order
UPDATE products SET display_order = id;

-- Modify products table to ensure required fields
ALTER TABLE products 
    MODIFY product_number VARCHAR(50) NOT NULL,
    MODIFY name VARCHAR(255) NOT NULL,
    MODIFY intro TEXT,
    MODIFY description TEXT,
    MODIFY image_path VARCHAR(255),
    MODIFY active BOOLEAN DEFAULT true,
    MODIFY display_order INT DEFAULT 0;

-- Update any existing products without product numbers
UPDATE products SET product_number = CONCAT('P', LPAD(id, 3, '0')) WHERE product_number IS NULL OR product_number = '';

-- Update existing products with sample prices
UPDATE products SET price = 
    CASE product_number
        WHEN 'P001' THEN 12.99  -- Classic Burger
        WHEN 'P002' THEN 15.99  -- Margherita Pizza
        WHEN 'P003' THEN 9.99   -- Caesar Salad
        WHEN 'P004' THEN 24.99  -- Grilled Salmon
        WHEN 'P005' THEN 8.99   -- Chocolate Lava Cake
        ELSE 0.00
    END;

-- Add price column to products table if it doesn't exist
ALTER TABLE products ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00 AFTER description; 