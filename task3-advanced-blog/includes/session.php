<?php
/**
 * Session Management Functions
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null User ID if logged in, null otherwise
 */
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

/**
 * Get current username
 * @return string|null Username if logged in, null otherwise
 */
function getCurrentUsername() {
    return isLoggedIn() ? $_SESSION['username'] : null;
}

/**
 * Get current user email
 * @return string|null User email if logged in, null otherwise
 */
function getCurrentUserEmail() {
    return isLoggedIn() ? $_SESSION['email'] : null;
}

/**
 * Login user by setting session variables
 * @param array $user User data array
 * @return bool True on success
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    return true;
}

/**
 * Logout user by destroying session
 * @return bool True on success
 */
function logoutUser() {
    // Clear all session variables
    $_SESSION = [];
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    return true;
}

/**
 * Set flash message
 * @param string $message Message text
 * @param string $type Message type (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 * @return array|null Flash message array or null if no message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Check if flash message exists
 * @return bool True if flash message exists
 */
function hasFlashMessage() {
    return isset($_SESSION['flash_message']);
}

/**
 * Require user to be logged in (redirect to login if not)
 * @param string $redirectUrl URL to redirect to after login
 */
function requireLogin($redirectUrl = null) {
    if (!isLoggedIn()) {
        $loginUrl = '/task3-advanced-blog/auth/login.php';
        
        if ($redirectUrl) {
            $loginUrl .= '?redirect=' . urlencode($redirectUrl);
        }
        
        header('Location: ' . $loginUrl);
        exit();
    }
}

/**
 * Require user to be guest (redirect to dashboard if logged in)
 * @param string $redirectUrl URL to redirect to if already logged in
 */
function requireGuest($redirectUrl = '/task3-advanced-blog/index.php') {
    if (isLoggedIn()) {
        header('Location: ' . $redirectUrl);
        exit();
    }
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if token is valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get session information for debugging
 * @return array Session information
 */
function getSessionInfo() {
    return [
        'logged_in' => isLoggedIn(),
        'user_id' => getCurrentUserId(),
        'username' => getCurrentUsername(),
        'email' => getCurrentUserEmail(),
        'login_time' => isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : null,
        'session_id' => session_id(),
        'has_flash' => hasFlashMessage()
    ];
}

/**
 * Check session timeout (optional security feature)
 * @param int $timeout Timeout in seconds (default: 2 hours)
 * @return bool True if session is still valid
 */
function checkSessionTimeout($timeout = 7200) {
    if (isLoggedIn() && isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $timeout) {
            logoutUser();
            setFlashMessage('Your session has expired. Please login again.', 'warning');
            return false;
        }
    }
    return true;
}

/**
 * Update last activity time
 */
function updateLastActivity() {
    if (isLoggedIn()) {
        $_SESSION['last_activity'] = time();
    }
}

// Auto-update last activity on each page load
updateLastActivity();
?>
