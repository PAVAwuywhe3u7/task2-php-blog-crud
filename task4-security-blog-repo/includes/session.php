<?php
/**
 * Enhanced Session Management
 * Task 4: Security-Enhanced PHP Blog Application
 * Aerospace Internship Project
 */

// Security check
if (!defined('SECURITY_INIT')) {
    require_once __DIR__ . '/../config/security.php';
}

/**
 * Initialize secure session
 */
function initializeSecureSession() {
    // Session security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isHTTPS());
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_lifetime', 0); // Session cookies only
    
    // Custom session name
    session_name('SECURE_BLOG_SESSION');
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
        session_regenerate_id(true);
    } elseif (time() - $_SESSION['last_regeneration'] > SESSION_REGENERATE_INTERVAL) {
        $_SESSION['last_regeneration'] = time();
        session_regenerate_id(true);
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        destroySession();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    
    // Validate session integrity
    if (!validateSessionIntegrity()) {
        destroySession();
        return false;
    }
    
    return true;
}

/**
 * Check if connection is HTTPS
 * @return bool True if HTTPS, false otherwise
 */
function isHTTPS() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
           $_SERVER['SERVER_PORT'] == 443 ||
           (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

/**
 * Validate session integrity
 * @return bool True if valid, false otherwise
 */
function validateSessionIntegrity() {
    // Check if session has required security markers
    if (!isset($_SESSION['user_agent_hash']) || !isset($_SESSION['ip_hash'])) {
        if (isset($_SESSION['user_id'])) {
            // Session exists but missing security markers - regenerate
            $_SESSION['user_agent_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $_SESSION['ip_hash'] = hash('sha256', getClientIP());
            return true;
        }
        return true; // No user logged in, no validation needed
    }
    
    // Validate user agent
    $currentUserAgentHash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($_SESSION['user_agent_hash'] !== $currentUserAgentHash) {
        logSecurityEvent('session_hijack_attempt', [
            'reason' => 'user_agent_mismatch',
            'session_user_id' => $_SESSION['user_id'] ?? null
        ]);
        return false;
    }
    
    // Validate IP address (with some flexibility for mobile users)
    $currentIPHash = hash('sha256', getClientIP());
    if ($_SESSION['ip_hash'] !== $currentIPHash) {
        // Log but don't immediately invalidate (mobile users may change IPs)
        logSecurityEvent('session_ip_change', [
            'session_user_id' => $_SESSION['user_id'] ?? null,
            'new_ip' => getClientIP()
        ]);
        
        // Update IP hash for mobile compatibility
        $_SESSION['ip_hash'] = $currentIPHash;
    }
    
    return true;
}

/**
 * Login user and create secure session
 * @param array $user User data from database
 * @return bool Success status
 */
function loginUser($user) {
    // Regenerate session ID on login
    session_regenerate_id(true);
    
    // Set session data
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'] ?? 'user';
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['last_regeneration'] = time();
    
    // Security markers
    $_SESSION['user_agent_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    $_SESSION['ip_hash'] = hash('sha256', getClientIP());
    
    // Store session in database
    storeSessionInDatabase(session_id(), $user['id']);
    
    // Update user's last login
    updateUserLastLogin($user['id']);
    
    // Log successful login
    logSecurityEvent('user_login', [
        'user_id' => $user['id'],
        'username' => $user['username']
    ], $user['id']);
    
    return true;
}

/**
 * Logout user and destroy session
 */
function logoutUser() {
    $userId = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'unknown';
    
    // Remove session from database
    if ($userId) {
        removeSessionFromDatabase(session_id());
        
        // Log logout
        logSecurityEvent('user_logout', [
            'user_id' => $userId,
            'username' => $username
        ], $userId);
    }
    
    // Destroy session
    destroySession();
}

/**
 * Destroy session completely
 */
function destroySession() {
    // Clear session data
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * @return string|null Username or null if not logged in
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Get current user role
 * @return string|null Role name or null if not logged in
 */
function getCurrentUserRole() {
    return $_SESSION['role_name'] ?? null;
}

/**
 * Get current user role ID
 * @return int|null Role ID or null if not logged in
 */
function getCurrentUserRoleId() {
    return $_SESSION['role_id'] ?? null;
}

/**
 * Require user to be logged in
 * @param string $redirectUrl URL to redirect to if not logged in
 */
function requireLogin($redirectUrl = 'auth/login.php') {
    if (!isLoggedIn()) {
        // Store intended URL for redirect after login
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        
        setFlashMessage('Please log in to access this page.', 'warning');
        header('Location: ' . $redirectUrl);
        exit();
    }
}

/**
 * Require user to be guest (not logged in)
 * @param string $redirectUrl URL to redirect to if logged in
 */
function requireGuest($redirectUrl = 'index.php') {
    if (isLoggedIn()) {
        header('Location: ' . $redirectUrl);
        exit();
    }
}

/**
 * Store session in database
 * @param string $sessionId Session ID
 * @param int $userId User ID
 */
function storeSessionInDatabase($sessionId, $userId) {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        // Remove old sessions for this user (limit concurrent sessions)
        modifyQuery("DELETE FROM user_sessions WHERE user_id = ?", [$userId]);
        
        // Insert new session
        insertQuery("
            INSERT INTO user_sessions (id, user_id, ip_address, user_agent, last_activity) 
            VALUES (?, ?, ?, ?, NOW())
        ", [
            $sessionId,
            $userId,
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
    } catch (Exception $e) {
        error_log("Session storage error: " . $e->getMessage());
    }
}

/**
 * Remove session from database
 * @param string $sessionId Session ID
 */
function removeSessionFromDatabase($sessionId) {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        modifyQuery("DELETE FROM user_sessions WHERE id = ?", [$sessionId]);
        
    } catch (Exception $e) {
        error_log("Session removal error: " . $e->getMessage());
    }
}

/**
 * Update user's last login time
 * @param int $userId User ID
 */
function updateUserLastLogin($userId) {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        modifyQuery("UPDATE users SET last_login = NOW() WHERE id = ?", [$userId]);
        
    } catch (Exception $e) {
        error_log("Last login update error: " . $e->getMessage());
    }
}

/**
 * Flash message functions
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Clean up expired sessions
 */
function cleanupExpiredSessions() {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        // Remove sessions older than 24 hours
        $cleaned = modifyQuery("
            DELETE FROM user_sessions 
            WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        if ($cleaned > 0) {
            logSecurityEvent('session_cleanup', ['sessions_removed' => $cleaned]);
        }
        
        return $cleaned;
        
    } catch (Exception $e) {
        error_log("Session cleanup error: " . $e->getMessage());
        return 0;
    }
}

// Initialize session on include
if (!headers_sent()) {
    initializeSecureSession();
}
?>
