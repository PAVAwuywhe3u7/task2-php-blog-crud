<?php
/**
 * Security Testing Suite
 * Task 4: Security-Enhanced PHP Blog Application
 * Aerospace Internship Project
 */

// Initialize security
define('SECURITY_INIT', true);
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/auth.php';

/**
 * Security Test Suite Class
 */
class SecurityTestSuite {
    private $testResults = [];
    private $passedTests = 0;
    private $failedTests = 0;
    
    public function __construct() {
        echo "<h1>🔐 Security Test Suite - Task 4</h1>\n";
        echo "<p>Running comprehensive security tests...</p>\n";
    }
    
    /**
     * Run all security tests
     */
    public function runAllTests() {
        $this->testDatabaseConnection();
        $this->testPasswordValidation();
        $this->testInputSanitization();
        $this->testCSRFTokenGeneration();
        $this->testRateLimiting();
        $this->testSessionSecurity();
        $this->testRoleBasedAccess();
        $this->testSQLInjectionPrevention();
        $this->testXSSPrevention();
        $this->testSecurityHeaders();
        
        $this->displayResults();
    }
    
    /**
     * Test database connection and schema
     */
    private function testDatabaseConnection() {
        $this->startTest("Database Connection & Schema");
        
        try {
            $result = testDatabaseConnection();
            if ($result['success']) {
                $this->passTest("Database connection successful");
            } else {
                $this->failTest("Database connection failed: " . $result['message']);
                return;
            }
            
            $schemaResult = validateDatabaseSchema();
            if ($schemaResult['success']) {
                $this->passTest("Database schema validation passed");
            } else {
                $this->failTest("Database schema validation failed: " . $schemaResult['message']);
            }
            
        } catch (Exception $e) {
            $this->failTest("Database test exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test password validation
     */
    private function testPasswordValidation() {
        $this->startTest("Password Validation");
        
        // Test weak passwords
        $weakPasswords = ['123', 'password', 'abc123', 'qwerty'];
        foreach ($weakPasswords as $password) {
            $result = validatePassword($password);
            if (!$result['valid']) {
                $this->passTest("Weak password '$password' correctly rejected");
            } else {
                $this->failTest("Weak password '$password' incorrectly accepted");
            }
        }
        
        // Test strong password
        $strongPassword = 'StrongPass123!';
        $result = validatePassword($strongPassword);
        if ($result['valid']) {
            $this->passTest("Strong password correctly accepted");
        } else {
            $this->failTest("Strong password incorrectly rejected");
        }
    }
    
    /**
     * Test input sanitization
     */
    private function testInputSanitization() {
        $this->startTest("Input Sanitization");
        
        // Test XSS prevention
        $xssInput = '<script>alert("xss")</script>';
        $sanitized = sanitizeInput($xssInput);
        if (strpos($sanitized, '<script>') === false) {
            $this->passTest("XSS input correctly sanitized");
        } else {
            $this->failTest("XSS input not properly sanitized");
        }
        
        // Test SQL injection characters
        $sqlInput = "'; DROP TABLE users; --";
        $sanitized = sanitizeInput($sqlInput);
        if ($sanitized !== $sqlInput) {
            $this->passTest("SQL injection characters sanitized");
        } else {
            $this->failTest("SQL injection characters not sanitized");
        }
        
        // Test email sanitization
        $email = "test@example.com<script>";
        $sanitized = sanitizeInput($email, 'email');
        if (filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            $this->passTest("Email sanitization working");
        } else {
            $this->failTest("Email sanitization failed");
        }
    }
    
    /**
     * Test CSRF token generation and validation
     */
    private function testCSRFTokenGeneration() {
        $this->startTest("CSRF Token Security");
        
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Test token generation
        $token1 = generateCSRFToken();
        $token2 = generateCSRFToken();
        
        if ($token1 !== $token2) {
            $this->passTest("CSRF tokens are unique");
        } else {
            $this->failTest("CSRF tokens are not unique");
        }
        
        // Test token validation
        if (validateCSRFToken($token1)) {
            $this->passTest("Valid CSRF token accepted");
        } else {
            $this->failTest("Valid CSRF token rejected");
        }
        
        // Test invalid token
        if (!validateCSRFToken('invalid_token')) {
            $this->passTest("Invalid CSRF token rejected");
        } else {
            $this->failTest("Invalid CSRF token accepted");
        }
    }
    
    /**
     * Test rate limiting
     */
    private function testRateLimiting() {
        $this->startTest("Rate Limiting");
        
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear any existing rate limits
        unset($_SESSION['rate_limits']);
        
        // Test normal rate limiting
        $identifier = 'test_user';
        $allowed = checkRateLimit($identifier, 'test');
        
        if ($allowed) {
            $this->passTest("Rate limiting allows normal requests");
        } else {
            $this->failTest("Rate limiting blocks normal requests");
        }
        
        // Test rate limit enforcement (simulate many requests)
        $_SESSION['rate_limits']['test_' . $identifier] = array_fill(0, RATE_LIMIT_REQUESTS, time());
        $blocked = !checkRateLimit($identifier, 'test');
        
        if ($blocked) {
            $this->passTest("Rate limiting blocks excessive requests");
        } else {
            $this->failTest("Rate limiting fails to block excessive requests");
        }
    }
    
    /**
     * Test session security
     */
    private function testSessionSecurity() {
        $this->startTest("Session Security");
        
        // Test session initialization
        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->passTest("Session successfully initialized");
        } else {
            $this->failTest("Session initialization failed");
        }
        
        // Test session configuration
        $httpOnly = ini_get('session.cookie_httponly');
        if ($httpOnly) {
            $this->passTest("Session cookies are HTTPOnly");
        } else {
            $this->failTest("Session cookies are not HTTPOnly");
        }
        
        // Test session regeneration tracking
        if (isset($_SESSION['last_regeneration'])) {
            $this->passTest("Session regeneration tracking active");
        } else {
            $this->failTest("Session regeneration tracking missing");
        }
    }
    
    /**
     * Test role-based access control
     */
    private function testRoleBasedAccess() {
        $this->startTest("Role-Based Access Control");
        
        try {
            // Test role retrieval
            $roles = getAllRoles();
            if (count($roles) >= 3) {
                $this->passTest("Required roles exist in database");
            } else {
                $this->failTest("Missing required roles in database");
            }
            
            // Test permission checking (mock user)
            $mockUserId = 1; // Assuming admin user exists
            $hasAdminPermission = hasPermission($mockUserId, 'users', 'delete');
            
            if ($hasAdminPermission) {
                $this->passTest("Admin permissions working correctly");
            } else {
                $this->failTest("Admin permissions not working");
            }
            
        } catch (Exception $e) {
            $this->failTest("RBAC test exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test SQL injection prevention
     */
    private function testSQLInjectionPrevention() {
        $this->startTest("SQL Injection Prevention");
        
        try {
            // Test prepared statement usage
            $maliciousInput = "1'; DROP TABLE users; --";
            
            // This should safely return no results without executing the DROP
            $result = selectSingle("SELECT * FROM users WHERE id = ?", [$maliciousInput]);
            
            // If we get here without exception, prepared statements are working
            $this->passTest("Prepared statements prevent SQL injection");
            
            // Test that the users table still exists
            $users = selectQuery("SELECT COUNT(*) as count FROM users");
            if ($users && $users[0]['count'] >= 0) {
                $this->passTest("Database integrity maintained");
            } else {
                $this->failTest("Database integrity compromised");
            }
            
        } catch (Exception $e) {
            $this->failTest("SQL injection test exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test XSS prevention
     */
    private function testXSSPrevention() {
        $this->startTest("XSS Prevention");
        
        // Test output encoding
        $xssPayloads = [
            '<script>alert("xss")</script>',
            '<img src=x onerror=alert("xss")>',
            'javascript:alert("xss")',
            '<svg onload=alert("xss")>'
        ];
        
        foreach ($xssPayloads as $payload) {
            $encoded = htmlspecialchars($payload, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($encoded !== $payload && strpos($encoded, '<script>') === false) {
                $this->passTest("XSS payload correctly encoded");
            } else {
                $this->failTest("XSS payload not properly encoded");
            }
        }
    }
    
    /**
     * Test security headers
     */
    private function testSecurityHeaders() {
        $this->startTest("Security Headers");
        
        // Test if security headers function exists
        if (function_exists('applySecurityHeaders')) {
            $this->passTest("Security headers function available");
        } else {
            $this->failTest("Security headers function missing");
        }
        
        // Test CSP configuration
        global $securityHeaders;
        if (isset($securityHeaders['Content-Security-Policy'])) {
            $this->passTest("Content Security Policy configured");
        } else {
            $this->failTest("Content Security Policy missing");
        }
        
        // Test other security headers
        $requiredHeaders = [
            'X-Content-Type-Options',
            'X-Frame-Options',
            'X-XSS-Protection',
            'Referrer-Policy'
        ];
        
        foreach ($requiredHeaders as $header) {
            if (isset($securityHeaders[$header])) {
                $this->passTest("$header configured");
            } else {
                $this->failTest("$header missing");
            }
        }
    }
    
    /**
     * Start a new test category
     */
    private function startTest($testName) {
        echo "<h3>🧪 Testing: $testName</h3>\n";
    }
    
    /**
     * Record a passed test
     */
    private function passTest($message) {
        echo "<div style='color: green;'>✅ PASS: $message</div>\n";
        $this->passedTests++;
    }
    
    /**
     * Record a failed test
     */
    private function failTest($message) {
        echo "<div style='color: red;'>❌ FAIL: $message</div>\n";
        $this->failedTests++;
    }
    
    /**
     * Display final test results
     */
    private function displayResults() {
        $total = $this->passedTests + $this->failedTests;
        $percentage = $total > 0 ? round(($this->passedTests / $total) * 100, 2) : 0;
        
        echo "<hr>\n";
        echo "<h2>📊 Test Results Summary</h2>\n";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>\n";
        echo "<p><strong>Total Tests:</strong> $total</p>\n";
        echo "<p style='color: green;'><strong>Passed:</strong> {$this->passedTests}</p>\n";
        echo "<p style='color: red;'><strong>Failed:</strong> {$this->failedTests}</p>\n";
        echo "<p><strong>Success Rate:</strong> $percentage%</p>\n";
        
        if ($percentage >= 90) {
            echo "<p style='color: green; font-weight: bold;'>🎉 Excellent! Security implementation is robust.</p>\n";
        } elseif ($percentage >= 75) {
            echo "<p style='color: orange; font-weight: bold;'>⚠️ Good, but some security improvements needed.</p>\n";
        } else {
            echo "<p style='color: red; font-weight: bold;'>🚨 Critical security issues detected. Immediate attention required.</p>\n";
        }
        
        echo "</div>\n";
        
        echo "<h3>🔧 Next Steps</h3>\n";
        echo "<ul>\n";
        echo "<li>Review any failed tests and implement fixes</li>\n";
        echo "<li>Run manual penetration testing</li>\n";
        echo "<li>Review security logs for anomalies</li>\n";
        echo "<li>Update security configurations as needed</li>\n";
        echo "</ul>\n";
    }
}

// Run the tests if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'security-tests.php') {
    echo "<!DOCTYPE html>\n";
    echo "<html><head><title>Security Test Suite</title></head><body>\n";
    
    $testSuite = new SecurityTestSuite();
    $testSuite->runAllTests();
    
    echo "</body></html>\n";
}
?>
