<?php
/**
 * Checkout Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

requireLogin();

$cartItems = getCartItems();
$cartTotal = getCartTotal();

if (empty($cartItems)) {
    header('Location: ' . SITE_URL . '/cart.php');
    exit;
}

$user = getCurrentUser();

// Get user addresses
$db = getDB();
$stmt = $db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$_SESSION['user_id']]);
$addresses = $stmt->fetchAll();

// Get shipping methods
$stmt = $db->query("SELECT * FROM shipping_methods WHERE status = 'active'");
$shippingMethods = $stmt->fetchAll();

// Get payment methods
$stmt = $db->query("SELECT * FROM payment_methods WHERE status = 'active'");
$paymentMethods = $stmt->fetchAll();

// Get applied coupon
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discount = $appliedCoupon['discount'] ?? 0;

$error = '';
$pageTitle = 'Checkout';

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addressId = intval($_POST['address_id'] ?? 0);
    $shippingMethodId = intval($_POST['shipping_method'] ?? 0);
    $paymentMethodId = intval($_POST['payment_method'] ?? 0);
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Validate
    if ($addressId <= 0) {
        $error = 'Pilih alamat pengiriman';
    } elseif ($shippingMethodId <= 0) {
        $error = 'Pilih metode pengiriman';
    } elseif ($paymentMethodId <= 0) {
        $error = 'Pilih metode pembayaran';
    } else {
        // Get address
        $stmt = $db->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$addressId, $_SESSION['user_id']]);
        $address = $stmt->fetch();
        
        // Get shipping method
        $stmt = $db->prepare("SELECT * FROM shipping_methods WHERE id = ?");
        $stmt->execute([$shippingMethodId]);
        $shipping = $stmt->fetch();
        
        // Get payment method
        $stmt = $db->prepare("SELECT * FROM payment_methods WHERE id = ?");
        $stmt->execute([$paymentMethodId]);
        $payment = $stmt->fetch();
        
        if (!$address || !$shipping || !$payment) {
            $error = 'Data tidak valid';
        } else {
            // Calculate totals
            $shippingCost = $shipping['cost'];
            $taxRate = 0.1; // 10%
            $taxAmount = $cartTotal * $taxRate;
            $totalAmount = $cartTotal - $discount + $shippingCost + $taxAmount;
            
            // Format address
            $shippingAddress = sprintf(
                "%s %s\n%s%s\n%s, %s %s\n%s\nTelp: %s",
                $address['first_name'],
                $address['last_name'],
                $address['address_line_1'],
                $address['address_line_2'] ? ', ' . $address['address_line_2'] : '',
                $address['city'],
                $address['state'],
                $address['postal_code'],
                $address['country'] ?? 'Indonesia',
                $address['phone']
            );
            
            try {
                $db->beginTransaction();
                
                // Create order
                $orderNumber = generateOrderNumber();
                $stmt = $db->prepare("
                    INSERT INTO orders (
                        order_number, user_id, status, total_amount, shipping_amount, 
                        tax_amount, discount_amount, payment_method, payment_status, 
                        shipping_address, billing_address, notes
                    ) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, 'pending', ?, ?, ?)
                ");
                $stmt->execute([
                    $orderNumber,
                    $_SESSION['user_id'],
                    $totalAmount,
                    $shippingCost,
                    $taxAmount,
                    $discount,
                    $payment['name'],
                    $shippingAddress,
                    $shippingAddress,
                    $notes
                ]);
                
                $orderId = $db->lastInsertId();
                
                // Create order items
                foreach ($cartItems as $item) {
                    // Handle both logged-in and guest cart structures
                    if (isLoggedIn()) {
                        $productId = $item['product_id'] ?? $item['id'];
                        $product = getProductById($productId);
                        $quantity = $item['quantity'];
                    } else {
                        $product = $item['product'];
                        $quantity = $item['quantity'];
                    }
                    
                    if (!$product) continue;
                    
                    $price = $product['sale_price'] ?? $product['price'];
                    $total = $price * $quantity;
                    
                    $stmt = $db->prepare("
                        INSERT INTO order_items (order_id, product_id, quantity, price, total) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$orderId, $product['id'], $quantity, $price, $total]);
                    
                    // Update stock
                    $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                    $stmt->execute([$quantity, $product['id']]);
                }
                
                // Add order status history
                $stmt = $db->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'pending', 'Order placed')");
                $stmt->execute([$orderId]);
                
                // Update coupon usage if applied
                if ($appliedCoupon) {
                    $stmt = $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
                    $stmt->execute([$appliedCoupon['id']]);
                    
                    $stmt = $db->prepare("INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_amount) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$appliedCoupon['id'], $_SESSION['user_id'], $orderId, $discount]);
                    
                    unset($_SESSION['applied_coupon']);
                }
                
                // Clear cart
                clearCart();
                
                $db->commit();
                
                // Redirect to order confirmation
                header('Location: ' . SITE_URL . '/order_confirmation.php?order=' . $orderNumber);
                exit;
                
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.';
                error_log('Checkout error: ' . $e->getMessage());
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-credit-card me-2"></i>Checkout</h2>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <?= csrfInput() ?>
        
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Shipping Address -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Alamat Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($addresses)): ?>
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Anda belum memiliki alamat tersimpan.
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            <i class="fas fa-plus me-2"></i>Tambah Alamat Baru
                        </button>
                        <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($addresses as $addr): ?>
                            <div class="col-md-6">
                                <div class="form-check card p-3 h-100 <?= $addr['is_default'] ? 'border-primary' : '' ?>">
                                    <input class="form-check-input" type="radio" name="address_id" id="addr<?= $addr['id'] ?>" value="<?= $addr['id'] ?>" <?= $addr['is_default'] ? 'checked' : '' ?>>
                                    <label class="form-check-label w-100" for="addr<?= $addr['id'] ?>">
                                        <strong><?= htmlspecialchars($addr['first_name'] . ' ' . $addr['last_name']) ?></strong>
                                        <?php if ($addr['is_default']): ?>
                                        <span class="badge bg-primary ms-2">Default</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($addr['address_line_1']) ?><br>
                                            <?= htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' ' . $addr['postal_code']) ?><br>
                                            <?= htmlspecialchars($addr['phone']) ?>
                                        </small>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            <i class="fas fa-plus me-1"></i>Tambah Alamat Baru
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Shipping Method -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i>Metode Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($shippingMethods as $method): ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="shipping_method" id="ship<?= $method['id'] ?>" value="<?= $method['id'] ?>" data-cost="<?= $method['cost'] ?>">
                            <label class="form-check-label d-flex justify-content-between w-100" for="ship<?= $method['id'] ?>">
                                <div>
                                    <strong><?= htmlspecialchars($method['name']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($method['estimated_days']) ?></small>
                                </div>
                                <span class="fw-bold"><?= $method['cost'] > 0 ? formatRupiah($method['cost']) : 'Gratis' ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-wallet me-2 text-primary"></i>Metode Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $paymentGroups = [];
                        foreach ($paymentMethods as $pm) {
                            $paymentGroups[$pm['type']][] = $pm;
                        }
                        
                        $typeLabels = [
                            'bank_transfer' => 'Transfer Bank (Verifikasi Otomatis/Manual)',
                            'e_wallet' => 'E-Wallet / Dompet Digital',
                            'cod' => 'Bayar di Tempat (Cash on Delivery)'
                        ];
                        ?>
                        
                        <?php foreach ($paymentGroups as $type => $methods): ?>
                        <div class="mb-3">
                            <h6 class="text-muted text-uppercase small fw-bold mb-2">
                                <?= $typeLabels[$type] ?? ucwords(str_replace('_', ' ', $type)) ?>
                            </h6>
                            <?php foreach ($methods as $method): ?>
                            <div class="form-check p-3 border rounded-3 mb-2 bg-light bg-opacity-50">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay<?= $method['id'] ?>" value="<?= $method['id'] ?>" required>
                                <label class="form-check-label w-100 cursor-pointer" for="pay<?= $method['id'] ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($method['name']) ?></strong>
                                        <?php if ($method['type'] === 'cod'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success small">Bayar Tunai</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($method['type'] === 'cod'): ?>
                                    <small class="text-muted d-block mt-1">Bayar langsung dengan uang pas ke kurir saat barang sampai di alamat Anda.</small>
                                    <?php elseif ($method['type'] === 'bank_transfer'): ?>
                                    <small class="text-muted d-block mt-1">Selesaikan transfer melalui ATM / M-Banking setelah pesanan dibuat.</small>
                                    <?php elseif ($method['type'] === 'e_wallet'): ?>
                                    <small class="text-muted d-block mt-1">Pembayaran via aplikasi e-wallet resmi.</small>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Order Notes -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2 text-primary"></i>Catatan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan untuk penjual (opsional)"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Order Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Ringkasan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="mb-4" style="max-height: 250px; overflow-y: auto;">
                            <?php foreach ($cartItems as $item): 
                                $product = isLoggedIn() ? $item : $item['product'];
                                $quantity = $item['quantity'];
                                $price = $product['sale_price'] ?? $product['price'];
                            ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= getProductImage($product['image']) ?>" alt="" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <small class="d-block"><?= htmlspecialchars($product['name']) ?></small>
                                    <small class="text-muted"><?= $quantity ?> x <?= formatRupiah($price) ?></small>
                                </div>
                                <strong><?= formatRupiah($price * $quantity) ?></strong>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <hr>
                        
                        <!-- Totals -->
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span><?= formatRupiah($cartTotal) ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Diskon</span>
                            <span class="text-success">-<?= formatRupiah($discount) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ongkos Kirim</span>
                            <span id="shippingDisplay">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pajak (10%)</span>
                            <span><?= formatRupiah($cartTotal * 0.1) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <strong class="fs-5">Total</strong>
                            <strong class="fs-5 text-primary" id="grandTotal"><?= formatRupiah($cartTotal - $discount + ($cartTotal * 0.1)) ?></strong>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-check me-2"></i>Buat Pesanan
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>Pembayaran aman & terenkripsi
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Alamat Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAddressForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Depan *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Belakang *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Telepon *</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat *</label>
                            <input type="text" name="address_line_1" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota *</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Provinsi *</label>
                            <input type="text" name="state" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Pos *</label>
                            <input type="text" name="postal_code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe</label>
                            <select name="type" class="form-select">
                                <option value="home">Rumah</option>
                                <option value="work">Kantor</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_default" class="form-check-input" id="isDefault">
                                <label class="form-check-label" for="isDefault">Jadikan alamat utama</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Alamat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update shipping cost display
document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const cost = parseFloat(this.dataset.cost);
        document.getElementById('shippingDisplay').textContent = cost > 0 ? formatRupiah(cost) : 'Gratis';
        updateGrandTotal(cost);
    });
});

function updateGrandTotal(shippingCost) {
    const subtotal = <?= $cartTotal ?>;
    const discount = <?= $discount ?>;
    const tax = subtotal * 0.1;
    const total = subtotal - discount + shippingCost + tax;
    document.getElementById('grandTotal').textContent = formatRupiah(total);
}

// Add address form submission
document.getElementById('addAddressForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    fetch(SITE_URL + '/api/address.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
