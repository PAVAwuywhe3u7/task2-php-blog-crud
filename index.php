<?php
/**
 * Home Page - Blog Post Listing
 * Task 2: PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/posts.php';

// Get all posts
$posts = getAllPosts();
$totalPosts = getTotalPostCount();

// Get flash message
$flashMessage = getFlashMessage();

$pageTitle = 'PHP Blog - Task 2';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
        }
        .post-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .post-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .post-content {
            color: #495057;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-blog me-2"></i>PHP Blog
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="posts/create.php">
                                <i class="fas fa-plus me-1"></i>New Post
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="posts/my-posts.php">
                                <i class="fas fa-user-edit me-1"></i>My Posts
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars(getCurrentUsername()); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="posts/my-posts.php">
                                    <i class="fas fa-user-edit me-2"></i>My Posts
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-3">
                <i class="fas fa-blog me-3"></i>PHP Blog Application
            </h1>
            <p class="lead mb-4">Task 2: Complete CRUD Blog with User Authentication</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3><?php echo $totalPosts; ?></h3>
                            <p>Total Posts</p>
                        </div>
                        <div class="col-md-4">
                            <h3><?php echo isLoggedIn() ? 'Welcome!' : 'Guest'; ?></h3>
                            <p><?php echo isLoggedIn() ? getCurrentUsername() : 'Please Login'; ?></p>
                        </div>
                        <div class="col-md-4">
                            <h3><i class="fas fa-shield-alt"></i></h3>
                            <p>Secure & Fast</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!isLoggedIn()): ?>
                <div class="mt-4">
                    <a href="auth/login.php" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a href="auth/register.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                </div>
            <?php else: ?>
                <div class="mt-4">
                    <a href="posts/create.php" class="btn btn-light btn-lg">
                        <i class="fas fa-plus me-2"></i>Create New Post
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Flash Messages -->
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($flashMessage['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Blog Posts -->
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4">
                    <i class="fas fa-newspaper me-2"></i>Latest Blog Posts
                </h2>

                <?php if (empty($posts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No posts yet</h4>
                        <p class="text-muted">Be the first to create a blog post!</p>
                        <?php if (isLoggedIn()): ?>
                            <a href="posts/create.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create First Post
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="card post-card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="posts/view.php?id=<?php echo $post['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h5>

                                <div class="post-meta mb-3">
                                    <i class="fas fa-user me-1"></i>
                                    By <strong><?php echo htmlspecialchars($post['author_name']); ?></strong>
                                    <i class="fas fa-calendar ms-3 me-1"></i>
                                    <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                    <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                        <small class="text-muted">(Updated: <?php echo date('F j, Y', strtotime($post['updated_at'])); ?>)</small>
                                    <?php endif; ?>
                                </div>

                                <div class="post-content">
                                    <?php
                                    $content = htmlspecialchars($post['content']);
                                    echo strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
                                    ?>
                                </div>

                                <div class="mt-3">
                                    <a href="posts/view.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Read More
                                    </a>

                                    <?php if (isLoggedIn() && getCurrentUserId() == $post['author_id']): ?>
                                        <a href="posts/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>About This Blog</h5>
                    </div>
                    <div class="card-body">
                        <p>This is a complete PHP blog application built for Task 2 of the Aerospace Internship project.</p>

                        <h6>Features:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>User Registration & Login</li>
                            <li><i class="fas fa-check text-success me-2"></i>Create, Edit, Delete Posts</li>
                            <li><i class="fas fa-check text-success me-2"></i>Secure Authentication</li>
                            <li><i class="fas fa-check text-success me-2"></i>Responsive Design</li>
                            <li><i class="fas fa-check text-success me-2"></i>CSRF Protection</li>
                        </ul>

                        <h6>Technologies:</h6>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge bg-primary">PHP 8+</span>
                            <span class="badge bg-primary">MySQL</span>
                            <span class="badge bg-primary">PDO</span>
                            <span class="badge bg-primary">Bootstrap 5</span>
                            <span class="badge bg-primary">Font Awesome</span>
                        </div>
                    </div>
                </div>

                <?php if (!isLoggedIn()): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5><i class="fas fa-user-plus me-2"></i>Join Our Community</h5>
                        </div>
                        <div class="card-body text-center">
                            <p>Create an account to start writing your own blog posts!</p>
                            <a href="auth/register.php" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Register Now
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-code me-2"></i>
                PHP Blog Application - Task 2 | Aerospace Internship Project
            </p>
            <small class="text-muted">Built with PHP, MySQL, Bootstrap & ❤️</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>