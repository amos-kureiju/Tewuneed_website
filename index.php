<?php
/**
 * Home Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

$pageTitle = 'Belanja Kebutuhan Harian Mudah & Terpercaya';

// Safe loading with error handling
try {
    $featuredProducts = getFeaturedProducts(12);
    $categories = getCategories();
} catch (Exception $e) {
    error_log('Index page error: ' . $e->getMessage());
    $featuredProducts = [];
    $categories = [];
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="container mt-4 mb-5">
    <div class="hero-banner p-4 p-md-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7 text-white position-relative" style="z-index: 2;">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold mb-3">
                    <i class="fas fa-sparkles me-1"></i> Promo Spesial Bulan Ini
                </span>
                <h1 class="display-5 fw-bold mb-3 text-white">
                    Kebutuhan Harian Segar & Berkualitas, Langsung ke Rumah Anda
                </h1>
                <p class="lead mb-4 text-white-50" style="font-size: 1.1rem; line-height: 1.7;">
                    Belanja sayur segar dari petani, sembako pilihan, kosmetik, hingga produk elektronik dengan jaminan harga terbaik dan pengiriman cepat.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= SITE_URL ?>/products.php" class="btn btn-warning btn-lg px-4 fw-bold shadow-sm">
                        <i class="fas fa-bag-shopping me-2"></i>Belanja Sekarang
                    </a>
                    <a href="<?= SITE_URL ?>/about.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                        Pelajari Layanan
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block position-relative" style="z-index: 2;">
                <img src="<?= SITE_URL ?>/Images/fres groceries.jpg" alt="Fresh Groceries TEWUNEED" class="img-fluid rounded-4 shadow-lg" style="max-height: 340px; width: 100%; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<!-- Value Proposition Features -->
<section class="container mb-5">
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="feature-box">
                <div class="feature-icon-circle bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <h6 class="fw-bold mb-1">Pengiriman Kilat</h6>
                <p class="text-muted small mb-0">Bebas ongkir min. transaksi Rp100.000</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="feature-box">
                <div class="feature-icon-circle bg-success bg-opacity-10 text-success">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h6 class="fw-bold mb-1">100% Produk Asli</h6>
                <p class="text-muted small mb-0">Terverifikasi langsung dari distributor</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="feature-box">
                <div class="feature-icon-circle bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-wallet"></i>
                </div>
                <h6 class="fw-bold mb-1">Pembayaran Aman</h6>
                <p class="text-muted small mb-0">Transfer bank, E-Wallet, dan COD</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="feature-box">
                <div class="feature-icon-circle bg-info bg-opacity-10 text-info">
                    <i class="fas fa-headset"></i>
                </div>
                <h6 class="fw-bold mb-1">Layanan 24/7</h6>
                <p class="text-muted small mb-0">Customer support siap melayani Anda</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="container mb-5">
    <div class="d-flex align-items-end justify-content-between mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">Kategori Pilihan</h2>
            <p class="text-muted small mb-0">Jelajahi beragam kategori produk kebutuhan Anda</p>
        </div>
        <a href="<?= SITE_URL ?>/products.php" class="btn btn-outline-primary btn-sm px-3 d-none d-sm-inline-flex align-items-center gap-1">
            Lihat Semua <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
    <div class="row g-4">
        <?php
        $categoryImages = [
            'electronics' => ['Elektronik.jpg', 'Elektronik & Gadget', 'Peralatan rumah dan gadget modern.'],
            'cosmetics' => ['kosmetik.jpg', 'Kosmetik & Skincare', 'Perawatan wajah dan kecantikan alami.'],
            'sports' => ['olahraga.jpg', 'Perlengkapan Olahraga', 'Peralatan fitnes dan gaya hidup aktif.'],
            'food-snacks' => ['sembako.jpg', 'Sembako & Makanan', 'Susu murni, kopi, mie instan, & camilan.'],
            'health-medicine' => ['Obat.jpg', 'Kesehatan & Obat', 'Vitamin dan suplemen harian terpercaya.'],
            'vegetables' => ['sayuran.jpg', 'Sayuran Segar', 'Sayuran segar langsung dari petani lokal.']
        ];
        
        foreach ($categories as $category):
            $slug = $category['slug'];
            $imageData = $categoryImages[$slug] ?? ['placeholder.jpg', $category['name'], $category['description']];
        ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="<?= SITE_URL ?>/category.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm category-card text-center p-3">
                    <div class="rounded-circle overflow-hidden mx-auto mb-3" style="width: 80px; height: 80px; background: #f1f5f9;">
                        <img src="<?= SITE_URL ?>/Images/<?= $imageData[0] ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="w-100 h-100" style="object-fit: cover;">
                    </div>
                    <h6 class="fw-bold text-dark mb-1 small"><?= htmlspecialchars($category['name']) ?></h6>
                    <small class="text-muted d-none d-lg-block" style="font-size: 0.75rem;">
                        <?= htmlspecialchars(mb_strimwidth($category['description'] ?? $imageData[2], 0, 35, '...')) ?>
                    </small>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Products Section -->
<section class="container mb-5">
    <div class="d-flex align-items-end justify-content-between mb-4">
        <div>
            <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold px-2 py-1 mb-2 small">Produk Pilihan</span>
            <h2 class="h3 fw-bold mb-1">Produk Unggulan Hari Ini</h2>
            <p class="text-muted small mb-0">Paling banyak dibeli dengan rating tertinggi dari pembeli</p>
        </div>
        <a href="<?= SITE_URL ?>/products.php" class="btn btn-outline-primary btn-sm px-3 d-flex align-items-center gap-1">
            Lihat Katalog <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
    <div class="row g-3">
        <?php foreach ($featuredProducts as $product): ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card h-100 border-0 shadow-sm product-card">
                <?php if ($product['sale_price']): ?>
                <span class="badge bg-danger position-absolute top-0 start-0 m-2 z-1">
                    Diskon
                </span>
                <?php endif; ?>

                <div class="position-relative overflow-hidden product-image-wrapper">
                    <a href="<?= SITE_URL ?>/product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
                        <img src="<?= getProductImage($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img" loading="lazy">
                    </a>
                    <button class="btn btn-light btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow-sm wishlist-btn" 
                            data-id="<?= $product['id'] ?>" 
                            onclick="toggleWishlist(<?= $product['id'] ?>)"
                            title="Tambah ke Wishlist">
                        <i class="<?= isInWishlist($product['id']) ? 'fas text-danger' : 'far' ?> fa-heart"></i>
                    </button>
                </div>

                <div class="card-body p-2 p-md-3 d-flex flex-column">
                    <span class="badge bg-light text-muted fw-normal align-self-start mb-2 px-2 py-1" style="font-size: 0.7rem;">
                        <?= htmlspecialchars($product['category_name'] ?? 'Kategori') ?>
                    </span>

                    <h6 class="card-title mb-2 text-truncate-2" style="font-size: 0.875rem;">
                        <a href="<?= SITE_URL ?>/product.php?slug=<?= htmlspecialchars($product['slug']) ?>" class="text-decoration-none text-dark fw-semibold">
                            <?= htmlspecialchars($product['name']) ?>
                        </a>
                    </h6>

                    <div class="mt-auto pt-2">
                        <div class="mb-2">
                            <?php if ($product['sale_price']): ?>
                            <div class="fw-bold text-danger small"><?= formatRupiah($product['sale_price']) ?></div>
                            <small class="text-muted text-decoration-line-through" style="font-size: 0.75rem;"><?= formatRupiah($product['price']) ?></small>
                            <?php else: ?>
                            <div class="fw-bold text-primary small"><?= formatRupiah($product['price']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex align-items-center justify-content-between small text-muted mb-2" style="font-size: 0.75rem;">
                            <span><?= renderStars(getProductRating($product['id']) ?: 4.5) ?></span>
                            <span>Stok: <?= $product['stock_quantity'] ?></span>
                        </div>

                        <button onclick="addToCart(<?= $product['id'] ?>)" 
                                class="btn btn-primary btn-sm w-100 rounded-3 d-flex align-items-center justify-content-center gap-1"
                                <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-cart-plus"></i>
                            <span><?= $product['stock_quantity'] <= 0 ? 'Habis' : 'Tambah' ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Banner Callout Promo -->
<section class="container mb-5">
    <div class="card border-0 rounded-4 shadow-sm p-4 text-white" style="background: linear-gradient(135deg, #0F766E 0%, #064E3B 100%);">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <h4 class="fw-bold mb-1">Ingin Belanja Lebih Banyak dengan Harga Grosir?</h4>
                <p class="text-white-50 mb-0 small">Dapatkan potongan harga khusus untuk pembelian sembako dan kebutuhan kantor dalam jumlah besar.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= SITE_URL ?>/contact.php" class="btn btn-warning fw-bold px-4">
                    Hubungi Penjualan
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
