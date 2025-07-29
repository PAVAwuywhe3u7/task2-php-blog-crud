<?php
/**
 * Fast Loading Homepage - Optimized for Speed
 */

// Minimal initialization
define('APP_INIT', true);

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=php_blog_final;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (Exception $e) {
    die('Database connection failed');
}

// Get posts quickly
$posts = $pdo->query("
    SELECT p.id, p.title, p.slug, p.excerpt, p.published_at, p.view_count,
           u.first_name, u.last_name, c.name as category_name
    FROM posts p
    LEFT JOIN users u ON p.author_id = u.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.published_at DESC
    LIMIT 6
")->fetchAll();

// Get categories
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task 5: Final Blog - Fast Version</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark hero">
        <div class="container">
            <a class="navbar-brand fw-bold" href="fast.php">
                <i class="fas fa-rocket me-2"></i>Task 5: Final Blog
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="login.php">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <a class="nav-link" href="index.php">
                    <i class="fas fa-cog me-1"></i>Full Version
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">
                <i class="fas fa-rocket me-3"></i>Final Project Complete!
            </h1>
            <p class="lead mb-4">
                Fast-loading PHP Blog Application - Aerospace Internship Program
            </p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="login.php" class="btn btn-light btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Login Demo
                </a>
                <a href="index.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-eye me-2"></i>Full Features
                </a>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Quick Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="index.php" class="row g-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="search" placeholder="Search posts...">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Blog Posts -->
                <div class="row">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <?= htmlspecialchars(substr($post['excerpt'] ?: 'No excerpt available', 0, 100)) ?>...
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-eye me-1"></i>
                                            <?= number_format($post['view_count']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-2"></i>View All Posts
                    </a>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Demo Credentials -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-key me-2"></i>Demo Login
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Admin:</strong><br>
                            <code>admin / AdminPass123!</code>
                        </div>
                        <div class="mb-3">
                            <strong>Editor:</strong><br>
                            <code>editor / EditorPass123!</code>
                        </div>
                        <div class="mb-0">
                            <strong>User:</strong><br>
                            <code>user / UserPass123!</code>
                        </div>
                        <hr>
                        <a href="login.php" class="btn btn-success w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Login Now
                        </a>
                    </div>
                </div>

                <!-- Categories -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tags me-2"></i>Categories
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($categories as $cat): ?>
                            <a href="index.php?category=<?= $cat['id'] ?>" 
                               class="badge bg-secondary text-decoration-none me-2 mb-2">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Features -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>Features
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-shield-alt text-success me-2"></i>
                                Secure Authentication
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-search text-info me-2"></i>
                                Advanced Search
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-users text-warning me-2"></i>
                                Role-Based Access
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-mobile-alt text-primary me-2"></i>
                                Responsive Design
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-rocket text-danger me-2"></i>
                                Fast Performance
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-rocket me-2"></i>
                Task 5: Final Project & Certification - Aerospace Internship Program
            </p>
            <small class="text-muted">
                Built by Pavan Karthik Tummepalli • Fast-loading optimized version
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
