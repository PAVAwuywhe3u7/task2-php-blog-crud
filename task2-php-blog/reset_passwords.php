<?php
/**
 * Reset Passwords Tool
 * Task 2: PHP Blog Application
 * Use this if login passwords are not working
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>🔧 Password Reset Tool</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_passwords'])) {
    try {
        $pdo = getDBConnection();
        
        // Reset admin password
        $adminPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$adminPassword]);
        
        // Reset john_doe password
        $johnPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'john_doe'");
        $stmt->execute([$johnPassword]);
        
        // Reset jane_smith password
        $janePassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'jane_smith'");
        $stmt->execute([$janePassword]);
        
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "✅ <strong>SUCCESS!</strong> All passwords have been reset to 'password123'<br>";
        echo "🔑 You can now login with:<br>";
        echo "• <strong>admin</strong> / password123<br>";
        echo "• <strong>john_doe</strong> / password123<br>";
        echo "• <strong>jane_smith</strong> / password123<br>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "❌ <strong>ERROR:</strong> " . $e->getMessage();
        echo "</div>";
    }
}

// Show current users
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT username, email, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    echo "<h3>📊 Current Users in Database:</h3>";
    if (empty($users)) {
        echo "<p style='color: red;'>❌ No users found! You need to import database_setup.sql first.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Username</th><th>Email</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($user['username']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>

<form method="POST" style="margin: 20px 0;">
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0; border-radius: 5px;">
        <h4>🔧 Reset All User Passwords</h4>
        <p>This will reset all user passwords to 'password123'</p>
        <button type="submit" name="reset_passwords" 
                style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;"
                onclick="return confirm('Are you sure you want to reset all passwords?')">
            🔄 Reset Passwords
        </button>
    </div>
</form>

<hr>
<h3>🎯 Quick Links:</h3>
<p><a href="debug_login.php">🔍 Debug Login</a></p>
<p><a href="auth/login.php">🔑 Login Page</a></p>
<p><a href="index.php">🏠 Homepage</a></p>
