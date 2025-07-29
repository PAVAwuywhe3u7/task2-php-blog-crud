<?php
/**
 * Fast Login - Optimized for Speed
 */

session_start();
$errors = [];
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Quick validation
    if (empty($username) || empty($password)) {
        $errors[] = 'Please enter both username and password.';
    } else {
        // Quick demo login (no database needed for demo)
        $demo_users = [
            'admin' => ['password' => 'AdminPass123!', 'role' => 'admin', 'name' => 'Administrator'],
            'editor' => ['password' => 'EditorPass123!', 'role' => 'editor', 'name' => 'Editor User'],
            'user' => ['password' => 'UserPass123!', 'role' => 'user', 'name' => 'Regular User']
        ];
        
        if (isset($demo_users[$username]) && $demo_users[$username]['password'] === $password) {
            // Set session
            $_SESSION['user'] = [
                'username' => $username,
                'role' => $demo_users[$username]['role'],
                'name' => $demo_users[$username]['name'],
                'logged_in' => true
            ];
            
            // Redirect to dashboard
            header('Location: fast-dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast Login - Task 5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .login-card { max-width: 400px; margin: 0 auto; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark hero">
        <div class="container">
            <a class="navbar-brand" href="ultra-fast.php">🚀 Task 5: Fast Login</a>
            <a href="ultra-fast.php" class="btn btn-light btn-sm">← Back</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="login-card">
            <div class="card shadow">
                <div class="card-header hero text-white text-center">
                    <h4><i class="fas fa-sign-in-alt me-2"></i>Demo Login</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer">
                    <h6 class="mb-2">Demo Credentials:</h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <small>
                                <strong>Admin</strong><br>
                                <code>admin</code><br>
                                <code>AdminPass123!</code>
                            </small>
                        </div>
                        <div class="col-4">
                            <small>
                                <strong>Editor</strong><br>
                                <code>editor</code><br>
                                <code>EditorPass123!</code>
                            </small>
                        </div>
                        <div class="col-4">
                            <small>
                                <strong>User</strong><br>
                                <code>user</code><br>
                                <code>UserPass123!</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Login Buttons -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="text-center mb-3">Quick Demo Login:</h6>
                    <div class="d-grid gap-2">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="username" value="admin">
                            <input type="hidden" name="password" value="AdminPass123!">
                            <button type="submit" class="btn btn-danger w-100">
                                👑 Login as Admin
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="username" value="editor">
                            <input type="hidden" name="password" value="EditorPass123!">
                            <button type="submit" class="btn btn-warning w-100">
                                ✏️ Login as Editor
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="username" value="user">
                            <input type="hidden" name="password" value="UserPass123!">
                            <button type="submit" class="btn btn-info w-100">
                                👤 Login as User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-3">
        <div class="container text-center">
            <p class="mb-0">🚀 Task 5: Fast Login Demo - No Database Lag</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
