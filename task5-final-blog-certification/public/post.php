<?php
/**
 * Task 5: Final Project & Certification - Single Post View
 * Aerospace Internship Program - Complete Blog Application
 */

// Initialize application
define('APP_INIT', true);
require_once '../config/config.php';

// Initialize classes
$post = new Post();
$db = Database::getInstance();

// Get post slug from URL
$slug = sanitizeInput($_GET['slug'] ?? '');

if (empty($slug)) {
    redirect('index.php');
}

// Get post by slug
$postData = $post->getPost($slug, true);

if (!$postData) {
    setFlashMessage('error', 'Post not found.');
    redirect('index.php');
}

// Check if post is published or user has permission to view
if ($postData['status'] !== 'published' && !isEditor()) {
    setFlashMessage('error', 'Post not available.');
    redirect('index.php');
}

// Increment view count
$userId = isLoggedIn() ? getCurrentUser()['id'] : null;
$post->incrementViewCount($postData['id'], $userId);

// Get related posts
$relatedPosts = [];
if ($postData['category_id']) {
    $relatedPosts = $post->getRelatedPosts($postData['id'], $postData['category_id'], 3);
}

// Get comments (simplified for demo)
$comments = $db->fetchAll("
    SELECT c.*, u.username, u.first_name, u.last_name 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = ? AND c.status = 'approved' 
    ORDER BY c.created_at ASC
", [$postData['id']]);

// Page meta
$pageTitle = $postData['meta_title'] ?: $postData['title'];
$pageDescription = $postData['meta_description'] ?: $postData['excerpt'];

// Include header
include '../templates/header.php';
?>

<div class="container">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Post Content -->
            <article class="card shadow-sm mb-4">
                <!-- Featured Image -->
                <?php if ($postData['featured_image']): ?>
                    <img src="<?= UPLOADS_URL ?>/<?= htmlspecialchars($postData['featured_image']) ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($postData['title']) ?>"
                         style="height: 400px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body">
                    <!-- Post Header -->
                    <div class="mb-4">
                        <?php if ($postData['is_featured']): ?>
                            <div class="badge bg-warning text-dark mb-2">
                                <i class="fas fa-star me-1"></i>Featured Post
                            </div>
                        <?php endif; ?>
                        
                        <h1 class="display-5 fw-bold mb-3"><?= htmlspecialchars($postData['title']) ?></h1>
                        
                        <!-- Post Meta -->
                        <div class="d-flex flex-wrap align-items-center text-muted mb-3">
                            <div class="me-4 mb-2">
                                <i class="fas fa-user me-1"></i>
                                <strong><?= htmlspecialchars($postData['first_name'] . ' ' . $postData['last_name']) ?></strong>
                            </div>
                            <div class="me-4 mb-2">
                                <i class="fas fa-calendar me-1"></i>
                                <?= formatDateTime($postData['published_at']) ?>
                            </div>
                            <div class="me-4 mb-2">
                                <i class="fas fa-eye me-1"></i>
                                <?= number_format($postData['view_count']) ?> views
                            </div>
                            <?php if ($postData['category_name']): ?>
                                <div class="mb-2">
                                    <a href="index.php?category=<?= $postData['category_id'] ?>" 
                                       class="badge bg-primary text-decoration-none">
                                        <i class="fas fa-tag me-1"></i><?= htmlspecialchars($postData['category_name']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Admin Actions -->
                        <?php if (isLoggedIn() && (getCurrentUser()['id'] == $postData['author_id'] || isAdmin())): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-tools me-2"></i>
                                <strong>Author Actions:</strong>
                                <a href="edit-post.php?id=<?= $postData['id'] ?>" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="fas fa-edit me-1"></i>Edit Post
                                </a>
                                <?php if (isAdmin()): ?>
                                    <button class="btn btn-sm btn-outline-danger ms-1" onclick="deletePost(<?= $postData['id'] ?>)">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Post Content -->
                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($postData['content'])) ?>
                    </div>
                    
                    <!-- Post Footer -->
                    <div class="border-top pt-4 mt-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-2">Share this post:</h6>
                                <div class="d-flex gap-2">
                                    <a href="#" class="btn btn-outline-primary btn-sm" onclick="sharePost('twitter')">
                                        <i class="fab fa-twitter me-1"></i>Twitter
                                    </a>
                                    <a href="#" class="btn btn-outline-primary btn-sm" onclick="sharePost('facebook')">
                                        <i class="fab fa-facebook me-1"></i>Facebook
                                    </a>
                                    <a href="#" class="btn btn-outline-primary btn-sm" onclick="sharePost('linkedin')">
                                        <i class="fab fa-linkedin me-1"></i>LinkedIn
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted">
                                    Last updated: <?= formatDateTime($postData['updated_at']) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Comments Section -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>
                        Comments (<?= count($comments) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($comments)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No comments yet</h6>
                            <p class="text-muted">Be the first to share your thoughts!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <?= strtoupper(substr($comment['first_name'] ?: $comment['username'], 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">
                                                <?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?= formatDateTime($comment['created_at']) ?>
                                            </small>
                                        </div>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Comment Form -->
                    <?php if (isLoggedIn()): ?>
                        <div class="mt-4">
                            <h6>Leave a Comment</h6>
                            <form method="POST" action="add-comment.php">
                                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="post_id" value="<?= $postData['id'] ?>">
                                
                                <div class="mb-3">
                                    <textarea class="form-control" 
                                              name="content" 
                                              rows="4" 
                                              placeholder="Share your thoughts..."
                                              required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Post Comment
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="text-center mt-4">
                            <p class="text-muted">
                                <a href="login.php" class="text-decoration-none">Login</a> to leave a comment
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 2rem;">
                <!-- Author Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>About the Author
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            <?= strtoupper(substr($postData['first_name'], 0, 1)) ?>
                        </div>
                        <h6><?= htmlspecialchars($postData['first_name'] . ' ' . $postData['last_name']) ?></h6>
                        <?php if ($postData['author_bio']): ?>
                            <p class="text-muted small"><?= htmlspecialchars($postData['author_bio']) ?></p>
                        <?php endif; ?>
                        <a href="index.php?author=<?= $postData['author_id'] ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View All Posts
                        </a>
                    </div>
                </div>

                <!-- Related Posts -->
                <?php if (!empty($relatedPosts)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bookmark me-2"></i>Related Posts
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($relatedPosts as $related): ?>
                                <div class="mb-3 pb-3 <?= $related !== end($relatedPosts) ? 'border-bottom' : '' ?>">
                                    <h6 class="mb-1">
                                        <a href="post.php?slug=<?= urlencode($related['slug']) ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($related['title']) ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        by <?= htmlspecialchars($related['first_name'] . ' ' . $related['last_name']) ?>
                                        • <?= formatDate($related['published_at']) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Navigation -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-compass me-2"></i>Navigation
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                            <?php if ($postData['category_name']): ?>
                                <a href="index.php?category=<?= $postData['category_id'] ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-tag me-2"></i><?= htmlspecialchars($postData['category_name']) ?>
                                </a>
                            <?php endif; ?>
                            <a href="index.php?author=<?= $postData['author_id'] ?>" class="btn btn-outline-info">
                                <i class="fas fa-user me-2"></i>More by Author
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.post-content {
    font-size: 1.1rem;
    line-height: 1.8;
}

.post-content p {
    margin-bottom: 1.5rem;
}

.comment {
    transition: background-color 0.2s ease;
}

.comment:hover {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin: -1rem;
}
</style>

<script>
// Share functionality
function sharePost(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    
    let shareUrl = '';
    
    switch(platform) {
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

// Delete post function
function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        // In a real application, this would be an AJAX call
        window.location.href = `delete-post.php?id=${postId}`;
    }
}

// Copy link functionality
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        alert('Link copied to clipboard!');
    });
}
</script>

<?php include '../templates/footer.php'; ?>
