<?php
/**
 * Order Detail Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

requireLogin();

$orderId = intval($_GET['id'] ?? 0);
$orderNumber = sanitize($_GET['order'] ?? '');

$db = getDB();

if ($orderId > 0) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
} elseif (!empty($orderNumber)) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
    $stmt->execute([$orderNumber, $_SESSION['user_id']]);
    $order = $stmt->fetch();
} else {
    header('Location: ' . SITE_URL . '/orders.php');
    exit;
}

if (!$order) {
    setFlash('error', 'Pesanan tidak ditemukan.');
    header('Location: ' . SITE_URL . '/orders.php');
    exit;
}

// Get order items
$orderItems = getOrderItems($order['id']);

$statusBadge = [
    'pending' => 'warning',
    'processing' => 'info',
    'shipped' => 'primary',
    'delivered' => 'success',
    'completed' => 'success',
    'cancelled' => 'danger',
    'refunded' => 'secondary'
];

$statusText = [
    'pending' => 'Menunggu Pembayaran',
    'processing' => 'Sedang Diproses',
    'shipped' => 'Dalam Pengiriman',
    'delivered' => 'Pesanan Tiba',
    'completed' => 'Pesanan Selesai',
    'cancelled' => 'Pesanan Dibatalkan',
    'refunded' => 'Dana Dikembalikan'
];

$pageTitle = 'Detail Pesanan #' . $order['order_number'];

include 'includes/header.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/orders.php">Pesanan Saya</a></li>
            <li class="breadcrumb-item active">#<?= htmlspecialchars($order['order_number']) ?></li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
            <span class="badge bg-<?= $statusBadge[$order['status']] ?? 'secondary' ?> px-3 py-2 rounded-pill fw-semibold mb-2">
                <?= $statusText[$order['status']] ?? ucfirst($order['status']) ?>
            </span>
            <h2 class="h4 fw-bold mb-0">Pesanan #<?= htmlspecialchars($order['order_number']) ?></h2>
            <small class="text-muted">Dipesan pada <?= date('d F Y, H:i', strtotime($order['created_at'])) ?> WIB</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="fas fa-print"></i> Cetak Invoice
            </button>
            <a href="<?= SITE_URL ?>/orders.php" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                <i class="fas fa-arrow-left"></i> Kembali ke Pesanan
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <!-- Product Items Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold fs-6">
                        <i class="fas fa-boxes-stacked text-primary me-2"></i>Daftar Produk yang Dipesan
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4 py-3">Produk</th>
                                    <th class="text-center py-3">Harga</th>
                                    <th class="text-center py-3">Jumlah</th>
                                    <th class="text-end pe-4 py-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= getProductImage($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="rounded-3 border" style="width: 54px; height: 54px; object-fit: contain; padding: 2px;">
                                            <div>
                                                <div class="fw-semibold text-dark small mb-1"><?= htmlspecialchars($item['name']) ?></div>
                                                <small class="text-muted">Item ID: #<?= $item['product_id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center py-3 small text-muted">
                                        <?= formatRupiah($item['price']) ?>
                                    </td>
                                    <td class="text-center py-3 small fw-semibold">
                                        <?= $item['quantity'] ?>x
                                    </td>
                                    <td class="text-end pe-4 py-3 fw-bold text-dark small">
                                        <?= formatRupiah($item['total']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Shipping & Delivery Info -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold fs-6 mb-3">
                    <i class="fas fa-truck-fast text-primary me-2"></i>Informasi Pengiriman & Alamat
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Alamat Penerima</div>
                        <p class="small text-dark mb-0 bg-light p-3 rounded-3" style="white-space: pre-line; line-height: 1.6;">
                            <?= htmlspecialchars($order['shipping_address']) ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Status Pengiriman</div>
                        <div class="bg-light p-3 rounded-3">
                            <div class="small fw-semibold text-dark mb-1">
                                No. Resi: <?= !empty($order['tracking_number']) ? htmlspecialchars($order['tracking_number']) : '<span class="text-muted">Menunggu input resi ekspedisi</span>' ?>
                            </div>
                            <div class="small text-muted mb-2">
                                Status: <span class="badge bg-<?= $statusBadge[$order['status']] ?? 'secondary' ?>"><?= $statusText[$order['status']] ?? ucfirst($order['status']) ?></span>
                            </div>
                            <?php if (!empty($order['notes'])): ?>
                            <div class="small text-muted pt-2 border-top">
                                <strong>Catatan:</strong> <?= htmlspecialchars($order['notes']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Payment & Financial Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold fs-6 mb-3">
                    <i class="fas fa-receipt text-primary me-2"></i>Rincian Pembayaran
                </h5>

                <div class="d-flex align-items-center justify-content-between mb-2 small">
                    <span class="text-muted">Metode Pembayaran</span>
                    <span class="fw-semibold text-dark"><?= htmlspecialchars($order['payment_method']) ?></span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 small">
                    <span class="text-muted">Status Pembayaran</span>
                    <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning text-dark' ?>">
                        <?= $order['payment_status'] === 'paid' ? 'Lunas' : 'Menunggu' ?>
                    </span>
                </div>

                <hr class="my-3 text-muted">

                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Subtotal Produk</span>
                    <span class="text-dark fw-semibold"><?= formatRupiah($order['total_amount'] - $order['shipping_amount'] - $order['tax_amount'] + $order['discount_amount']) ?></span>
                </div>

                <?php if ($order['discount_amount'] > 0): ?>
                <div class="d-flex justify-content-between mb-2 small text-success">
                    <span>Diskon Kupon</span>
                    <span>-<?= formatRupiah($order['discount_amount']) ?></span>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Biaya Pengiriman</span>
                    <span class="text-dark fw-semibold"><?= $order['shipping_amount'] > 0 ? formatRupiah($order['shipping_amount']) : 'Gratis' ?></span>
                </div>

                <div class="d-flex justify-content-between mb-3 small">
                    <span class="text-muted">Pajak PPN (10%)</span>
                    <span class="text-dark fw-semibold"><?= formatRupiah($order['tax_amount']) ?></span>
                </div>

                <div class="d-flex justify-content-between pt-3 border-top mb-4">
                    <span class="fw-bold text-dark fs-6">Total Pembayaran</span>
                    <span class="fw-bold text-primary fs-5"><?= formatRupiah($order['total_amount']) ?></span>
                </div>

                <?php 
                $paymentInfo = getPaymentMethodDetails($order['payment_method'], $order['total_amount'], $order['payment_status']);
                if ($order['status'] === 'pending'): 
                ?>
                    <?php if ($paymentInfo['type'] === 'cod'): ?>
                    <div class="alert small p-3 rounded-3 mb-0 border-0" role="alert" style="background-color: #f0fdfa; color: #0f766e;">
                        <div class="fw-bold mb-1"><i class="fas fa-hand-holding-dollar me-1"></i>Metode: COD (Bayar di Tempat)</div>
                        Siapkan uang tunai pas sebesar <strong><?= formatRupiah($order['total_amount']) ?></strong> saat kurir menyerahkan pesanan. Tidak perlu transfer sekarang.
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning small p-3 rounded-3 mb-0" role="alert">
                        <div class="fw-bold mb-1"><i class="fas fa-clock me-1"></i>Segera Selesaikan Pembayaran</div>
                        Silakan transfer <?= formatRupiah($order['total_amount']) ?> sesuai instruksi pembayaran <strong><?= htmlspecialchars($order['payment_method']) ?></strong>.
                    </div>
                    <?php endif; ?>
                <?php elseif ($order['payment_status'] === 'paid'): ?>
                <div class="alert alert-success small p-3 rounded-3 mb-0 border-0" role="alert">
                    <div class="fw-bold mb-1"><i class="fas fa-circle-check me-1"></i>Pembayaran Lunas</div>
                    Terima kasih, pembayaran pesanan ini telah terverifikasi lunas.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
