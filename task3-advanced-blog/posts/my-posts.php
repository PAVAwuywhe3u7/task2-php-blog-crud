<?php
/**
 * My Posts - User's Posts with Pagination
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/posts.php';
require_once __DIR__ . '/../includes/pagination.php';

// Require login
requireLogin();

// Get pagination parameters
$params = getPaginationParams(10);
$currentPage = $params['page'];
$postsPerPage = $params['per_page'];
$searchTerm = $params['search'];

// Get user's posts with search and pagination
$userId = getCurrentUserId();
$posts = getPostsByAuthor($userId, $searchTerm, $postsPerPage, ($currentPage - 1) * $postsPerPage);
$totalPosts = getTotalPostCountByAuthor($userId, $searchTerm);

// Calculate pagination
$pagination = calculatePagination($totalPosts, $currentPage, $postsPerPage);

// Get flash message
$flashMessage = getFlashMessage();

$pageTitle = 'My Posts - Task 3 Blog';
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
            padding: 3rem 0;
            text-align: center;
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
        
        .search-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fas fa-rocket me-2"></i>Task 3 Blog
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-home me-1"></i>Home
                </a>
                <a class="nav-link" href="create.php">
                    <i class="fas fa-plus me-1"></i>Create Post
                </a>
                <a class="nav-link active" href="my-posts.php">
                    <i class="fas fa-user-edit me-1"></i>My Posts
                </a>
                <a class="nav-link" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-5 mb-3">
                <i class="fas fa-user-edit me-2"></i>My Posts
            </h1>
            <p class="lead">Manage your blog posts</p>
            <p class="mb-0">Welcome back, <strong><?php echo htmlspecialchars(getCurrentUsername()); ?></strong>!</p>
        </div>
    </section>

    <div class="container">
        <!-- Flash Messages -->
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search Container -->
        <div class="search-container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-3 mb-md-0">
                        <i class="fas fa-search me-2"></i>Search My Posts
                    </h4>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="my-posts.php">
                        <div class="input-group">
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Search your posts..." 
                                   value="<?php echo htmlspecialchars($searchTerm); ?>">
                            <button class="btn btn-gradient" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (!empty($searchTerm)): ?>
                <div class="mt-3">
                    <span class="badge bg-info">
                        <i class="fas fa-search me-1"></i>
                        Searching for: "<?php echo htmlspecialchars($searchTerm); ?>"
                    </span>
                    <a href="my-posts.php" class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Posts Section -->
        <div class="row">
            <div class="col-lg-8">
                <!-- Posts Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>
                        <?php echo !empty($searchTerm) ? 'Search Results' : 'Your Posts'; ?>
                        <span class="badge bg-primary"><?php echo $totalPosts; ?></span>
                    </h3>
                    
                    <a href="create.php" class="btn btn-gradient">
                        <i class="fas fa-plus me-1"></i>New Post
                    </a>
                </div>

                <!-- Pagination Info -->
                <?php if ($totalPosts > 0): ?>
                    <div class="mb-3 text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo generatePaginationInfo($pagination); ?>
                    </div>
                <?php endif; ?>

                <!-- Posts List -->
                <?php if (empty($posts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-4x text-muted mb-4"></i>
                        <?php if (!empty($searchTerm)): ?>
                            <h4 class="text-muted">No posts found</h4>
                            <p class="text-muted mb-4">
                                No posts match your search for "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>".
                            </p>
                            <a href="my-posts.php" class="btn btn-gradient">
                                <i class="fas fa-list me-2"></i>View All My Posts
                            </a>
                        <?php else: ?>
                            <h4 class="text-muted">No posts yet</h4>
                            <p class="text-muted mb-4">You haven't created any blog posts yet. Start sharing your thoughts!</p>
                            <a href="create.php" class="btn btn-gradient">
                                <i class="fas fa-plus me-2"></i>Create Your First Post
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="view.php?id=<?php echo $post['id']; ?>" class="text-decoration-none">
                                        <?php 
                                        $title = htmlspecialchars($post['title']);
                                        echo !empty($searchTerm) ? highlightSearchTerms($title, $searchTerm) : $title;
                                        ?>
                                    </a>
                                </h5>
                                
                                <div class="text-muted mb-3">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('g:i A', strtotime($post['created_at'])); ?>
                                    
                                    <?php if ($post['updated_at'] && $post['updated_at'] != $post['created_at']): ?>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-edit me-1"></i>
                                        Updated <?php echo date('M j', strtotime($post['updated_at'])); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-text">
                                    <?php 
                                    $content = htmlspecialchars(substr($post['content'], 0, 200));
                                    if (strlen($post['content']) > 200) {
                                        $content .= '...';
                                    }
                                    echo !empty($searchTerm) ? highlightSearchTerms($content, $searchTerm) : $content;
                                    ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="view.php?id=<?php echo $post['id']; ?>" class="btn btn-gradient btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                    </div>
                                    
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="confirmDelete(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?php 
                        $paginationParams = ['search' => $searchTerm, 'per_page' => $postsPerPage];
                        echo generatePaginationHTML($pagination, 'my-posts.php', $paginationParams); 
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Your Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="text-primary"><?php echo $totalPosts; ?></h3>
                                <small class="text-muted">Total Posts</small>
                            </div>
                            <div class="col-6">
                                <h3 class="text-success"><?php echo count($posts); ?></h3>
                                <small class="text-muted">This Page</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(postId, postTitle) {
            if (confirm('Are you sure you want to delete "' + postTitle + '"?\n\nThis action cannot be undone.')) {
                window.location.href = 'delete.php?id=' + postId;
            }
        }
    </script>
</body>
</html>
