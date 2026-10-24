<?php
/**
 * Products Listing Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

$db = getDB();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Filters
$search = sanitize($_GET['search'] ?? $_GET['q'] ?? '');
$categorySlug = sanitize($_GET['category'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');
$minPrice = intval($_GET['min_price'] ?? 0);
$maxPrice = intval($_GET['max_price'] ?? 0);

$where = ["p.status = 'active'"];
$params = [];

if ($search) {
    $where[] = "(p.name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categorySlug) {
    $where[] = "c.slug = ?";
    $params[] = $categorySlug;
}

if ($minPrice > 0) {
    $where[] = "COALESCE(p.sale_price, p.price) >= ?";
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $where[] = "COALESCE(p.sale_price, p.price) <= ?";
    $params[] = $maxPrice;
}

$whereClause = implode(' AND ', $where);

// Sorting
$orderBy = "p.created_at DESC";
if ($sort === 'price_low') $orderBy = "COALESCE(p.sale_price, p.price) ASC";
elseif ($sort === 'price_high') $orderBy = "COALESCE(p.sale_price, p.price) DESC";
elseif ($sort === 'popular') $orderBy = "p.sold_count DESC";
elseif ($sort === 'name') $orderBy = "p.name ASC";

// Get total count
$stmt = $db->prepare("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereClause");
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Get products
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE $whereClause 
    ORDER BY $orderBy 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = getCategories();

$pageTitle = $search ? "Hasil Pencarian: $search" : 'Semua Produk';

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <?php endif; ?>
                        
                        <!-- Categories -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Kategori</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="category" value="" id="catAll" <?= !$categorySlug ? 'checked' : '' ?>>
                                <label class="form-check-label" for="catAll">Semua Kategori</label>
                            </div>
                            <?php foreach ($categories as $cat): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="category" value="<?= htmlspecialchars($cat['slug']) ?>" id="cat<?= $cat['id'] ?>" <?= $categorySlug === $cat['slug'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cat<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Harga</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?= $minPrice ?: '' ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?= $maxPrice ?: '' ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Terapkan Filter
                        </button>
                        
                        <?php if ($categorySlug || $minPrice || $maxPrice): ?>
                        <a href="<?= SITE_URL ?>/products.php<?= $search ? '?search=' . urlencode($search) : '' ?>" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-times me-2"></i>Reset Filter
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Products -->
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><?= htmlspecialchars($pageTitle) ?></h4>
                    <small class="text-muted"><?= number_format($totalProducts) ?> produk ditemukan</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted d-none d-md-inline">Urutkan:</span>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="updateSort(this.value)">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Terpopuler</option>
                        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nama A-Z</option>
                    </select>
                </div>
            </div>
            
            <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4>Produk Tidak Ditemukan</h4>
                <p class="text-muted">Coba ubah filter atau kata kunci pencarian Anda</p>
            </div>
            <?php else: ?>
            
            <!-- Products Grid -->
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                <div class="col-6 col-md-4">
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
                            <span class="badge bg-light text-muted fw-normal align-self-start mb-2 px-2 py-1 small">
                                <?= htmlspecialchars($product['category_name'] ?? 'Umum') ?>
                            </span>

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
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateSort(value) {
    const url = new URL(window.location);
    url.searchParams.set('sort', value);
    window.location = url;
}
</script>

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
