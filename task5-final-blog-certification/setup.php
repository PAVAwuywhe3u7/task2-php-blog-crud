<?php
/**
 * Task 5: Final Project & Certification - Setup Script
 * Aerospace Internship Program - Complete Blog Application
 * 
 * Automated setup and password fixing script
 */

// Initialize application
define('APP_INIT', true);
require_once 'config/config.php';

$setupResults = [];
$errors = [];

/**
 * Run setup step
 */
function runSetupStep($stepName, $stepFunction) {
    global $setupResults, $errors;
    
    try {
        $result = $stepFunction();
        if ($result === true) {
            $setupResults[] = [
                'step' => $stepName,
                'status' => 'SUCCESS',
                'message' => 'Completed successfully'
            ];
        } else {
            $setupResults[] = [
                'step' => $stepName,
                'status' => 'WARNING',
                'message' => $result ?: 'Completed with warnings'
            ];
        }
    } catch (Exception $e) {
        $errors[] = $stepName . ': ' . $e->getMessage();
        $setupResults[] = [
            'step' => $stepName,
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ];
    }
}

// Step 1: Test Database Connection
runSetupStep('Database Connection Test', function() {
    $db = Database::getInstance();
    if (!$db->testConnection()) {
        throw new Exception('Cannot connect to database. Please check your configuration.');
    }
    return true;
});

// Step 2: Verify Required Tables
runSetupStep('Database Schema Verification', function() {
    $db = Database::getInstance();
    $requiredTables = ['users', 'posts', 'categories', 'comments', 'user_sessions', 'post_views'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        if (!$db->tableExists($table)) {
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        throw new Exception('Missing tables: ' . implode(', ', $missingTables) . '. Please import database_setup.sql');
    }
    
    return true;
});

// Step 3: Fix User Passwords
runSetupStep('User Password Setup', function() {
    $db = Database::getInstance();
    
    // Define correct passwords for each user
    $userPasswords = [
        'admin' => 'AdminPass123!',
        'editor' => 'EditorPass123!',
        'user' => 'UserPass123!',
        'john_doe' => 'JohnPass123!',
        'jane_smith' => 'JanePass123!'
    ];
    
    $updatedUsers = [];
    
    foreach ($userPasswords as $username => $password) {
        // Check if user exists
        $user = $db->fetch("SELECT id, username FROM users WHERE username = ?", [$username]);
        
        if ($user) {
            // Generate new hash
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Update password
            $updated = $db->update('users', ['password' => $hash], ['username' => $username]);
            
            if ($updated) {
                $updatedUsers[] = $username;
            }
        }
    }
    
    if (empty($updatedUsers)) {
        return 'No users found to update';
    }
    
    return 'Updated passwords for: ' . implode(', ', $updatedUsers);
});

// Step 4: Create Required Directories
runSetupStep('Directory Structure Setup', function() {
    $directories = [
        'logs',
        'cache',
        'assets/uploads'
    ];
    
    $createdDirs = [];
    
    foreach ($directories as $dir) {
        $fullPath = ROOT_PATH . '/' . $dir;
        if (!is_dir($fullPath)) {
            if (mkdir($fullPath, 0755, true)) {
                $createdDirs[] = $dir;
            }
        }
    }
    
    // Create .gitkeep files
    $gitkeepDirs = ['logs', 'cache', 'assets/uploads'];
    foreach ($gitkeepDirs as $dir) {
        $gitkeepFile = ROOT_PATH . '/' . $dir . '/.gitkeep';
        if (!file_exists($gitkeepFile)) {
            file_put_contents($gitkeepFile, '');
        }
    }
    
    return empty($createdDirs) ? 'All directories already exist' : 'Created directories: ' . implode(', ', $createdDirs);
});

// Step 5: Test Authentication
runSetupStep('Authentication System Test', function() {
    $auth = new Auth();
    
    // Test login with demo credentials
    $result = $auth->login('admin', 'AdminPass123!');
    
    if (!$result['success']) {
        throw new Exception('Authentication test failed: ' . implode(', ', $result['errors']));
    }
    
    // Logout to clean up
    $auth->logout();
    
    return true;
});

// Step 6: Test Post System
runSetupStep('Post System Test', function() {
    $post = new Post();
    
    // Test getting posts
    $posts = $post->getPosts(1, 5);
    
    if (!is_array($posts)) {
        throw new Exception('Post system not working correctly');
    }
    
    return 'Found ' . count($posts) . ' posts in system';
});

// Step 7: Verify Configuration
runSetupStep('Configuration Verification', function() {
    $configChecks = [
        'APP_NAME' => defined('APP_NAME'),
        'DB_HOST' => defined('DB_HOST'),
        'DB_NAME' => defined('DB_NAME'),
        'CSRF_TOKEN_NAME' => defined('CSRF_TOKEN_NAME'),
        'SESSION_LIFETIME' => defined('SESSION_LIFETIME')
    ];
    
    $missingConfig = [];
    foreach ($configChecks as $config => $exists) {
        if (!$exists) {
            $missingConfig[] = $config;
        }
    }
    
    if (!empty($missingConfig)) {
        throw new Exception('Missing configuration: ' . implode(', ', $missingConfig));
    }
    
    return true;
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Task 5 Final Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .setup-success { color: #198754; }
        .setup-warning { color: #fd7e14; }
        .setup-error { color: #dc3545; }
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header hero-gradient text-white text-center py-4">
                        <h1 class="mb-0">
                            <i class="fas fa-rocket me-2"></i>
                            Task 5: Final Project Setup
                        </h1>
                        <p class="mb-0 opacity-75">Aerospace Internship Program - Complete Blog Application</p>
                    </div>
                </div>

                <!-- Setup Results -->
                <div class="card shadow border-0">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>Setup Results
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Critical Errors</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Setup Step</th>
                                        <th>Status</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($setupResults as $result): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($result['step']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'SUCCESS' => 'setup-success',
                                                    'WARNING' => 'setup-warning',
                                                    'ERROR' => 'setup-error'
                                                ];
                                                $statusIcon = [
                                                    'SUCCESS' => 'fa-check-circle',
                                                    'WARNING' => 'fa-exclamation-triangle',
                                                    'ERROR' => 'fa-times-circle'
                                                ];
                                                ?>
                                                <span class="<?= $statusClass[$result['status']] ?>">
                                                    <i class="fas <?= $statusIcon[$result['status']] ?> me-1"></i>
                                                    <?= $result['status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($result['message']) ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <?php
                        $successCount = count(array_filter($setupResults, fn($r) => $r['status'] === 'SUCCESS'));
                        $totalSteps = count($setupResults);
                        $isSetupComplete = empty($errors) && $successCount === $totalSteps;
                        ?>

                        <div class="alert <?= $isSetupComplete ? 'alert-success' : 'alert-warning' ?> mt-4">
                            <h5 class="mb-2">
                                <i class="fas fa-<?= $isSetupComplete ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                                Setup <?= $isSetupComplete ? 'Complete' : 'Incomplete' ?>
                            </h5>
                            <p class="mb-0">
                                <?= $successCount ?>/<?= $totalSteps ?> steps completed successfully.
                                <?php if ($isSetupComplete): ?>
                                    Your Task 5 Final Project is ready to use!
                                <?php else: ?>
                                    Please resolve the issues above before proceeding.
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Demo Credentials -->
                        <?php if ($isSetupComplete): ?>
                            <div class="card bg-light mt-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-key me-2"></i>Demo Credentials
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <h6>Administrator</h6>
                                            <code>admin / AdminPass123!</code>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <h6>Editor</h6>
                                            <code>editor / EditorPass123!</code>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <h6>User</h6>
                                            <code>user / UserPass123!</code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="text-center mt-4">
                            <?php if ($isSetupComplete): ?>
                                <a href="public/index.php" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-home me-2"></i>Go to Application
                                </a>
                                <a href="public/login.php" class="btn btn-success btn-lg me-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </a>
                                <a href="tests/comprehensive-tests.php" class="btn btn-info btn-lg">
                                    <i class="fas fa-vial me-2"></i>Run Tests
                                </a>
                            <?php else: ?>
                                <button onclick="location.reload()" class="btn btn-warning btn-lg">
                                    <i class="fas fa-redo me-2"></i>Retry Setup
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-footer text-center text-muted">
                        <small>
                            <i class="fas fa-clock me-1"></i>
                            Setup completed at <?= date('Y-m-d H:i:s') ?>
                        </small>
                    </div>
                </div>

                <!-- Next Steps -->
                <?php if ($isSetupComplete): ?>
                    <div class="card shadow border-0 mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list-check me-2"></i>Next Steps
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-play me-2"></i>Getting Started</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>Login with demo credentials</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Explore the dashboard</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Create your first post</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Test search functionality</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-shield-alt me-2"></i>Security Testing</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>Run comprehensive tests</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Test role-based access</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Verify CSRF protection</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Check input validation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
