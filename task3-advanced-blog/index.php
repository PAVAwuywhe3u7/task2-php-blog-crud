<?php
/**
 * Enhanced Home Page with Search & Pagination
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/posts.php';
require_once __DIR__ . '/includes/pagination.php';

// Get pagination parameters
$params = getPaginationParams(10); // Default 10 posts per page
$currentPage = $params['page'];
$postsPerPage = $params['per_page'];
$searchTerm = $params['search'];

// Get posts with search and pagination
$posts = getAllPosts($searchTerm, $postsPerPage, ($currentPage - 1) * $postsPerPage);
$totalPosts = getTotalPostCount($searchTerm);

// Calculate pagination
$pagination = calculatePagination($totalPosts, $currentPage, $postsPerPage);

// Get flash message
$flashMessage = getFlashMessage();

// Get recent posts for sidebar
$recentPosts = getRecentPosts(5);

$pageTitle = 'Advanced PHP Blog - Task 3';
if (!empty($searchTerm)) {
    $pageTitle = 'Search Results for "' . htmlspecialchars($searchTerm) . '" - Task 3';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="1000,100 1000,0 0,100"/></svg>');
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .search-container {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .search-form {
            position: relative;
        }
        
        .search-input {
            border: none;
            border-radius: 50px;
            padding: 15px 60px 15px 20px;
            font-size: 1.1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: var(--secondary-gradient);
            color: white;
            border-radius: 50px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .post-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .post-card .card-body {
            padding: 2rem;
        }
        
        .post-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .post-title a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }
        
        .post-title a:hover {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .post-meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .post-content {
            color: #495057;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .sidebar-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .sidebar-card .card-header {
            background: var(--success-gradient);
            color: white;
            border: none;
            border-radius: 15px 15px 0 0;
            padding: 1rem 1.5rem;
        }
        
        .pagination-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin: 2rem 0;
        }
        
        .pagination .page-link {
            border: none;
            color: #667eea;
            margin: 0 2px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .pagination .page-item.active .page-link {
            background: var(--primary-gradient);
            border: none;
        }
        
        .pagination .page-link:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-2px);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .no-posts {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .search-highlight {
            background: linear-gradient(120deg, #a8edea 0%, #fed6e3 100%);
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 0;
            }
            
            .search-container {
                padding: 1rem;
            }
            
            .post-card .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-rocket me-2"></i>Task 3 Blog
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>

                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="posts/create.php">
                                <i class="fas fa-plus me-1"></i>Create Post
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="posts/my-posts.php">
                                <i class="fas fa-user-edit me-1"></i>My Posts
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars(getCurrentUsername()); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
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

    <!-- Hero Section with Search -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="display-4 mb-3 fw-bold">
                    <i class="fas fa-search me-3"></i>Advanced PHP Blog
                </h1>
                <p class="lead mb-4">Task 3: Enhanced with Search, Pagination & Modern UI</p>

                <!-- Statistics -->
                <div class="row justify-content-center mb-4">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-number"><?php echo $totalPosts; ?></div>
                                    <p class="mb-0">Total Posts</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-number"><?php echo isLoggedIn() ? 'Welcome!' : 'Guest'; ?></div>
                                    <p class="mb-0"><?php echo isLoggedIn() ? getCurrentUsername() : 'Please Login'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-number"><i class="fas fa-shield-alt"></i></div>
                                    <p class="mb-0">Secure & Fast</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Container -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="search-container">
                            <h3 class="mb-3">
                                <i class="fas fa-search me-2"></i>Search Blog Posts
                            </h3>
                            <form method="GET" action="index.php" class="search-form">
                                <div class="position-relative">
                                    <input type="text"
                                           name="search"
                                           class="form-control search-input"
                                           placeholder="Search posts by title or content..."
                                           value="<?php echo htmlspecialchars($searchTerm); ?>"
                                           autocomplete="off">
                                    <button type="submit" class="search-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>

                                <?php if (!empty($searchTerm)): ?>
                                    <div class="mt-3">
                                        <a href="index.php" class="btn btn-outline-light btn-sm">
                                            <i class="fas fa-times me-1"></i>Clear Search
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
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
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Flash Messages -->
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search Results Info -->
        <?php if (!empty($searchTerm)): ?>
            <div class="alert alert-info">
                <i class="fas fa-search me-2"></i>
                <strong>Search Results:</strong> Found <?php echo $totalPosts; ?> post(s) for
                "<em><?php echo htmlspecialchars($searchTerm); ?></em>"
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Main Content Column -->
            <div class="col-lg-8">
                <!-- Posts Per Page Selector -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="fas fa-newspaper me-2"></i>
                        <?php echo !empty($searchTerm) ? 'Search Results' : 'Latest Blog Posts'; ?>
                    </h2>

                    <?php echo generatePerPageSelector($postsPerPage, [5, 10, 20, 50]); ?>
                </div>

                <!-- Pagination Info -->
                <?php if ($totalPosts > 0): ?>
                    <div class="mb-3 text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo generatePaginationInfo($pagination); ?>
                    </div>
                <?php endif; ?>

                <!-- Blog Posts -->
                <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <i class="fas fa-file-alt fa-4x text-muted mb-4"></i>
                        <?php if (!empty($searchTerm)): ?>
                            <h4 class="text-muted">No posts found</h4>
                            <p class="text-muted mb-4">
                                No posts match your search for "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>".
                                <br>Try different keywords or browse all posts.
                            </p>
                            <a href="index.php" class="btn btn-gradient">
                                <i class="fas fa-list me-2"></i>View All Posts
                            </a>
                        <?php else: ?>
                            <h4 class="text-muted">No posts yet</h4>
                            <p class="text-muted mb-4">Be the first to create a blog post!</p>
                            <?php if (isLoggedIn()): ?>
                                <a href="posts/create.php" class="btn btn-gradient">
                                    <i class="fas fa-plus me-2"></i>Create First Post
                                </a>
                            <?php else: ?>
                                <a href="auth/login.php" class="btn btn-gradient">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login to Create Posts
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card card">
                            <div class="card-body">
                                <h3 class="post-title">
                                    <a href="posts/view.php?id=<?php echo $post['id']; ?>">
                                        <?php
                                        $title = htmlspecialchars($post['title']);
                                        echo !empty($searchTerm) ? highlightSearchTerms($title, $searchTerm) : $title;
                                        ?>
                                    </a>
                                </h3>

                                <div class="post-meta">
                                    <i class="fas fa-user me-1"></i>
                                    <strong><?php echo htmlspecialchars($post['author_name']); ?></strong>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('g:i A', strtotime($post['created_at'])); ?>
                                </div>

                                <div class="post-content">
                                    <?php
                                    $content = htmlspecialchars(substr($post['content'], 0, 300));
                                    if (strlen($post['content']) > 300) {
                                        $content .= '...';
                                    }
                                    echo !empty($searchTerm) ? highlightSearchTerms($content, $searchTerm) : $content;
                                    ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="posts/view.php?id=<?php echo $post['id']; ?>" class="btn btn-gradient btn-sm">
                                            <i class="fas fa-eye me-1"></i>Read More
                                        </a>

                                        <?php if (isLoggedIn() && getCurrentUserId() == $post['author_id']): ?>
                                            <a href="posts/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <small class="text-muted">
                                        <?php if ($post['updated_at'] && $post['updated_at'] != $post['created_at']): ?>
                                            <i class="fas fa-edit me-1"></i>Updated <?php echo date('M j', strtotime($post['updated_at'])); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="pagination-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted">
                                <?php echo generatePaginationInfo($pagination); ?>
                            </div>
                            <div class="d-none d-md-block">
                                <?php echo generatePerPageSelector($postsPerPage, [5, 10, 20, 50]); ?>
                            </div>
                        </div>

                        <!-- Desktop Pagination -->
                        <div class="d-none d-md-block">
                            <?php
                            $paginationParams = ['search' => $searchTerm, 'per_page' => $postsPerPage];
                            echo generatePaginationHTML($pagination, 'index.php', $paginationParams);
                            ?>
                        </div>

                        <!-- Mobile Pagination -->
                        <div class="d-md-none">
                            <?php echo generateCompactPagination($pagination, 'index.php', $paginationParams); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Search Widget (Mobile) -->
                <div class="sidebar-card d-lg-none mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>Search Posts
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="index.php">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                                <button class="btn btn-gradient" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recent Posts -->
                <?php if (!empty($recentPosts)): ?>
                    <div class="sidebar-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Recent Posts
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($recentPosts as $recentPost): ?>
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <div class="flex-shrink-0">
                                        <div class="bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px; background: var(--primary-gradient);">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">
                                            <a href="posts/view.php?id=<?php echo $recentPost['id']; ?>"
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars(substr($recentPost['title'], 0, 50)); ?>
                                                <?php echo strlen($recentPost['title']) > 50 ? '...' : ''; ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($recentPost['author_name']); ?>
                                            <br>
                                            <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($recentPost['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Blog Stats -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Blog Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stats-number"><?php echo $totalPosts; ?></div>
                                <small class="text-muted">Total Posts</small>
                            </div>
                            <div class="col-6">
                                <div class="stats-number"><?php echo count($recentPosts); ?></div>
                                <small class="text-muted">Recent Posts</small>
                            </div>
                        </div>

                        <?php if (!empty($searchTerm)): ?>
                            <hr>
                            <div class="text-center">
                                <div class="stats-number"><?php echo count($posts); ?></div>
                                <small class="text-muted">Search Results</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Features -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>Task 3 Features
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="feature-list">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-search text-primary me-3"></i>
                                <div>
                                    <strong>Advanced Search</strong>
                                    <br><small class="text-muted">Search by title & content</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-list text-success me-3"></i>
                                <div>
                                    <strong>Smart Pagination</strong>
                                    <br><small class="text-muted">Configurable posts per page</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-mobile-alt text-info me-3"></i>
                                <div>
                                    <strong>Responsive Design</strong>
                                    <br><small class="text-muted">Mobile-first approach</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt text-warning me-3"></i>
                                <div>
                                    <strong>Secure & Fast</strong>
                                    <br><small class="text-muted">PDO & prepared statements</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <?php if (!isLoggedIn()): ?>
                    <div class="sidebar-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-plus me-2"></i>Join Our Community
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <p class="mb-3">Create an account to start writing your own blog posts!</p>
                            <a href="auth/register.php" class="btn btn-gradient w-100 mb-2">
                                <i class="fas fa-user-plus me-2"></i>Register Now
                            </a>
                            <a href="auth/login.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="fas fa-rocket me-2"></i>Task 3: Advanced PHP Blog
                    </h5>
                    <p class="mb-3">
                        Enhanced PHP CRUD blog application with advanced search functionality,
                        smart pagination, and modern responsive UI design.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">PHP 8+</span>
                        <span class="badge bg-primary">MySQL</span>
                        <span class="badge bg-primary">Bootstrap 5</span>
                        <span class="badge bg-primary">PDO</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light text-decoration-none">
                            <i class="fas fa-home me-1"></i>Home
                        </a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="posts/create.php" class="text-light text-decoration-none">
                                <i class="fas fa-plus me-1"></i>Create Post
                            </a></li>
                            <li><a href="posts/my-posts.php" class="text-light text-decoration-none">
                                <i class="fas fa-user-edit me-1"></i>My Posts
                            </a></li>
                        <?php else: ?>
                            <li><a href="auth/login.php" class="text-light text-decoration-none">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a></li>
                            <li><a href="auth/register.php" class="text-light text-decoration-none">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Features</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-search me-1"></i>Advanced Search</li>
                        <li><i class="fas fa-list me-1"></i>Smart Pagination</li>
                        <li><i class="fas fa-mobile-alt me-1"></i>Responsive Design</li>
                        <li><i class="fas fa-shield-alt me-1"></i>Secure Authentication</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">
                        &copy; <?php echo date('Y'); ?> Aerospace Internship - Task 3.
                        Built with ❤️ using PHP & Bootstrap.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <i class="fas fa-code me-1"></i>Advanced PHP Blog Application
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Auto-focus search input on page load
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput && !searchInput.value) {
                // Only focus if no search term is present
                // searchInput.focus();
            }
        });

        // Search form enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.querySelector('.search-form');
            const searchInput = document.querySelector('.search-input');

            if (searchForm && searchInput) {
                // Add loading state on form submit
                searchForm.addEventListener('submit', function() {
                    const submitBtn = searchForm.querySelector('.search-btn');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        submitBtn.disabled = true;
                    }
                });

                // Clear search functionality
                const clearBtn = document.querySelector('.btn-outline-light');
                if (clearBtn && clearBtn.textContent.includes('Clear Search')) {
                    clearBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        window.location.href = 'index.php';
                    });
                }
            }
        });

        // Smooth scroll for pagination
        document.addEventListener('DOMContentLoaded', function() {
            const paginationLinks = document.querySelectorAll('.pagination .page-link');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Scroll to top of posts section
                    const postsSection = document.querySelector('.col-lg-8 h2');
                    if (postsSection) {
                        postsSection.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
        });

        // Enhanced hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const postCards = document.querySelectorAll('.post-card');
            postCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Per page selector functionality
        function changePerPage(perPage) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1); // Reset to first page
            window.location = url.toString();
        }
    </script>
</body>
</html>
