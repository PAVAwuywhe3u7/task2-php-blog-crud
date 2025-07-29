<?php
/**
 * Task 5: Final Project & Certification - Login Page
 * Aerospace Internship Program - Complete Blog Application
 */

// Initialize application
define('APP_INIT', true);
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Initialize Auth class
$auth = new Auth();

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        if (empty($username)) {
            $errors[] = 'Username or email is required';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }

        if (empty($errors)) {
            $result = $auth->login($username, $password, $rememberMe);
            
            if ($result['success']) {
                setFlashMessage('success', 'Welcome back! You have been successfully logged in.');
                
                // Redirect to intended page or dashboard
                $redirectTo = $_GET['redirect'] ?? 'dashboard.php';
                redirect($redirectTo);
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

// Page meta
$pageTitle = 'Login';
$pageDescription = 'Login to your account to access the complete blog application features';

// Include header
include '../templates/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>Welcome Back
                    </h3>
                    <p class="mb-0 opacity-75">Sign in to your account</p>
                </div>
                
                <div class="card-body p-4">
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

                    <!-- Success Message -->
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRFToken() ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user me-1"></i>Username or Email
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="username" 
                                   name="username" 
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   placeholder="Enter your username or email"
                                   required>
                            <div class="invalid-feedback">
                                Please enter your username or email.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter your password"
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword"
                                        title="Show/Hide Password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Please enter your password.
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="remember_me" 
                                   name="remember_me"
                                   <?= isset($_POST['remember_me']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="remember_me">
                                Remember me for 30 days
                            </label>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </div>
                    </form>

                    <!-- Demo Credentials -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-1"></i>Demo Credentials
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Admin:</strong><br>
                                <small>admin / AdminPass123!</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Editor:</strong><br>
                                <small>editor / EditorPass123!</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-center py-3">
                    <p class="mb-0 text-muted">
                        Don't have an account? 
                        <a href="register.php" class="text-decoration-none">
                            <i class="fas fa-user-plus me-1"></i>Create Account
                        </a>
                    </p>
                </div>
            </div>

            <!-- Features Section -->
            <div class="row mt-4">
                <div class="col-md-4 text-center mb-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                            <h6>Secure Login</h6>
                            <small class="text-muted">Protected with advanced security measures</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <h6>Role-Based Access</h6>
                            <small class="text-muted">Different permissions for different roles</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <i class="fas fa-mobile-alt fa-2x text-info mb-2"></i>
                            <h6>Responsive Design</h6>
                            <small class="text-muted">Works perfectly on all devices</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password toggle functionality
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordField = document.getElementById('password');
    const toggleIcon = this.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
        this.title = 'Hide Password';
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
        this.title = 'Show Password';
    }
});

// Form submission with loading state
document.querySelector('form').addEventListener('submit', function(e) {
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    submitButton.innerHTML = '<span class="loading"></span> Signing In...';
    submitButton.disabled = true;
    
    // Re-enable button after 5 seconds in case of error
    setTimeout(function() {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }, 5000);
});

// Auto-focus on username field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('username').focus();
});

// Demo credential quick fill
document.addEventListener('DOMContentLoaded', function() {
    const demoButtons = document.querySelectorAll('.alert-info small');
    demoButtons.forEach(function(button) {
        button.style.cursor = 'pointer';
        button.addEventListener('click', function() {
            const text = this.textContent;
            const parts = text.split(' / ');
            if (parts.length === 2) {
                document.getElementById('username').value = parts[0];
                document.getElementById('password').value = parts[1];
            }
        });
    });
});
</script>

<?php include '../templates/footer.php'; ?>
