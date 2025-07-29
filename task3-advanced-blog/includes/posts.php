<?php
/**
 * Enhanced Blog Posts Functions with Search
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get all blog posts with optional search and pagination
 * @param string $search Search term (optional)
 * @param int $limit Number of posts to retrieve
 * @param int $offset Offset for pagination
 * @return array Array of posts
 */
function getAllPosts($search = '', $limit = 10, $offset = 0) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT p.*, u.username as author_name
                FROM posts p
                JOIN users u ON p.author_id = u.id";
        
        $params = [];
        
        // Add search condition if search term provided
        if (!empty($search)) {
            $sql .= " WHERE (p.title LIKE ? OR p.content LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get posts error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of posts with optional search
 * @param string $search Search term (optional)
 * @return int Total number of posts
 */
function getTotalPostCount($search = '') {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT COUNT(*) as count FROM posts p";
        $params = [];
        
        // Add search condition if search term provided
        if (!empty($search)) {
            $sql .= " WHERE (p.title LIKE ? OR p.content LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['count'];
        
    } catch (PDOException $e) {
        error_log("Get post count error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Search posts by title and content
 * @param string $search Search term
 * @param int $limit Number of posts to retrieve
 * @param int $offset Offset for pagination
 * @return array Array of matching posts
 */
function searchPosts($search, $limit = 10, $offset = 0) {
    return getAllPosts($search, $limit, $offset);
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
        $sql = "UPDATE posts SET title = ?, content = ?, updated_at = NOW() WHERE id = ?";
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
 * Get posts by author with optional search and pagination
 * @param int $authorId Author user ID
 * @param string $search Search term (optional)
 * @param int $limit Number of posts to retrieve
 * @param int $offset Offset for pagination
 * @return array Array of posts
 */
function getPostsByAuthor($authorId, $search = '', $limit = 10, $offset = 0) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT p.*, u.username as author_name
                FROM posts p
                JOIN users u ON p.author_id = u.id
                WHERE p.author_id = ?";
        
        $params = [$authorId];
        
        // Add search condition if search term provided
        if (!empty($search)) {
            $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get posts by author error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of posts by author with optional search
 * @param int $authorId Author user ID
 * @param string $search Search term (optional)
 * @return int Total number of posts
 */
function getTotalPostCountByAuthor($authorId, $search = '') {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT COUNT(*) as count FROM posts p WHERE p.author_id = ?";
        $params = [$authorId];
        
        // Add search condition if search term provided
        if (!empty($search)) {
            $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['count'];
        
    } catch (PDOException $e) {
        error_log("Get post count by author error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Highlight search terms in text
 * @param string $text Text to highlight
 * @param string $search Search term
 * @return string Text with highlighted search terms
 */
function highlightSearchTerms($text, $search) {
    if (empty($search)) {
        return $text;
    }
    
    $highlighted = preg_replace(
        '/(' . preg_quote($search, '/') . ')/i',
        '<mark class="bg-warning">$1</mark>',
        $text
    );
    
    return $highlighted;
}

/**
 * Get recent posts for sidebar or widgets
 * @param int $limit Number of recent posts
 * @return array Array of recent posts
 */
function getRecentPosts($limit = 5) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT p.id, p.title, p.created_at, u.username as author_name
                FROM posts p
                JOIN users u ON p.author_id = u.id
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get recent posts error: " . $e->getMessage());
        return [];
    }
}
?>
