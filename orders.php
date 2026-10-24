<?php
/**
 * My Orders Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

requireLogin();

$orders = getUserOrders($_SESSION['user_id']);

$pageTitle = 'Pesanan Saya';

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-box me-2"></i>Pesanan Saya</h2>
    
    <?php if (empty($orders)): ?>
    <div class="text-center py-5">
        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
        <h4>Belum Ada Pesanan</h4>
        <p class="text-muted">Anda belum memiliki pesanan. Ayo mulai belanja!</p>
        <a href="<?= SITE_URL ?>/index.php" class="btn btn-primary mt-2">
            <i class="fas fa-shopping-cart me-2"></i>Mulai Belanja
        </a>
    </div>
    <?php else: ?>
    
    <!-- Order Status Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="#" data-filter="all">Semua</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-filter="pending">Menunggu Pembayaran</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-filter="processing">Diproses</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-filter="shipped">Dikirim</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-filter="completed">Selesai</a>
        </li>
    </ul>
    
    <!-- Orders List -->
    <div id="ordersList">
        <?php foreach ($orders as $order): 
            $items = getOrderItems($order['id']);
            
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
                'processing' => 'Diproses',
                'shipped' => 'Dikirim',
                'delivered' => 'Terkirim',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                'refunded' => 'Dikembalikan'
            ];
        ?>
        <div class="card border-0 shadow-sm mb-4 order-card" data-status="<?= $order['status'] ?>">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <strong class="text-primary"><?= htmlspecialchars($order['order_number']) ?></strong>
                            <span class="badge bg-<?= $statusBadge[$order['status']] ?? 'secondary' ?>">
                                <?= $statusText[$order['status']] ?? ucfirst($order['status']) ?>
                            </span>
                        </div>
                        <small class="text-muted"><?= date('d F Y, H:i', strtotime($order['created_at'])) ?></small>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <a href="<?= SITE_URL ?>/order_detail.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>Lihat Detail
                        </a>
                        <?php if ($order['status'] === 'pending'): ?>
                        <button class="btn btn-danger btn-sm" onclick="cancelOrder(<?= $order['id'] ?>)">
                            <i class="fas fa-times me-1"></i>Batalkan
                        </button>
                        <?php endif; ?>
                        <?php if ($order['status'] === 'delivered'): ?>
                        <button class="btn btn-success btn-sm" onclick="confirmReceived(<?= $order['id'] ?>)">
                            <i class="fas fa-check me-1"></i>Pesanan Diterima
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <?php 
                        $displayItems = array_slice($items, 0, 2);
                        $moreCount = count($items) - 2;
                        
                        foreach ($displayItems as $item): 
                        ?>
                        <div class="d-flex align-items-center mb-2">
                            <img src="<?= getProductImage($item['image']) ?>" alt="" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <div><?= htmlspecialchars($item['name']) ?></div>
                                <small class="text-muted"><?= $item['quantity'] ?> x <?= formatRupiah($item['price']) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if ($moreCount > 0): ?>
                        <small class="text-muted">+<?= $moreCount ?> produk lainnya</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <small class="text-muted d-block">Total Pesanan</small>
                        <strong class="fs-5 text-primary"><?= formatRupiah($order['total_amount']) ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</div>

<script>
// Tab filtering
document.querySelectorAll('[data-filter]').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update active tab
        document.querySelectorAll('.nav-link').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        
        document.querySelectorAll('.order-card').forEach(card => {
            if (filter === 'all' || card.dataset.status === filter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

function cancelOrder(orderId) {
    if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) return;
    
    $.ajax({
        url: SITE_URL + '/api/orders.php',
        method: 'POST',
        data: { action: 'cancel', order_id: orderId },
        success: function(response) {
            if (response.success) {
                showToast('Pesanan berhasil dibatalkan', 'success');
                location.reload();
            } else {
                showToast(response.message, 'error');
            }
        }
    });
}

function confirmReceived(orderId) {
    if (!confirm('Konfirmasi bahwa pesanan telah diterima?')) return;
    
    $.ajax({
        url: SITE_URL + '/api/orders.php',
        method: 'POST',
        data: { action: 'confirm_received', order_id: orderId },
        success: function(response) {
            if (response.success) {
                showToast('Terima kasih! Pesanan Anda telah selesai', 'success');
                location.reload();
            } else {
                showToast(response.message, 'error');
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
