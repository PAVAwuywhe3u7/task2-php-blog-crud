<?php
/**
 * Delete Post Page
 * Task 2: PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/posts.php';

// Require user to be logged in
requireLogin();

// Get post ID from URL
$postId = $_GET['id'] ?? null;

if (!$postId || !is_numeric($postId)) {
    setFlashMessage('Invalid post ID.', 'error');
    header('Location: ../index.php');
    exit();
}

// Get the post to verify ownership
$post = getPostById($postId);

if (!$post) {
    setFlashMessage('Post not found.', 'error');
    header('Location: ../index.php');
    exit();
}

// Check if user is the author
if ($post['author_id'] != getCurrentUserId()) {
    setFlashMessage('You can only delete your own posts.', 'error');
    header('Location: view.php?id=' . $postId);
    exit();
}

// Process deletion
$result = deletePost($postId, getCurrentUserId());

if ($result['success']) {
    setFlashMessage('Post "' . $post['title'] . '" has been deleted successfully.', 'success');
    header('Location: my-posts.php');
} else {
    setFlashMessage($result['message'], 'error');
    header('Location: view.php?id=' . $postId);
}

exit();
?>
