<?php
/**
 * Task 5: Final Project & Certification - My Posts Management
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
    setFlashMessage('error', 'You do not have permission to manage posts.');
    redirect('dashboard.php');
}

// Initialize classes
$post = new Post();
$user = getCurrentUser();

// Get parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$status = sanitizeInput($_GET['status'] ?? '');

// Get user's posts
$userPosts = $post->getPostsByAuthor($user['id'], $page, ADMIN_POSTS_PER_PAGE, $status ?: null);
$totalPosts = $post->getAuthorPostCount($user['id'], $status ?: null);
$totalPages = ceil($totalPosts / ADMIN_POSTS_PER_PAGE);

// Get post counts by status
$statusCounts = [
    'all' => $post->getAuthorPostCount($user['id']),
    'published' => $post->getAuthorPostCount($user['id'], 'published'),
    'draft' => $post->getAuthorPostCount($user['id'], 'draft'),
    'archived' => $post->getAuthorPostCount($user['id'], 'archived')
];

// Page meta
$pageTitle = 'My Posts';
$pageDescription = 'Manage your blog posts and content';

// Include header
include '../templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-file-alt me-2"></i>My Posts
                    </h2>
                    <p class="text-muted mb-0">Manage your blog content and drafts</p>
                </div>
                <div>
                    <a href="create-post.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Post
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <h4 class="mb-0"><?= number_format($statusCounts['all']) ?></h4>
                            <small>Total Posts</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-eye fa-2x mb-2"></i>
                            <h4 class="mb-0"><?= number_format($statusCounts['published']) ?></h4>
                            <small>Published</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-edit fa-2x mb-2"></i>
                            <h4 class="mb-0"><?= number_format($statusCounts['draft']) ?></h4>
                            <small>Drafts</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-secondary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-archive fa-2x mb-2"></i>
                            <h4 class="mb-0"><?= number_format($statusCounts['archived']) ?></h4>
                            <small>Archived</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?= empty($status) ? 'active' : '' ?>" href="my-posts.php">
                                All Posts (<?= $statusCounts['all'] ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $status === 'published' ? 'active' : '' ?>" href="my-posts.php?status=published">
                                Published (<?= $statusCounts['published'] ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $status === 'draft' ? 'active' : '' ?>" href="my-posts.php?status=draft">
                                Drafts (<?= $statusCounts['draft'] ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $status === 'archived' ? 'active' : '' ?>" href="my-posts.php?status=archived">
                                Archived (<?= $statusCounts['archived'] ?>)
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <?php if (empty($userPosts)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No posts found</h4>
                            <p class="text-muted">
                                <?php if ($status): ?>
                                    No <?= htmlspecialchars($status) ?> posts yet.
                                <?php else: ?>
                                    You haven't created any posts yet.
                                <?php endif; ?>
                            </p>
                            <a href="create-post.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create Your First Post
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Posts Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userPosts as $postItem): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($postItem['is_featured']): ?>
                                                        <i class="fas fa-star text-warning me-2" title="Featured Post"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($postItem['title']) ?></strong>
                                                        <?php if ($postItem['excerpt']): ?>
                                                            <br><small class="text-muted">
                                                                <?= htmlspecialchars(truncateText($postItem['excerpt'], 60)) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($postItem['category_name']): ?>
                                                    <span class="badge bg-secondary">
                                                        <?= htmlspecialchars($postItem['category_name']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Uncategorized</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'published' => 'success',
                                                    'draft' => 'warning',
                                                    'archived' => 'secondary'
                                                ];
                                                $statusIcon = [
                                                    'published' => 'fa-eye',
                                                    'draft' => 'fa-edit',
                                                    'archived' => 'fa-archive'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusClass[$postItem['status']] ?? 'secondary' ?>">
                                                    <i class="fas <?= $statusIcon[$postItem['status']] ?? 'fa-question' ?> me-1"></i>
                                                    <?= ucfirst($postItem['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <i class="fas fa-eye me-1"></i>
                                                    <?= number_format($postItem['view_count']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= formatDate($postItem['created_at']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= formatDate($postItem['updated_at']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($postItem['status'] === 'published'): ?>
                                                        <a href="post.php?slug=<?= urlencode($postItem['slug']) ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="View Post"
                                                           target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="edit-post.php?id=<?= $postItem['id'] ?>" 
                                                       class="btn btn-outline-secondary" 
                                                       title="Edit Post">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger" 
                                                            title="Delete Post"
                                                            onclick="deletePost(<?= $postItem['id'] ?>, '<?= htmlspecialchars($postItem['title']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Posts pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <!-- Previous Page -->
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $status ? '&status=' . urlencode($status) : '' ?>">
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
                                            <a class="page-link" href="?page=<?= $i ?><?= $status ? '&status=' . urlencode($status) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Page -->
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $status ? '&status=' . urlencode($status) : '' ?>">
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
            </div>
        </div>
    </div>
</div>

<script>
// Delete post function
function deletePost(postId, postTitle) {
    if (confirm(`Are you sure you want to delete "${postTitle}"?\n\nThis action cannot be undone.`)) {
        // Create a form to submit the delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete-post.php';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= CSRF_TOKEN_NAME ?>';
        csrfInput.value = '<?= generateCSRFToken() ?>';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'post_id';
        idInput.value = postId;
        
        form.appendChild(csrfInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Quick status change
function changeStatus(postId, newStatus) {
    if (confirm(`Change post status to "${newStatus}"?`)) {
        // Create a form to submit the status change
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'change-post-status.php';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= CSRF_TOKEN_NAME ?>';
        csrfInput.value = '<?= generateCSRFToken() ?>';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'post_id';
        idInput.value = postId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = newStatus;
        
        form.appendChild(csrfInput);
        form.appendChild(idInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-refresh every 30 seconds for real-time updates
setInterval(function() {
    // Only refresh if user is still on the page
    if (document.visibilityState === 'visible') {
        // Subtle refresh indicator
        console.log('Checking for updates...');
    }
}, 30000);
</script>

<?php include '../templates/footer.php'; ?>
