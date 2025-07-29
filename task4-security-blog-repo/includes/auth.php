<?php
/**
 * Enhanced Authentication Functions
 * Task 4: Security-Enhanced PHP Blog Application
 * Aerospace Internship Project
 */

// Security check
if (!defined('SECURITY_INIT')) {
    require_once __DIR__ . '/../config/security.php';
}

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Authenticate user with enhanced security
 * @param string $username Username or email
 * @param string $password Password
 * @return array Result with success status and user data or error message
 */
function authenticateUser($username, $password) {
    try {
        // Rate limiting check
        $clientIP = getClientIP();
        if (!checkRateLimit($clientIP, 'login')) {
            logSecurityEvent('login_rate_limit_exceeded', ['ip' => $clientIP]);
            return [
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.',
                'locked' => true
            ];
        }
        
        // Get user by username or email
        $user = selectSingle("
            SELECT u.*, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
        ", [$username, $username]);
        
        if (!$user) {
            logSecurityEvent('login_attempt_invalid_user', ['username' => $username]);
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }
        
        // Check if account is locked
        if (isAccountLocked($user)) {
            logSecurityEvent('login_attempt_locked_account', [
                'user_id' => $user['id'],
                'username' => $user['username']
            ]);
            
            $lockTime = strtotime($user['locked_until']) - time();
            $minutes = ceil($lockTime / 60);
            
            return [
                'success' => false,
                'message' => "Account is locked. Try again in $minutes minutes.",
                'locked' => true
            ];
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            // Increment failed login attempts
            incrementFailedLoginAttempts($user['id']);
            
            logSecurityEvent('login_attempt_invalid_password', [
                'user_id' => $user['id'],
                'username' => $user['username']
            ]);
            
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }
        
        // Check if password needs rehashing (security improvement)
        if (password_needs_rehash($user['password'], PASSWORD_ARGON2ID)) {
            $newHash = hashPassword($password);
            modifyQuery("UPDATE users SET password = ? WHERE id = ?", [$newHash, $user['id']]);
        }
        
        // Reset failed login attempts on successful login
        resetFailedLoginAttempts($user['id']);
        
        // Login successful
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Login successful.'
        ];
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Authentication system error. Please try again.'
        ];
    }
}

/**
 * Register new user with enhanced validation
 * @param array $userData User registration data
 * @return array Result with success status and message
 */
function registerUser($userData) {
    try {
        // Validate registration data
        $validation = validateRegistration($userData);
        if (!$validation->isValid) {
            return [
                'success' => false,
                'errors' => $validation->errors
            ];
        }
        
        $sanitized = $validation->sanitizedData;
        
        // Hash password
        $hashedPassword = hashPassword($userData['password']);
        
        // Default role is 'user' (role_id = 3)
        $defaultRoleId = 3;
        
        // Begin transaction
        beginTransaction();
        
        try {
            // Insert user
            $userId = insertQuery("
                INSERT INTO users (username, email, password, role_id, first_name, last_name, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [
                $sanitized['username'],
                $sanitized['email'],
                $hashedPassword,
                $defaultRoleId,
                $sanitized['first_name'],
                $sanitized['last_name']
            ]);
            
            // Commit transaction
            commitTransaction();
            
            // Log successful registration
            logSecurityEvent('user_registered', [
                'user_id' => $userId,
                'username' => $sanitized['username'],
                'email' => $sanitized['email']
            ]);
            
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Registration successful! You can now log in.'
            ];
            
        } catch (Exception $e) {
            rollbackTransaction();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        
        // Check for duplicate key errors
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            if (strpos($e->getMessage(), 'username') !== false) {
                return [
                    'success' => false,
                    'errors' => ['username' => 'Username is already taken.']
                ];
            } elseif (strpos($e->getMessage(), 'email') !== false) {
                return [
                    'success' => false,
                    'errors' => ['email' => 'Email is already registered.']
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Registration failed. Please try again.'
        ];
    }
}

/**
 * Change user password with validation
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return array Result with success status and message
 */
function changePassword($userId, $currentPassword, $newPassword) {
    try {
        // Get user
        $user = selectSingle("SELECT password FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            logSecurityEvent('password_change_invalid_current', ['user_id' => $userId], $userId);
            return [
                'success' => false,
                'message' => 'Current password is incorrect.'
            ];
        }
        
        // Validate new password
        $validation = validatePassword($newPassword);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }
        
        // Hash new password
        $hashedPassword = hashPassword($newPassword);
        
        // Update password
        $affected = modifyQuery("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $userId]);
        
        if ($affected > 0) {
            logSecurityEvent('password_changed', ['user_id' => $userId], $userId);
            return [
                'success' => true,
                'message' => 'Password changed successfully.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update password.'
        ];
        
    } catch (Exception $e) {
        error_log("Password change error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Password change failed. Please try again.'
        ];
    }
}

/**
 * Check if account is locked
 * @param array $user User data
 * @return bool True if locked, false otherwise
 */
function isAccountLocked($user) {
    if ($user['failed_login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return true;
        }
    }
    return false;
}

/**
 * Increment failed login attempts
 * @param int $userId User ID
 */
function incrementFailedLoginAttempts($userId) {
    try {
        // Get current attempts
        $user = selectSingle("SELECT failed_login_attempts FROM users WHERE id = ?", [$userId]);
        $attempts = ($user['failed_login_attempts'] ?? 0) + 1;
        
        // Calculate lock time if max attempts reached
        $lockUntil = null;
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + ACCOUNT_LOCKOUT_TIME);
        }
        
        // Update attempts and lock time
        modifyQuery("
            UPDATE users 
            SET failed_login_attempts = ?, locked_until = ? 
            WHERE id = ?
        ", [$attempts, $lockUntil, $userId]);
        
        if ($lockUntil) {
            logSecurityEvent('account_locked', [
                'user_id' => $userId,
                'attempts' => $attempts,
                'locked_until' => $lockUntil
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Failed login increment error: " . $e->getMessage());
    }
}

/**
 * Reset failed login attempts
 * @param int $userId User ID
 */
function resetFailedLoginAttempts($userId) {
    try {
        modifyQuery("
            UPDATE users 
            SET failed_login_attempts = 0, locked_until = NULL 
            WHERE id = ?
        ", [$userId]);
        
    } catch (Exception $e) {
        error_log("Failed login reset error: " . $e->getMessage());
    }
}

/**
 * Get user by ID with role information
 * @param int $userId User ID
 * @return array|null User data with role
 */
function getUserById($userId) {
    try {
        return selectSingle("
            SELECT u.*, r.name as role_name, r.permissions 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ", [$userId]);
        
    } catch (Exception $e) {
        error_log("Get user by ID error: " . $e->getMessage());
        return null;
    }
}

/**
 * Update user profile
 * @param int $userId User ID
 * @param array $data Profile data
 * @return array Result with success status and message
 */
function updateUserProfile($userId, $data) {
    try {
        $sanitized = [];
        
        // Validate and sanitize data
        if (isset($data['first_name'])) {
            $firstName = trim($data['first_name']);
            if (empty($firstName) || strlen($firstName) > 50) {
                return [
                    'success' => false,
                    'message' => 'Invalid first name.'
                ];
            }
            $sanitized['first_name'] = sanitizeInput($firstName);
        }
        
        if (isset($data['last_name'])) {
            $lastName = trim($data['last_name']);
            if (empty($lastName) || strlen($lastName) > 50) {
                return [
                    'success' => false,
                    'message' => 'Invalid last name.'
                ];
            }
            $sanitized['last_name'] = sanitizeInput($lastName);
        }
        
        if (isset($data['email'])) {
            $email = trim($data['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format.'
                ];
            }
            
            // Check if email is already taken by another user
            $existing = selectSingle("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'Email is already taken.'
                ];
            }
            
            $sanitized['email'] = sanitizeInput($email, 'email');
        }
        
        if (empty($sanitized)) {
            return [
                'success' => false,
                'message' => 'No valid data to update.'
            ];
        }
        
        // Build update query
        $setParts = [];
        $values = [];
        
        foreach ($sanitized as $field => $value) {
            $setParts[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $userId; // For WHERE clause
        
        $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
        $affected = modifyQuery($sql, $values);
        
        if ($affected > 0) {
            logSecurityEvent('profile_updated', [
                'user_id' => $userId,
                'updated_fields' => array_keys($sanitized)
            ], $userId);
            
            return [
                'success' => true,
                'message' => 'Profile updated successfully.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'No changes were made.'
        ];
        
    } catch (Exception $e) {
        error_log("Profile update error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Profile update failed. Please try again.'
        ];
    }
}

/**
 * Validate login form data
 * @param string $username Username
 * @param string $password Password
 * @return array Validation result
 */
function validateLogin($username, $password) {
    $errors = [];
    
    if (empty(trim($username))) {
        $errors[] = 'Username is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Get all users with pagination (admin only)
 * @param int $page Page number
 * @param int $perPage Items per page
 * @param string $search Search term
 * @return array Users data with pagination info
 */
function getAllUsers($page = 1, $perPage = 20, $search = '') {
    try {
        $offset = ($page - 1) * $perPage;
        $searchCondition = '';
        $params = [];
        
        if (!empty($search)) {
            $searchCondition = "WHERE u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        // Get users
        $users = selectQuery("
            SELECT u.*, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            $searchCondition
            ORDER BY u.created_at DESC 
            LIMIT ? OFFSET ?
        ", array_merge($params, [$perPage, $offset]));
        
        // Get total count
        $totalResult = selectSingle("
            SELECT COUNT(*) as total 
            FROM users u 
            $searchCondition
        ", $params);
        
        $total = $totalResult['total'] ?? 0;
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
        
    } catch (Exception $e) {
        error_log("Get all users error: " . $e->getMessage());
        return [
            'users' => [],
            'total' => 0,
            'page' => 1,
            'per_page' => $perPage,
            'total_pages' => 0
        ];
    }
}
?>
