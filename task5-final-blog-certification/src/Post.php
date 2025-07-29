<?php
/**
 * Task 5: Final Project & Certification - Post Class
 * Aerospace Internship Program - Complete Blog Application
 * 
 * Complete CRUD operations for blog posts with search and pagination
 */

class Post {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all published posts with pagination and search
     */
    public function getPosts($page = 1, $limit = POSTS_PER_PAGE, $search = '', $categoryId = null, $authorId = null) {
        $offset = ($page - 1) * $limit;
        $params = [];
        
        $sql = "SELECT p.*, 
                       u.username as author_username, 
                       u.first_name, 
                       u.last_name,
                       c.name as category_name,
                       c.slug as category_slug,
                       (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'published'";

        // Add search condition
        if (!empty($search)) {
            $sql .= " AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add category filter
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }

        // Add author filter
        if ($authorId) {
            $sql .= " AND p.author_id = ?";
            $params[] = $authorId;
        }

        $sql .= " ORDER BY p.is_featured DESC, p.published_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get total post count for pagination
     */
    public function getPostCount($search = '', $categoryId = null, $authorId = null) {
        $params = [];
        
        $sql = "SELECT COUNT(*) as total FROM posts p WHERE p.status = 'published'";

        // Add search condition
        if (!empty($search)) {
            $sql .= " AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add category filter
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }

        // Add author filter
        if ($authorId) {
            $sql .= " AND p.author_id = ?";
            $params[] = $authorId;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Get single post by ID or slug
     */
    public function getPost($identifier, $bySlug = false) {
        $field = $bySlug ? 'slug' : 'id';
        
        $sql = "SELECT p.*, 
                       u.username as author_username, 
                       u.first_name, 
                       u.last_name,
                       u.bio as author_bio,
                       c.name as category_name,
                       c.slug as category_slug
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.{$field} = ?";

        return $this->db->fetch($sql, [$identifier]);
    }

    /**
     * Create new post
     */
    public function createPost($data) {
        try {
            // Validate required fields
            $errors = $this->validatePost($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title']);
            } else {
                $data['slug'] = $this->generateUniqueSlug($data['slug']);
            }

            // Generate excerpt if not provided
            if (empty($data['excerpt'])) {
                $data['excerpt'] = $this->generateExcerpt($data['content']);
            }

            // Set published_at if status is published
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }

            // Set meta fields if not provided
            if (empty($data['meta_title'])) {
                $data['meta_title'] = $data['title'];
            }
            if (empty($data['meta_description'])) {
                $data['meta_description'] = $data['excerpt'];
            }

            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $postId = $this->db->insert('posts', $data);

            if ($postId) {
                logSecurityEvent('post_created', ['post_id' => $postId, 'author_id' => $data['author_id']]);
                return ['success' => true, 'post_id' => $postId];
            }

            return ['success' => false, 'errors' => ['Failed to create post']];

        } catch (Exception $e) {
            error_log("Create post error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to create post. Please try again.']];
        }
    }

    /**
     * Update existing post
     */
    public function updatePost($id, $data) {
        try {
            // Check if post exists
            $existingPost = $this->getPost($id);
            if (!$existingPost) {
                return ['success' => false, 'errors' => ['Post not found']];
            }

            // Validate required fields
            $errors = $this->validatePost($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Update slug if title changed
            if ($data['title'] !== $existingPost['title']) {
                if (empty($data['slug']) || $data['slug'] === $existingPost['slug']) {
                    $data['slug'] = $this->generateUniqueSlug($data['title'], $id);
                } else {
                    $data['slug'] = $this->generateUniqueSlug($data['slug'], $id);
                }
            }

            // Generate excerpt if not provided
            if (empty($data['excerpt'])) {
                $data['excerpt'] = $this->generateExcerpt($data['content']);
            }

            // Set published_at if status changed to published
            if ($data['status'] === 'published' && $existingPost['status'] !== 'published' && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }

            // Set meta fields if not provided
            if (empty($data['meta_title'])) {
                $data['meta_title'] = $data['title'];
            }
            if (empty($data['meta_description'])) {
                $data['meta_description'] = $data['excerpt'];
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            $updated = $this->db->update('posts', $data, ['id' => $id]);

            if ($updated) {
                logSecurityEvent('post_updated', ['post_id' => $id, 'author_id' => $data['author_id']]);
                return ['success' => true];
            }

            return ['success' => false, 'errors' => ['No changes made']];

        } catch (Exception $e) {
            error_log("Update post error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to update post. Please try again.']];
        }
    }

    /**
     * Delete post
     */
    public function deletePost($id) {
        try {
            $post = $this->getPost($id);
            if (!$post) {
                return ['success' => false, 'errors' => ['Post not found']];
            }

            $deleted = $this->db->delete('posts', ['id' => $id]);

            if ($deleted) {
                logSecurityEvent('post_deleted', ['post_id' => $id, 'title' => $post['title']]);
                return ['success' => true];
            }

            return ['success' => false, 'errors' => ['Failed to delete post']];

        } catch (Exception $e) {
            error_log("Delete post error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to delete post. Please try again.']];
        }
    }

    /**
     * Get posts by author
     */
    public function getPostsByAuthor($authorId, $page = 1, $limit = ADMIN_POSTS_PER_PAGE, $status = null) {
        $offset = ($page - 1) * $limit;
        $params = [$authorId];
        
        $sql = "SELECT p.*, c.name as category_name
                FROM posts p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.author_id = ?";

        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get author post count
     */
    public function getAuthorPostCount($authorId, $status = null) {
        $params = [$authorId];
        $sql = "SELECT COUNT(*) as total FROM posts WHERE author_id = ?";

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Increment post view count
     */
    public function incrementViewCount($postId, $userId = null) {
        try {
            // Record the view
            $viewData = [
                'post_id' => $postId,
                'user_id' => $userId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'viewed_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('post_views', $viewData);

            // Update post view count
            $sql = "UPDATE posts SET view_count = view_count + 1 WHERE id = ?";
            $this->db->query($sql, [$postId]);

        } catch (Exception $e) {
            error_log("View count error: " . $e->getMessage());
        }
    }

    /**
     * Get related posts
     */
    public function getRelatedPosts($postId, $categoryId, $limit = 3) {
        $sql = "SELECT p.*, u.username as author_username, u.first_name, u.last_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.status = 'published' 
                AND p.id != ? 
                AND p.category_id = ?
                ORDER BY p.published_at DESC 
                LIMIT ?";

        return $this->db->fetchAll($sql, [$postId, $categoryId, $limit]);
    }

    /**
     * Get featured posts
     */
    public function getFeaturedPosts($limit = 3) {
        $sql = "SELECT p.*, u.username as author_username, u.first_name, u.last_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.status = 'published' AND p.is_featured = 1
                ORDER BY p.published_at DESC 
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Validate post data
     */
    private function validatePost($data) {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        } elseif (strlen($data['title']) > 255) {
            $errors[] = 'Title must be less than 255 characters';
        }

        if (empty($data['content'])) {
            $errors[] = 'Content is required';
        }

        if (empty($data['author_id'])) {
            $errors[] = 'Author is required';
        }

        if (!in_array($data['status'], ['draft', 'published', 'archived'])) {
            $errors[] = 'Invalid status';
        }

        return $errors;
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug($title, $excludeId = null) {
        $slug = generateSlug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists($slug, $excludeId = null) {
        $sql = "SELECT id FROM posts WHERE slug = ?";
        $params = [$slug];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return !empty($result);
    }

    /**
     * Generate excerpt from content
     */
    private function generateExcerpt($content, $length = 200) {
        $text = strip_tags($content);
        return truncateText($text, $length);
    }
}
?>
