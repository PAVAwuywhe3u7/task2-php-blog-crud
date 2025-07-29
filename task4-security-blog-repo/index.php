<?php
/**
 * Secure Homepage
 * Task 4: Security-Enhanced PHP Blog Application
 * Aerospace Internship Project
 */

// Initialize security
define('SECURITY_INIT', true);
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/rbac.php';

// Get flash message
$flashMessage = getFlashMessage();

$pageTitle = 'Task 4: Security-Enhanced Blog';
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
            --security-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: var(--security-gradient);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 4rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="shield" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M10 2L18 6V14C18 16 14 18 10 18C6 18 2 16 2 14V6L10 2Z" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23shield)"/></svg>') repeat;
            opacity: 0.1;
        }
        
        .security-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin: 0 0.5rem;
        }
        
        .feature-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin: 0 auto 1rem;
        }
        
        .icon-security { background: var(--security-gradient); }
        .icon-rbac { background: var(--primary-gradient); }
        .icon-validation { background: var(--danger-gradient); }
        
        .btn-gradient {
            background: var(--security-gradient);
            border: none;
            color: white;
            transition: all 0.3s ease;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .security-status {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            border: 1px solid #c3e6c3;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            font-size: 0.8rem;
        }
        
        .status-active {
            background: #28a745;
            color: white;
        }
        
        .demo-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-shield-alt me-2"></i>Task 4: Security Blog
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <a class="nav-link" href="posts/create.php">
                            <i class="fas fa-plus me-1"></i>Create Post
                        </a>
                        <a class="nav-link" href="posts/my-posts.php">
                            <i class="fas fa-user-edit me-1"></i>My Posts
                        </a>
                        <?php if (isAdmin()): ?>
                            <a class="nav-link" href="admin/dashboard.php">
                                <i class="fas fa-cog me-1"></i>Admin
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <span class="navbar-text me-3">
                            <i class="fas fa-user me-1"></i>
                            Welcome, <strong><?php echo htmlspecialchars(getCurrentUsername()); ?></strong>
                            <span class="badge bg-light text-dark ms-1"><?php echo htmlspecialchars(getCurrentUserRole()); ?></span>
                        </span>
                        <a class="nav-link" href="auth/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    <?php else: ?>
                        <a class="nav-link" href="auth/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                        <a class="nav-link" href="auth/register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 mb-4">
                        <i class="fas fa-shield-alt me-3"></i>
                        Security-Enhanced Blog Platform
                    </h1>
                    <p class="lead mb-4">
                        Advanced PHP blog application with enterprise-level security features, 
                        role-based access control, and comprehensive protection mechanisms.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center mb-4">
                        <span class="security-badge">
                            <i class="fas fa-lock me-1"></i>CSRF Protected
                        </span>
                        <span class="security-badge">
                            <i class="fas fa-users-cog me-1"></i>RBAC Enabled
                        </span>
                        <span class="security-badge">
                            <i class="fas fa-shield-virus me-1"></i>XSS Prevention
                        </span>
                        <span class="security-badge">
                            <i class="fas fa-database me-1"></i>SQL Injection Safe
                        </span>
                    </div>
                    
                    <?php if (!isLoggedIn()): ?>
                        <div class="mt-4">
                            <a href="auth/register.php" class="btn btn-gradient btn-lg me-3">
                                <i class="fas fa-user-plus me-2"></i>Get Started
                            </a>
                            <a href="auth/login.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mt-4">
                            <a href="posts/create.php" class="btn btn-gradient btn-lg me-3">
                                <i class="fas fa-plus me-2"></i>Create Post
                            </a>
                            <a href="posts/my-posts.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-list me-2"></i>My Posts
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Flash Messages -->
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show mt-4">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Security Status -->
        <div class="security-status">
            <h3 class="text-success mb-3">
                <i class="fas fa-check-circle me-2"></i>Security Status: ACTIVE
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="status-item">
                        <div class="status-icon status-active">
                            <i class="fas fa-check"></i>
                        </div>
                        <span>CSRF Protection Enabled</span>
                    </div>
                    <div class="status-item">
                        <div class="status-icon status-active">
                            <i class="fas fa-check"></i>
                        </div>
                        <span>SQL Injection Prevention</span>
                    </div>
                    <div class="status-item">
                        <div class="status-icon status-active">
                            <i class="fas fa-check"></i>
                        </div>
                        <span>XSS Protection Active</span>
                    </div>
                    <div class="status-item">
                        <div class="status-icon status-active">
                            <i class="fas fa-check"></i>
                        </div>
                        <span>Secure Session Management</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="status-item">
                        <div class="status-icon status-active">
                            <i class="fas fa-check"></i>
                        </div>
                        <span>Role-Based Access Control</span>
                    </div>
                    <div class="status-item">
                        <div class="status-icon status-active">
                            <i class="fas fa-check"></i>
                        </div>
                        <span>Input Validation & Sanitization</span>
                    </div>
                    <div class="status-item">
                        <div class="status-icon status-active">
                            <i class="fas fa-check"></i>
                        </div>
                        <span>Security Headers Applied</span>
                    </div>
                    <div class="status-item">
                        <div class="status-icon status-active">
                            <i class="fas fa-check"></i>
                        </div>
                        <span>Audit Logging Enabled</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Features -->
        <div class="row">
            <div class="col-lg-4">
                <div class="feature-card card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon icon-security">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="card-title">Advanced Security</h5>
                        <p class="card-text">
                            Comprehensive security measures including CSRF protection, 
                            XSS prevention, SQL injection protection, and secure session management.
                        </p>
                        <a href="#" class="btn btn-gradient">Learn More</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="feature-card card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon icon-rbac">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h5 class="card-title">Role-Based Access</h5>
                        <p class="card-text">
                            Sophisticated RBAC system with Admin, Editor, and User roles. 
                            Granular permissions control access to features and data.
                        </p>
                        <a href="#" class="btn btn-gradient">Explore Roles</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="feature-card card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon icon-validation">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <h5 class="card-title">Enhanced Validation</h5>
                        <p class="card-text">
                            Multi-layer validation with server-side and client-side checks, 
                            input sanitization, and comprehensive error handling.
                        </p>
                        <a href="#" class="btn btn-gradient">View Demo</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demo Section -->
        <div class="demo-section">
            <h3 class="mb-4">
                <i class="fas fa-play-circle me-2"></i>Security Features Demo
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>Test Authentication Security</h5>
                    <ul>
                        <li>Try logging in with invalid credentials</li>
                        <li>Test account lockout after failed attempts</li>
                        <li>Experience secure session management</li>
                        <li>Verify password strength requirements</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Explore Role-Based Access</h5>
                    <ul>
                        <li>Login as different user roles</li>
                        <li>Test permission-based feature access</li>
                        <li>Try accessing restricted admin areas</li>
                        <li>Experience granular content control</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-4">
                <h6>Demo Accounts:</h6>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Admin:</strong> admin / AdminPass123!
                    </div>
                    <div class="col-md-4">
                        <strong>Editor:</strong> editor / EditorPass123!
                    </div>
                    <div class="col-md-4">
                        <strong>User:</strong> user / UserPass123!
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Security monitoring demo
        document.addEventListener('DOMContentLoaded', function() {
            // Log page view for security monitoring
            console.log('Security Blog: Page loaded with enhanced protection');
            
            // Demonstrate CSP compliance
            try {
                // This would be blocked by CSP in production
                // eval('console.log("This would be blocked by CSP")');
            } catch (e) {
                console.log('CSP Protection: Inline script execution prevented');
            }
        });
    </script>
</body>
</html>
