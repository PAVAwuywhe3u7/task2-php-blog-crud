<?php
/**
 * Enhanced Form Validation Functions
 * Task 4: Security-Enhanced PHP Blog Application
 * Aerospace Internship Project
 */

// Security check
if (!defined('SECURITY_INIT')) {
    require_once __DIR__ . '/../config/security.php';
}

/**
 * Validation result structure
 */
class ValidationResult {
    public $isValid;
    public $errors;
    public $sanitizedData;
    
    public function __construct($isValid = true, $errors = [], $sanitizedData = []) {
        $this->isValid = $isValid;
        $this->errors = $errors;
        $this->sanitizedData = $sanitizedData;
    }
}

/**
 * Validate user registration data
 * @param array $data Raw form data
 * @return ValidationResult
 */
function validateRegistration($data) {
    $errors = [];
    $sanitized = [];
    
    // Username validation
    $username = trim($data['username'] ?? '');
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters long';
    } elseif (strlen($username) > MAX_USERNAME_LENGTH) {
        $errors['username'] = 'Username must be less than ' . MAX_USERNAME_LENGTH . ' characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores';
    } elseif (isUsernameTaken($username)) {
        $errors['username'] = 'Username is already taken';
    }
    $sanitized['username'] = sanitizeInput($username);
    
    // Email validation
    $email = trim($data['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif (strlen($email) > MAX_EMAIL_LENGTH) {
        $errors['email'] = 'Email must be less than ' . MAX_EMAIL_LENGTH . ' characters';
    } elseif (isEmailTaken($email)) {
        $errors['email'] = 'Email is already registered';
    }
    $sanitized['email'] = sanitizeInput($email, 'email');
    
    // First name validation
    $firstName = trim($data['first_name'] ?? '');
    if (empty($firstName)) {
        $errors['first_name'] = 'First name is required';
    } elseif (strlen($firstName) > 50) {
        $errors['first_name'] = 'First name must be less than 50 characters';
    } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $firstName)) {
        $errors['first_name'] = 'First name contains invalid characters';
    }
    $sanitized['first_name'] = sanitizeInput($firstName);
    
    // Last name validation
    $lastName = trim($data['last_name'] ?? '');
    if (empty($lastName)) {
        $errors['last_name'] = 'Last name is required';
    } elseif (strlen($lastName) > 50) {
        $errors['last_name'] = 'Last name must be less than 50 characters';
    } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $lastName)) {
        $errors['last_name'] = 'Last name contains invalid characters';
    }
    $sanitized['last_name'] = sanitizeInput($lastName);
    
    // Password validation
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    
    $passwordValidation = validatePassword($password);
    if (!$passwordValidation['valid']) {
        $errors['password'] = $passwordValidation['errors'];
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // Terms acceptance validation
    if (empty($data['accept_terms'])) {
        $errors['accept_terms'] = 'You must accept the terms and conditions';
    }
    
    return new ValidationResult(empty($errors), $errors, $sanitized);
}

/**
 * Validate user login data
 * @param array $data Raw form data
 * @return ValidationResult
 */
function validateLogin($data) {
    $errors = [];
    $sanitized = [];
    
    // Username validation
    $username = trim($data['username'] ?? '');
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    }
    $sanitized['username'] = sanitizeInput($username);
    
    // Password validation
    $password = $data['password'] ?? '';
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // Remember me validation
    $rememberMe = !empty($data['remember_me']);
    $sanitized['remember_me'] = $rememberMe;
    
    return new ValidationResult(empty($errors), $errors, $sanitized);
}

/**
 * Validate post creation/edit data
 * @param array $data Raw form data
 * @return ValidationResult
 */
function validatePost($data) {
    $errors = [];
    $sanitized = [];
    
    // Title validation
    $title = trim($data['title'] ?? '');
    if (empty($title)) {
        $errors['title'] = 'Title is required';
    } elseif (strlen($title) > MAX_POST_TITLE_LENGTH) {
        $errors['title'] = 'Title must be less than ' . MAX_POST_TITLE_LENGTH . ' characters';
    } elseif (strlen($title) < 5) {
        $errors['title'] = 'Title must be at least 5 characters long';
    }
    $sanitized['title'] = sanitizeInput($title);
    
    // Content validation
    $content = trim($data['content'] ?? '');
    if (empty($content)) {
        $errors['content'] = 'Content is required';
    } elseif (strlen($content) < 10) {
        $errors['content'] = 'Content must be at least 10 characters long';
    } elseif (strlen($content) > MAX_POST_CONTENT_LENGTH) {
        $errors['content'] = 'Content must be less than ' . MAX_POST_CONTENT_LENGTH . ' characters';
    }
    $sanitized['content'] = $content; // Don't sanitize content as it may contain HTML
    
    // Excerpt validation (optional)
    $excerpt = trim($data['excerpt'] ?? '');
    if (!empty($excerpt) && strlen($excerpt) > 500) {
        $errors['excerpt'] = 'Excerpt must be less than 500 characters';
    }
    $sanitized['excerpt'] = sanitizeInput($excerpt);
    
    // Status validation
    $status = $data['status'] ?? 'draft';
    $allowedStatuses = ['draft', 'published'];
    if (!in_array($status, $allowedStatuses)) {
        $errors['status'] = 'Invalid post status';
    }
    $sanitized['status'] = $status;
    
    // Featured validation
    $featured = !empty($data['is_featured']);
    $sanitized['is_featured'] = $featured;
    
    return new ValidationResult(empty($errors), $errors, $sanitized);
}

/**
 * Validate password change data
 * @param array $data Raw form data
 * @return ValidationResult
 */
function validatePasswordChange($data) {
    $errors = [];
    $sanitized = [];
    
    // Current password validation
    $currentPassword = $data['current_password'] ?? '';
    if (empty($currentPassword)) {
        $errors['current_password'] = 'Current password is required';
    }
    
    // New password validation
    $newPassword = $data['new_password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    
    $passwordValidation = validatePassword($newPassword);
    if (!$passwordValidation['valid']) {
        $errors['new_password'] = $passwordValidation['errors'];
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if ($currentPassword === $newPassword) {
        $errors['new_password'] = 'New password must be different from current password';
    }
    
    return new ValidationResult(empty($errors), $errors, $sanitized);
}

/**
 * Validate search query
 * @param string $query Search query
 * @return ValidationResult
 */
function validateSearchQuery($query) {
    $errors = [];
    $sanitized = [];
    
    $query = trim($query);
    
    if (strlen($query) > 100) {
        $errors['query'] = 'Search query must be less than 100 characters';
    }
    
    // Remove potentially dangerous characters
    $query = preg_replace('/[<>"\']/', '', $query);
    $sanitized['query'] = $query;
    
    return new ValidationResult(empty($errors), $errors, $sanitized);
}

/**
 * Check if username is already taken
 * @param string $username Username to check
 * @return bool True if taken, false if available
 */
function isUsernameTaken($username) {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        $result = selectSingle("SELECT id FROM users WHERE username = ?", [$username]);
        return $result !== false;
        
    } catch (Exception $e) {
        error_log("Username check error: " . $e->getMessage());
        return true; // Assume taken on error for security
    }
}

/**
 * Check if email is already taken
 * @param string $email Email to check
 * @return bool True if taken, false if available
 */
function isEmailTaken($email) {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        $result = selectSingle("SELECT id FROM users WHERE email = ?", [$email]);
        return $result !== false;
        
    } catch (Exception $e) {
        error_log("Email check error: " . $e->getMessage());
        return true; // Assume taken on error for security
    }
}

/**
 * Validate file upload
 * @param array $file $_FILES array element
 * @param array $allowedTypes Allowed file types
 * @param int $maxSize Maximum file size in bytes
 * @return ValidationResult
 */
function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
    $errors = [];
    $sanitized = [];
    
    if (!isset($file['error']) || is_array($file['error'])) {
        $errors['file'] = 'Invalid file upload';
        return new ValidationResult(false, $errors, $sanitized);
    }
    
    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            $errors['file'] = 'No file was uploaded';
            return new ValidationResult(false, $errors, $sanitized);
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errors['file'] = 'File is too large';
            return new ValidationResult(false, $errors, $sanitized);
        default:
            $errors['file'] = 'File upload error';
            return new ValidationResult(false, $errors, $sanitized);
    }
    
    // Check file size
    $maxSize = $maxSize ?? MAX_FILE_SIZE;
    if ($file['size'] > $maxSize) {
        $errors['file'] = 'File is too large. Maximum size: ' . formatBytes($maxSize);
        return new ValidationResult(false, $errors, $sanitized);
    }
    
    // Check file type
    $allowedTypes = $allowedTypes ?? ALLOWED_FILE_TYPES;
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        $errors['file'] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
        return new ValidationResult(false, $errors, $sanitized);
    }
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    if (isset($allowedMimes[$fileExtension]) && $mimeType !== $allowedMimes[$fileExtension]) {
        $errors['file'] = 'File type mismatch. File may be corrupted or malicious.';
        return new ValidationResult(false, $errors, $sanitized);
    }
    
    $sanitized['file'] = [
        'name' => sanitizeInput(basename($file['name'])),
        'type' => $mimeType,
        'size' => $file['size'],
        'tmp_name' => $file['tmp_name'],
        'extension' => $fileExtension
    ];
    
    return new ValidationResult(empty($errors), $errors, $sanitized);
}

/**
 * Format bytes to human readable format
 * @param int $bytes Number of bytes
 * @return string Formatted string
 */
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>
