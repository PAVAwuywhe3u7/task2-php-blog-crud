<?php
/**
 * Delete Post
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/posts.php';

// Require login
requireLogin();

// Get post ID from URL
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$postId) {
    setFlashMessage('Invalid post ID.', 'error');
    header('Location: ../index.php');
    exit();
}

// Get post to verify ownership
$post = getPostById($postId);

if (!$post) {
    setFlashMessage('Post not found.', 'error');
    header('Location: ../index.php');
    exit();
}

// Check if user owns the post
if ($post['author_id'] != getCurrentUserId()) {
    setFlashMessage('You can only delete your own posts.', 'error');
    header('Location: ../index.php');
    exit();
}

// Delete the post
$result = deletePost($postId, getCurrentUserId());

if ($result['success']) {
    setFlashMessage('Post "' . htmlspecialchars($post['title']) . '" has been deleted successfully.', 'success');
} else {
    setFlashMessage($result['message'], 'error');
}

// Redirect to home page
header('Location: ../index.php');
exit();
?>
