<?php
/**
 * Role-Based Access Control (RBAC) System
 * Task 4: Security-Enhanced PHP Blog Application
 * Aerospace Internship Project
 */

// Security check
if (!defined('SECURITY_INIT')) {
    require_once __DIR__ . '/../config/security.php';
}

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Role definitions and permissions
 */
class Role {
    const ADMIN = 'admin';
    const EDITOR = 'editor';
    const USER = 'user';
    
    // Permission constants
    const PERM_CREATE = 'create';
    const PERM_READ = 'read';
    const PERM_UPDATE = 'update';
    const PERM_DELETE = 'delete';
    const PERM_PUBLISH = 'publish';
    const PERM_MANAGE = 'manage';
    const PERM_AUDIT = 'audit';
}

/**
 * Get user's role and permissions
 * @param int $userId User ID
 * @return array|null Role data with permissions
 */
function getUserRole($userId) {
    try {
        $result = selectSingle("
            SELECT u.role_id, r.name as role_name, r.permissions 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ? AND u.is_active = 1
        ", [$userId]);
        
        if ($result) {
            $result['permissions'] = json_decode($result['permissions'], true) ?? [];
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Get user role error: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if user has permission for a specific action on a resource
 * @param int $userId User ID
 * @param string $resource Resource name (e.g., 'posts', 'users')
 * @param string $action Action name (e.g., 'create', 'read', 'update', 'delete')
 * @param array $context Additional context (e.g., resource owner)
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($userId, $resource, $action, $context = []) {
    // Get user role
    $roleData = getUserRole($userId);
    if (!$roleData) {
        return false;
    }
    
    $permissions = $roleData['permissions'];
    $roleName = $roleData['role_name'];
    
    // Admin has all permissions
    if ($roleName === Role::ADMIN) {
        return true;
    }
    
    // Check if resource exists in permissions
    if (!isset($permissions[$resource])) {
        return false;
    }
    
    $resourcePermissions = $permissions[$resource];
    
    // Check for exact permission
    if (in_array($action, $resourcePermissions)) {
        return true;
    }
    
    // Check for ownership-based permissions
    $ownAction = $action . '_own';
    if (in_array($ownAction, $resourcePermissions)) {
        // Check if user owns the resource
        if (isset($context['owner_id']) && $context['owner_id'] == $userId) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if current logged-in user has permission
 * @param string $resource Resource name
 * @param string $action Action name
 * @param array $context Additional context
 * @return bool True if user has permission, false otherwise
 */
function currentUserHasPermission($resource, $action, $context = []) {
    $userId = getCurrentUserId();
    if (!$userId) {
        return false;
    }
    
    return hasPermission($userId, $resource, $action, $context);
}

/**
 * Require permission for current user
 * @param string $resource Resource name
 * @param string $action Action name
 * @param array $context Additional context
 * @param string $redirectUrl URL to redirect to if permission denied
 */
function requirePermission($resource, $action, $context = [], $redirectUrl = 'index.php') {
    if (!currentUserHasPermission($resource, $action, $context)) {
        logSecurityEvent('access_denied', [
            'resource' => $resource,
            'action' => $action,
            'context' => $context
        ], getCurrentUserId());
        
        setFlashMessage('Access denied. You do not have permission to perform this action.', 'error');
        header('Location: ' . $redirectUrl);
        exit();
    }
}

/**
 * Require specific role
 * @param string|array $requiredRoles Required role(s)
 * @param string $redirectUrl URL to redirect to if role not matched
 */
function requireRole($requiredRoles, $redirectUrl = 'index.php') {
    $currentRole = getCurrentUserRole();
    
    if (!$currentRole) {
        setFlashMessage('Please log in to access this page.', 'warning');
        header('Location: auth/login.php');
        exit();
    }
    
    $requiredRoles = is_array($requiredRoles) ? $requiredRoles : [$requiredRoles];
    
    if (!in_array($currentRole, $requiredRoles)) {
        logSecurityEvent('role_access_denied', [
            'required_roles' => $requiredRoles,
            'user_role' => $currentRole
        ], getCurrentUserId());
        
        setFlashMessage('Access denied. Insufficient privileges.', 'error');
        header('Location: ' . $redirectUrl);
        exit();
    }
}

/**
 * Check if user is admin
 * @param int|null $userId User ID (null for current user)
 * @return bool True if admin, false otherwise
 */
function isAdmin($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    if (!$userId) {
        return false;
    }
    
    $roleData = getUserRole($userId);
    return $roleData && $roleData['role_name'] === Role::ADMIN;
}

/**
 * Check if user is editor or higher
 * @param int|null $userId User ID (null for current user)
 * @return bool True if editor or admin, false otherwise
 */
function isEditor($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    if (!$userId) {
        return false;
    }
    
    $roleData = getUserRole($userId);
    return $roleData && in_array($roleData['role_name'], [Role::ADMIN, Role::EDITOR]);
}

/**
 * Get all available roles
 * @return array Array of roles
 */
function getAllRoles() {
    try {
        return selectQuery("SELECT * FROM roles ORDER BY id");
    } catch (Exception $e) {
        error_log("Get all roles error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get role by ID
 * @param int $roleId Role ID
 * @return array|null Role data
 */
function getRoleById($roleId) {
    try {
        $result = selectSingle("SELECT * FROM roles WHERE id = ?", [$roleId]);
        if ($result) {
            $result['permissions'] = json_decode($result['permissions'], true) ?? [];
        }
        return $result;
    } catch (Exception $e) {
        error_log("Get role by ID error: " . $e->getMessage());
        return null;
    }
}

/**
 * Update user role
 * @param int $userId User ID
 * @param int $newRoleId New role ID
 * @param int $adminUserId Admin user ID performing the action
 * @return bool Success status
 */
function updateUserRole($userId, $newRoleId, $adminUserId) {
    try {
        // Check if admin has permission
        if (!hasPermission($adminUserId, 'users', 'update')) {
            return false;
        }
        
        // Get old role for logging
        $oldRole = getUserRole($userId);
        $newRole = getRoleById($newRoleId);
        
        if (!$newRole) {
            return false;
        }
        
        // Update role
        $affected = modifyQuery("UPDATE users SET role_id = ? WHERE id = ?", [$newRoleId, $userId]);
        
        if ($affected > 0) {
            // Log role change
            logSecurityEvent('user_role_changed', [
                'target_user_id' => $userId,
                'old_role' => $oldRole['role_name'] ?? 'unknown',
                'new_role' => $newRole['name'],
                'changed_by' => $adminUserId
            ], $adminUserId);
            
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Update user role error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user can access post
 * @param int $userId User ID
 * @param array $post Post data
 * @param string $action Action to perform
 * @return bool True if can access, false otherwise
 */
function canAccessPost($userId, $post, $action = 'read') {
    // Public read access for published posts
    if ($action === 'read' && $post['status'] === 'published') {
        return true;
    }
    
    // Check general permission
    if (hasPermission($userId, 'posts', $action)) {
        return true;
    }
    
    // Check ownership-based permission
    if (hasPermission($userId, 'posts', $action, ['owner_id' => $post['author_id']])) {
        return true;
    }
    
    return false;
}

/**
 * Filter posts based on user permissions
 * @param array $posts Array of posts
 * @param int $userId User ID
 * @return array Filtered posts
 */
function filterPostsByPermission($posts, $userId) {
    return array_filter($posts, function($post) use ($userId) {
        return canAccessPost($userId, $post, 'read');
    });
}

/**
 * Get user management permissions for current user
 * @return array Permissions array
 */
function getUserManagementPermissions() {
    $userId = getCurrentUserId();
    if (!$userId) {
        return [];
    }
    
    return [
        'can_view_users' => hasPermission($userId, 'users', 'read'),
        'can_create_users' => hasPermission($userId, 'users', 'create'),
        'can_edit_users' => hasPermission($userId, 'users', 'update'),
        'can_delete_users' => hasPermission($userId, 'users', 'delete'),
        'can_manage_roles' => hasPermission($userId, 'roles', 'manage'),
        'can_view_audit_log' => hasPermission($userId, 'system', 'audit')
    ];
}

/**
 * Get post management permissions for current user
 * @param array|null $post Post data (for ownership checks)
 * @return array Permissions array
 */
function getPostManagementPermissions($post = null) {
    $userId = getCurrentUserId();
    if (!$userId) {
        return [];
    }
    
    $context = $post ? ['owner_id' => $post['author_id']] : [];
    
    return [
        'can_create_posts' => hasPermission($userId, 'posts', 'create'),
        'can_edit_posts' => hasPermission($userId, 'posts', 'update', $context),
        'can_delete_posts' => hasPermission($userId, 'posts', 'delete', $context),
        'can_publish_posts' => hasPermission($userId, 'posts', 'publish'),
        'can_view_all_posts' => hasPermission($userId, 'posts', 'read')
    ];
}

/**
 * Log permission check for audit
 * @param string $resource Resource name
 * @param string $action Action name
 * @param bool $granted Whether permission was granted
 * @param array $context Additional context
 */
function logPermissionCheck($resource, $action, $granted, $context = []) {
    logSecurityEvent('permission_check', [
        'resource' => $resource,
        'action' => $action,
        'granted' => $granted,
        'context' => $context
    ], getCurrentUserId());
}

/**
 * Initialize RBAC system
 */
function initializeRBAC() {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Update session with current role if user is logged in
    $userId = getCurrentUserId();
    if ($userId) {
        $roleData = getUserRole($userId);
        if ($roleData) {
            $_SESSION['role_name'] = $roleData['role_name'];
            $_SESSION['role_id'] = $roleData['role_id'];
        }
    }
}

// Initialize RBAC on include
initializeRBAC();
?>
