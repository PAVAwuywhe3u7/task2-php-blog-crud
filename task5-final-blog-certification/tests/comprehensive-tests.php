<?php
/**
 * Task 5: Final Project & Certification - Comprehensive Test Suite
 * Aerospace Internship Program - Complete Blog Application
 * 
 * End-to-end testing for all application features
 */

// Initialize application
define('APP_INIT', true);
require_once '../config/config.php';

// Test results storage
$testResults = [];
$totalTests = 0;
$passedTests = 0;

/**
 * Test runner function
 */
function runTest($testName, $testFunction) {
    global $testResults, $totalTests, $passedTests;
    
    $totalTests++;
    
    try {
        $result = $testFunction();
        if ($result === true) {
            $passedTests++;
            $testResults[] = [
                'name' => $testName,
                'status' => 'PASS',
                'message' => 'Test completed successfully'
            ];
        } else {
            $testResults[] = [
                'name' => $testName,
                'status' => 'FAIL',
                'message' => $result ?: 'Test failed'
            ];
        }
    } catch (Exception $e) {
        $testResults[] = [
            'name' => $testName,
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ];
    }
}

// Test 1: Database Connection
runTest('Database Connection', function() {
    $db = Database::getInstance();
    return $db->testConnection();
});

// Test 2: Database Schema Validation
runTest('Database Schema Validation', function() {
    $db = Database::getInstance();
    $requiredTables = ['users', 'posts', 'categories', 'comments', 'user_sessions', 'post_views'];
    
    foreach ($requiredTables as $table) {
        if (!$db->tableExists($table)) {
            return "Table '$table' does not exist";
        }
    }
    return true;
});

// Test 3: User Registration
runTest('User Registration System', function() {
    $auth = new Auth();
    
    // Test with valid data
    $testUser = [
        'username' => 'testuser_' . time(),
        'email' => 'test_' . time() . '@example.com',
        'password' => 'TestPass123!',
        'first_name' => 'Test',
        'last_name' => 'User'
    ];
    
    $result = $auth->register(
        $testUser['username'],
        $testUser['email'],
        $testUser['password'],
        $testUser['first_name'],
        $testUser['last_name']
    );
    
    if (!$result['success']) {
        return 'Registration failed: ' . implode(', ', $result['errors']);
    }
    
    // Clean up test user
    $db = Database::getInstance();
    $db->delete('users', ['username' => $testUser['username']]);
    
    return true;
});

// Test 4: Password Validation
runTest('Password Validation', function() {
    $auth = new Auth();
    
    // Test weak passwords
    $weakPasswords = ['123', 'password', 'abc123', 'Password'];
    
    foreach ($weakPasswords as $weakPassword) {
        $result = $auth->register('testuser', 'test@example.com', $weakPassword);
        if ($result['success']) {
            return "Weak password '$weakPassword' was accepted";
        }
    }
    
    return true;
});

// Test 5: User Authentication
runTest('User Authentication', function() {
    $auth = new Auth();
    
    // Test with demo credentials
    $result = $auth->login('admin', 'AdminPass123!');
    
    if (!$result['success']) {
        return 'Login failed with valid credentials: ' . implode(', ', $result['errors']);
    }
    
    // Test with invalid credentials
    $result = $auth->login('admin', 'wrongpassword');
    
    if ($result['success']) {
        return 'Login succeeded with invalid credentials';
    }
    
    return true;
});

// Test 6: CSRF Token Generation
runTest('CSRF Token Generation', function() {
    $token1 = generateCSRFToken();
    $token2 = generateCSRFToken();
    
    if (empty($token1) || strlen($token1) < 32) {
        return 'CSRF token is too short or empty';
    }
    
    if ($token1 !== $token2) {
        return 'CSRF tokens should be consistent within session';
    }
    
    return true;
});

// Test 7: Input Sanitization
runTest('Input Sanitization', function() {
    $maliciousInput = '<script>alert("XSS")</script>';
    $sanitized = sanitizeInput($maliciousInput);
    
    if (strpos($sanitized, '<script>') !== false) {
        return 'XSS vulnerability detected in input sanitization';
    }
    
    return true;
});

// Test 8: Post CRUD Operations
runTest('Post CRUD Operations', function() {
    $post = new Post();
    
    // Create test post
    $testPostData = [
        'title' => 'Test Post ' . time(),
        'content' => 'This is a test post content.',
        'author_id' => 1, // Admin user
        'status' => 'draft'
    ];
    
    $createResult = $post->createPost($testPostData);
    if (!$createResult['success']) {
        return 'Post creation failed: ' . implode(', ', $createResult['errors']);
    }
    
    $postId = $createResult['post_id'];
    
    // Read post
    $retrievedPost = $post->getPost($postId);
    if (!$retrievedPost || $retrievedPost['title'] !== $testPostData['title']) {
        return 'Post retrieval failed';
    }
    
    // Update post
    $updateData = [
        'title' => 'Updated Test Post ' . time(),
        'content' => 'Updated content.',
        'author_id' => 1,
        'status' => 'published'
    ];
    
    $updateResult = $post->updatePost($postId, $updateData);
    if (!$updateResult['success']) {
        return 'Post update failed: ' . implode(', ', $updateResult['errors']);
    }
    
    // Delete post
    $deleteResult = $post->deletePost($postId);
    if (!$deleteResult['success']) {
        return 'Post deletion failed: ' . implode(', ', $deleteResult['errors']);
    }
    
    return true;
});

// Test 9: Search Functionality
runTest('Search Functionality', function() {
    $post = new Post();
    
    // Test search with existing content
    $searchResults = $post->getPosts(1, 10, 'PHP');
    
    if (!is_array($searchResults)) {
        return 'Search results should be an array';
    }
    
    // Test search count
    $searchCount = $post->getPostCount('PHP');
    
    if (!is_numeric($searchCount) || $searchCount < 0) {
        return 'Search count should be a non-negative number';
    }
    
    return true;
});

// Test 10: Pagination
runTest('Pagination System', function() {
    $post = new Post();
    
    // Test pagination with different page sizes
    $page1 = $post->getPosts(1, 2);
    $page2 = $post->getPosts(2, 2);
    
    if (!is_array($page1) || !is_array($page2)) {
        return 'Pagination should return arrays';
    }
    
    if (count($page1) > 2 || count($page2) > 2) {
        return 'Pagination limit not working correctly';
    }
    
    return true;
});

// Test 11: Role-Based Access Control
runTest('Role-Based Access Control', function() {
    // Simulate different user roles
    $_SESSION['user'] = ['id' => 1, 'role' => 'admin'];
    
    if (!isAdmin()) {
        return 'Admin role detection failed';
    }
    
    if (!isEditor()) {
        return 'Editor role detection failed for admin';
    }
    
    $_SESSION['user'] = ['id' => 2, 'role' => 'editor'];
    
    if (isAdmin()) {
        return 'Admin role incorrectly detected for editor';
    }
    
    if (!isEditor()) {
        return 'Editor role detection failed';
    }
    
    $_SESSION['user'] = ['id' => 3, 'role' => 'user'];
    
    if (isAdmin() || isEditor()) {
        return 'Elevated roles incorrectly detected for user';
    }
    
    return true;
});

// Test 12: Session Security
runTest('Session Security', function() {
    // Test session regeneration
    $oldSessionId = session_id();
    session_regenerate_id(true);
    $newSessionId = session_id();
    
    if ($oldSessionId === $newSessionId) {
        return 'Session ID regeneration failed';
    }
    
    return true;
});

// Test 13: File Upload Validation
runTest('File Upload Validation', function() {
    // Test allowed file types
    $allowedTypes = ALLOWED_IMAGE_TYPES;
    
    if (!in_array('jpg', $allowedTypes) || !in_array('png', $allowedTypes)) {
        return 'Basic image types not allowed';
    }
    
    // Test max file size
    if (UPLOAD_MAX_SIZE < 1024 * 1024) {
        return 'Upload size limit too restrictive';
    }
    
    return true;
});

// Test 14: Email Validation
runTest('Email Validation', function() {
    $validEmails = ['test@example.com', 'user.name@domain.co.uk', 'test+tag@example.org'];
    $invalidEmails = ['invalid-email', '@example.com', 'test@', 'test..test@example.com'];
    
    foreach ($validEmails as $email) {
        if (!isValidEmail($email)) {
            return "Valid email '$email' was rejected";
        }
    }
    
    foreach ($invalidEmails as $email) {
        if (isValidEmail($email)) {
            return "Invalid email '$email' was accepted";
        }
    }
    
    return true;
});

// Test 15: URL Slug Generation
runTest('URL Slug Generation', function() {
    $testCases = [
        'Hello World' => 'hello-world',
        'PHP & MySQL Tutorial' => 'php-mysql-tutorial',
        'Special Characters!@#$%' => 'special-characters',
        'Multiple   Spaces' => 'multiple-spaces'
    ];
    
    foreach ($testCases as $input => $expected) {
        $actual = generateSlug($input);
        if ($actual !== $expected) {
            return "Slug generation failed: '$input' -> '$actual' (expected '$expected')";
        }
    }
    
    return true;
});

// Clean up any test data
unset($_SESSION['user']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Test Suite - Task 5 Final Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-pass { color: #198754; }
        .test-fail { color: #dc3545; }
        .test-error { color: #fd7e14; }
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring-circle {
            transition: stroke-dashoffset 0.35s;
            transform-origin: 50% 50%;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">
                            <i class="fas fa-vial me-2"></i>
                            Comprehensive Test Suite
                        </h2>
                        <p class="mb-0 opacity-75">Task 5: Final Project & Certification</p>
                    </div>
                    
                    <div class="card-body">
                        <!-- Test Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h3 class="mb-0"><?= $passedTests ?></h3>
                                        <small>Tests Passed</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h3 class="mb-0"><?= $totalTests - $passedTests ?></h3>
                                        <small>Tests Failed</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h3 class="mb-0"><?= round(($passedTests / $totalTests) * 100, 1) ?>%</h3>
                                        <small>Success Rate</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" 
                                     style="width: <?= ($passedTests / $totalTests) * 100 ?>%">
                                    <?= $passedTests ?>/<?= $totalTests ?> Tests Passed
                                </div>
                            </div>
                        </div>

                        <!-- Test Results -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Test Name</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($testResults as $test): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($test['name']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'PASS' => 'test-pass',
                                                    'FAIL' => 'test-fail',
                                                    'ERROR' => 'test-error'
                                                ];
                                                $statusIcon = [
                                                    'PASS' => 'fa-check-circle',
                                                    'FAIL' => 'fa-times-circle',
                                                    'ERROR' => 'fa-exclamation-triangle'
                                                ];
                                                ?>
                                                <span class="<?= $statusClass[$test['status']] ?>">
                                                    <i class="fas <?= $statusIcon[$test['status']] ?> me-1"></i>
                                                    <?= $test['status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($test['message']) ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Test Categories -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5><i class="fas fa-shield-alt me-2"></i>Security Tests</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Password Validation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>CSRF Protection</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Input Sanitization</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Session Security</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Role-Based Access</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-cogs me-2"></i>Functionality Tests</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Database Operations</li>
                                    <li><i class="fas fa-check text-success me-2"></i>CRUD Operations</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Search & Pagination</li>
                                    <li><i class="fas fa-check text-success me-2"></i>File Upload Validation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Email Validation</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center mt-4">
                            <a href="../public/index.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Go to Application
                            </a>
                            <button onclick="location.reload()" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Run Tests Again
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-footer text-muted text-center">
                        <small>
                            <i class="fas fa-clock me-1"></i>
                            Test completed at <?= date('Y-m-d H:i:s') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
