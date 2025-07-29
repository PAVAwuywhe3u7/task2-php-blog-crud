<?php
/**
 * View Single Post
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/posts.php';

// Get post ID from URL
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$postId) {
    setFlashMessage('Invalid post ID.', 'error');
    header('Location: ../index.php');
    exit();
}

// Get post data
$post = getPostById($postId);

if (!$post) {
    setFlashMessage('Post not found.', 'error');
    header('Location: ../index.php');
    exit();
}

// Get flash message
$flashMessage = getFlashMessage();

$pageTitle = htmlspecialchars($post['title']) . ' - Task 3 Blog';
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
        
        .post-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 2rem 0;
        }
        
        .post-header {
            background: var(--primary-gradient);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .post-content {
            padding: 3rem 2rem;
        }
        
        .post-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .post-meta {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .post-body {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #495057;
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
        }
        
        .action-buttons {
            background: #f8f9fa;
            padding: 2rem;
            border-top: 1px solid #e9ecef;
        }
        
        @media (max-width: 768px) {
            .post-title {
                font-size: 2rem;
            }
            
            .post-header {
                padding: 2rem 1rem;
            }
            
            .post-content {
                padding: 2rem 1rem;
            }
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
                
                <?php if (isLoggedIn()): ?>
                    <a class="nav-link" href="create.php">
                        <i class="fas fa-plus me-1"></i>Create Post
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

    <div class="container">
        <!-- Flash Messages -->
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="post-container">
                    <!-- Post Header -->
                    <div class="post-header">
                        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                        <div class="post-meta">
                            <i class="fas fa-user me-2"></i>
                            <strong><?php echo htmlspecialchars($post['author_name']); ?></strong>
                            <span class="mx-3">•</span>
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                            <span class="mx-3">•</span>
                            <i class="fas fa-clock me-2"></i>
                            <?php echo date('g:i A', strtotime($post['created_at'])); ?>
                            
                            <?php if ($post['updated_at'] && $post['updated_at'] != $post['created_at']): ?>
                                <br>
                                <small class="mt-2 d-block">
                                    <i class="fas fa-edit me-1"></i>
                                    Last updated: <?php echo date('F j, Y \a\t g:i A', strtotime($post['updated_at'])); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Post Content -->
                    <div class="post-content">
                        <div class="post-body">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="../index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Posts
                                </a>
                            </div>
                            
                            <?php if (isLoggedIn() && getCurrentUserId() == $post['author_id']): ?>
                                <div>
                                    <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-gradient me-2">
                                        <i class="fas fa-edit me-1"></i>Edit Post
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            <?php endif; ?>
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
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>Confirm Delete
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this post?</p>
                        <p><strong>"<?php echo htmlspecialchars($post['title']); ?>"</strong></p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <a href="delete.php?id=<?php echo $post['id']; ?>" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete Post
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
