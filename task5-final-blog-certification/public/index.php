<?php
/**
 * Task 5: Final Project & Certification - Optimized Main Index
 * Aerospace Internship Program - Complete Blog Application
 *
 * Fast-loading homepage with optimized queries
 */

// Initialize application
define('APP_INIT', true);
require_once '../config/config.php';

// Get parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$search = sanitizeInput($_GET['search'] ?? '');
$category = (int)($_GET['category'] ?? 0);
$author = (int)($_GET['author'] ?? 0);

// Initialize database
$db = Database::getInstance();

// Optimized single query for posts with all needed data
$offset = ($page - 1) * POSTS_PER_PAGE;
$params = [];

$sql = "SELECT p.*,
               u.username as author_username,
               u.first_name,
               u.last_name,
               c.name as category_name,
               c.slug as category_slug,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
        FROM posts p
        LEFT JOIN users u ON p.author_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'";

// Add search condition
if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Add category filter
if ($category) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
}

// Add author filter
if ($author) {
    $sql .= " AND p.author_id = ?";
    $params[] = $author;
}

$sql .= " ORDER BY p.is_featured DESC, p.published_at DESC LIMIT ? OFFSET ?";
$params[] = POSTS_PER_PAGE;
$params[] = $offset;

// Get posts
$posts = $db->fetchAll($sql, $params);

// Quick count query
$countSql = "SELECT COUNT(*) as total FROM posts p WHERE p.status = 'published'";
$countParams = [];

if (!empty($search)) {
    $countSql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
}
if ($category) {
    $countSql .= " AND p.category_id = ?";
    $countParams[] = $category;
}
if ($author) {
    $countSql .= " AND p.author_id = ?";
    $countParams[] = $author;
}

$totalPosts = $db->fetch($countSql, $countParams)['total'];
$totalPages = ceil($totalPosts / POSTS_PER_PAGE);

// Get categories (cached)
$categories = $db->fetchAll("SELECT id, name FROM categories ORDER BY name LIMIT 20");

// Get featured posts (only if needed)
$featuredPosts = [];
if (empty($search) && !$category && $page === 1) {
    $featuredPosts = $db->fetchAll("
        SELECT p.id, p.title, p.slug, p.published_at, u.first_name, u.last_name
        FROM posts p
        LEFT JOIN users u ON p.author_id = u.id
        WHERE p.status = 'published' AND p.is_featured = 1
        ORDER BY p.published_at DESC
        LIMIT 3
    ");
}

// Page title and meta
$pageTitle = 'Home';
if ($search) {
    $pageTitle = "Search: " . htmlspecialchars($search);
}
if ($category) {
    $categoryName = '';
    foreach ($categories as $cat) {
        if ($cat['id'] == $category) {
            $categoryName = $cat['name'];
            break;
        }
    }
    if ($categoryName) {
        $pageTitle = "Category: " . $categoryName;
    }
}

$pageDescription = 'Fast PHP Blog - Aerospace Internship Final Project';

// Include header
include '../templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Hero Section -->
            <?php if ($page === 1 && empty($search) && empty($category)): ?>
            <div class="hero-section bg-primary text-white rounded-3 p-5 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-4 fw-bold mb-3">
                            <i class="fas fa-rocket me-3"></i>Final Blog Certification
                        </h1>
                        <p class="lead mb-4">
                            Complete PHP Blog Application showcasing advanced features including 
                            authentication, CRUD operations, search, pagination, and role-based access control.
                        </p>
                        <div class="d-flex gap-3">
                            <?php if (!isLoggedIn()): ?>
                                <a href="login.php" class="btn btn-light btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </a>
                                <a href="register.php" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Register
                                </a>
                            <?php else: ?>
                                <a href="dashboard.php" class="btn btn-light btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                                <?php if (isEditor()): ?>
                                    <a href="create-post.php" class="btn btn-outline-light btn-lg">
                                        <i class="fas fa-plus me-2"></i>New Post
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-blog display-1 opacity-75"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Search and Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       value="<?= htmlspecialchars($search) ?>"
                                       placeholder="Search posts...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Info -->
            <?php if ($search || $category): ?>
            <div class="alert alert-info d-flex align-items-center mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <div>
                    Found <strong><?= $totalPosts ?></strong> post(s)
                    <?php if ($search): ?>
                        for "<strong><?= htmlspecialchars($search) ?></strong>"
                    <?php endif; ?>
                    <?php if ($category): ?>
                        in category "<strong><?= htmlspecialchars($categoryInfo['name'] ?? '') ?></strong>"
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-sm btn-outline-primary ms-3">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Blog Posts -->
            <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search display-1 text-muted mb-3"></i>
                    <h3 class="text-muted">No posts found</h3>
                    <p class="text-muted">
                        <?php if ($search || $category): ?>
                            Try adjusting your search criteria or browse all posts.
                        <?php else: ?>
                            Be the first to create a post!
                        <?php endif; ?>
                    </p>
                    <?php if (isEditor()): ?>
                        <a href="create-post.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create First Post
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($posts as $postItem): ?>
                        <div class="col-md-6 mb-4">
                            <article class="card h-100 shadow-sm">
                                <?php if ($postItem['featured_image']): ?>
                                    <img src="<?= UPLOADS_URL ?>/<?= htmlspecialchars($postItem['featured_image']) ?>" 
                                         class="card-img-top" 
                                         alt="<?= htmlspecialchars($postItem['title']) ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                
                                <div class="card-body d-flex flex-column">
                                    <?php if ($postItem['is_featured']): ?>
                                        <div class="badge bg-warning text-dark mb-2 align-self-start">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h5 class="card-title">
                                        <a href="post.php?slug=<?= urlencode($postItem['slug']) ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($postItem['title']) ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted flex-grow-1">
                                        <?= htmlspecialchars($postItem['excerpt']) ?>
                                    </p>
                                    
                                    <div class="card-footer bg-transparent border-0 px-0 pb-0">
                                        <div class="d-flex justify-content-between align-items-center text-muted small">
                                            <div>
                                                <i class="fas fa-user me-1"></i>
                                                <a href="?author=<?= $postItem['author_id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($postItem['first_name'] . ' ' . $postItem['last_name']) ?>
                                                </a>
                                            </div>
                                            <div>
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= formatDate($postItem['published_at']) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <div class="text-muted small">
                                                <?php if ($postItem['category_name']): ?>
                                                    <a href="?category=<?= $postItem['category_id'] ?>" 
                                                       class="badge bg-secondary text-decoration-none">
                                                        <?= htmlspecialchars($postItem['category_name']) ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-eye me-1"></i><?= number_format($postItem['view_count']) ?>
                                                <i class="fas fa-comments ms-2 me-1"></i><?= $postItem['comment_count'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Blog pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Page -->
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $category ? '&category=' . $category : '' ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $category ? '&category=' . $category : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $category ? '&category=' . $category : '' ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        
                        <div class="text-center text-muted small mt-2">
                            Showing page <?= $page ?> of <?= $totalPages ?> 
                            (<?= number_format($totalPosts) ?> total posts)
                        </div>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 2rem;">
                <!-- User Info -->
                <?php if (isLoggedIn()): ?>
                    <?php $user = getCurrentUser(); ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>Welcome Back
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                            </p>
                            <p class="text-muted small mb-3">
                                Role: <span class="badge bg-primary"><?= ucfirst($user['role']) ?></span>
                            </p>
                            <div class="d-grid gap-2">
                                <a href="dashboard.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                                <?php if (isEditor()): ?>
                                    <a href="create-post.php" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus me-1"></i>New Post
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Featured Posts -->
                <?php if (!empty($featuredPosts)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-star me-2"></i>Featured Posts
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($featuredPosts as $featured): ?>
                                <div class="mb-3 pb-3 <?= $featured !== end($featuredPosts) ? 'border-bottom' : '' ?>">
                                    <h6 class="mb-1">
                                        <a href="post.php?slug=<?= urlencode($featured['slug']) ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($featured['title']) ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        by <?= htmlspecialchars($featured['first_name'] . ' ' . $featured['last_name']) ?>
                                        • <?= formatDate($featured['published_at']) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Categories -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tags me-2"></i>Categories
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($categories as $cat): ?>
                                <a href="?category=<?= $cat['id'] ?>" 
                                   class="badge bg-secondary text-decoration-none">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Blog Stats
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stats = $db->getStats();
                        ?>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="h4 text-primary mb-0"><?= number_format($stats['posts'] ?? 0) ?></div>
                                <small class="text-muted">Posts</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="h4 text-success mb-0"><?= number_format($stats['users'] ?? 0) ?></div>
                                <small class="text-muted">Authors</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-info mb-0"><?= number_format($stats['categories'] ?? 0) ?></div>
                                <small class="text-muted">Categories</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-warning mb-0"><?= number_format($stats['comments'] ?? 0) ?></div>
                                <small class="text-muted">Comments</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
