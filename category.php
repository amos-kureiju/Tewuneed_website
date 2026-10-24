<?php
/**
 * Category Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Get category with error handling
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND status = 'active'");
    $stmt->execute([$slug]);
    $category = $stmt->fetch();
    
    if (!$category) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
    
    // Pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    // Get products count
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
    $stmt->execute([$category['id']]);
    $totalProducts = $stmt->fetchColumn();
    $totalPages = ceil($totalProducts / $limit);
    
    // Sorting
    $sort = sanitize($_GET['sort'] ?? 'newest');
    
    // Get products
    $products = getProducts($category['id'], $limit, $offset, $sort);
    
} catch (Exception $e) {
    error_log('Category page error: ' . $e->getMessage());
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$pageTitle = $category['name'];

include 'includes/header.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($category['name']) ?></li>
        </ol>
    </nav>
    
    <!-- Category Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1"><?= htmlspecialchars($category['name']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($category['description'] ?? 'Temukan produk ' . $category['name'] . ' terbaik hanya di ' . SITE_NAME) ?></p>
        </div>
        <div class="col-md-4">
            <div class="d-flex align-items-center justify-content-md-end gap-2">
                <span class="text-muted">Urutkan:</span>
                <select class="form-select form-select-sm" style="width: auto;" onchange="location.href='?slug=<?= $slug ?>&sort=' + this.value">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Terpopuler</option>
                </select>
            </div>
        </div>
    </div>
    
    <?php if (empty($products)): ?>
    <div class="text-center py-5">
        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
        <h4>Belum Ada Produk</h4>
        <p class="text-muted">Produk kategori ini belum tersedia saat ini.</p>
        <a href="<?= SITE_URL ?>/index.php" class="btn btn-primary mt-2">Kembali ke Home</a>
    </div>
    <?php else: ?>
    
    <!-- Products Grid -->
    <div class="row g-4">
        <?php foreach ($products as $product): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm product-card">
                <?php if ($product['sale_price']): ?>
                <span class="badge bg-danger position-absolute top-0 start-0 m-2 z-1">Diskon</span>
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
                
                <div class="card-body p-3 d-flex flex-column">
                    <h6 class="card-title mb-2 text-truncate-2" style="font-size: 0.9rem;">
                        <a href="<?= SITE_URL ?>/product.php?slug=<?= htmlspecialchars($product['slug']) ?>" class="text-decoration-none text-dark fw-semibold">
                            <?= htmlspecialchars($product['name']) ?>
                        </a>
                    </h6>
                    
                    <div class="mt-auto pt-2">
                        <div class="mb-2">
                            <?php if ($product['sale_price']): ?>
                            <div class="fw-bold text-danger fs-6"><?= formatRupiah($product['sale_price']) ?></div>
                            <small class="text-muted text-decoration-line-through"><?= formatRupiah($product['price']) ?></small>
                            <?php else: ?>
                            <div class="fw-bold text-primary fs-6"><?= formatRupiah($product['price']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-between small text-muted mb-3" style="font-size: 0.75rem;">
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
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?slug=<?= $slug ?>&page=<?= $page - 1 ?>&sort=<?= $sort ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?slug=<?= $slug ?>&page=<?= $i ?>&sort=<?= $sort ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="?slug=<?= $slug ?>&page=<?= $page + 1 ?>&sort=<?= $sort ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    
    <?php endif; ?>
</div>

<style>
.product-card {
    transition: transform 0.3s, box-shadow 0.3s;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
</style>

<?php include 'includes/footer.php'; ?>
