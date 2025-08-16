-- Create database
CREATE DATABASE IF NOT EXISTS food_catalog;
USE food_catalog;

-- Create countries table
CREATE TABLE IF NOT EXISTS countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create food_items table
CREATE TABLE IF NOT EXISTS food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
);

-- Create admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, password, email) 
VALUES ('admin', '$2y$10$8K1p/hzDWxn1KHli9BfKA.cqFpwN/DEZmImzI99.3KbFVrR7GHYs.', 'admin@example.com');

-- Insert some sample countries
INSERT INTO countries (name, description) VALUES 
('Italy', 'Known for pasta, pizza, and Mediterranean cuisine'),
('Japan', 'Famous for sushi, ramen, and tempura'),
('India', 'Known for spicy curries, biryanis, and diverse regional cuisines'),
('Mexico', 'Famous for tacos, enchiladas, and flavorful spices');

-- Insert some sample food items
INSERT INTO food_items (country_id, name, description, featured) VALUES 
(1, 'Pizza Margherita', 'Classic Italian pizza with tomatoes, mozzarella, and basil', true),
(1, 'Pasta Carbonara', 'Pasta with egg, cheese, pancetta, and black pepper', false),
(2, 'Sushi', 'Japanese dish with vinegared rice and various toppings', true),
(2, 'Ramen', 'Japanese noodle soup dish with various toppings', false),
(3, 'Butter Chicken', 'Rich and creamy curry with tender chicken pieces', true),
(3, 'Biryani', 'Fragrant rice dish with spices and meat or vegetables', false),
(4, 'Tacos', 'Corn or wheat tortilla filled with various ingredients', true),
(4, 'Guacamole', 'Avocado-based dip with various seasonings', false);