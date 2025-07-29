<?php
/**
 * Secure Registration Page
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
$formData = [
    'username' => '',
    'email' => '',
    'first_name' => '',
    'last_name' => ''
];

// Check if CSRF token is valid for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $errors['csrf'] = 'Security token validation failed. Please try again.';
        logSecurityEvent('csrf_token_invalid', ['action' => 'register']);
    } else {
        // Process registration
        $registrationData = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'accept_terms' => !empty($_POST['accept_terms'])
        ];
        
        // Validate registration data
        $validation = validateRegistration($registrationData);
        
        if ($validation->isValid) {
            // Attempt registration
            $result = registerUser($registrationData);
            
            if ($result['success']) {
                setFlashMessage('Registration successful! You can now log in with your credentials.', 'success');
                header('Location: login.php');
                exit();
            } else {
                if (isset($result['errors'])) {
                    $errors = array_merge($errors, $result['errors']);
                } else {
                    $errors['general'] = $result['message'];
                }
            }
        } else {
            $errors = array_merge($errors, $validation->errors);
        }
        
        // Preserve form data (except passwords)
        $formData = [
            'username' => $validation->sanitizedData['username'] ?? '',
            'email' => $validation->sanitizedData['email'] ?? '',
            'first_name' => $validation->sanitizedData['first_name'] ?? '',
            'last_name' => $validation->sanitizedData['last_name'] ?? ''
        ];
    }
}

// Generate CSRF token for the form
$csrfToken = generateCSRFToken();

$pageTitle = 'Secure Registration - Task 4 Blog';
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
        }
        
        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            padding: 2rem 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        .register-header {
            background: var(--security-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-form {
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
        
        .btn-secure-register {
            background: var(--security-gradient);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-secure-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-medium { background: #ffc107; width: 50%; }
        .strength-good { background: #17a2b8; width: 75%; }
        .strength-strong { background: #28a745; width: 100%; }
        
        .password-requirements {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .requirement {
            color: #dc3545;
            transition: color 0.3s ease;
        }
        
        .requirement.met {
            color: #28a745;
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: #f8f9fa;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="register-container">
                    <!-- Header -->
                    <div class="register-header">
                        <h2 class="mb-3">
                            <i class="fas fa-user-shield me-2"></i>Secure Registration
                        </h2>
                        <p class="mb-0">Create your secure account</p>
                        <div class="mt-3">
                            <a href="../index.php" class="back-link">
                                <i class="fas fa-arrow-left me-1"></i>Back to Blog
                            </a>
                        </div>
                    </div>
                    
                    <!-- Form -->
                    <div class="register-form">
                        <!-- Error Messages -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Please correct the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $field => $error): ?>
                                        <?php if (is_array($error)): ?>
                                            <?php foreach ($error as $err): ?>
                                                <li><?php echo htmlspecialchars($err); ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Registration Form -->
                        <form method="POST" action="register.php" id="registerForm" novalidate>
                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>First Name *
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="<?php echo htmlspecialchars($formData['first_name']); ?>"
                                           placeholder="Enter your first name"
                                           required
                                           maxlength="50">
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Last Name *
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="<?php echo htmlspecialchars($formData['last_name']); ?>"
                                           placeholder="Enter your last name"
                                           required
                                           maxlength="50">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-at me-1"></i>Username *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($formData['username']); ?>"
                                       placeholder="Choose a unique username"
                                       required
                                       autocomplete="username"
                                       maxlength="50">
                                <div class="form-text">3-50 characters, letters, numbers, and underscores only</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address *
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($formData['email']); ?>"
                                       placeholder="Enter your email address"
                                       required
                                       autocomplete="email"
                                       maxlength="100">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Password *
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Create a strong password"
                                           required
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                                <div class="password-requirements">
                                    <div class="requirement" id="req-length">
                                        <i class="fas fa-times me-1"></i>At least 8 characters
                                    </div>
                                    <div class="requirement" id="req-uppercase">
                                        <i class="fas fa-times me-1"></i>One uppercase letter
                                    </div>
                                    <div class="requirement" id="req-lowercase">
                                        <i class="fas fa-times me-1"></i>One lowercase letter
                                    </div>
                                    <div class="requirement" id="req-number">
                                        <i class="fas fa-times me-1"></i>One number
                                    </div>
                                    <div class="requirement" id="req-special">
                                        <i class="fas fa-times me-1"></i>One special character
                                    </div>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Confirm Password *
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="Confirm your password"
                                       required
                                       autocomplete="new-password">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="accept_terms" name="accept_terms" required>
                                <label class="form-check-label" for="accept_terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a> 
                                    and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a> *
                                </label>
                                <div class="invalid-feedback">You must accept the terms and conditions</div>
                            </div>
                            
                            <button type="submit" class="btn btn-secure-register w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Create Secure Account
                            </button>
                        </form>
                        
                        <!-- Login Link -->
                        <div class="text-center">
                            <span class="text-muted">Already have an account?</span>
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-sign-in-alt me-1"></i>Sign In
                            </a>
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
                    <h5 class="modal-title">Terms of Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>By using this secure blog platform, you agree to:</p>
                    <ul>
                        <li>Use the platform responsibly and legally</li>
                        <li>Not attempt to compromise security measures</li>
                        <li>Respect other users' privacy and content</li>
                        <li>Follow community guidelines</li>
                    </ul>
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
                    <p>We protect your privacy by:</p>
                    <ul>
                        <li>Encrypting all sensitive data</li>
                        <li>Using secure authentication methods</li>
                        <li>Not sharing personal information with third parties</li>
                        <li>Implementing comprehensive security measures</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const togglePassword = document.getElementById('togglePassword');
            const strengthBar = document.getElementById('passwordStrength');
            
            // Auto-focus first name field
            document.getElementById('first_name').focus();
            
            // Password visibility toggle
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
            
            // Password strength checker
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
            
            // Password confirmation validation
            confirmPasswordInput.addEventListener('input', function() {
                if (passwordInput.value !== this.value) {
                    this.setCustomValidity('Passwords do not match');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
            
            function checkPasswordStrength(password) {
                const requirements = {
                    'req-length': password.length >= 8,
                    'req-uppercase': /[A-Z]/.test(password),
                    'req-lowercase': /[a-z]/.test(password),
                    'req-number': /[0-9]/.test(password),
                    'req-special': /[^A-Za-z0-9]/.test(password)
                };
                
                let score = 0;
                
                for (const [reqId, met] of Object.entries(requirements)) {
                    const element = document.getElementById(reqId);
                    const icon = element.querySelector('i');
                    
                    if (met) {
                        element.classList.add('met');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-check');
                        score++;
                    } else {
                        element.classList.remove('met');
                        icon.classList.remove('fa-check');
                        icon.classList.add('fa-times');
                    }
                }
                
                // Update strength bar
                strengthBar.className = 'password-strength';
                if (score === 0) {
                    strengthBar.style.width = '0%';
                } else if (score <= 2) {
                    strengthBar.classList.add('strength-weak');
                } else if (score <= 3) {
                    strengthBar.classList.add('strength-medium');
                } else if (score <= 4) {
                    strengthBar.classList.add('strength-good');
                } else {
                    strengthBar.classList.add('strength-strong');
                }
            }
            
            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Check all required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
