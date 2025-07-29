<?php
/**
 * View Single Post Page
 * Task 2: PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/posts.php';

// Get post ID from URL
$postId = $_GET['id'] ?? null;

if (!$postId || !is_numeric($postId)) {
    setFlashMessage('Invalid post ID.', 'error');
    header('Location: ../index.php');
    exit();
}

// Get the post
$post = getPostById($postId);

if (!$post) {
    setFlashMessage('Post not found.', 'error');
    header('Location: ../index.php');
    exit();
}

// Get flash message
$flashMessage = getFlashMessage();

$pageTitle = htmlspecialchars($post['title']);
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
        .post-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .post-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        .post-meta {
            color: #6c757d;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
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
                <?php if (isLoggedIn()): ?>
                    <a class="nav-link" href="create.php">
                        <i class="fas fa-plus me-1"></i>New Post
                    </a>
                    <a class="nav-link" href="my-posts.php">
                        <i class="fas fa-user-edit me-1"></i>My Posts
                    </a>
                    <a class="nav-link" href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="../auth/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Post Header -->
    <section class="post-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-5 mb-3"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="lead">
                        <i class="fas fa-user me-2"></i>
                        By <strong><?php echo htmlspecialchars($post['author_name']); ?></strong>
                        <i class="fas fa-calendar ms-4 me-2"></i>
                        <?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                    </div>
                    <?php if ($post['updated_at'] !== $post['created_at']): ?>
                        <small class="text-light">
                            <i class="fas fa-edit me-1"></i>
                            Last updated: <?php echo date('F j, Y \a\t g:i A', strtotime($post['updated_at'])); ?>
                        </small>
                    <?php endif; ?>
                </div>
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

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Post Content -->
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Post Actions -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="../index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Blog
                                </a>
                            </div>
                            
                            <?php if (isLoggedIn() && getCurrentUserId() == $post['author_id']): ?>
                                <div>
                                    <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-primary me-2">
                                        <i class="fas fa-edit me-2"></i>Edit Post
                                    </a>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash me-2"></i>Delete Post
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Author Info -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-user-circle me-2"></i>About the Author</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-user-circle fa-3x text-muted"></i>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($post['author_name']); ?></h6>
                                <p class="text-muted mb-0">Blog contributor sharing insights and experiences.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <?php if (isLoggedIn() && getCurrentUserId() == $post['author_id']): ?>
        <div class="modal fade" id="deleteModal" tabindex="-1">
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
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
