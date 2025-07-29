<?php
/**
 * Task 5: Final Project & Certification - Dashboard
 * Aerospace Internship Program - Complete Blog Application
 */

// Initialize application
define('APP_INIT', true);
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Initialize classes
$post = new Post();
$auth = new Auth();
$db = Database::getInstance();

$user = getCurrentUser();

// Get user statistics
$userStats = $auth->getUserStats($user['id']);
$userPosts = $post->getPostsByAuthor($user['id'], 1, 5);

// Get recent activity
$recentPosts = $db->fetchAll("
    SELECT p.*, c.name as category_name 
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.author_id = ? 
    ORDER BY p.updated_at DESC 
    LIMIT 5
", [$user['id']]);

// Get system stats for admin
$systemStats = [];
if (isAdmin()) {
    $systemStats = [
        'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'],
        'total_posts' => $db->fetch("SELECT COUNT(*) as count FROM posts")['count'],
        'published_posts' => $db->fetch("SELECT COUNT(*) as count FROM posts WHERE status = 'published'")['count'],
        'draft_posts' => $db->fetch("SELECT COUNT(*) as count FROM posts WHERE status = 'draft'")['count'],
        'total_comments' => $db->fetch("SELECT COUNT(*) as count FROM comments")['count'],
        'pending_comments' => $db->fetch("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'")['count']
    ];
}

// Page meta
$pageTitle = 'Dashboard';
$pageDescription = 'User dashboard for managing blog posts and account settings';

// Include header
include '../templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Welcome Section -->
            <div class="card bg-gradient-primary text-white mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Welcome back, <?= htmlspecialchars($user['first_name'] ?: $user['username']) ?>!
                            </h2>
                            <p class="mb-3 opacity-75">
                                You're logged in as <strong><?= ucfirst($user['role']) ?></strong>. 
                                Here's your activity overview and quick actions.
                            </p>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if (isEditor()): ?>
                                    <a href="create-post.php" class="btn btn-light btn-sm">
                                        <i class="fas fa-plus me-1"></i>New Post
                                    </a>
                                    <a href="my-posts.php" class="btn btn-outline-light btn-sm">
                                        <i class="fas fa-file-alt me-1"></i>My Posts
                                    </a>
                                <?php endif; ?>
                                <a href="profile.php" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-user me-1"></i>Profile
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-user-circle display-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <h4 class="mb-0"><?= number_format($userStats['post_count'] ?? 0) ?></h4>
                            <small>My Posts</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-eye fa-2x mb-2"></i>
                            <h4 class="mb-0"><?= number_format($userStats['total_views'] ?? 0) ?></h4>
                            <small>Total Views</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar fa-2x mb-2"></i>
                            <h4 class="mb-0">
                                <?= $userStats['last_post_date'] ? formatDate($userStats['last_post_date']) : 'Never' ?>
                            </h4>
                            <small>Last Post</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <h4 class="mb-0"><?= formatDate($user['created_at']) ?></h4>
                            <small>Member Since</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Posts -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Recent Posts
                    </h5>
                    <?php if (isEditor()): ?>
                        <a href="my-posts.php" class="btn btn-sm btn-outline-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPosts)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No posts yet</h6>
                            <p class="text-muted">Start creating your first blog post!</p>
                            <?php if (isEditor()): ?>
                                <a href="create-post.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create First Post
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPosts as $postItem): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($postItem['title']) ?></strong>
                                                <?php if ($postItem['is_featured']): ?>
                                                    <span class="badge bg-warning text-dark ms-1">Featured</span>
                                                <?php endif; ?>
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
                                                ?>
                                                <span class="badge bg-<?= $statusClass[$postItem['status']] ?? 'secondary' ?>">
                                                    <?= ucfirst($postItem['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= formatDateTime($postItem['updated_at']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($postItem['status'] === 'published'): ?>
                                                        <a href="post.php?slug=<?= urlencode($postItem['slug']) ?>" 
                                                           class="btn btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="edit-post.php?id=<?= $postItem['id'] ?>" 
                                                       class="btn btn-outline-secondary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Admin System Stats -->
            <?php if (isAdmin()): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>System Overview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <h6 class="mb-0">Total Users</h6>
                                        <small class="text-muted">Registered accounts</small>
                                    </div>
                                    <div class="text-primary">
                                        <h4 class="mb-0"><?= number_format($systemStats['total_users']) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <h6 class="mb-0">Published Posts</h6>
                                        <small class="text-muted">Live content</small>
                                    </div>
                                    <div class="text-success">
                                        <h4 class="mb-0"><?= number_format($systemStats['published_posts']) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <h6 class="mb-0">Draft Posts</h6>
                                        <small class="text-muted">Pending content</small>
                                    </div>
                                    <div class="text-warning">
                                        <h4 class="mb-0"><?= number_format($systemStats['draft_posts']) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <h6 class="mb-0">Pending Comments</h6>
                                        <small class="text-muted">Awaiting moderation</small>
                                    </div>
                                    <div class="text-info">
                                        <h4 class="mb-0"><?= number_format($systemStats['pending_comments']) ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="admin/dashboard.php" class="btn btn-primary">
                                <i class="fas fa-cog me-2"></i>Admin Panel
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 2rem;">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (isEditor()): ?>
                                <a href="create-post.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create New Post
                                </a>
                                <a href="my-posts.php" class="btn btn-outline-primary">
                                    <i class="fas fa-file-alt me-2"></i>Manage My Posts
                                </a>
                            <?php endif; ?>
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="fas fa-user me-2"></i>Edit Profile
                            </a>
                            <a href="settings.php" class="btn btn-outline-secondary">
                                <i class="fas fa-cog me-2"></i>Account Settings
                            </a>
                            <?php if (isAdmin()): ?>
                                <hr>
                                <a href="admin/users.php" class="btn btn-outline-warning">
                                    <i class="fas fa-users me-2"></i>Manage Users
                                </a>
                                <a href="admin/posts.php" class="btn btn-outline-warning">
                                    <i class="fas fa-file-alt me-2"></i>All Posts
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>Account Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Username:</strong><br>
                            <span class="text-muted"><?= htmlspecialchars($user['username']) ?></span>
                        </div>
                        <div class="mb-3">
                            <strong>Email:</strong><br>
                            <span class="text-muted"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="mb-3">
                            <strong>Role:</strong><br>
                            <span class="badge bg-primary"><?= ucfirst($user['role']) ?></span>
                        </div>
                        <div class="mb-3">
                            <strong>Last Login:</strong><br>
                            <span class="text-muted">
                                <?= $user['last_login'] ? formatDateTime($user['last_login']) : 'First time' ?>
                            </span>
                        </div>
                        <div class="mb-0">
                            <strong>Member Since:</strong><br>
                            <span class="text-muted"><?= formatDate($user['created_at']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Recent Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <i class="fas fa-sign-in-alt text-success"></i>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Logged In</h6>
                                    <small class="text-muted">Just now</small>
                                </div>
                            </div>
                            <?php if (!empty($recentPosts)): ?>
                                <?php foreach (array_slice($recentPosts, 0, 3) as $activity): ?>
                                    <div class="timeline-item">
                                        <i class="fas fa-edit text-primary"></i>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Updated Post</h6>
                                            <p class="mb-1 small"><?= htmlspecialchars($activity['title']) ?></p>
                                            <small class="text-muted"><?= formatDateTime($activity['updated_at']) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-item i {
    position: absolute;
    left: -30px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    background: white;
    border: 2px solid currentColor;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -21px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    background: #dee2e6;
}
</style>

<?php include '../templates/footer.php'; ?>
