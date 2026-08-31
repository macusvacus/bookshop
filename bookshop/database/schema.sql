-- Bookshop E-commerce Database Schema

CREATE DATABASE IF NOT EXISTS bookshop;
USE bookshop;

-- Admin users who can log into the dashboard
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- stores a bcrypt hash, never plain text
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Book categories (Fiction, Sci-Fi, Self-Help, etc.)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Books added by the admin
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category_id INT,
    cover_image VARCHAR(255),         -- filename stored in /uploads
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- One row per completed checkout
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    address TEXT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Line items inside each order (one book = one row)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,     -- price at time of purchase
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Seed data
INSERT INTO categories (name) VALUES ('Fiction'), ('Non-Fiction'), ('Sci-Fi'), ('Self-Help'), ('Children');

-- NOTE: No admin account is seeded here on purpose (a guessed/copy-pasted
-- bcrypt hash is a security risk). Run admin/setup.php once after importing
-- this schema to create your real admin username/password. Delete setup.php
-- afterwards.
