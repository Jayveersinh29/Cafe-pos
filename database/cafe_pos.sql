-- =========================================================
-- Cafe POS System - Database Schema
-- Import this file in phpMyAdmin, or via:
--   mysql -u root -p < cafe_pos.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS cafe_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cafe_pos;

-- ---------------------------------------------------------
-- Users (staff / admin)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- NOTE ABOUT PASSWORDS:
-- Real bcrypt hashes cannot be safely hand-typed into SQL, so this seed
-- creates the two accounts with NO usable password, and setup.php
-- (run once from the browser) sets them to admin123 / staff123 using
-- PHP's own password_hash(). See README.md "First-time setup".
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '', 'Cafe Admin', 'admin'),
('staff', '', 'Front Desk Staff', 'staff');

-- ---------------------------------------------------------
-- Categories
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
('Coffee'),('Tea'),('Burger'),('Pizza'),('Sandwich'),
('Snacks'),('Dessert'),('Cold Drinks'),('Other');

-- ---------------------------------------------------------
-- Products
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO products (name, category_id, price, image) VALUES
('Espresso', 1, 90.00, NULL),
('Cappuccino', 1, 130.00, NULL),
('Cold Coffee', 1, 150.00, NULL),
('Masala Tea', 2, 40.00, NULL),
('Green Tea', 2, 60.00, NULL),
('Classic Burger', 3, 120.00, NULL),
('Cheese Burger', 3, 150.00, NULL),
('Margherita Pizza', 4, 220.00, NULL),
('Farmhouse Pizza', 4, 280.00, NULL),
('Veg Sandwich', 5, 90.00, NULL),
('Grilled Sandwich', 5, 110.00, NULL),
('French Fries', 6, 100.00, NULL),
('Nachos', 6, 130.00, NULL),
('Chocolate Brownie', 7, 90.00, NULL),
('Ice Cream Sundae', 7, 110.00, NULL),
('Coke', 8, 50.00, NULL),
('Fresh Lime Soda', 8, 60.00, NULL);

-- ---------------------------------------------------------
-- Customers
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) DEFAULT 'Walk-in',
    mobile VARCHAR(20) DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Bills
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_no VARCHAR(30) NOT NULL UNIQUE,
    customer_id INT DEFAULT NULL,
    user_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('Cash','UPI','Card','Other') NOT NULL DEFAULT 'Cash',
    amount_received DECIMAL(10,2) DEFAULT NULL,
    change_amount DECIMAL(10,2) DEFAULT NULL,
    status ENUM('completed','held') NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Bill items
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS bill_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;
