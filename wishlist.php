<?php
/**
 * Wishlist Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

requireLogin();

$wishlistItems = getWishlistItems();

$pageTitle = 'Wishlist';

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-heart text-danger me-2"></i>Wishlist Saya</h2>
    
    <?php if (empty($wishlistItems)): ?>
    <div class="text-center py-5">
        <i class="far fa-heart fa-4x text-muted mb-3"></i>
        <h4>Wishlist Kosong</h4>
        <p class="text-muted">Anda belum memiliki produk favorit</p>
        <a href="<?= SITE_URL ?>/index.php" class="btn btn-primary mt-2">
            <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
        </a>
    </div>
    <?php else: ?>
    
    <div class="row g-4">
        <?php foreach ($wishlistItems as $item): ?>
        <div class="col-6 col-md-4 col-lg-3" id="wishlist-item-<?= $item['product_id'] ?>">
            <div class="card h-100 border-0 shadow-sm product-card">
                <?php if ($item['sale_price']): ?>
                <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
                <?php endif; ?>
                
                <a href="<?= SITE_URL ?>/product.php?slug=<?= htmlspecialchars($item['slug']) ?>">
                    <img src="<?= getProductImage($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
                </a>
                
                <button class="btn btn-light position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeWishlist(<?= $item['product_id'] ?>)">
                    <i class="fas fa-times text-danger"></i>
                </button>
                
                <div class="card-body">
                    <h6 class="card-title text-truncate">
                        <a href="<?= SITE_URL ?>/product.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="text-decoration-none text-dark">
                            <?= htmlspecialchars($item['name']) ?>
                        </a>
                    </h6>
                    
                    <div class="mb-3">
                        <?php if ($item['sale_price']): ?>
                        <span class="text-danger fw-bold"><?= formatRupiah($item['sale_price']) ?></span>
                        <small class="text-muted text-decoration-line-through ms-1"><?= formatRupiah($item['price']) ?></small>
                        <?php else: ?>
                        <span class="fw-bold text-primary"><?= formatRupiah($item['price']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid">
                        <button onclick="addToCart(<?= $item['product_id'] ?>); removeWishlist(<?= $item['product_id'] ?>);" class="btn btn-primary btn-sm" <?= $item['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-cart-plus me-1"></i>Tambah Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</div>

<script>
function removeWishlist(productId) {
    $.ajax({
        url: SITE_URL + '/api/wishlist.php',
        method: 'POST',
        data: { action: 'remove', product_id: productId },
        success: function(response) {
            if (response.success) {
                document.getElementById('wishlist-item-' + productId).remove();
                showToast('Produk dihapus dari wishlist', 'success');
                
                // Check if wishlist is empty
                if (document.querySelectorAll('[id^="wishlist-item-"]').length === 0) {
                    location.reload();
                }
            }
        }
    });
}
</script>

<style>
.product-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s;
}
</style>

<?php include 'includes/footer.php'; ?>
