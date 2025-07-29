<?php
// Quick loading version with database
$start_time = microtime(true);

// Fast database connection with timeout
try {
    $pdo = new PDO("mysql:host=localhost;dbname=php_blog_final;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 2,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    // Quick queries with limits
    $posts = $pdo->query("SELECT title, excerpt FROM posts WHERE status='published' LIMIT 2")->fetchAll();
    $user_count = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $db_connected = true;
    
} catch (Exception $e) {
    $posts = [
        ['title' => 'Welcome to Task 5 Blog', 'excerpt' => 'Complete PHP blog application with all features integrated.'],
        ['title' => 'Security Features Demo', 'excerpt' => 'Advanced security with CSRF protection, input validation, and more.']
    ];
    $user_count = 5;
    $db_connected = false;
}

$load_time = round((microtime(true) - $start_time) * 1000, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task 5: Quick Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .load-time { position: fixed; top: 10px; right: 10px; z-index: 1000; }
    </style>
</head>
<body>
    <!-- Load Time Display -->
    <div class="load-time">
        <span class="badge bg-success">⚡ <?= $load_time ?>ms</span>
    </div>

    <nav class="navbar navbar-dark hero">
        <div class="container">
            <span class="navbar-brand">🚀 Task 5: Quick Demo</span>
            <div>
                <span class="badge bg-light text-dark me-2">
                    <?= $db_connected ? '✅ DB Connected' : '⚠️ Demo Mode' ?>
                </span>
                <a href="login.php" class="btn btn-light btn-sm">Login</a>
            </div>
        </div>
    </nav>

    <div class="hero text-white py-4">
        <div class="container text-center">
            <h1>🎓 Task 5 Complete!</h1>
            <p class="lead">Fast-loading PHP Blog - Aerospace Internship Program</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="alert alert-success">
                        <h5>✅ All Features Integrated Successfully!</h5>
                        <p class="mb-0">Authentication • CRUD • Search • Security • Role-based Access</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row">
            <div class="col-md-8">
                <h3>📝 Recent Posts</h3>
                
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($post['title']) ?></h5>
                            <p class="text-muted"><?= htmlspecialchars($post['excerpt']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="text-center">
                    <a href="ultra-fast.php" class="btn btn-success me-2">🚀 Ultra Fast</a>
                    <a href="login.php" class="btn btn-primary">🔐 Login Demo</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>🔑 Demo Credentials</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Admin:</strong><br><code>admin / AdminPass123!</code></p>
                        <p><strong>Editor:</strong><br><code>editor / EditorPass123!</code></p>
                        <p class="mb-3"><strong>User:</strong><br><code>user / UserPass123!</code></p>
                        <a href="login.php" class="btn btn-primary w-100">Login Now</a>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>📊 Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <p>👥 Users: <?= $user_count ?></p>
                        <p>📝 Posts: <?= count($posts) ?> shown</p>
                        <p>⚡ Load Time: <?= $load_time ?>ms</p>
                        <p>🗄️ Database: <?= $db_connected ? 'Connected' : 'Demo Mode' ?></p>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>🎯 Version Comparison</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="ultra-fast.php" class="btn btn-success btn-sm">🚀 Ultra Fast (~30ms)</a>
                            <a href="quick.php" class="btn btn-warning btn-sm">⚡ Quick (~100ms)</a>
                            <a href="index.php" class="btn btn-secondary btn-sm">🔧 Full Features</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-3">
        <div class="container text-center">
            <h6>🎓 Task 5: Final Project & Certification</h6>
            <p class="mb-0">Aerospace Internship Program • Built by Pavan Karthik Tummepalli</p>
            <small>⚡ Loaded in <?= $load_time ?>ms • <?= $db_connected ? 'Live Database' : 'Demo Mode' ?></small>
        </div>
    </footer>

    <script>
        // Show load time in console
        console.log('Page loaded in: <?= $load_time ?>ms');
        
        // Auto-refresh load time display
        setTimeout(() => {
            document.querySelector('.load-time .badge').innerHTML = '✅ Ready';
        }, 2000);
    </script>
</body>
</html>
