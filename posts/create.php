<?php
/**
 * Create New Post Page
 * Task 2: PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/posts.php';

// Require user to be logged in
requireLogin();

$errors = [];
$title = '';
$content = '';

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
            // Create the post
            $result = createPost($title, $content, getCurrentUserId());
            
            if ($result['success']) {
                setFlashMessage('Post created successfully!', 'success');
                header('Location: view.php?id=' . $result['post_id']);
                exit();
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Create New Post';
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
        .create-header {
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
    <section class="create-header">
        <div class="container text-center">
            <h1><i class="fas fa-plus-circle me-3"></i>Create New Post</h1>
            <p class="lead">Share your thoughts with the world</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h4><i class="fas fa-edit me-2"></i>Write Your Post</h4>
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
                                <a href="../index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Publish Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Writing Tips -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-lightbulb me-2"></i>Writing Tips</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Choose a clear, descriptive title</li>
                            <li><i class="fas fa-check text-success me-2"></i>Write in a conversational tone</li>
                            <li><i class="fas fa-check text-success me-2"></i>Break up long paragraphs</li>
                            <li><i class="fas fa-check text-success me-2"></i>Proofread before publishing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
