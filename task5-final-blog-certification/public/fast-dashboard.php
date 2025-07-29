<?php
/**
 * Fast Dashboard - No Database Lag
 */

session_start();

// Check if logged in
if (!isset($_SESSION['user']['logged_in'])) {
    header('Location: fast-login.php');
    exit;
}

$user = $_SESSION['user'];

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: fast-login.php');
    exit;
}

// Demo data (no database needed)
$stats = [
    'admin' => ['posts' => 15, 'users' => 25, 'comments' => 45, 'views' => 1250],
    'editor' => ['posts' => 8, 'users' => 0, 'comments' => 23, 'views' => 890],
    'user' => ['posts' => 2, 'users' => 0, 'comments' => 5, 'views' => 120]
];

$user_stats = $stats[$user['username']] ?? $stats['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast Dashboard - <?= htmlspecialchars($user['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark hero">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tachometer-alt me-2"></i>Fast Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <strong><?= htmlspecialchars($user['name']) ?></strong>
                    <span class="badge bg-light text-dark ms-2"><?= ucfirst($user['role']) ?></span>
                </span>
                <a href="?logout=1" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Welcome Section -->
    <div class="hero text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Welcome back, <?= htmlspecialchars($user['name']) ?>! 👋</h2>
                    <p class="mb-0">Your dashboard is loading lightning fast with no database lag.</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="badge bg-success fs-6">
                        <i class="fas fa-bolt me-1"></i>Ultra Fast Mode
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card border-0 bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                        <h3 class="mb-0"><?= $user_stats['posts'] ?></h3>
                        <small>My Posts</small>
                    </div>
                </div>
            </div>
            
            <?php if ($user['role'] === 'admin'): ?>
            <div class="col-md-3 mb-3">
                <div class="card stat-card border-0 bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h3 class="mb-0"><?= $user_stats['users'] ?></h3>
                        <small>Total Users</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="col-md-3 mb-3">
                <div class="card stat-card border-0 bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-comments fa-2x mb-2"></i>
                        <h3 class="mb-0"><?= $user_stats['comments'] ?></h3>
                        <small>Comments</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stat-card border-0 bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-eye fa-2x mb-2"></i>
                        <h3 class="mb-0"><?= number_format($user_stats['views']) ?></h3>
                        <small>Total Views</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Quick Actions -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if ($user['role'] === 'admin' || $user['role'] === 'editor'): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                                        <h6>Create New Post</h6>
                                        <button class="btn btn-primary btn-sm" onclick="showDemo('create-post')">
                                            <i class="fas fa-plus me-1"></i>New Post
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-edit fa-2x text-success mb-2"></i>
                                        <h6>Manage Posts</h6>
                                        <button class="btn btn-success btn-sm" onclick="showDemo('manage-posts')">
                                            <i class="fas fa-list me-1"></i>My Posts
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user['role'] === 'admin'): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users-cog fa-2x text-warning mb-2"></i>
                                        <h6>User Management</h6>
                                        <button class="btn btn-warning btn-sm" onclick="showDemo('user-management')">
                                            <i class="fas fa-users me-1"></i>Manage Users
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-shield-alt fa-2x text-danger mb-2"></i>
                                        <h6>Security Tests</h6>
                                        <a href="../tests/comprehensive-tests.php" class="btn btn-danger btn-sm" target="_blank">
                                            <i class="fas fa-vial me-1"></i>Run Tests
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-user-edit fa-2x text-info mb-2"></i>
                                        <h6>Profile Settings</h6>
                                        <button class="btn btn-info btn-sm" onclick="showDemo('profile')">
                                            <i class="fas fa-cog me-1"></i>Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-home fa-2x text-secondary mb-2"></i>
                                        <h6>View Blog</h6>
                                        <a href="ultra-fast.php" class="btn btn-secondary btn-sm" target="_blank">
                                            <i class="fas fa-external-link-alt me-1"></i>Visit Blog
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user me-2"></i>Account Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                        </div>
                        
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'editor' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Demo Notice -->
                <div class="card mt-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Demo Mode</h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">This dashboard loads instantly without database lag!</p>
                        <div class="d-grid gap-2">
                            <a href="ultra-fast.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-home me-1"></i>Homepage
                            </a>
                            <a href="../demo.php" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-list me-1"></i>All Versions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demo Modal -->
    <div class="modal fade" id="demoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Demo Feature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Feature Demo</h6>
                        <p class="mb-0">This is a fast-loading demo. In the full version, this would connect to the database and show real functionality.</p>
                    </div>
                    <p>Available features in full version:</p>
                    <ul>
                        <li>✅ Create and edit blog posts</li>
                        <li>✅ User management (Admin only)</li>
                        <li>✅ Comment moderation</li>
                        <li>✅ Profile settings</li>
                        <li>✅ Security monitoring</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <a href="index.php" class="btn btn-primary" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>Try Full Version
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-3">
        <div class="container text-center">
            <p class="mb-0">🚀 Task 5: Fast Dashboard - Lightning Speed Demo</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDemo(feature) {
            const modal = new bootstrap.Modal(document.getElementById('demoModal'));
            modal.show();
        }

        // Show loading time
        window.addEventListener('load', function() {
            console.log('Dashboard loaded in:', performance.now().toFixed(2), 'ms');
        });
    </script>
</body>
</html>
