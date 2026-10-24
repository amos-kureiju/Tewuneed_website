<?php
/**
 * Admin Login Page
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Silakan masukkan username dan password.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND status = 'active'");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();
        
        if ($admin && verifyPassword($password, $admin['password'])) {
            // Set session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = $admin['role'];
            
            // Update last login
            $stmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$admin['id']]);
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Roboto, sans-serif;
            padding: 1.5rem;
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            background: #ffffff;
        }
        .login-header {
            background: linear-gradient(135deg, #0F766E 0%, #115E59 100%);
            padding: 35px 25px;
            text-align: center;
            color: white;
        }
        .login-header i {
            font-size: 46px;
            margin-bottom: 12px;
            color: #F59E0B;
        }
        .login-body {
            padding: 35px 30px;
        }
        .form-floating input {
            border-radius: 10px;
            border: 1.5px solid #E2E8F0;
        }
        .form-floating input:focus {
            border-color: #0F766E;
            box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.15);
        }
        .btn-login {
            background: #0F766E;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            color: #ffffff;
            transition: all 0.2s ease;
        }
        .btn-login:hover {
            background: #0D9488;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="card login-card border-0">
        <div class="login-header">
            <i class="fas fa-user-shield"></i>
            <h3 class="mb-0">Admin Panel</h3>
            <small>TeWuNeed Marketplace</small>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?= csrfInput() ?>
                
                <div class="form-floating mb-3">
                    <input type="text" name="username" class="form-control" id="username" placeholder="Username" required>
                    <label for="username"><i class="fas fa-user me-2"></i>Username atau Email</label>
                </div>
                
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="<?= SITE_URL ?>/index.php" class="text-muted text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Toko
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
