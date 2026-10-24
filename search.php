<?php
/**
 * Search Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

$q = sanitize($_GET['q'] ?? $_GET['search'] ?? '');

$db = getDB();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Filters
$categorySlug = sanitize($_GET['category'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');
$minPrice = intval($_GET['min_price'] ?? 0);
$maxPrice = intval($_GET['max_price'] ?? 0);

$where = ["p.status = 'active'"];
$params = [];

if (!empty($q)) {
    $where[] = "(p.name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
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
if ($sort === 'price_low') {
    $orderBy = "COALESCE(p.sale_price, p.price) ASC";
} elseif ($sort === 'price_high') {
    $orderBy = "COALESCE(p.sale_price, p.price) DESC";
} elseif ($sort === 'popular') {
    $orderBy = "p.sold_count DESC, p.views DESC";
} elseif ($sort === 'name') {
    $orderBy = "p.name ASC";
}

// Get total count
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Get products
$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE $whereClause 
    ORDER BY $orderBy 
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = getCategories();

$pageTitle = !empty($q) ? 'Pencarian: "' . htmlspecialchars($q) . '"' : 'Semua Produk';

include 'includes/header.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active"><?= !empty($q) ? 'Pencarian: ' . htmlspecialchars($q) : 'Katalog Produk' ?></li>
        </ol>
    </nav>

    <!-- Header bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h2 class="h3 fw-bold mb-1">
                <?= !empty($q) ? 'Hasil Pencarian untuk "' . htmlspecialchars($q) . '"' : 'Katalog Produk' ?>
            </h2>
            <p class="text-muted mb-0 small">Menemukan <?= $totalProducts ?> produk</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="text-muted small text-nowrap">Urutkan:</label>
            <select class="form-select form-select-sm" style="width: auto;" onchange="updateSort(this.value)">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Harga: Rendah ke Tinggi</option>
                <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Harga: Tinggi ke Rendah</option>
                <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Terpopuler</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nama A-Z</option>
            </select>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 sticky-top" style="top: 100px;">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fas fa-filter text-primary"></i> Filter Produk
                </h5>
                <form method="GET" action="<?= SITE_URL ?>/search.php" id="filterForm">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

                    <!-- Kategori -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-uppercase text-muted">Kategori</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="category" value="" id="cat_all" <?= empty($categorySlug) ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label class="form-check-label small" for="cat_all">Semua Kategori</label>
                        </div>
                        <?php foreach ($categories as $cat): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="category" value="<?= htmlspecialchars($cat['slug']) ?>" id="cat_<?= $cat['id'] ?>" <?= $categorySlug === $cat['slug'] ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label class="form-check-label small" for="cat_<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Rentang Harga -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-uppercase text-muted">Rentang Harga (Rp)</label>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?= $minPrice > 0 ? $minPrice : '' ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?= $maxPrice > 0 ? $maxPrice : '' ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                            Terapkan Harga
                        </button>
                    </div>

                    <?php if (!empty($q) || !empty($categorySlug) || $minPrice > 0 || $maxPrice > 0): ?>
                    <a href="<?= SITE_URL ?>/search.php" class="btn btn-light btn-sm w-100 text-muted">
                        <i class="fas fa-rotate-left me-1"></i> Reset Filter
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Product List -->
        <div class="col-lg-9">
            <?php if (empty($products)): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
                <div class="mb-3 text-muted">
                    <i class="fas fa-box-open fa-3x"></i>
                </div>
                <h4 class="fw-bold mb-2">Produk Tidak Ditemukan</h4>
                <p class="text-muted mb-4">Coba cari dengan kata kunci lain atau kurangi filter pencarian Anda.</p>
                <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary px-4">
                    Lihat Semua Produk
                </a>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($products as $product): ?>
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <?php if ($product['sale_price']): ?>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 z-1">
                            Diskon
                        </span>
                        <?php endif; ?>

                        <div class="position-relative overflow-hidden product-image-wrapper">
                            <a href="<?= SITE_URL ?>/product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
                                <img src="<?= getProductImage($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card-img-top product-img" loading="lazy">
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

                            <h6 class="card-title mb-2 text-truncate-2">
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

                                <div class="d-flex align-items-center justify-content-between small text-muted mb-3">
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
            <nav class="mt-5" aria-label="Halaman navigasi">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateSort(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}
</script>

<?php include 'includes/footer.php'; ?>
