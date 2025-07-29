<?php
/**
 * Task 5: Final Project & Certification - Create Post
 * Aerospace Internship Program - Complete Blog Application
 */

// Initialize application
define('APP_INIT', true);
require_once '../config/config.php';

// Check if user is logged in and has editor permissions
if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isEditor()) {
    setFlashMessage('error', 'You do not have permission to create posts.');
    redirect('dashboard.php');
}

// Initialize classes
$post = new Post();
$db = Database::getInstance();

$errors = [];
$success = '';

// Get categories for dropdown
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $user = getCurrentUser();
        
        $postData = [
            'title' => sanitizeInput($_POST['title'] ?? ''),
            'slug' => sanitizeInput($_POST['slug'] ?? ''),
            'content' => $_POST['content'] ?? '', // Don't sanitize content here, let the Post class handle it
            'excerpt' => sanitizeInput($_POST['excerpt'] ?? ''),
            'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
            'status' => sanitizeInput($_POST['status'] ?? 'draft'),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'meta_title' => sanitizeInput($_POST['meta_title'] ?? ''),
            'meta_description' => sanitizeInput($_POST['meta_description'] ?? ''),
            'author_id' => $user['id']
        ];

        // Handle featured image upload (simplified for demo)
        if (!empty($_FILES['featured_image']['name'])) {
            // In a real application, you would handle file upload here
            // For now, we'll just store the filename
            $postData['featured_image'] = sanitizeInput($_FILES['featured_image']['name']);
        }

        $result = $post->createPost($postData);
        
        if ($result['success']) {
            setFlashMessage('success', 'Post created successfully!');
            redirect('edit-post.php?id=' . $result['post_id']);
        } else {
            $errors = $result['errors'];
        }
    }
}

// Page meta
$pageTitle = 'Create New Post';
$pageDescription = 'Create a new blog post with rich content and media';

// Include header
include '../templates/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-plus me-2"></i>Create New Post
                    </h4>
                </div>
                
                <div class="card-body">
                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Post Form -->
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRFToken() ?>">
                        
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Post Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="title" 
                                   name="title" 
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                   placeholder="Enter an engaging post title"
                                   maxlength="255"
                                   required>
                            <div class="form-text">
                                <span id="titleCount">0</span>/255 characters
                            </div>
                            <div class="invalid-feedback">
                                Please enter a post title.
                            </div>
                        </div>

                        <!-- Slug -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">
                                <i class="fas fa-link me-1"></i>URL Slug
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><?= APP_URL ?>/post/</span>
                                <input type="text" 
                                       class="form-control" 
                                       id="slug" 
                                       name="slug" 
                                       value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>"
                                       placeholder="auto-generated-from-title">
                            </div>
                            <div class="form-text">
                                Leave empty to auto-generate from title
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>Content <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" 
                                      id="content" 
                                      name="content" 
                                      rows="15"
                                      placeholder="Write your post content here..."
                                      required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                            <div class="form-text">
                                <span id="contentCount">0</span> characters
                            </div>
                            <div class="invalid-feedback">
                                Please enter post content.
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">
                                <i class="fas fa-quote-left me-1"></i>Excerpt
                            </label>
                            <textarea class="form-control" 
                                      id="excerpt" 
                                      name="excerpt" 
                                      rows="3"
                                      maxlength="300"
                                      placeholder="Brief description of your post (optional - will be auto-generated if empty)"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                            <div class="form-text">
                                <span id="excerptCount">0</span>/300 characters
                            </div>
                        </div>

                        <!-- Category and Status Row -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-tags me-1"></i>Category
                                </label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                                <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-eye me-1"></i>Status
                                </label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft" <?= (($_POST['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>>
                                        Draft
                                    </option>
                                    <option value="published" <?= (($_POST['status'] ?? '') === 'published') ? 'selected' : '' ?>>
                                        Published
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Featured Image -->
                        <div class="mb-3">
                            <label for="featured_image" class="form-label">
                                <i class="fas fa-image me-1"></i>Featured Image
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="featured_image" 
                                   name="featured_image"
                                   accept="image/*">
                            <div class="form-text">
                                Upload a featured image for your post (optional)
                            </div>
                        </div>

                        <!-- Featured Post Checkbox -->
                        <?php if (isAdmin()): ?>
                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_featured" 
                                       name="is_featured"
                                       <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_featured">
                                    <i class="fas fa-star me-1"></i>Mark as Featured Post
                                </label>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Post
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                <i class="fas fa-file me-2"></i>Save as Draft
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-danger">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 2rem;">
                <!-- SEO Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>SEO Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="meta_title" 
                                   name="meta_title" 
                                   value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>"
                                   maxlength="60"
                                   placeholder="SEO title (auto-filled from post title)">
                            <div class="form-text">
                                <span id="metaTitleCount">0</span>/60 characters
                            </div>
                        </div>
                        
                        <div class="mb-0">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control" 
                                      id="meta_description" 
                                      name="meta_description" 
                                      rows="3"
                                      maxlength="160"
                                      placeholder="SEO description (auto-filled from excerpt)"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                            <div class="form-text">
                                <span id="metaDescCount">0</span>/160 characters
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Writing Tips -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Writing Tips
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Use engaging headlines
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Break content into sections
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Add relevant images
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Include call-to-actions
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                Proofread before publishing
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Keyboard Shortcuts -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-keyboard me-2"></i>Shortcuts
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">
                                    <kbd>Ctrl</kbd> + <kbd>S</kbd><br>
                                    Save Draft
                                </small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">
                                    <kbd>Ctrl</kbd> + <kbd>Enter</kbd><br>
                                    Publish
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Character counters
function updateCharCount(inputId, countId, maxLength = null) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(countId);
    
    if (input && counter) {
        const count = input.value.length;
        counter.textContent = count;
        
        if (maxLength && count > maxLength * 0.9) {
            counter.classList.add('text-warning');
        } else {
            counter.classList.remove('text-warning');
        }
        
        if (maxLength && count > maxLength) {
            counter.classList.add('text-danger');
        } else {
            counter.classList.remove('text-danger');
        }
    }
}

// Initialize character counters
document.addEventListener('DOMContentLoaded', function() {
    const counters = [
        ['title', 'titleCount', 255],
        ['content', 'contentCount'],
        ['excerpt', 'excerptCount', 300],
        ['meta_title', 'metaTitleCount', 60],
        ['meta_description', 'metaDescCount', 160]
    ];
    
    counters.forEach(([inputId, countId, maxLength]) => {
        const input = document.getElementById(inputId);
        if (input) {
            updateCharCount(inputId, countId, maxLength);
            input.addEventListener('input', () => updateCharCount(inputId, countId, maxLength));
        }
    });
});

// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const title = this.value;
    const slugField = document.getElementById('slug');
    
    if (!slugField.value || slugField.dataset.autoGenerated) {
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        
        slugField.value = slug;
        slugField.dataset.autoGenerated = 'true';
    }
});

// Mark slug as manually edited
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});

// Auto-fill meta fields
document.getElementById('title').addEventListener('input', function() {
    const metaTitleField = document.getElementById('meta_title');
    if (!metaTitleField.value) {
        metaTitleField.value = this.value.substring(0, 60);
        updateCharCount('meta_title', 'metaTitleCount', 60);
    }
});

document.getElementById('excerpt').addEventListener('input', function() {
    const metaDescField = document.getElementById('meta_description');
    if (!metaDescField.value) {
        metaDescField.value = this.value.substring(0, 160);
        updateCharCount('meta_description', 'metaDescCount', 160);
    }
});

// Save as draft function
function saveDraft() {
    document.getElementById('status').value = 'draft';
    document.querySelector('form').submit();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey) {
        if (e.key === 's') {
            e.preventDefault();
            saveDraft();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('status').value = 'published';
            document.querySelector('form').submit();
        }
    }
});

// Form submission with loading state
document.querySelector('form').addEventListener('submit', function(e) {
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    submitButton.innerHTML = '<span class="loading"></span> Creating Post...';
    submitButton.disabled = true;
    
    // Re-enable button after 10 seconds in case of error
    setTimeout(function() {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }, 10000);
});

// Auto-save functionality (simplified)
let autoSaveTimer;
function autoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        console.log('Auto-saving draft...');
        // In a real application, you would make an AJAX call here
    }, 30000); // Auto-save every 30 seconds
}

// Trigger auto-save on content changes
['title', 'content', 'excerpt'].forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('input', autoSave);
    }
});
</script>

<?php include '../templates/footer.php'; ?>
