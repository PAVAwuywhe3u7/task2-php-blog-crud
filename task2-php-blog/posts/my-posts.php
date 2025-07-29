<?php
/**
 * My Posts Page
 * Task 2: PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/posts.php';

// Require user to be logged in
requireLogin();

// Get user's posts
$userPosts = getPostsByAuthor(getCurrentUserId());

// Get flash message
$flashMessage = getFlashMessage();

$pageTitle = 'My Posts';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - PHP Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .posts-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .post-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .post-card:hover {
            transform: translateY(-2px);
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-blog me-2"></i>PHP Blog
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-home me-1"></i>Home
                </a>
                <a class="nav-link" href="create.php">
                    <i class="fas fa-plus me-1"></i>New Post
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

    <!-- Header -->
    <section class="posts-header">
        <div class="container text-center">
            <h1><i class="fas fa-user-edit me-3"></i>My Posts</h1>
            <p class="lead">Manage your blog posts</p>
            <div class="mt-3">
                <span class="badge bg-light text-dark fs-6">
                    <?php echo count($userPosts); ?> Post<?php echo count($userPosts) !== 1 ? 's' : ''; ?>
                </span>
            </div>
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

        <!-- Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-list me-2"></i>Your Posts</h2>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create New Post
            </a>
        </div>

        <!-- Posts List -->
        <?php if (empty($userPosts)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">No posts yet</h4>
                <p class="text-muted mb-4">You haven't created any blog posts yet. Start sharing your thoughts!</p>
                <a href="create.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Create Your First Post
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($userPosts as $post): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card post-card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <a href="view.php?id=<?php echo $post['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h5>
                                
                                <div class="post-meta mb-3">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                    <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                        <br><small class="text-muted">
                                            <i class="fas fa-edit me-1"></i>
                                            Updated: <?php echo date('M j, Y', strtotime($post['updated_at'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-text flex-grow-1">
                                    <?php 
                                    $content = htmlspecialchars($post['content']);
                                    echo strlen($content) > 120 ? substr($content, 0, 120) . '...' : $content;
                                    ?>
                                </div>
                                
                                <div class="mt-3">
                                    <div class="btn-group w-100" role="group">
                                        <a href="view.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal<?php echo $post['id']; ?>">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal for each post -->
                    <div class="modal fade" id="deleteModal<?php echo $post['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                        Confirm Delete
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this post? This action cannot be undone.</p>
                                    <p><strong>Post:</strong> <?php echo htmlspecialchars($post['title']); ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <a href="delete.php?id=<?php echo $post['id']; ?>" class="btn btn-danger">
                                        <i class="fas fa-trash me-2"></i>Delete Post
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
