<?php
/**
 * Admin Settings Page
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

requireAdminLogin();

$db = getDB();
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => sanitize($_POST['site_name'] ?? ''),
        'site_email' => sanitize($_POST['site_email'] ?? ''),
        'site_phone' => sanitize($_POST['site_phone'] ?? ''),
        'site_address' => sanitize($_POST['site_address'] ?? ''),
        'tax_rate' => floatval($_POST['tax_rate'] ?? 10),
        'free_shipping_min' => floatval($_POST['free_shipping_min'] ?? 100000),
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$key, $value, $value]);
    }
    
    $message = 'Pengaturan berhasil disimpan';
}

// Get current settings
$stmt = $db->query("SELECT `key`, `value` FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['key']] = $row['value'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Admin <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/admin.css">
</head>
<body>
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
                <li class="nav-item"><a class="nav-link" href="orders.php"><i class="fas fa-shopping-cart"></i>Pesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i>Pelanggan</a></li>
                <li class="nav-item"><a class="nav-link" href="coupons.php"><i class="fas fa-ticket-alt"></i>Kupon</a></li>
                <li class="nav-item"><a class="nav-link active" href="settings.php"><i class="fas fa-cog"></i>Pengaturan</a></li>
                <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main-content">
        <h4 class="mb-4">Pengaturan</h4>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row g-4">
                <!-- General Settings -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-store me-2"></i>Informasi Toko</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Toko</label>
                                <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="site_phone" class="form-control" value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="site_address" class="form-control" rows="3"><?= htmlspecialchars($settings['site_address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Business Settings -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Pengaturan Bisnis</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Pajak (%)</label>
                                <input type="number" name="tax_rate" class="form-control" value="<?= htmlspecialchars($settings['tax_rate'] ?? 10) ?>" step="0.01">
                                <small class="text-muted">Persentase pajak yang akan diterapkan pada setiap transaksi</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Minimum Gratis Ongkir (Rp)</label>
                                <input type="number" name="free_shipping_min" class="form-control" value="<?= htmlspecialchars($settings['free_shipping_min'] ?? 100000) ?>">
                                <small class="text-muted">Minimum belanja untuk mendapatkan gratis ongkir</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">Database: <strong><?= DB_NAME ?></strong></p>
                            <p class="text-muted mb-0">Host: <strong><?= DB_HOST ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
