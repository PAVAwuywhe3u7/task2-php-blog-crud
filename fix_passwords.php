<?php
// Database connection
$host = 'localhost';
$dbname = 'php_blog_final';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully!<br><br>";

    // Fix password hashes for all users
    $passwords = [
        'admin' => 'AdminPass123!',
        'editor' => 'EditorPass123!',
        'john_doe' => 'UserPass123!',
        'jane_smith' => 'UserPass123!',
        'alex_wilson' => 'UserPass123!'
    ];

    echo "Updating password hashes in database:<br><br>";

    foreach ($passwords as $user => $pass) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        echo "Username: $user<br>";
        echo "Password: $pass<br>";
        echo "Hash: $hash<br>";

        // Update in database
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $result = $stmt->execute([$hash, $user]);

        if ($result) {
            echo "✅ Updated successfully!<br><br>";
        } else {
            echo "❌ Update failed!<br><br>";
        }
    }

    // Test verification
    echo "Testing password verification:<br>";
    $testHash = password_hash('EditorPass123!', PASSWORD_DEFAULT);
    $verify = password_verify('EditorPass123!', $testHash);
    echo "Test hash: $testHash<br>";
    echo "Verification result: " . ($verify ? 'SUCCESS' : 'FAILED') . "<br><br>";

    echo "All passwords updated successfully!<br>";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
