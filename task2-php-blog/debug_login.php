<?php
/**
 * Debug Login - Check Database and Users
 * Task 2: PHP Blog Application
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>🔍 Login Debug Information</h2>";

// Test database connection
echo "<h3>1. Database Connection Test:</h3>";
try {
    $pdo = getDBConnection();
    echo "✅ <strong>Database connection: SUCCESS</strong><br>";
} catch (Exception $e) {
    echo "❌ <strong>Database connection: FAILED</strong><br>";
    echo "Error: " . $e->getMessage() . "<br>";
    exit();
}

// Check if users table exists and has data
echo "<h3>2. Users Table Check:</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ <strong>Users table exists</strong><br>";
    echo "📊 <strong>Total users in database: " . $result['count'] . "</strong><br>";
} catch (Exception $e) {
    echo "❌ <strong>Users table: ERROR</strong><br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Show all users (without passwords)
echo "<h3>3. Available Users:</h3>";
try {
    $stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "❌ <strong>No users found in database!</strong><br>";
        echo "<p>You need to run the database_setup.sql file in phpMyAdmin.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($user['username']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "❌ <strong>Error fetching users:</strong><br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test password verification for admin user
echo "<h3>4. Password Verification Test:</h3>";
try {
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ <strong>Admin user found</strong><br>";
        echo "🔑 <strong>Testing password 'password123'...</strong><br>";
        
        if (password_verify('password123', $admin['password'])) {
            echo "✅ <strong>Password verification: SUCCESS</strong><br>";
            echo "🎉 <strong>Login should work with admin/password123</strong><br>";
        } else {
            echo "❌ <strong>Password verification: FAILED</strong><br>";
            echo "🔧 <strong>Need to reset admin password</strong><br>";
        }
    } else {
        echo "❌ <strong>Admin user not found!</strong><br>";
    }
} catch (Exception $e) {
    echo "❌ <strong>Error testing password:</strong><br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>🎯 Quick Actions:</h3>";
echo "<p><a href='index.php'>← Back to Blog</a></p>";
echo "<p><a href='auth/login.php'>🔑 Try Login Page</a></p>";
echo "<p><a href='auth/register.php'>📝 Register New Account</a></p>";

// Show current session info
echo "<h3>5. Session Information:</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "✅ <strong>You are currently logged in as:</strong> " . htmlspecialchars($_SESSION['username']) . "<br>";
    echo "<p><a href='auth/logout.php'>Logout</a></p>";
} else {
    echo "ℹ️ <strong>You are not currently logged in</strong><br>";
}
?>
