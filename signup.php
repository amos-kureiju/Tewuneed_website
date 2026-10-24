<?php
/**
 * Sign Up Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$error = '';
$success = '';

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Semua bidang wajib diisi kecuali nomor telepon.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal harus 6 karakter.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $db = getDB();
        
        // Check if email already registered
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Alamat email ini sudah terdaftar. Silakan masuk.';
        } else {
            // Hash password and insert
            $hashedPassword = hashPassword($password);
            
            $stmt = $db->prepare("
                INSERT INTO users (first_name, last_name, email, phone, password, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'active', NOW())
            ");
            
            if ($stmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword])) {
                $userId = $db->lastInsertId();
                
                // Automatically log user in
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                
                // Merge guest cart
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $productId => $item) {
                        addToCart($productId, $item['quantity']);
                    }
                    unset($_SESSION['cart']);
                }
                
                setFlash('success', 'Selamat datang di ' . SITE_NAME . '! Pendaftaran Anda berhasil.');
                header('Location: ' . SITE_URL . '/index.php');
                exit;
            } else {
                $error = 'Gagal mendaftar akun. Silakan coba kembali beberapa saat lagi.';
            }
        }
    }
}

$pageTitle = 'Daftar Akun Baru';
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
        <div class="mb-4 text-center text-md-start" style="max-width: 960px; margin: 0 auto;">
            <a href="<?= SITE_URL ?>/index.php" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-2 hover-primary">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mx-auto" style="max-width: 960px;">
            <div class="row g-0">
                <!-- Left Banner (Brand & Perks) -->
                <div class="col-lg-5 p-5 text-white d-none d-lg-flex flex-column justify-content-between" style="background: linear-gradient(135deg, #0F766E 0%, #115E59 100%);">
                    <div>
                        <a href="<?= SITE_URL ?>/index.php" class="navbar-brand text-decoration-none mb-4">
                            <span class="text-white">TEWU</span><span class="text-warning">NEED</span>
                        </a>
                        <h3 class="fw-bold text-white mt-4 mb-3">Gabung Bersama Ribuan Pelanggan Puas</h3>
                        <p class="text-white-50 small mb-4" style="line-height: 1.7;">
                            Dapatkan kemudahan belanja harian lebih hemat, garansi produk segar, dan promo diskon 15% untuk pembelian pertama Anda.
                        </p>

                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="fas fa-check-circle text-warning"></i>
                                <span>Kupon Diskon 15% (Kode: WELCOME15)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="fas fa-check-circle text-warning"></i>
                                <span>Gratis Ongkos Kirim Se-Jabodetabek</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="fas fa-check-circle text-warning"></i>
                                <span>Poin Reward & Promo Member Rutin</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-top border-white border-opacity-25 small text-white-50">
                        Keamanan data pribadi Anda terlindungi 100%.
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
                        <h2 class="h4 fw-bold mb-1">Daftar Akun Baru</h2>
                        <p class="text-muted small">Lengkapi formulir di bawah ini untuk memulai berbelanja.</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 small rounded-3 mb-4" role="alert">
                        <i class="fas fa-circle-exclamation"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold text-muted">Nama Depan <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" placeholder="Nama Depan" value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold text-muted">Nama Belakang <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" placeholder="Nama Belakang" value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Nomor WhatsApp / Telepon</label>
                            <input type="tel" name="phone" class="form-control" placeholder="Contoh: 081234567890" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold text-muted">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold text-muted">Ulangi Password <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3 fw-bold">
                            Daftar Sekarang <i class="fas fa-user-plus ms-2"></i>
                        </button>

                        <div class="text-center pt-3 border-top">
                            <span class="text-muted small">Sudah memiliki akun?</span>
                            <a href="<?= SITE_URL ?>/login.php" class="fw-bold small text-primary ms-1">
                                Masuk di sini
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
