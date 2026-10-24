<?php
/**
 * Admin Dashboard
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

requireAdminLogin();

$admin = getCurrentAdmin();
$db = getDB();

// Get dashboard statistics
$stats = [];

// Total products
$stmt = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
$stats['total_products'] = $stmt->fetchColumn();

// Total available stock
$stmt = $db->query("SELECT SUM(stock_quantity) FROM products WHERE status = 'active'");
$stats['total_stock'] = $stmt->fetchColumn() ?? 0;

// Products sold (last 30 days)
$stmt = $db->query("SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND o.status != 'cancelled'");
$stats['products_sold'] = $stmt->fetchColumn();

// Total revenue (last 30 days)
$stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status != 'cancelled'");
$stats['revenue'] = $stmt->fetchColumn();

// Pending orders
$stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetchColumn();

// Total users
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
$stats['total_users'] = $stmt->fetchColumn();

// Recent orders
$stmt = $db->query("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$recentOrders = $stmt->fetchAll();

// Low stock products
$stmt = $db->query("SELECT * FROM products WHERE stock_quantity <= 30 AND status = 'active' ORDER BY stock_quantity ASC LIMIT 5");
$lowStockProducts = $stmt->fetchAll();

$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/admin.css">
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
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-tachometer-alt"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">
                        <i class="fas fa-box"></i>Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags"></i>Kategori
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="fas fa-shopping-cart"></i>Pesanan
                        <?php if ($stats['pending_orders'] > 0): ?>
                        <span class="badge bg-danger ms-auto"><?= $stats['pending_orders'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i>Pelanggan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="coupons.php">
                        <i class="fas fa-ticket-alt"></i>Kupon
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reviews.php">
                        <i class="fas fa-star"></i>Ulasan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar"></i>Laporan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cog"></i>Pengaturan
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h4 class="mb-0">Dashboard</h4>
                <small class="text-muted">Selamat datang, <?= htmlspecialchars($admin['name']) ?>!</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= SITE_URL ?>/index.php" class="btn btn-outline-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt me-1"></i>Lihat Toko
                </a>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($admin['name']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fas fa-box"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= number_format($stats['total_products']) ?></h3>
                            <small class="text-muted">Total Produk</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= number_format($stats['total_stock']) ?></h3>
                            <small class="text-muted">Stok Tersedia</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= number_format($stats['products_sold']) ?></h3>
                            <small class="text-muted">Terjual (30 hari)</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= formatRupiah($stats['revenue']) ?></h3>
                            <small class="text-muted">Pendapatan (30 hari)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-lg-8">
                <div class="card table-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pesanan Terbaru</h5>
                        <a href="orders.php" class="btn btn-primary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">Order</th>
                                        <th class="border-0">Pelanggan</th>
                                        <th class="border-0">Total</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): 
                                        $statusBadge = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'shipped' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                    ?>
                                    <tr>
                                        <td><a href="order_detail.php?id=<?= $order['id'] ?>"><?= $order['order_number'] ?></a></td>
                                        <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                        <td><?= formatRupiah($order['total_amount']) ?></td>
                                        <td><span class="badge bg-<?= $statusBadge[$order['status']] ?? 'secondary' ?>"><?= ucfirst($order['status']) ?></span></td>
                                        <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada pesanan</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <div class="col-lg-4">
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Stok Rendah</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($lowStockProducts as $product): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div><?= htmlspecialchars($product['name']) ?></div>
                                    <small class="text-muted"><?= formatRupiah($product['price']) ?></small>
                                </div>
                                <span class="badge bg-danger"><?= $product['stock_quantity'] ?></span>
                            </li>
                            <?php endforeach; ?>
                            <?php if (empty($lowStockProducts)): ?>
                            <li class="list-group-item text-center py-4 text-muted">
                                Semua stok aman
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
