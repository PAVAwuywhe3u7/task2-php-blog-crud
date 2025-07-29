<?php
/**
 * Task 5: Final Project & Certification - Authentication Class
 * Aerospace Internship Program - Complete Blog Application
 * 
 * Secure user authentication and authorization
 */

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Register a new user
     */
    public function register($username, $email, $password, $firstName = '', $lastName = '') {
        try {
            // Validate input
            $errors = $this->validateRegistration($username, $email, $password);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Check if user already exists
            if ($this->userExists($username, $email)) {
                return ['success' => false, 'errors' => ['Username or email already exists']];
            }

            // Hash password
            $hashedPassword = hashPassword($password);

            // Insert user
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => 'user',
                'is_active' => 1,
                'email_verified' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->db->insert('users', $userData);

            if ($userId) {
                logSecurityEvent('user_registered', ['user_id' => $userId, 'username' => $username]);
                return ['success' => true, 'user_id' => $userId];
            }

            return ['success' => false, 'errors' => ['Registration failed. Please try again.']];

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password, $rememberMe = false) {
        try {
            $identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            // Check rate limiting
            if (!checkRateLimit($identifier)) {
                logSecurityEvent('login_rate_limited', ['identifier' => $identifier]);
                return ['success' => false, 'errors' => ['Too many login attempts. Please try again later.']];
            }

            // Find user
            $user = $this->getUserByUsernameOrEmail($username);
            
            if (!$user) {
                recordFailedAttempt($identifier);
                logSecurityEvent('login_user_not_found', ['username' => $username]);
                return ['success' => false, 'errors' => ['Invalid username or password']];
            }

            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                logSecurityEvent('login_account_locked', ['user_id' => $user['id']]);
                return ['success' => false, 'errors' => ['Account is temporarily locked. Please try again later.']];
            }

            // Check if account is active
            if (!$user['is_active']) {
                logSecurityEvent('login_inactive_account', ['user_id' => $user['id']]);
                return ['success' => false, 'errors' => ['Account is deactivated. Please contact administrator.']];
            }

            // Verify password
            if (!verifyPassword($password, $user['password'])) {
                $this->incrementFailedAttempts($user['id']);
                recordFailedAttempt($identifier);
                logSecurityEvent('login_invalid_password', ['user_id' => $user['id']]);
                return ['success' => false, 'errors' => ['Invalid username or password']];
            }

            // Successful login
            $this->resetFailedAttempts($user['id']);
            clearRateLimit($identifier);
            $this->updateLastLogin($user['id']);
            
            // Set session
            $this->setUserSession($user);
            
            // Create session record
            $this->createSessionRecord($user['id']);

            logSecurityEvent('login_successful', ['user_id' => $user['id']]);
            
            return ['success' => true, 'user' => $user];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Login failed. Please try again.']];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        $user = getCurrentUser();
        
        if ($user) {
            // Remove session record
            $this->removeSessionRecord($user['id']);
            logSecurityEvent('logout', ['user_id' => $user['id']]);
        }

        // Clear session
        session_unset();
        session_destroy();
        
        // Start new session
        session_start();
        session_regenerate_id(true);
    }

    /**
     * Change user password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get user
            $user = $this->getUserById($userId);
            if (!$user) {
                return ['success' => false, 'errors' => ['User not found']];
            }

            // Verify current password
            if (!verifyPassword($currentPassword, $user['password'])) {
                logSecurityEvent('password_change_invalid_current', ['user_id' => $userId]);
                return ['success' => false, 'errors' => ['Current password is incorrect']];
            }

            // Validate new password
            $errors = $this->validatePassword($newPassword);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Update password
            $hashedPassword = hashPassword($newPassword);
            $updated = $this->db->update('users', 
                ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')], 
                ['id' => $userId]
            );

            if ($updated) {
                logSecurityEvent('password_changed', ['user_id' => $userId]);
                return ['success' => true];
            }

            return ['success' => false, 'errors' => ['Password update failed']];

        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Password change failed. Please try again.']];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ? AND is_active = 1";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Get user by username or email
     */
    public function getUserByUsernameOrEmail($identifier) {
        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1";
        return $this->db->fetch($sql, [$identifier, $identifier]);
    }

    /**
     * Check if user exists
     */
    public function userExists($username, $email) {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $result = $this->db->fetch($sql, [$username, $email]);
        return !empty($result);
    }

    /**
     * Validate registration data
     */
    private function validateRegistration($username, $email, $password) {
        $errors = [];

        // Username validation
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Username must be less than 50 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }

        // Email validation
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address';
        }

        // Password validation
        $passwordErrors = $this->validatePassword($password);
        $errors = array_merge($errors, $passwordErrors);

        return $errors;
    }

    /**
     * Validate password strength
     */
    private function validatePassword($password) {
        $errors = [];

        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        return $errors;
    }

    /**
     * Set user session
     */
    private function setUserSession($user) {
        // Remove sensitive data
        unset($user['password']);
        
        $_SESSION['user'] = $user;
        $_SESSION['login_time'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    /**
     * Increment failed login attempts
     */
    private function incrementFailedAttempts($userId) {
        $sql = "UPDATE users SET failed_login_attempts = failed_login_attempts + 1";
        
        // Lock account after max attempts
        $sql .= ", locked_until = CASE 
                    WHEN failed_login_attempts + 1 >= ? 
                    THEN DATE_ADD(NOW(), INTERVAL ? SECOND) 
                    ELSE locked_until 
                  END";
        
        $sql .= " WHERE id = ?";
        
        $this->db->query($sql, [MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME, $userId]);
    }

    /**
     * Reset failed login attempts
     */
    private function resetFailedAttempts($userId) {
        $this->db->update('users', 
            ['failed_login_attempts' => 0, 'locked_until' => null], 
            ['id' => $userId]
        );
    }

    /**
     * Update last login time
     */
    private function updateLastLogin($userId) {
        $this->db->update('users', 
            ['last_login' => date('Y-m-d H:i:s')], 
            ['id' => $userId]
        );
    }

    /**
     * Create session record
     */
    private function createSessionRecord($userId) {
        // Clean old sessions first
        $this->cleanOldSessions();
        
        $sessionData = [
            'id' => session_id(),
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('user_sessions', $sessionData);
    }

    /**
     * Remove session record
     */
    private function removeSessionRecord($userId) {
        $this->db->delete('user_sessions', ['user_id' => $userId, 'id' => session_id()]);
    }

    /**
     * Clean old sessions
     */
    private function cleanOldSessions() {
        $sql = "DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)";
        $this->db->query($sql, [SESSION_LIFETIME]);
    }

    /**
     * Check if current session is valid
     */
    public function validateSession() {
        if (!isLoggedIn()) {
            return false;
        }

        $user = getCurrentUser();
        $sessionId = session_id();
        
        $sql = "SELECT id FROM user_sessions WHERE id = ? AND user_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        $session = $this->db->fetch($sql, [$sessionId, $user['id'], SESSION_LIFETIME]);
        
        if (!$session) {
            $this->logout();
            return false;
        }

        // Update session activity
        $this->db->update('user_sessions', 
            ['last_activity' => date('Y-m-d H:i:s')], 
            ['id' => $sessionId]
        );

        return true;
    }

    /**
     * Get user statistics
     */
    public function getUserStats($userId) {
        $sql = "SELECT 
                    COUNT(p.id) as post_count,
                    SUM(p.view_count) as total_views,
                    MAX(p.published_at) as last_post_date
                FROM posts p 
                WHERE p.author_id = ? AND p.status = 'published'";
        
        return $this->db->fetch($sql, [$userId]);
    }
}
?>
