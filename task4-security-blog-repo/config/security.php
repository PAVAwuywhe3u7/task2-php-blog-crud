<?php
/**
 * Security Configuration
 * Task 4: Security-Enhanced PHP Blog Application
 * Aerospace Internship Project
 */

// Prevent direct access
if (!defined('SECURITY_INIT')) {
    die('Direct access not permitted');
}

// Security Constants
define('SECURITY_INIT', true);

// CSRF Protection
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Password Security
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_MAX_LENGTH', 128);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('PASSWORD_HASH_COST', 12);

// Account Security
define('MAX_LOGIN_ATTEMPTS', 5);
define('ACCOUNT_LOCKOUT_TIME', 900); // 15 minutes
define('SESSION_TIMEOUT', 7200); // 2 hours
define('SESSION_REGENERATE_INTERVAL', 300); // 5 minutes

// Input Validation
define('MAX_USERNAME_LENGTH', 50);
define('MAX_EMAIL_LENGTH', 100);
define('MAX_POST_TITLE_LENGTH', 255);
define('MAX_POST_CONTENT_LENGTH', 65535);

// File Upload Security (if implemented)
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Rate Limiting
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Security Headers Configuration
$securityHeaders = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com;",
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()'
];

// Apply security headers
function applySecurityHeaders() {
    global $securityHeaders;
    
    foreach ($securityHeaders as $header => $value) {
        header("$header: $value");
    }
    
    // Remove server information
    header_remove('X-Powered-By');
    header_remove('Server');
}

// CSRF Token Functions
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    
    $token = bin2hex(random_bytes(32));
    $expiry = time() + CSRF_TOKEN_EXPIRY;
    
    $_SESSION['csrf_tokens'][$token] = $expiry;
    
    // Clean expired tokens
    cleanExpiredCSRFTokens();
    
    return $token;
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_tokens'][$token])) {
        return false;
    }
    
    if ($_SESSION['csrf_tokens'][$token] < time()) {
        unset($_SESSION['csrf_tokens'][$token]);
        return false;
    }
    
    // Token is valid, remove it (one-time use)
    unset($_SESSION['csrf_tokens'][$token]);
    return true;
}

function cleanExpiredCSRFTokens() {
    if (!isset($_SESSION['csrf_tokens'])) {
        return;
    }
    
    $currentTime = time();
    foreach ($_SESSION['csrf_tokens'] as $token => $expiry) {
        if ($expiry < $currentTime) {
            unset($_SESSION['csrf_tokens'][$token]);
        }
    }
}

// Input Sanitization Functions
function sanitizeInput($input, $type = 'string') {
    if (is_array($input)) {
        return array_map(function($item) use ($type) {
            return sanitizeInput($item, $type);
        }, $input);
    }
    
    // Remove null bytes
    $input = str_replace("\0", '', $input);
    
    switch ($type) {
        case 'email':
            return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            
        case 'url':
            return filter_var(trim($input), FILTER_SANITIZE_URL);
            
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
        case 'html':
            return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
        case 'string':
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// Password Validation
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long';
    }
    
    if (strlen($password) > PASSWORD_MAX_LENGTH) {
        $errors[] = 'Password must be less than ' . PASSWORD_MAX_LENGTH . ' characters long';
    }
    
    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    // Check for common weak passwords
    $weakPasswords = [
        'password', 'password123', '123456', 'qwerty', 'admin', 'letmein',
        'welcome', 'monkey', '1234567890', 'password1', 'abc123'
    ];
    
    if (in_array(strtolower($password), $weakPasswords)) {
        $errors[] = 'Password is too common and easily guessable';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// Secure Password Hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // 64 MB
        'time_cost' => 4,       // 4 iterations
        'threads' => 3          // 3 threads
    ]);
}

// Rate Limiting
function checkRateLimit($identifier, $action = 'general') {
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    $key = $action . '_' . $identifier;
    $currentTime = time();
    $windowStart = $currentTime - RATE_LIMIT_WINDOW;
    
    // Clean old entries
    foreach ($_SESSION['rate_limits'] as $k => $timestamps) {
        $_SESSION['rate_limits'][$k] = array_filter($timestamps, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        if (empty($_SESSION['rate_limits'][$k])) {
            unset($_SESSION['rate_limits'][$k]);
        }
    }
    
    // Check current rate
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [];
    }
    
    if (count($_SESSION['rate_limits'][$key]) >= RATE_LIMIT_REQUESTS) {
        return false;
    }
    
    // Add current request
    $_SESSION['rate_limits'][$key][] = $currentTime;
    return true;
}

// IP Address Validation
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Security Event Logging
function logSecurityEvent($event, $details = [], $userId = null) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'user_id' => $userId,
        'ip_address' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];
    
    // Log to file (ensure logs directory exists and is writable)
    $logFile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    
    // Also log to database if available
    try {
        require_once __DIR__ . '/database.php';
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO audit_log (user_id, action, old_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $event,
            json_encode($details),
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // Silently fail database logging, file logging is primary
        error_log("Failed to log security event to database: " . $e->getMessage());
    }
}

// Initialize security
if (!headers_sent()) {
    applySecurityHeaders();
}

// Clean expired CSRF tokens on each request
if (session_status() === PHP_SESSION_ACTIVE) {
    cleanExpiredCSRFTokens();
}
?>
