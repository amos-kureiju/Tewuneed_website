<?php
/**
 * Admin Coupons Management
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

requireAdminLogin();

$db = getDB();
$message = '';
$error = '';

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $db->prepare("DELETE FROM coupons WHERE id = ?")->execute([intval($_GET['id'])]);
    $message = 'Kupon berhasil dihapus';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $couponId = intval($_POST['coupon_id'] ?? 0);
    $code = strtoupper(sanitize($_POST['code'] ?? ''));
    $type = sanitize($_POST['type'] ?? 'percentage');
    $value = floatval($_POST['value'] ?? 0);
    $minAmount = floatval($_POST['min_amount'] ?? 0);
    $maxUses = intval($_POST['max_uses'] ?? 0);
    $expiresAt = sanitize($_POST['expires_at'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');
    
    if (empty($code) || $value <= 0) {
        $error = 'Kode dan nilai kupon wajib diisi';
    } else {
        if ($couponId > 0) {
            $stmt = $db->prepare("UPDATE coupons SET code = ?, type = ?, value = ?, min_amount = ?, max_uses = ?, expires_at = ?, status = ? WHERE id = ?");
            if ($stmt->execute([$code, $type, $value, $minAmount, $maxUses, $expiresAt ?: null, $status, $couponId])) {
                $message = 'Kupon berhasil diperbarui';
            }
        } else {
            $stmt = $db->prepare("INSERT INTO coupons (code, type, value, min_amount, max_uses, expires_at, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$code, $type, $value, $minAmount, $maxUses, $expiresAt ?: null, $status])) {
                $message = 'Kupon berhasil ditambahkan';
            }
        }
    }
}

// Get coupons
$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();

// Edit mode
$editCoupon = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([intval($_GET['id'])]);
    $editCoupon = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kupon - Admin <?= SITE_NAME ?></title>
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
                <li class="nav-item"><a class="nav-link active" href="coupons.php"><i class="fas fa-ticket-alt"></i>Kupon</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i>Pengaturan</a></li>
                <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Kelola Kupon</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#couponModal">
                <i class="fas fa-plus me-2"></i>Tambah Kupon
            </button>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Kode</th>
                            <th class="border-0">Tipe</th>
                            <th class="border-0 text-end">Nilai</th>
                            <th class="border-0 text-end">Min. Belanja</th>
                            <th class="border-0 text-center">Penggunaan</th>
                            <th class="border-0">Berlaku Hingga</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $coupon): 
                            $isExpired = $coupon['expires_at'] && strtotime($coupon['expires_at']) < time();
                        ?>
                        <tr>
                            <td><code class="fw-bold"><?= htmlspecialchars($coupon['code']) ?></code></td>
                            <td><?= $coupon['type'] === 'percentage' ? 'Persentase' : 'Nominal' ?></td>
                            <td class="text-end">
                                <?= $coupon['type'] === 'percentage' ? $coupon['value'] . '%' : formatRupiah($coupon['value']) ?>
                            </td>
                            <td class="text-end"><?= formatRupiah($coupon['min_amount']) ?></td>
                            <td class="text-center"><?= $coupon['used_count'] ?> / <?= $coupon['max_uses'] ?: '∞' ?></td>
                            <td>
                                <?= $coupon['expires_at'] ? date('d M Y', strtotime($coupon['expires_at'])) : 'Tidak ada' ?>
                                <?php if ($isExpired): ?>
                                <span class="badge bg-danger">Expired</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $coupon['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($coupon['status']) ?></span>
                            </td>
                            <td class="text-center">
                                <a href="?action=edit&id=<?= $coupon['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <a href="?action=delete&id=<?= $coupon['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kupon ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($coupons)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada kupon</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Coupon Modal -->
    <div class="modal fade" id="couponModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="coupon_id" value="<?= $editCoupon['id'] ?? '' ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= $editCoupon ? 'Edit' : 'Tambah' ?> Kupon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode Kupon *</label>
                                <input type="text" name="code" class="form-control text-uppercase" value="<?= htmlspecialchars($editCoupon['code'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipe *</label>
                                <select name="type" class="form-select">
                                    <option value="percentage" <?= ($editCoupon['type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Persentase (%)</option>
                                    <option value="fixed" <?= ($editCoupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Nominal (Rp)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nilai *</label>
                                <input type="number" name="value" class="form-control" value="<?= $editCoupon['value'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Min. Belanja</label>
                                <input type="number" name="min_amount" class="form-control" value="<?= $editCoupon['min_amount'] ?? 0 ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maks. Penggunaan</label>
                                <input type="number" name="max_uses" class="form-control" value="<?= $editCoupon['max_uses'] ?? '' ?>" placeholder="0 = unlimited">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Berlaku Hingga</label>
                                <input type="date" name="expires_at" class="form-control" value="<?= $editCoupon['expires_at'] ? date('Y-m-d', strtotime($editCoupon['expires_at'])) : '' ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= ($editCoupon['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="inactive" <?= ($editCoupon['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($editCoupon): ?>
    <script>new bootstrap.Modal(document.getElementById('couponModal')).show();</script>
    <?php endif; ?>
</body>
</html>
