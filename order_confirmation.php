<?php
/**
 * Order Confirmation Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

requireLogin();

$orderNumber = sanitize($_GET['order'] ?? '');
$orderId = intval($_GET['id'] ?? 0);

$db = getDB();

if (!empty($orderNumber)) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
    $stmt->execute([$orderNumber, $_SESSION['user_id']]);
    $order = $stmt->fetch();
} elseif ($orderId > 0) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
} else {
    header('Location: ' . SITE_URL . '/orders.php');
    exit;
}

if (!$order) {
    header('Location: ' . SITE_URL . '/orders.php');
    exit;
}

// Get order items
$orderItems = getOrderItems($order['id']);

$pageTitle = 'Konfirmasi Pesanan';

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                    <i class="fas fa-check fa-3x text-white"></i>
                </div>
                <h2 class="mb-3">Pesanan Berhasil Dibuat!</h2>
                <p class="text-muted fs-5">Terima kasih telah berbelanja di <?= SITE_NAME ?></p>
            </div>
            
            <!-- Order Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i>Detail Pesanan</h5>
                        <span class="badge bg-warning fs-6"><?= ucfirst($order['status']) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Nomor Pesanan</h6>
                            <p class="fw-bold fs-5 text-primary mb-3"><?= htmlspecialchars($order['order_number']) ?></p>
                            
                            <h6 class="text-muted mb-2">Tanggal Pesanan</h6>
                            <p class="mb-3"><?= date('d F Y, H:i', strtotime($order['created_at'])) ?></p>
                            
                            <h6 class="text-muted mb-2">Metode Pembayaran</h6>
                            <p class="mb-0"><?= htmlspecialchars($order['payment_method']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Alamat Pengiriman</h6>
                            <p class="mb-0" style="white-space: pre-line;"><?= htmlspecialchars($order['shipping_address']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-box me-2 text-primary"></i>Produk Dipesan</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 ps-4">Produk</th>
                                    <th class="border-0 text-center">Harga</th>
                                    <th class="border-0 text-center">Jumlah</th>
                                    <th class="border-0 text-end pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= getProductImage($item['image']) ?>" alt="" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <span><?= htmlspecialchars($item['name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle"><?= formatRupiah($item['price']) ?></td>
                                    <td class="text-center align-middle"><?= $item['quantity'] ?></td>
                                    <td class="text-end pe-4 align-middle fw-bold"><?= formatRupiah($item['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span><?= formatRupiah($order['total_amount'] - $order['shipping_amount'] - $order['tax_amount'] + $order['discount_amount']) ?></span>
                            </div>
                            <?php if ($order['discount_amount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Diskon</span>
                                <span class="text-success">-<?= formatRupiah($order['discount_amount']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Ongkos Kirim</span>
                                <span><?= $order['shipping_amount'] > 0 ? formatRupiah($order['shipping_amount']) : 'Gratis' ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Pajak</span>
                                <span><?= formatRupiah($order['tax_amount']) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong class="fs-5">Total</strong>
                                <strong class="fs-5 text-primary"><?= formatRupiah($order['total_amount']) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Instructions / Information -->
            <?php 
            $paymentInfo = getPaymentMethodDetails($order['payment_method'], $order['total_amount'], $order['payment_status']);
            ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 border" style="<?= $paymentInfo['type'] === 'cod' ? 'border-color: #0d9488 !important;' : '' ?>">
                <div class="card-header py-3 <?= $paymentInfo['type'] === 'cod' ? 'text-white' : 'bg-warning text-dark' ?>" style="<?= $paymentInfo['type'] === 'cod' ? 'background: #0f766e;' : '' ?>">
                    <h5 class="mb-0 fw-bold fs-6">
                        <i class="fas <?= $paymentInfo['icon'] ?> me-2"></i><?= $paymentInfo['headline'] ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($paymentInfo['type'] === 'cod'): ?>
                    <div class="alert border-0 rounded-3 mb-3 d-flex align-items-start gap-3" style="background-color: #f0fdfa; color: #0f766e;">
                        <i class="fas fa-circle-check fs-4 mt-1" style="color: #0d9488;"></i>
                        <div>
                            <div class="fw-bold mb-1">Metode Pembayaran: COD (Bayar di Tempat)</div>
                            <p class="mb-0 small" style="line-height: 1.6;">
                                <?= $paymentInfo['note'] ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="bg-light rounded-3 p-3 mb-3 border">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Metode Pembayaran</small>
                                <span class="fw-semibold text-dark">Cash on Delivery (COD)</span>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Total yang Harus Disiapkan</small>
                                <span class="fw-bold fs-5" style="color: #0d9488;"><?= formatRupiah($order['total_amount']) ?></span>
                            </div>
                            <div class="col-12 pt-2 border-top mt-2">
                                <small class="text-muted d-block">Waktu Pembayaran</small>
                                <span class="small fw-semibold text-dark">Serahkan uang tunai pas kepada kurir saat pesanan sampai di alamat Anda</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="small text-muted d-flex align-items-center gap-2">
                        <i class="fas fa-truck-fast" style="color: #0d9488;"></i>
                        <span>Pesanan Anda sedang dipersiapkan dan akan segera dikirim oleh pihak kurir.</span>
                    </div>

                    <?php else: ?>
                    <p class="mb-3 small text-dark">
                        Silakan selesaikan pembayaran pesanan Anda dalam waktu <strong>24 jam</strong> ke rekening/akun berikut:
                    </p>
                    <div class="bg-light rounded-3 p-3 mb-3 border">
                        <?php foreach ($paymentInfo['details'] as $label => $val): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span class="text-muted"><?= htmlspecialchars($label) ?></span>
                            <span class="fw-bold text-dark"><?= $val ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="mt-2 mb-0 text-muted small">
                        <i class="fas fa-circle-info me-1"></i>
                        <?= $paymentInfo['note'] ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= SITE_URL ?>/orders.php" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-list me-2"></i>Lihat Semua Pesanan
                </a>
                <a href="<?= SITE_URL ?>/index.php" class="btn btn-outline-primary btn-lg px-4">
                    <i class="fas fa-shopping-bag me-2"></i>Lanjut Belanja
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
