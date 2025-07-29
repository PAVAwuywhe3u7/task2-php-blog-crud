<?php
/**
 * Debug script to identify the 500 error
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Debug Information</h1>";

// Test 1: PHP Version
echo "<h3>1. PHP Version</h3>";
echo "PHP Version: " . phpversion() . "<br>";

// Test 2: Basic database connection
echo "<h3>2. Database Connection Test</h3>";
try {
    $pdo = new PDO("mysql:host=localhost", 'root', '');
    echo "✅ MySQL connection successful<br>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'php_blog_final'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Database 'php_blog_final' exists<br>";
    } else {
        echo "❌ Database 'php_blog_final' does not exist<br>";
        echo "<strong>Creating database...</strong><br>";
        $pdo->exec("CREATE DATABASE php_blog_final CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Database 'php_blog_final' created<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 3: File permissions
echo "<h3>3. File Permissions</h3>";
$dirs = ['config', 'src', 'public', 'templates'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ Directory '$dir' exists and is readable<br>";
    } else {
        echo "❌ Directory '$dir' missing<br>";
    }
}

// Test 4: Required files
echo "<h3>4. Required Files</h3>";
$files = [
    'config/config.php',
    'src/Database.php',
    'src/Auth.php',
    'src/Post.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ File '$file' exists<br>";
    } else {
        echo "❌ File '$file' missing<br>";
    }
}

// Test 5: Try to include config
echo "<h3>5. Configuration Test</h3>";
try {
    define('APP_INIT', true);
    require_once 'config/config.php';
    echo "✅ Configuration loaded successfully<br>";
    echo "App Name: " . APP_NAME . "<br>";
    echo "Database Host: " . DB_HOST . "<br>";
    echo "Database Name: " . DB_NAME . "<br>";
} catch (Exception $e) {
    echo "❌ Configuration error: " . $e->getMessage() . "<br>";
}

// Test 6: Database class test
echo "<h3>6. Database Class Test</h3>";
try {
    if (class_exists('Database')) {
        $db = Database::getInstance();
        echo "✅ Database class instantiated<br>";
        
        if ($db->testConnection()) {
            echo "✅ Database connection test passed<br>";
        } else {
            echo "❌ Database connection test failed<br>";
        }
    } else {
        echo "❌ Database class not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Database class error: " . $e->getMessage() . "<br>";
}

echo "<h3>7. Next Steps</h3>";
echo "<p>If all tests pass, the main application should work.</p>";
echo "<p><a href='public/index.php'>Try Main Application</a></p>";
echo "<p><a href='setup.php'>Run Setup Script</a></p>";
?>
