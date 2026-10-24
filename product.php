<?php
/**
 * Product Detail Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$product = getProductBySlug($slug);

if (!$product) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Get product reviews
$reviews = getProductReviews($product['id']);
$rating = getProductRating($product['id']);

// Get related products
$db = getDB();
$stmt = $db->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? AND status = 'active' 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $stmt->fetchAll();

$pageTitle = $product['name'];

include 'includes/header.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/category.php?slug=<?= htmlspecialchars($product['category_slug'] ?? 'products') ?>"><?= htmlspecialchars($product['category_name'] ?? 'Products') ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>
    
    <div class="row g-5">
        <!-- Product Images -->
        <div class="col-lg-5">
            <div class="position-sticky" style="top: 100px;">
                <?php if ($product['sale_price']): ?>
                <span class="badge bg-danger position-absolute top-0 start-0 m-3 fs-6 z-1">
                    Diskon -<?= round((1 - $product['sale_price'] / $product['price']) * 100) ?>%
                </span>
                <?php endif; ?>
                
                <div class="bg-white rounded-4 shadow-sm p-4 border text-center" style="min-height: 380px; display: flex; align-items: center; justify-content: center;">
                    <img src="<?= getProductImage($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid" style="max-height: 400px; max-width: 100%; object-fit: contain;">
                </div>
                
                <!-- Wishlist Button -->
                <button class="btn btn-light position-absolute top-0 end-0 m-3 rounded-circle shadow-sm wishlist-btn" 
                        data-id="<?= $product['id'] ?>" onclick="toggleWishlist(<?= $product['id'] ?>)">
                    <i class="<?= isInWishlist($product['id']) ? 'fas text-danger' : 'far' ?> fa-heart"></i>
                </button>
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-lg-7">
            <h1 class="h2 mb-3"><?= htmlspecialchars($product['name']) ?></h1>
            
            <!-- Rating -->
            <div class="mb-3">
                <?= renderStars($rating ?: 4.5) ?>
                <span class="ms-2 text-muted">(<?= count($reviews) ?> ulasan)</span>
            </div>
            
            <!-- Price -->
            <div class="mb-4">
                <?php if ($product['sale_price']): ?>
                <span class="h3 text-danger fw-bold"><?= formatRupiah($product['sale_price']) ?></span>
                <span class="h5 text-muted text-decoration-line-through ms-2"><?= formatRupiah($product['price']) ?></span>
                <?php else: ?>
                <span class="h3 text-primary fw-bold"><?= formatRupiah($product['price']) ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Short Description -->
            <?php if ($product['short_description']): ?>
            <p class="lead text-muted mb-4"><?= htmlspecialchars($product['short_description']) ?></p>
            <?php endif; ?>
            
            <!-- Stock Status -->
            <div class="mb-4">
                <?php if ($product['stock_quantity'] > 10): ?>
                <span class="badge bg-success fs-6"><i class="fas fa-check me-1"></i>Stok Tersedia (<?= $product['stock_quantity'] ?>)</span>
                <?php elseif ($product['stock_quantity'] > 0): ?>
                <span class="badge bg-warning fs-6"><i class="fas fa-exclamation-triangle me-1"></i>Stok Terbatas (<?= $product['stock_quantity'] ?>)</span>
                <?php else: ?>
                <span class="badge bg-danger fs-6"><i class="fas fa-times me-1"></i>Stok Habis</span>
                <?php endif; ?>
            </div>
            
            <!-- Quantity & Add to Cart -->
            <?php if ($product['stock_quantity'] > 0): ?>
            <div class="row g-3 mb-4">
                <div class="col-auto">
                    <label class="form-label small text-muted">Jumlah</label>
                    <div class="input-group" style="max-width: 150px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(-1)">-</button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(1)">+</button>
                    </div>
                </div>
                <div class="col">
                    <label class="form-label small text-muted">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button onclick="addToCart(<?= $product['id'] ?>, document.getElementById('quantity').value)" class="btn btn-primary btn-lg flex-grow-1">
                            <i class="fas fa-cart-plus me-2"></i>Tambah Keranjang
                        </button>
                        <button onclick="buyNow(<?= $product['id'] ?>)" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-bolt me-1"></i>Beli
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Product Info Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 text-center">
                        <i class="fas fa-truck text-primary mb-2"></i>
                        <div class="small">Gratis Ongkir</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 text-center">
                        <i class="fas fa-shield-alt text-success mb-2"></i>
                        <div class="small">Garansi Resmi</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 text-center">
                        <i class="fas fa-undo text-warning mb-2"></i>
                        <div class="small">7 Hari Retur</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 text-center">
                        <i class="fas fa-headset text-info mb-2"></i>
                        <div class="small">Support 24/7</div>
                    </div>
                </div>
            </div>
            
            <!-- SKU & Category -->
            <div class="small text-muted">
                <?php if ($product['sku']): ?>
                <div>SKU: <?= htmlspecialchars($product['sku']) ?></div>
                <?php endif; ?>
                <div>Kategori: <a href="<?= SITE_URL ?>/category.php?slug=<?= htmlspecialchars($product['category_slug'] ?? 'products') ?>"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></a></div>
            </div>
        </div>
    </div>
    
    <!-- Tabs: Description & Reviews -->
    <div class="mt-5">
        <ul class="nav nav-tabs" id="productTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description">Deskripsi</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">Ulasan (<?= count($reviews) ?>)</button>
            </li>
        </ul>
        
        <div class="tab-content py-4">
            <!-- Description Tab -->
            <div class="tab-pane fade show active" id="description">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <?php if ($product['description']): ?>
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                        <?php else: ?>
                        <p class="text-muted">Tidak ada deskripsi tersedia untuk produk ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews">
                <?php if (empty($reviews)): ?>
                <div class="text-center py-5">
                    <i class="far fa-comment-dots fa-3x text-muted mb-3"></i>
                    <h5>Belum Ada Ulasan</h5>
                    <p class="text-muted">Jadilah yang pertama mengulas produk ini!</p>
                </div>
                <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></strong>
                                <?php if ($review['verified_purchase']): ?>
                                <span class="badge bg-success ms-2"><i class="fas fa-check"></i> Terverifikasi</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted"><?= date('d M Y', strtotime($review['created_at'])) ?></small>
                        </div>
                        <div class="my-2"><?= renderStars($review['rating']) ?></div>
                        <?php if ($review['title']): ?>
                        <h6><?= htmlspecialchars($review['title']) ?></h6>
                        <?php endif; ?>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <div class="mt-5">
        <h4 class="mb-4">Produk Terkait</h4>
        <div class="row g-4">
            <?php foreach ($relatedProducts as $related): ?>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm product-card">
                    <a href="<?= SITE_URL ?>/product.php?slug=<?= htmlspecialchars($related['slug']) ?>">
                        <img src="<?= getProductImage($related['image']) ?>" alt="<?= htmlspecialchars($related['name']) ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                    </a>
                    <div class="card-body">
                        <h6 class="card-title text-truncate">
                            <a href="<?= SITE_URL ?>/product.php?slug=<?= htmlspecialchars($related['slug']) ?>" class="text-decoration-none text-dark">
                                <?= htmlspecialchars($related['name']) ?>
                            </a>
                        </h6>
                        <div class="fw-bold text-primary"><?= formatRupiah($related['sale_price'] ?? $related['price']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function changeQty(change) {
    const input = document.getElementById('quantity');
    let newVal = parseInt(input.value) + change;
    const max = parseInt(input.max);
    
    if (newVal < 1) newVal = 1;
    if (newVal > max) newVal = max;
    
    input.value = newVal;
}

function buyNow(productId) {
    const qty = document.getElementById('quantity').value;
    addToCart(productId, qty);
    setTimeout(() => {
        window.location.href = SITE_URL + '/checkout.php';
    }, 500);
}
</script>

<style>
.product-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s;
}
</style>

<?php include 'includes/footer.php'; ?>
