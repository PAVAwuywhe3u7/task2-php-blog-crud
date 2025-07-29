<?php
/**
 * Secure Logout
 * Task 4: Security-Enhanced PHP Blog Application
 * Aerospace Internship Project
 */

// Initialize security
define('SECURITY_INIT', true);
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/session.php';

// Check if user is logged in
if (isLoggedIn()) {
    $username = getCurrentUsername();
    
    // Logout user securely
    logoutUser();
    
    // Set flash message
    setFlashMessage('You have been logged out successfully. Stay secure, ' . htmlspecialchars($username) . '!', 'success');
} else {
    setFlashMessage('You are not logged in.', 'info');
}

// Redirect to home page
header('Location: ../index.php');
exit();
?>
