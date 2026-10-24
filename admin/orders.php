<?php
/**
 * Admin Orders Management
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

requireAdminLogin();

$db = getDB();
$admin = getCurrentAdmin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = intval($_POST['order_id'] ?? 0);
    $action = $_POST['action'];
    
    if ($orderId > 0) {
        $newStatus = '';
        $notes = '';
        
        switch ($action) {
            case 'confirm_payment':
                $newStatus = 'processing';
                $notes = 'Payment confirmed by admin';
                $db->prepare("UPDATE orders SET payment_status = 'paid', payment_date = NOW() WHERE id = ?")->execute([$orderId]);
                break;
            case 'ship':
                $newStatus = 'shipped';
                $trackingNumber = sanitize($_POST['tracking_number'] ?? '');
                $notes = 'Order shipped' . ($trackingNumber ? ' - Tracking: ' . $trackingNumber : '');
                if ($trackingNumber) {
                    $db->prepare("UPDATE orders SET tracking_number = ? WHERE id = ?")->execute([$trackingNumber, $orderId]);
                }
                break;
            case 'deliver':
                $newStatus = 'delivered';
                $notes = 'Order delivered';
                break;
            case 'cancel':
                $newStatus = 'cancelled';
                $notes = 'Cancelled by admin: ' . sanitize($_POST['reason'] ?? '');
                // Restore stock
                $items = getOrderItems($orderId);
                foreach ($items as $item) {
                    $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
                }
                break;
        }
        
        if ($newStatus) {
            $db->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $orderId]);
            $db->prepare("INSERT INTO order_status_history (order_id, status, notes, created_by) VALUES (?, ?, ?, ?)")->execute([$orderId, $newStatus, $notes, $_SESSION['admin_id']]);
        }
    }
    
    header('Location: orders.php');
    exit;
}

// Pagination & Filters
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$statusFilter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$where = [];
$params = [];

if ($statusFilter) {
    $where[] = "o.status = ?";
    $params[] = $statusFilter;
}
if ($search) {
    $where[] = "(o.order_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get orders
$stmt = $db->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    $whereClause 
    ORDER BY o.created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get total count
$stmt = $db->prepare("SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id $whereClause");
$stmt->execute($params);
$totalOrders = $stmt->fetchColumn();
$totalPages = ceil($totalOrders / $limit);

// Stats
$stats = [];
$stmt = $db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while ($row = $stmt->fetch()) {
    $stats[$row['status']] = $row['count'];
}

$pageTitle = 'Kelola Pesanan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan - Admin <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/admin.css">
    <style>
        .stat-card { border-left: 4px solid !important; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-none d-md-block">
        <div class="sidebar-brand">
            <h4><span class="brand-tewu">TEWU</span><span class="brand-need">NEED</span></h4>
            <small>Admin Portal</small>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php"><i class="fas fa-box"></i>Produk</a></li>
                <li class="nav-item"><a class="nav-link" href="categories.php"><i class="fas fa-tags"></i>Kategori</a></li>
                <li class="nav-item"><a class="nav-link active" href="orders.php"><i class="fas fa-shopping-cart"></i>Pesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i>Pelanggan</a></li>
                <li class="nav-item"><a class="nav-link" href="coupons.php"><i class="fas fa-ticket-alt"></i>Kupon</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i>Pengaturan</a></li>
                <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main-content">
        <h4 class="mb-4">Kelola Pesanan</h4>
        
        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card stat-card" style="border-color: #ffc107;">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= $stats['pending'] ?? 0 ?></h4>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card" style="border-color: #17a2b8;">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= $stats['processing'] ?? 0 ?></h4>
                        <small class="text-muted">Diproses</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card" style="border-color: #007bff;">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= $stats['shipped'] ?? 0 ?></h4>
                        <small class="text-muted">Dikirim</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card" style="border-color: #28a745;">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= ($stats['delivered'] ?? 0) + ($stats['completed'] ?? 0) ?></h4>
                        <small class="text-muted">Selesai</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card" style="border-color: #dc3545;">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= $stats['cancelled'] ?? 0 ?></h4>
                        <small class="text-muted">Dibatalkan</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Cari order number, nama, atau email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $statusFilter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">Order</th>
                                <th class="border-0">Pelanggan</th>
                                <th class="border-0 text-end">Total</th>
                                <th class="border-0 text-center">Pembayaran</th>
                                <th class="border-0 text-center">Status</th>
                                <th class="border-0">Tanggal</th>
                                <th class="border-0 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): 
                                $statusBadge = ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'completed' => 'success', 'cancelled' => 'danger'];
                                $paymentBadge = ['pending' => 'warning', 'paid' => 'success', 'failed' => 'danger', 'refunded' => 'secondary'];
                            ?>
                            <tr>
                                <td>
                                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="fw-bold"><?= $order['order_number'] ?></a>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($order['email']) ?></small>
                                </td>
                                <td class="text-end fw-bold"><?= formatRupiah($order['total_amount']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $paymentBadge[$order['payment_status']] ?? 'secondary' ?>"><?= ucfirst($order['payment_status']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $statusBadge[$order['status']] ?? 'secondary' ?>"><?= ucfirst($order['status']) ?></span>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Aksi</button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="order_detail.php?id=<?= $order['id'] ?>"><i class="fas fa-eye me-2"></i>Lihat Detail</a></li>
                                            <?php if ($order['status'] === 'pending' && $order['payment_status'] === 'pending'): ?>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" name="action" value="confirm_payment" class="dropdown-item"><i class="fas fa-check me-2"></i>Konfirmasi Pembayaran</button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($order['status'] === 'processing'): ?>
                                            <li>
                                                <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#shipModal" data-order-id="<?= $order['id'] ?>"><i class="fas fa-truck me-2"></i>Kirim Pesanan</a>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($order['status'] === 'shipped'): ?>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" name="action" value="deliver" class="dropdown-item"><i class="fas fa-check-double me-2"></i>Tandai Terkirim</button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a href="#" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#cancelModal" data-order-id="<?= $order['id'] ?>"><i class="fas fa-times me-2"></i>Batalkan</a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($orders)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada pesanan</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $statusFilter ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Ship Modal -->
    <div class="modal fade" id="shipModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="order_id" id="shipOrderId">
                    <div class="modal-header">
                        <h5 class="modal-title">Kirim Pesanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nomor Resi</label>
                            <input type="text" name="tracking_number" class="form-control" placeholder="Masukkan nomor resi">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="action" value="ship" class="btn btn-primary">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="order_id" id="cancelOrderId">
                    <div class="modal-header">
                        <h5 class="modal-title">Batalkan Pesanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Alasan Pembatalan</label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="action" value="cancel" class="btn btn-danger">Batalkan Pesanan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('shipModal').addEventListener('show.bs.modal', function(e) {
            document.getElementById('shipOrderId').value = e.relatedTarget.dataset.orderId;
        });
        document.getElementById('cancelModal').addEventListener('show.bs.modal', function(e) {
            document.getElementById('cancelOrderId').value = e.relatedTarget.dataset.orderId;
        });
    </script>
</body>
</html>
