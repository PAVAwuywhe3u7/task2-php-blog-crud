<?php
/**
 * Task 5: Final Project & Certification - Logout
 * Aerospace Internship Program - Complete Blog Application
 */

// Initialize application
define('APP_INIT', true);
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

// Initialize Auth class
$auth = new Auth();

// Perform logout
$auth->logout();

// Set success message
setFlashMessage('success', 'You have been successfully logged out. Thank you for using our blog!');

// Redirect to homepage
redirect('index.php');
?>
