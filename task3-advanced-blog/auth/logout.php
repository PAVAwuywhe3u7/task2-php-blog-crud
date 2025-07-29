<?php
/**
 * User Logout
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';

// Check if user is logged in
if (isLoggedIn()) {
    $username = getCurrentUsername();
    
    // Logout user
    logoutUser();
    
    // Set flash message
    setFlashMessage('You have been logged out successfully. See you soon, ' . htmlspecialchars($username) . '!', 'success');
} else {
    setFlashMessage('You are not logged in.', 'info');
}

// Redirect to home page
header('Location: ../index.php');
exit();
?>
