<?php
/**
 * Quick Password Fix Tool
 * Task 2: PHP Blog Application
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>🔧 Quick Password Fix</h2>";

try {
    $pdo = getDBConnection();

    // First, let's clear everything and start fresh
    echo "<p>🔄 Clearing existing data...</p>";
    $pdo->exec("DELETE FROM posts");
    $pdo->exec("DELETE FROM users");

    // Create users with proper password hashing
    echo "<p>👥 Creating users with proper passwords...</p>";
    $adminPassword = password_hash('password123', PASSWORD_DEFAULT);
    $johnPassword = password_hash('password123', PASSWORD_DEFAULT);
    $janePassword = password_hash('password123', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute(['admin', 'admin@example.com', $adminPassword]);
    $stmt->execute(['john_doe', 'john@example.com', $johnPassword]);
    $stmt->execute(['jane_smith', 'jane@example.com', $janePassword]);

    // Create sample posts
    echo "<p>📝 Creating sample posts...</p>";
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, author_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute(['Welcome to Our Blog!', 'This is the first post on our amazing blog. We are excited to share our thoughts and experiences with you. Stay tuned for more interesting content!', 1]);
    $stmt->execute(['Getting Started with PHP', 'PHP is a powerful server-side scripting language that is perfect for web development. In this post, we will explore some basic concepts and best practices for PHP development.', 2]);
    $stmt->execute(['The Future of Web Development', 'Web development is constantly evolving. From new frameworks to emerging technologies, there is always something exciting happening in the world of web development.', 3]);

    // Test the admin login
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();

    if ($admin && password_verify('password123', $admin['password'])) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h3>🎉 SUCCESS!</h3>";
        echo "<p><strong>Database reset complete! Login should now work with:</strong></p>";
        echo "<p>👤 <strong>Username:</strong> admin</p>";
        echo "<p>🔑 <strong>Password:</strong> password123</p>";
        echo "<p><a href='auth/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔑 Try Login Now</a></p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Something is still wrong. Check the debug page.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='debug_login.php'>🔍 Debug Page</a> | <a href='index.php'>🏠 Homepage</a></p>";
?>
