<?php
/**
 * Database Creation and Setup Script
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Setup</h1>";

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=localhost", 'root', '');
    echo "✅ Connected to MySQL<br>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS php_blog_final CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database 'php_blog_final' created<br>";
    
    // Switch to the database
    $pdo->exec("USE php_blog_final");
    echo "✅ Using database 'php_blog_final'<br>";
    
    // Read and execute SQL file
    $sqlFile = 'database_setup.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $executed = 0;
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $pdo->exec($statement);
                    $executed++;
                } catch (Exception $e) {
                    // Ignore errors for statements that might already exist
                    if (!strpos($e->getMessage(), 'already exists')) {
                        echo "⚠️ Warning: " . $e->getMessage() . "<br>";
                    }
                }
            }
        }
        
        echo "✅ Executed $executed SQL statements<br>";
    } else {
        echo "❌ SQL file not found: $sqlFile<br>";
    }
    
    // Verify tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Created tables: " . implode(', ', $tables) . "<br>";
    
    // Check user count
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "✅ Users in database: $userCount<br>";
    
    // Fix passwords
    echo "<h3>Fixing User Passwords</h3>";
    $users = [
        'admin' => 'AdminPass123!',
        'editor' => 'EditorPass123!',
        'user' => 'UserPass123!',
        'john_doe' => 'JohnPass123!',
        'jane_smith' => 'JanePass123!'
    ];
    
    foreach ($users as $username => $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $updated = $stmt->execute([$hash, $username]);
        if ($updated) {
            echo "✅ Updated password for: $username<br>";
        }
    }
    
    echo "<h3>✅ Database Setup Complete!</h3>";
    echo "<p><a href='public/index.php'>Go to Application</a></p>";
    echo "<p><a href='setup.php'>Run Full Setup</a></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
