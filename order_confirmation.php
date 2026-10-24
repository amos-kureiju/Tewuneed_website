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
            
            <!-- Payment Instructions -->
            <?php if ($order['payment_status'] === 'pending'): ?>
            <div class="card border-0 shadow-sm border-warning mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Instruksi Pembayaran</h5>
                </div>
                <div class="card-body">
                    <p>Silakan selesaikan pembayaran Anda dalam waktu <strong>24 jam</strong> ke rekening berikut:</p>
                    <div class="bg-light rounded p-3">
                        <p class="mb-1"><strong>Bank BCA</strong></p>
                        <p class="mb-1">No. Rekening: <strong>1234567890</strong></p>
                        <p class="mb-1">Atas Nama: <strong>PT TEWUNEED INDONESIA</strong></p>
                        <p class="mb-0">Jumlah: <strong class="text-primary"><?= formatRupiah($order['total_amount']) ?></strong></p>
                    </div>
                    <p class="mt-3 mb-0 text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Setelah melakukan pembayaran, pesanan akan diproses dan dikirimkan ke alamat Anda.
                    </p>
                </div>
            </div>
            <?php endif; ?>
            
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
