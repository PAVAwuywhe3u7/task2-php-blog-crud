<?php
/**
 * Task 5: Final Project & Certification - Registration Page
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
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $agreeTerms = isset($_POST['agree_terms']);

        // Basic validation
        if (empty($username)) {
            $errors[] = 'Username is required';
        }
        if (empty($email)) {
            $errors[] = 'Email is required';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        if (!$agreeTerms) {
            $errors[] = 'You must agree to the terms and conditions';
        }

        if (empty($errors)) {
            $result = $auth->register($username, $email, $password, $firstName, $lastName);
            
            if ($result['success']) {
                setFlashMessage('success', 'Account created successfully! You can now log in.');
                redirect('login.php');
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

// Page meta
$pageTitle = 'Register';
$pageDescription = 'Create a new account to access all blog features';

// Include header
include '../templates/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </h3>
                    <p class="mb-0 opacity-75">Join our blog community</p>
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

                    <!-- Registration Form -->
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRFToken() ?>">
                        
                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>First Name
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                                       placeholder="Enter your first name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Last Name
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                                       placeholder="Enter your last name">
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-at me-1"></i>Username <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   placeholder="Choose a unique username"
                                   pattern="[a-zA-Z0-9_]{3,50}"
                                   required>
                            <div class="form-text">
                                3-50 characters, letters, numbers, and underscores only
                            </div>
                            <div class="invalid-feedback">
                                Please enter a valid username.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   placeholder="Enter your email address"
                                   required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>

                        <!-- Password Fields -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Create a strong password"
                                       minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword"
                                        title="Show/Hide Password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Minimum <?= PASSWORD_MIN_LENGTH ?> characters with uppercase, lowercase, number, and special character
                            </div>
                            <div class="invalid-feedback">
                                Password must meet the requirements above.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock me-1"></i>Confirm Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Confirm your password"
                                   required>
                            <div class="invalid-feedback">
                                Passwords must match.
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="agree_terms" 
                                   name="agree_terms"
                                   required>
                            <label class="form-check-label" for="agree_terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> 
                                and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="invalid-feedback">
                                You must agree to the terms and conditions.
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer text-center py-3">
                    <p class="mb-0 text-muted">
                        Already have an account? 
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i>Sign In
                        </a>
                    </p>
                </div>
            </div>

            <!-- Features Section -->
            <div class="row mt-4">
                <div class="col-md-4 text-center mb-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <i class="fas fa-edit fa-2x text-primary mb-2"></i>
                            <h6>Create Posts</h6>
                            <small class="text-muted">Share your thoughts and ideas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <i class="fas fa-comments fa-2x text-success mb-2"></i>
                            <h6>Engage Community</h6>
                            <small class="text-muted">Comment and interact with others</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                            <h6>Track Progress</h6>
                            <small class="text-muted">Monitor your blog statistics</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Acceptance of Terms</h6>
                <p>By creating an account, you agree to these terms and conditions.</p>
                
                <h6>2. User Responsibilities</h6>
                <p>You are responsible for maintaining the confidentiality of your account and password.</p>
                
                <h6>3. Content Guidelines</h6>
                <p>All content must be appropriate and not violate any laws or regulations.</p>
                
                <h6>4. Privacy</h6>
                <p>We respect your privacy and will protect your personal information.</p>
                
                <h6>5. Termination</h6>
                <p>We reserve the right to terminate accounts that violate these terms.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Information We Collect</h6>
                <p>We collect information you provide when creating an account and using our services.</p>
                
                <h6>How We Use Information</h6>
                <p>Your information is used to provide and improve our services.</p>
                
                <h6>Information Sharing</h6>
                <p>We do not sell or share your personal information with third parties.</p>
                
                <h6>Security</h6>
                <p>We implement security measures to protect your information.</p>
                
                <h6>Contact Us</h6>
                <p>If you have questions about this privacy policy, please contact us.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

// Username availability check (simulated)
document.getElementById('username').addEventListener('blur', function() {
    const username = this.value;
    if (username.length >= 3) {
        // Simulate username check
        setTimeout(() => {
            // This would normally be an AJAX call
            console.log('Checking username availability:', username);
        }, 500);
    }
});

// Form submission with loading state
document.querySelector('form').addEventListener('submit', function(e) {
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    submitButton.innerHTML = '<span class="loading"></span> Creating Account...';
    submitButton.disabled = true;
    
    // Re-enable button after 5 seconds in case of error
    setTimeout(function() {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }, 5000);
});

// Auto-focus on first name field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('first_name').focus();
});
</script>

<?php include '../templates/footer.php'; ?>
