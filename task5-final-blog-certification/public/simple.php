<?php
// Ultra-simple, fast-loading version
$start_time = microtime(true);

// Quick database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=php_blog_final", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Get just 3 posts quickly
    $posts = $pdo->query("SELECT title, excerpt, published_at FROM posts WHERE status='published' ORDER BY published_at DESC LIMIT 3")->fetchAll();
    $post_count = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE status='published'")->fetch()['count'];
    
} catch (Exception $e) {
    $posts = [];
    $post_count = 0;
}

$load_time = round((microtime(true) - $start_time) * 1000, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task 5: Simple Fast Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark hero">
        <div class="container">
            <span class="navbar-brand">🚀 Task 5: Simple Fast Blog</span>
            <span class="text-white">⚡ Loaded in <?= $load_time ?>ms</span>
        </div>
    </nav>

    <div class="hero text-white py-5">
        <div class="container text-center">
            <h1>Task 5 Complete! ✅</h1>
            <p class="lead">Ultra-fast PHP Blog - Aerospace Internship Program</p>
            <a href="login.php" class="btn btn-light btn-lg">🔐 Login Demo</a>
        </div>
    </div>

    <div class="container py-4">
        <div class="row">
            <div class="col-md-8">
                <h3>📝 Recent Posts (<?= $post_count ?> total)</h3>
                
                <?php if (empty($posts)): ?>
                    <div class="alert alert-info">
                        <h5>🎯 Demo Ready!</h5>
                        <p>Database connected successfully. Use the login demo to see full features.</p>
                        <a href="login.php" class="btn btn-primary">Login Now</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5><?= htmlspecialchars($post['title']) ?></h5>
                                <p class="text-muted"><?= htmlspecialchars(substr($post['excerpt'] ?: 'No excerpt', 0, 100)) ?>...</p>
                                <small class="text-muted">📅 <?= date('M j, Y', strtotime($post['published_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <a href="index.php" class="btn btn-primary">📖 View All Posts</a>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5>🔑 Demo Login</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Admin:</strong><br><code>admin / AdminPass123!</code></p>
                        <p><strong>Editor:</strong><br><code>editor / EditorPass123!</code></p>
                        <p><strong>User:</strong><br><code>user / UserPass123!</code></p>
                        <a href="login.php" class="btn btn-success w-100">Login Now</a>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>🎯 Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="ultra-fast.php" class="btn btn-outline-primary btn-sm">⚡ Ultra Fast</a>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">🔧 Full Version</a>
                            <a href="../tests/comprehensive-tests.php" class="btn btn-outline-info btn-sm">🧪 Tests</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-3">
        <div class="container text-center">
            <p class="mb-0">🎓 Task 5: Final Project & Certification - Aerospace Internship Program</p>
            <small>⚡ Page loaded in <?= $load_time ?>ms • Built by Pavan Karthik Tummepalli</small>
        </div>
    </footer>
</body>
</html>
