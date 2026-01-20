-- Create Database
CREATE DATABASE IF NOT EXISTS SEUSL;
USE SEUSL;

-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a test student and admin
-- Passwords are 'password123'
INSERT INTO users (username, email, password, role) VALUES 
('student', 'student@fas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('admin', 'admin@fas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE role=VALUES(role);

-- Insert a test user (password is 'password123')
-- Note: In a real scenario, use PHP password_hash() to generate the hash. 
-- For this SQL, we'll insert a dummy hash for testing if you want to manually test without the register page first.
-- The hash below is for 'password123' using PASSWORD_DEFAULT
INSERT INTO users (username, email, password) VALUES 
('student', 'student@fas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Create Projects Table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    features TEXT,
    file_path VARCHAR(255),
    status VARCHAR(50) DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
