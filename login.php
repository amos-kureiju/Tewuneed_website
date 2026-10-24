<?php
/**
 * Login Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Silakan masukkan email dan password Anda.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Merge guest cart with user cart
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $productId => $item) {
                    addToCart($productId, $item['quantity']);
                }
                unset($_SESSION['cart']);
            }
            
            setFlash('success', 'Selamat datang kembali, ' . $user['first_name'] . '!');
            
            // Redirect to intended page or home
            if (isset($_SESSION['redirect_path'])) {
                $redirectUrl = SITE_URL . $_SESSION['redirect_path'];
                unset($_SESSION['redirect_path']);
            } else {
                $redirectUrl = SITE_URL . '/index.php';
            }
            unset($_SESSION['redirect_url']);
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $error = 'Email atau password yang Anda masukkan salah.';
        }
    }
}

$pageTitle = 'Masuk ke Akun';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-4">
    <div class="container">
        <!-- Return to Home -->
        <div class="mb-4 text-center text-md-start" style="max-width: 900px; margin: 0 auto;">
            <a href="<?= SITE_URL ?>/index.php" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-2 hover-primary">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mx-auto" style="max-width: 900px;">
            <div class="row g-0">
                <!-- Left Banner (Brand & Value) -->
                <div class="col-lg-5 p-5 text-white d-none d-lg-flex flex-column justify-content-between" style="background: linear-gradient(135deg, #0F766E 0%, #115E59 100%);">
                    <div>
                        <a href="<?= SITE_URL ?>/index.php" class="navbar-brand text-decoration-none mb-4">
                            <span class="text-white">TEWU</span><span class="text-warning">NEED</span>
                        </a>
                        <h3 class="fw-bold text-white mt-4 mb-3">Selamat Datang Kembali!</h3>
                        <p class="text-white-50 small mb-0" style="line-height: 1.7;">
                            Akses riwayat pesanan Anda, pantau pengiriman belanja harian, dan nikmati beragam penawaran promo eksklusif untuk member.
                        </p>
                    </div>

                    <div class="pt-4 border-top border-white border-opacity-25">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-warning" style="width: 42px; height: 42px;">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                            <div>
                                <div class="fw-bold small text-white">Akun Aman & Terlindungi</div>
                                <div class="text-white-50" style="font-size: 0.75rem;">Enkripsi data standar industri</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Form Side -->
                <div class="col-lg-7 p-4 p-md-5 bg-white">
                    <div class="mb-4">
                        <div class="d-lg-none mb-3">
                            <a href="<?= SITE_URL ?>/index.php" class="navbar-brand text-decoration-none">
                                <span class="brand-tewu">TEWU</span><span class="brand-need">NEED</span>
                            </a>
                        </div>
                        <h2 class="h4 fw-bold mb-1">Masuk ke Akun Anda</h2>
                        <p class="text-muted small">Silakan masukkan email dan password yang terdaftar.</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 small rounded-3 mb-4" role="alert">
                        <i class="fas fa-circle-exclamation"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="nama@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-muted mb-0">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0 ps-0" placeholder="Masukkan password" required>
                                <button type="button" class="input-group-text bg-light border-start-0 text-muted" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3 fw-bold">
                            Masuk Sekarang <i class="fas fa-arrow-right ms-2"></i>
                        </button>

                        <div class="text-center pt-3 border-top">
                            <span class="text-muted small">Belum memiliki akun?</span>
                            <a href="<?= SITE_URL ?>/signup.php" class="fw-bold small text-primary ms-1">
                                Daftar Akun Baru
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePasswordVisibility() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>
