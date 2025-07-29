<?php
/**
 * Edit Post Page
 * Task 2: PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/posts.php';

// Require user to be logged in
requireLogin();

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

// Check if user is the author
if ($post['author_id'] != getCurrentUserId()) {
    setFlashMessage('You can only edit your own posts.', 'error');
    header('Location: view.php?id=' . $postId);
    exit();
}

$errors = [];
$title = $post['title'];
$content = $post['content'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Validate input
        $validation = validatePost($title, $content);
        
        if (!$validation['valid']) {
            $errors = $validation['errors'];
        } else {
            // Update the post
            $result = updatePost($postId, $title, $content, getCurrentUserId());
            
            if ($result['success']) {
                setFlashMessage('Post updated successfully!', 'success');
                header('Location: view.php?id=' . $postId);
                exit();
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Edit Post';
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
        .edit-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
                <a class="nav-link" href="my-posts.php">
                    <i class="fas fa-user-edit me-1"></i>My Posts
                </a>
                <a class="nav-link" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="edit-header">
        <div class="container text-center">
            <h1><i class="fas fa-edit me-3"></i>Edit Post</h1>
            <p class="lead">Update your blog post</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h4><i class="fas fa-edit me-2"></i>Edit Your Post</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">
                                    <i class="fas fa-heading me-1"></i>Post Title
                                </label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($title); ?>" 
                                       placeholder="Enter an engaging title for your post" required>
                                <div class="form-text">3-200 characters</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="content" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>Post Content
                                </label>
                                <textarea class="form-control" id="content" name="content" rows="12" 
                                          placeholder="Write your blog post content here..." required><?php echo htmlspecialchars($content); ?></textarea>
                                <div class="form-text">Minimum 10 characters</div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="view.php?id=<?php echo $postId; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Update Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Post Info -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>Post Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Created:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Last Updated:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($post['updated_at'])); ?></p>
                            </div>
                        </div>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($post['author_name']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
