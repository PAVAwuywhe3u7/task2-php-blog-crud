<?php
/**
 * Authentication Functions
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Register a new user
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Plain text password
 * @return array Result array with success status and message
 */
function registerUser($username, $email, $password) {
    try {
        $pdo = getDBConnection();

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);

        return ['success' => true, 'message' => 'User registered successfully'];

    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Authenticate user login
 * @param string $username Username
 * @param string $password Plain text password
 * @return array Result array with success status, message, and user data
 */
function authenticateUser($username, $password) {
    try {
        $pdo = getDBConnection();

        // Get user by username with email for session
        $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

/**
 * Get user by ID
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    try {
        $pdo = getDBConnection();

        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        return $stmt->fetch();

    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get user statistics
 * @param int $userId User ID
 * @return array User statistics
 */
function getUserStats($userId) {
    try {
        $pdo = getDBConnection();
        
        // Count user's posts
        $stmt = $pdo->prepare("SELECT COUNT(*) as post_count FROM posts WHERE author_id = ?");
        $stmt->execute([$userId]);
        $postCount = $stmt->fetch()['post_count'];
        
        // Get latest post date
        $stmt = $pdo->prepare("SELECT MAX(created_at) as latest_post FROM posts WHERE author_id = ?");
        $stmt->execute([$userId]);
        $latestPost = $stmt->fetch()['latest_post'];
        
        return [
            'post_count' => $postCount,
            'latest_post' => $latestPost
        ];
        
    } catch (PDOException $e) {
        error_log("Get user stats error: " . $e->getMessage());
        return [
            'post_count' => 0,
            'latest_post' => null
        ];
    }
}

/**
 * Validate registration input
 * @param string $username Username
 * @param string $email Email
 * @param string $password Password
 * @param string $confirmPassword Confirm password
 * @return array Validation result
 */
function validateRegistration($username, $email, $password, $confirmPassword) {
    $errors = [];

    // Username validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long';
    } elseif (strlen($username) > 50) {
        $errors[] = 'Username must be less than 50 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }

    // Email validation
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    // Password validation
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }

    // Confirm password validation
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate login input
 * @param string $username Username
 * @param string $password Password
 * @return array Validation result
 */
function validateLogin($username, $password) {
    $errors = [];

    if (empty($username)) {
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
 * Check if username is available
 * @param string $username Username to check
 * @return bool True if available, false if taken
 */
function isUsernameAvailable($username) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        return !$stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Username check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if email is available
 * @param string $email Email to check
 * @return bool True if available, false if taken
 */
function isEmailAvailable($email) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        return !$stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Email check error: " . $e->getMessage());
        return false;
    }
}
?>
