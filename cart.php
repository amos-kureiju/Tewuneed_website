<?php
/**
 * Shopping Cart Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

$pageTitle = 'Keranjang Belanja';

// Safe loading with error handling
try {
    $cartItems = getCartItems();
    $cartTotal = getCartTotal();
} catch (Exception $e) {
    error_log('Cart page error: ' . $e->getMessage());
    $cartItems = [];
    $cartTotal = 0;
}

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Keranjang Belanja</h2>
    
    <?php if (empty($cartItems)): ?>
    <div class="text-center py-5">
        <img src="<?= SITE_URL ?>/Images/placeholder.jpg" alt="Empty Cart" style="max-width: 200px; opacity: 0.5;">
        <h4 class="mt-4">Keranjang Anda Kosong</h4>
        <p class="text-muted">Belum ada produk di keranjang Anda. Ayo mulai belanja!</p>
        <a href="<?= SITE_URL ?>/index.php" class="btn btn-primary mt-3">
            <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
        </a>
    </div>
    <?php else: ?>
    
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="cartTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 py-3 ps-4">Produk</th>
                                    <th class="border-0 py-3 text-center">Harga</th>
                                    <th class="border-0 py-3 text-center">Jumlah</th>
                                    <th class="border-0 py-3 text-center">Subtotal</th>
                                    <th class="border-0 py-3 text-center pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): 
                                    // Handle both logged-in and guest cart structures
                                    if (isLoggedIn()) {
                                        // For logged-in users, get fresh product data
                                        $productId = $item['product_id'] ?? $item['id'];
                                        $product = getProductById($productId);
                                        $quantity = $item['quantity'];
                                    } else {
                                        // For guests, item has 'product' key
                                        $product = $item['product'];
                                        $quantity = $item['quantity'];
                                    }
                                    
                                    // Safety check
                                    if (!$product) continue;
                                    
                                    $price = $product['sale_price'] ?? $product['price'];
                                    $subtotal = $price * $quantity;
                                ?>
                                <tr data-product-id="<?= $product['id'] ?>">
                                    <td class="py-3 ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= getProductImage($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                                                <small class="text-muted">Stok: <?= $product['stock_quantity'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center align-middle">
                                        <?php if ($product['sale_price']): ?>
                                        <span class="text-danger fw-bold"><?= formatRupiah($product['sale_price']) ?></span>
                                        <br><small class="text-muted text-decoration-line-through"><?= formatRupiah($product['price']) ?></small>
                                        <?php else: ?>
                                        <span class="fw-bold"><?= formatRupiah($product['price']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-center align-middle">
                                        <div class="input-group input-group-sm justify-content-center" style="max-width: 120px; margin: 0 auto;">
                                            <button class="btn btn-outline-secondary qty-btn" type="button" onclick="updateQuantity(<?= $product['id'] ?>, -1)">-</button>
                                            <input type="number" class="form-control text-center qty-input" value="<?= $quantity ?>" min="1" max="<?= $product['stock_quantity'] ?>" data-product-id="<?= $product['id'] ?>" onchange="setQuantity(<?= $product['id'] ?>, this.value)">
                                            <button class="btn btn-outline-secondary qty-btn" type="button" onclick="updateQuantity(<?= $product['id'] ?>, 1)">+</button>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center align-middle">
                                        <span class="fw-bold text-primary subtotal" data-price="<?= $price ?>"><?= formatRupiah($subtotal) ?></span>
                                    </td>
                                    <td class="py-3 text-center align-middle pe-4">
                                        <button class="btn btn-outline-danger btn-sm" onclick="removeFromCart(<?= $product['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="<?= SITE_URL ?>/index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                </a>
                <button class="btn btn-outline-danger" onclick="clearCart()">
                    <i class="fas fa-trash me-2"></i>Kosongkan Keranjang
                </button>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    <!-- Coupon Code -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Kode Kupon</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="couponCode" placeholder="Masukkan kode kupon">
                            <button class="btn btn-outline-primary" type="button" onclick="applyCoupon()">Terapkan</button>
                        </div>
                        <div id="couponMessage" class="mt-2"></div>
                    </div>
                    
                    <!-- Price Summary -->
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold" id="subtotal"><?= formatRupiah($cartTotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Diskon Kupon</span>
                            <span class="text-success" id="discountAmount">-<?= formatRupiah(0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ongkos Kirim</span>
                            <span id="shippingCost">Dihitung saat checkout</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-5 text-primary" id="grandTotal"><?= formatRupiah($cartTotal) ?></span>
                        </div>
                    </div>
                    
                    <a href="<?= SITE_URL ?>/checkout.php" class="btn btn-primary w-100 py-3">
                        <i class="fas fa-credit-card me-2"></i>Checkout
                    </a>
                    
                    <!-- Security Note -->
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lock me-1"></i>Pembayaran aman & terenkripsi
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<script>
let discount = 0;

function updateQuantity(productId, change) {
    const input = document.querySelector(`input.qty-input[data-product-id="${productId}"]`);
    let newQty = parseInt(input.value) + change;
    const max = parseInt(input.max);
    
    if (newQty < 1) newQty = 1;
    if (newQty > max) newQty = max;
    
    setQuantity(productId, newQty);
}

function setQuantity(productId, quantity) {
    $.ajax({
        url: SITE_URL + '/api/cart.php',
        method: 'POST',
        data: {
            action: 'update',
            product_id: productId,
            quantity: quantity
        },
        success: function(response) {
            if (response.success) {
                // Update input value
                const input = document.querySelector(`input.qty-input[data-product-id="${productId}"]`);
                input.value = quantity;
                
                // Update subtotal
                const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                const price = parseFloat(row.querySelector('.subtotal').dataset.price);
                const subtotal = price * quantity;
                row.querySelector('.subtotal').textContent = formatRupiah(subtotal);
                
                // Update totals
                updateTotals();
                updateCartCount(response.cart_count);
            } else {
                showToast(response.message || 'Gagal memperbarui jumlah', 'error');
            }
        },
        error: function() {
            showToast('Terjadi kesalahan', 'error');
        }
    });
}

function removeFromCart(productId) {
    if (!confirm('Hapus produk ini dari keranjang?')) return;
    
    $.ajax({
        url: SITE_URL + '/api/cart.php',
        method: 'POST',
        data: {
            action: 'remove',
            product_id: productId
        },
        success: function(response) {
            if (response.success) {
                const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                row.remove();
                updateTotals();
                updateCartCount(response.cart_count);
                showToast('Produk dihapus dari keranjang', 'success');
                
                // Reload if cart empty
                if (document.querySelectorAll('#cartTable tbody tr').length === 0) {
                    location.reload();
                }
            }
        }
    });
}

function clearCart() {
    if (!confirm('Kosongkan semua produk dari keranjang?')) return;
    
    $.ajax({
        url: SITE_URL + '/api/cart.php',
        method: 'POST',
        data: { action: 'clear' },
        success: function(response) {
            if (response.success) {
                location.reload();
            }
        }
    });
}

function updateTotals() {
    let subtotal = 0;
    document.querySelectorAll('#cartTable tbody tr').forEach(row => {
        const price = parseFloat(row.querySelector('.subtotal').dataset.price);
        const qty = parseInt(row.querySelector('.qty-input').value);
        subtotal += price * qty;
    });
    
    const grandTotal = subtotal - discount;
    
    document.getElementById('subtotal').textContent = formatRupiah(subtotal);
    document.getElementById('grandTotal').textContent = formatRupiah(grandTotal);
}

function applyCoupon() {
    const code = document.getElementById('couponCode').value;
    if (!code) {
        showToast('Masukkan kode kupon', 'warning');
        return;
    }
    
    $.ajax({
        url: SITE_URL + '/api/coupon.php',
        method: 'POST',
        data: { code: code },
        success: function(response) {
            const msgDiv = document.getElementById('couponMessage');
            if (response.success) {
                discount = response.discount;
                msgDiv.innerHTML = '<small class="text-success"><i class="fas fa-check me-1"></i>' + response.message + '</small>';
                document.getElementById('discountAmount').textContent = '-' + formatRupiah(discount);
                updateTotals();
                showToast('Kupon berhasil diterapkan!', 'success');
            } else {
                msgDiv.innerHTML = '<small class="text-danger"><i class="fas fa-times me-1"></i>' + response.message + '</small>';
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
