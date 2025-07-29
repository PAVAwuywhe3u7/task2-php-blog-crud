-- =============================================
-- Task 2: PHP Blog Database Setup
-- Aerospace Internship Project
-- =============================================

-- Create database
CREATE DATABASE IF NOT EXISTS php_blog_task2;
USE php_blog_task2;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;

-- =============================================
-- Users Table
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- Posts Table
-- =============================================
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- Insert Sample Data
-- =============================================

-- Sample users (password is 'password123' hashed)
INSERT INTO users (username, email, password) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample blog posts
INSERT INTO posts (title, content, author_id) VALUES
('Welcome to Our Blog', 'This is the first post on our amazing blog! We will be sharing insights about web development, PHP, and much more.', 1),
('Getting Started with PHP', 'PHP is a powerful server-side scripting language. In this post, we will explore the basics of PHP programming and how to build dynamic web applications.', 2),
('Database Design Best Practices', 'When designing databases, it is important to follow certain principles to ensure data integrity, performance, and scalability.', 1),
('Introduction to MySQL', 'MySQL is one of the most popular relational database management systems. Let us learn how to use it effectively with PHP.', 3),
('Building Secure Web Applications', 'Security should be a top priority when developing web applications. Here are some essential practices to keep your applications safe.', 2);

-- =============================================
-- Verify Setup
-- =============================================
SELECT 'Database setup completed successfully!' as status;
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_posts FROM posts;