<?php
/**
 * Create New Post
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/posts.php';

// Require login
requireLogin();

$errors = [];
$title = '';
$content = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Basic validation
    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Title must be less than 255 characters';
    }
    
    if (empty($content)) {
        $errors[] = 'Content is required';
    } elseif (strlen($content) < 10) {
        $errors[] = 'Content must be at least 10 characters long';
    }
    
    // Create post if no errors
    if (empty($errors)) {
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

$pageTitle = 'Create New Post - Task 3 Blog';
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
        
        .create-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 2rem 0;
        }
        
        .create-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .create-form {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
        }
        
        .character-count {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .preview-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
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
                <a class="nav-link" href="my-posts.php">
                    <i class="fas fa-user-edit me-1"></i>My Posts
                </a>
                <a class="nav-link" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="create-container">
                    <!-- Header -->
                    <div class="create-header">
                        <h2 class="mb-3">
                            <i class="fas fa-plus me-2"></i>Create New Post
                        </h2>
                        <p class="mb-0">Share your thoughts with the world</p>
                    </div>
                    
                    <!-- Form -->
                    <div class="create-form">
                        <!-- Error Messages -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php foreach ($errors as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Create Form -->
                        <form method="POST" action="create.php" id="createForm">
                            <div class="mb-4">
                                <label for="title" class="form-label">
                                    <i class="fas fa-heading me-1"></i>Post Title
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="title" 
                                       name="title" 
                                       value="<?php echo htmlspecialchars($title); ?>"
                                       placeholder="Enter an engaging title for your post"
                                       maxlength="255"
                                       required>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Make it catchy and descriptive</small>
                                    <small class="character-count" id="titleCount">0/255</small>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="content" class="form-label">
                                    <i class="fas fa-edit me-1"></i>Post Content
                                </label>
                                <textarea class="form-control" 
                                          id="content" 
                                          name="content" 
                                          rows="12"
                                          placeholder="Write your post content here... Share your ideas, experiences, or knowledge with the community."
                                          required><?php echo htmlspecialchars($content); ?></textarea>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Minimum 10 characters required</small>
                                    <small class="character-count" id="contentCount">0 characters</small>
                                </div>
                            </div>
                            
                            <!-- Preview Toggle -->
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-secondary" id="previewToggle">
                                    <i class="fas fa-eye me-1"></i>Preview Post
                                </button>
                            </div>
                            
                            <!-- Preview Container -->
                            <div class="preview-container d-none" id="previewContainer">
                                <h5><i class="fas fa-eye me-2"></i>Preview</h5>
                                <hr>
                                <h4 id="previewTitle">Post Title</h4>
                                <div class="text-muted mb-3">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars(getCurrentUsername()); ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-calendar me-1"></i><?php echo date('F j, Y'); ?>
                                </div>
                                <div id="previewContent">Post content will appear here...</div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="../index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Cancel
                                </a>
                                
                                <div>
                                    <button type="button" class="btn btn-outline-primary me-2" id="saveDraft">
                                        <i class="fas fa-save me-1"></i>Save Draft
                                    </button>
                                    <button type="submit" class="btn btn-gradient">
                                        <i class="fas fa-paper-plane me-1"></i>Publish Post
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const contentInput = document.getElementById('content');
            const titleCount = document.getElementById('titleCount');
            const contentCount = document.getElementById('contentCount');
            const previewToggle = document.getElementById('previewToggle');
            const previewContainer = document.getElementById('previewContainer');
            const previewTitle = document.getElementById('previewTitle');
            const previewContent = document.getElementById('previewContent');
            
            // Character counting
            function updateTitleCount() {
                const count = titleInput.value.length;
                titleCount.textContent = count + '/255';
                titleCount.className = count > 240 ? 'character-count text-warning' : 'character-count text-muted';
            }
            
            function updateContentCount() {
                const count = contentInput.value.length;
                contentCount.textContent = count + ' characters';
                contentCount.className = count < 10 ? 'character-count text-danger' : 'character-count text-muted';
            }
            
            titleInput.addEventListener('input', updateTitleCount);
            contentInput.addEventListener('input', updateContentCount);
            
            // Initial count
            updateTitleCount();
            updateContentCount();
            
            // Preview functionality
            previewToggle.addEventListener('click', function() {
                if (previewContainer.classList.contains('d-none')) {
                    // Show preview
                    previewTitle.textContent = titleInput.value || 'Post Title';
                    previewContent.textContent = contentInput.value || 'Post content will appear here...';
                    previewContainer.classList.remove('d-none');
                    previewToggle.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Hide Preview';
                } else {
                    // Hide preview
                    previewContainer.classList.add('d-none');
                    previewToggle.innerHTML = '<i class="fas fa-eye me-1"></i>Preview Post';
                }
            });
            
            // Auto-save draft functionality (placeholder)
            document.getElementById('saveDraft').addEventListener('click', function() {
                alert('Draft save functionality would be implemented here');
            });
            
            // Focus title on load
            titleInput.focus();
        });
    </script>
</body>
</html>
