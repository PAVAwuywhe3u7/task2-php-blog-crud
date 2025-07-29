<?php
/**
 * Database Configuration
 * Task 2: PHP Blog Application
 * Aerospace Internship Project
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'php_blog_task2');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection using PDO
 * @return PDO Database connection object
 * @throws PDOException if connection fails
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log error in production, display for development
        error_log("Database connection failed: " . $e->getMessage());
        throw new PDOException("Database connection failed. Please try again later.");
    }
}

/**
 * Test database connection
 * @return bool True if connection successful
 */
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
?>