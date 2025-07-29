<?php
/**
 * Secure Login Page
 * Task 4: Security-Enhanced PHP Blog Application
 * Aerospace Internship Project
 */

// Initialize security
define('SECURITY_INIT', true);
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';

// Redirect if already logged in
requireGuest();

$errors = [];
$username = '';
$showCaptcha = false;

// Check if CSRF token is valid for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $errors[] = 'Security token validation failed. Please try again.';
        logSecurityEvent('csrf_token_invalid', ['action' => 'login']);
    } else {
        // Process login
        $loginData = [
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'remember_me' => !empty($_POST['remember_me'])
        ];
        
        // Validate input
        $validation = validateLogin($loginData);
        
        if ($validation->isValid) {
            // Attempt authentication
            $result = authenticateUser($validation->sanitizedData['username'], $loginData['password']);
            
            if ($result['success']) {
                // Login successful
                loginUser($result['user']);
                
                // Redirect to intended page or dashboard
                $redirectUrl = $_SESSION['intended_url'] ?? 'index.php';
                unset($_SESSION['intended_url']);
                
                setFlashMessage('Welcome back, ' . htmlspecialchars($result['user']['username']) . '!', 'success');
                header('Location: ../' . $redirectUrl);
                exit();
            } else {
                $errors[] = $result['message'];
                if (isset($result['locked']) && $result['locked']) {
                    $showCaptcha = true;
                }
            }
        } else {
            $errors = $validation->errors;
        }
        
        $username = $validation->sanitizedData['username'] ?? '';
    }
}

// Generate CSRF token for the form
$csrfToken = generateCSRFToken();

$pageTitle = 'Secure Login - Task 4 Blog';
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
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --security-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        .login-header {
            background: var(--security-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .security-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
        
        .login-form {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
        }
        
        .btn-secure-login {
            background: var(--security-gradient);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-secure-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .security-features {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background: #dc3545; }
        .strength-medium { background: #ffc107; }
        .strength-strong { background: #28a745; }
        
        .back-link {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: #f8f9fa;
            transform: translateX(-5px);
        }
        
        .error-alert {
            border-left: 4px solid #dc3545;
        }
        
        .security-info {
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <!-- Header -->
                    <div class="login-header">
                        <h2 class="mb-3">
                            <i class="fas fa-shield-alt me-2"></i>Secure Login
                        </h2>
                        <p class="mb-0">Enhanced Security Authentication</p>
                        <div class="security-badge">
                            <i class="fas fa-lock me-1"></i>
                            Protected by Advanced Security
                        </div>
                        <div class="mt-3">
                            <a href="../index.php" class="back-link">
                                <i class="fas fa-arrow-left me-1"></i>Back to Blog
                            </a>
                        </div>
                    </div>
                    
                    <!-- Form -->
                    <div class="login-form">
                        <!-- Security Features Info -->
                        <div class="security-features">
                            <h6 class="text-success mb-2">
                                <i class="fas fa-check-circle me-1"></i>Security Features Active
                            </h6>
                            <ul class="mb-0 small">
                                <li>CSRF Protection</li>
                                <li>Rate Limiting</li>
                                <li>Account Lockout Protection</li>
                                <li>Secure Session Management</li>
                            </ul>
                        </div>
                        
                        <!-- Error Messages -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger error-alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php foreach ($errors as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Login Form -->
                        <form method="POST" action="login.php" id="loginForm" novalidate>
                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-1"></i>Username or Email
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           value="<?php echo htmlspecialchars($username); ?>"
                                           placeholder="Enter your username or email"
                                           required
                                           autocomplete="username"
                                           maxlength="100">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter your password"
                                           required
                                           autocomplete="current-password">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Remember me for 30 days
                                </label>
                            </div>
                            
                            <?php if ($showCaptcha): ?>
                            <div class="mb-3">
                                <label class="form-label">Security Verification</label>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Additional security verification required due to multiple failed attempts.
                                </div>
                                <!-- Simple math captcha for demo -->
                                <?php 
                                $num1 = rand(1, 10);
                                $num2 = rand(1, 10);
                                $_SESSION['captcha_answer'] = $num1 + $num2;
                                ?>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <?php echo "$num1 + $num2 = "; ?>
                                    </span>
                                    <input type="number" class="form-control" name="captcha" required>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-secure-login w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Secure Sign In
                            </button>
                        </form>
                        
                        <!-- Additional Links -->
                        <div class="text-center">
                            <div class="mb-2">
                                <a href="forgot-password.php" class="text-decoration-none">
                                    <i class="fas fa-key me-1"></i>Forgot Password?
                                </a>
                            </div>
                            <div>
                                <span class="text-muted">Don't have an account?</span>
                                <a href="register.php" class="text-decoration-none">
                                    <i class="fas fa-user-plus me-1"></i>Create Account
                                </a>
                            </div>
                        </div>
                        
                        <!-- Security Info -->
                        <div class="security-info mt-4 pt-3 border-top">
                            <div class="row text-center">
                                <div class="col-4">
                                    <i class="fas fa-shield-alt text-success"></i>
                                    <div>SSL Encrypted</div>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-lock text-success"></i>
                                    <div>Secure Auth</div>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-eye-slash text-success"></i>
                                    <div>Privacy Protected</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            
            // Auto-focus username field
            usernameInput.focus();
            
            // Password visibility toggle
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
            
            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Username validation
                if (usernameInput.value.trim().length < 3) {
                    showFieldError(usernameInput, 'Username must be at least 3 characters long');
                    isValid = false;
                } else {
                    clearFieldError(usernameInput);
                }
                
                // Password validation
                if (passwordInput.value.length < 6) {
                    showFieldError(passwordInput, 'Password must be at least 6 characters long');
                    isValid = false;
                } else {
                    clearFieldError(passwordInput);
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            // Real-time validation
            usernameInput.addEventListener('blur', function() {
                if (this.value.trim().length > 0 && this.value.trim().length < 3) {
                    showFieldError(this, 'Username must be at least 3 characters long');
                } else {
                    clearFieldError(this);
                }
            });
            
            passwordInput.addEventListener('blur', function() {
                if (this.value.length > 0 && this.value.length < 6) {
                    showFieldError(this, 'Password must be at least 6 characters long');
                } else {
                    clearFieldError(this);
                }
            });
            
            function showFieldError(field, message) {
                field.classList.add('is-invalid');
                const feedback = field.parentNode.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = message;
                }
            }
            
            function clearFieldError(field) {
                field.classList.remove('is-invalid');
                const feedback = field.parentNode.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = '';
                }
            }
            
            // Security monitoring (demo)
            let loginAttempts = 0;
            form.addEventListener('submit', function() {
                loginAttempts++;
                if (loginAttempts > 3) {
                    console.log('Security Alert: Multiple login attempts detected');
                }
            });
        });
    </script>
</body>
</html>
