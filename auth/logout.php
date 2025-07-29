<?php
/**
 * Logout Page
 * Task 2: PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';

// Check if user is logged in
if (isLoggedIn()) {
    $username = getCurrentUsername();
    logoutUser();
    setFlashMessage("Goodbye, $username! You have been logged out successfully.", 'success');
} else {
    setFlashMessage('You are not logged in.', 'warning');
}

// Redirect to home page
header('Location: /task2-php-blog/index.php');
exit();
?>