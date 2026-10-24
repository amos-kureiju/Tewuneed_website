<?php
/**
 * Header Component
 * TeWuNeed E-Commerce Website
 */

if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/functions.php';
}

// Safe loading with error handling
try {
    $currentUser = getCurrentUser();
    $cartCount = getCartCount();
    $categories = getCategories();
} catch (Exception $e) {
    error_log('Header error: ' . $e->getMessage());
    $currentUser = null;
    $cartCount = 0;
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= SITE_NAME ?> Marketplace</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Toastify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= SITE_URL ?>/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <script>
        window.SITE_URL = '<?= SITE_URL ?>';
        var SITE_URL = '<?= SITE_URL ?>';
    </script>
</head>
<body>
    <!-- Top Announcement Bar -->
    <div class="top-announcement-bar py-2 text-center">
        <div class="container d-flex flex-wrap align-items-center justify-content-center justify-content-md-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-shield-halved text-warning"></i>
                <span>Belanja kebutuhan harian lebih hemat! Gunakan kode kupon:</span>
                <span class="badge px-2 py-1">WELCOME15</span>
            </div>
            <div class="d-none d-md-flex align-items-center gap-3 small text-white-50">
                <a href="<?= SITE_URL ?>/about.php" class="text-white-50 text-decoration-none hover-white">Tentang Kami</a>
                <span>|</span>
                <a href="<?= SITE_URL ?>/contact.php" class="text-white-50 text-decoration-none hover-white">Bantuan & Kontak</a>
            </div>
        </div>
    </div>
    
    <!-- Main Header -->
    <header class="main-header">
        <div class="container py-3">
            <div class="row align-items-center g-3">
                <!-- Logo -->
                <div class="col-auto">
                    <a href="<?= SITE_URL ?>/index.php" class="navbar-brand text-decoration-none">
                        <span class="brand-tewu">TEWU</span><span class="brand-need">NEED</span>
                    </a>
                </div>
                
                <!-- Search Bar -->
                <div class="col d-none d-md-block">
                    <form action="<?= SITE_URL ?>/search.php" method="GET" class="header-search mx-auto">
                        <input type="text" name="q" class="form-control" placeholder="Cari beras, minyak, susu, sayur, elektronik..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : (isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '') ?>">
                        <button type="submit" class="search-btn" title="Cari">
                            <i class="fas fa-magnifying-glass"></i>
                        </button>
                    </form>
                </div>
                
                <!-- User Actions -->
                <div class="col-auto d-flex align-items-center gap-2 ms-auto">
                    <!-- Wishlist -->
                    <?php if ($currentUser): ?>
                    <a href="<?= SITE_URL ?>/wishlist.php" class="header-action-btn" title="Wishlist">
                        <i class="far fa-heart"></i>
                    </a>
                    <?php endif; ?>

                    <!-- Cart -->
                    <a href="<?= SITE_URL ?>/cart.php" class="header-action-btn" title="Keranjang Belanja">
                        <i class="fas fa-cart-shopping"></i>
                        <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- User Menu / Login -->
                    <?php if ($currentUser): ?>
                    <div class="dropdown">
                        <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill px-3 py-2 border shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 13px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="fw-semibold text-dark small d-none d-lg-inline">
                                <?= htmlspecialchars($currentUser['first_name']) ?>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2 p-2" style="min-width: 200px;">
                            <li class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark small"><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></div>
                                <div class="text-muted small text-truncate"><?= htmlspecialchars($currentUser['email']) ?></div>
                            </li>
                            <li><a class="dropdown-item rounded-2 py-2" href="<?= SITE_URL ?>/my_account.php"><i class="fas fa-user-circle me-2 text-muted"></i>Profil Saya</a></li>
                            <li><a class="dropdown-item rounded-2 py-2" href="<?= SITE_URL ?>/orders.php"><i class="fas fa-box-open me-2 text-muted"></i>Pesanan Saya</a></li>
                            <li><a class="dropdown-item rounded-2 py-2" href="<?= SITE_URL ?>/wishlist.php"><i class="far fa-heart me-2 text-muted"></i>Wishlist Favorit</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item rounded-2 py-2 text-danger" href="<?= SITE_URL ?>/logout.php"><i class="fas fa-arrow-right-from-bracket me-2"></i>Keluar Akun</a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-primary btn-sm px-3">Masuk</a>
                        <a href="<?= SITE_URL ?>/signup.php" class="btn btn-primary btn-sm px-3 d-none d-sm-inline-block">Daftar</a>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="header-action-btn d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Search Bar (Only on Mobile) -->
            <div class="d-md-none mt-3">
                <form action="<?= SITE_URL ?>/search.php" method="GET" class="header-search">
                    <input type="text" name="q" class="form-control" placeholder="Cari produk kebutuhan Anda..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                    <button type="submit" class="search-btn" title="Cari">
                        <i class="fas fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Navigation Categories -->
        <nav class="main-nav d-none d-md-block">
            <div class="container">
                <ul class="nav-category-list">
                    <li class="nav-category-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/index.php">
                            <i class="fas fa-house me-1 text-primary"></i> Home
                        </a>
                    </li>
                    <li class="nav-category-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'products.php' && !isset($_GET['category']) ? 'active' : '' ?>" href="<?= SITE_URL ?>/products.php">
                            Semua Produk
                        </a>
                    </li>
                    <?php 
                    $currentCat = $_GET['slug'] ?? $_GET['category'] ?? '';
                    foreach ($categories as $category): 
                    ?>
                    <li class="nav-category-item">
                        <a class="nav-link <?= $currentCat === $category['slug'] ? 'active' : '' ?>" href="<?= SITE_URL ?>/category.php?slug=<?= htmlspecialchars($category['slug']) ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <li class="nav-category-item ms-auto">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/orders.php">
                            <i class="fas fa-receipt me-1 text-muted"></i> Status Pesanan
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    
    <!-- Mobile Menu Offcanvas -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title navbar-brand mb-0">
                <span class="brand-tewu">TEWU</span><span class="brand-need">NEED</span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 text-dark fw-semibold" href="<?= SITE_URL ?>/index.php">
                        <i class="fas fa-house text-primary" style="width: 20px;"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 text-dark fw-semibold" href="<?= SITE_URL ?>/products.php">
                        <i class="fas fa-box text-primary" style="width: 20px;"></i> Semua Produk
                    </a>
                </li>
                
                <li class="nav-item mt-3 mb-1">
                    <span class="px-3 text-muted text-uppercase small fw-bold">Kategori Produk</span>
                </li>
                <?php foreach ($categories as $category): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 text-muted" href="<?= SITE_URL ?>/category.php?slug=<?= htmlspecialchars($category['slug']) ?>">
                        <i class="fas fa-tag text-muted" style="width: 20px;"></i> <?= htmlspecialchars($category['name']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <li class="nav-item mt-3 mb-1">
                    <span class="px-3 text-muted text-uppercase small fw-bold">Menu Akun</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 text-dark" href="<?= SITE_URL ?>/cart.php">
                        <i class="fas fa-cart-shopping text-primary" style="width: 20px;"></i> Keranjang (<?= $cartCount ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 text-dark" href="<?= SITE_URL ?>/orders.php">
                        <i class="fas fa-receipt text-primary" style="width: 20px;"></i> Pesanan Saya
                    </a>
                </li>
                <?php if ($currentUser): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 text-dark" href="<?= SITE_URL ?>/my_account.php">
                        <i class="fas fa-user-circle text-primary" style="width: 20px;"></i> Profil Saya
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 text-danger" href="<?= SITE_URL ?>/logout.php">
                        <i class="fas fa-arrow-right-from-bracket text-danger" style="width: 20px;"></i> Keluar Akun
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item mt-2 pt-2 border-top">
                    <div class="d-grid gap-2">
                        <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-primary">Masuk</a>
                        <a href="<?= SITE_URL ?>/signup.php" class="btn btn-primary">Daftar Akun Baru</a>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <!-- Main Content Container -->
    <main>
        <div class="container pt-3">
            <?php displayFlash(); ?>
        </div>
