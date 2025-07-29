<?php
/**
 * Blog Posts Functions
 * Task 2: PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get all blog posts with author information
 * @param int $limit Number of posts to retrieve
 * @param int $offset Offset for pagination
 * @return array Array of posts
 */
function getAllPosts($limit = 10, $offset = 0) {
    try {
        $pdo = getDBConnection();

        $sql = "SELECT p.*, u.username as author_name
                FROM posts p
                JOIN users u ON p.author_id = u.id
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);

        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Get posts error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single post by ID with author information
 * @param int $postId Post ID
 * @return array|null Post data or null if not found
 */
function getPostById($postId) {
    try {
        $pdo = getDBConnection();

        $sql = "SELECT p.*, u.username as author_name
                FROM posts p
                JOIN users u ON p.author_id = u.id
                WHERE p.id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$postId]);

        return $stmt->fetch();

    } catch (PDOException $e) {
        error_log("Get post error: " . $e->getMessage());
        return null;
    }
}

/**
 * Create a new blog post
 * @param string $title Post title
 * @param string $content Post content
 * @param int $authorId Author user ID
 * @return array Result array with success status and message
 */
function createPost($title, $content, $authorId) {
    try {
        $pdo = getDBConnection();

        $sql = "INSERT INTO posts (title, content, author_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $content, $authorId]);

        $postId = $pdo->lastInsertId();

        return [
            'success' => true,
            'message' => 'Post created successfully',
            'post_id' => $postId
        ];

    } catch (PDOException $e) {
        error_log("Create post error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create post. Please try again.'];
    }
}

/**
 * Update an existing blog post
 * @param int $postId Post ID
 * @param string $title Post title
 * @param string $content Post content
 * @param int $authorId Author user ID (for authorization)
 * @return array Result array with success status and message
 */
function updatePost($postId, $title, $content, $authorId) {
    try {
        $pdo = getDBConnection();

        // Check if post exists and user is the author
        $checkSql = "SELECT author_id FROM posts WHERE id = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$postId]);
        $post = $checkStmt->fetch();

        if (!$post) {
            return ['success' => false, 'message' => 'Post not found'];
        }

        if ($post['author_id'] != $authorId) {
            return ['success' => false, 'message' => 'You can only edit your own posts'];
        }

        // Update the post
        $sql = "UPDATE posts SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $content, $postId]);

        return ['success' => true, 'message' => 'Post updated successfully'];

    } catch (PDOException $e) {
        error_log("Update post error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update post. Please try again.'];
    }
}

/**
 * Delete a blog post
 * @param int $postId Post ID
 * @param int $authorId Author user ID (for authorization)
 * @return array Result array with success status and message
 */
function deletePost($postId, $authorId) {
    try {
        $pdo = getDBConnection();

        // Check if post exists and user is the author
        $checkSql = "SELECT author_id FROM posts WHERE id = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$postId]);
        $post = $checkStmt->fetch();

        if (!$post) {
            return ['success' => false, 'message' => 'Post not found'];
        }

        if ($post['author_id'] != $authorId) {
            return ['success' => false, 'message' => 'You can only delete your own posts'];
        }

        // Delete the post
        $sql = "DELETE FROM posts WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$postId]);

        return ['success' => true, 'message' => 'Post deleted successfully'];

    } catch (PDOException $e) {
        error_log("Delete post error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete post. Please try again.'];
    }
}

/**
 * Get posts by author
 * @param int $authorId Author user ID
 * @param int $limit Number of posts to retrieve
 * @param int $offset Offset for pagination
 * @return array Array of posts
 */
function getPostsByAuthor($authorId, $limit = 10, $offset = 0) {
    try {
        $pdo = getDBConnection();

        $sql = "SELECT p.*, u.username as author_name
                FROM posts p
                JOIN users u ON p.author_id = u.id
                WHERE p.author_id = ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$authorId, $limit, $offset]);

        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Get posts by author error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total number of posts
 * @return int Total post count
 */
function getTotalPostCount() {
    try {
        $pdo = getDBConnection();

        $sql = "SELECT COUNT(*) as total FROM posts";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['total'];

    } catch (PDOException $e) {
        error_log("Get post count error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Validate post input
 * @param string $title Post title
 * @param string $content Post content
 * @return array Validation result
 */
function validatePost($title, $content) {
    $errors = [];

    // Title validation
    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) < 3) {
        $errors[] = 'Title must be at least 3 characters long';
    } elseif (strlen($title) > 200) {
        $errors[] = 'Title must be less than 200 characters';
    }

    // Content validation
    if (empty($content)) {
        $errors[] = 'Content is required';
    } elseif (strlen($content) < 10) {
        $errors[] = 'Content must be at least 10 characters long';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
?>