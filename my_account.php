<?php
/**
 * My Account Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        
        if (empty($firstName) || empty($lastName)) {
            $error = 'Nama depan dan nama belakang wajib diisi';
        } else {
            $db = getDB();
            $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
            if ($stmt->execute([$firstName, $lastName, $phone, $_SESSION['user_id']])) {
                $success = 'Profil berhasil diperbarui';
                $user = getCurrentUser();
            } else {
                $error = 'Gagal memperbarui profil';
            }
        }
    }
    
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Semua field password wajib diisi';
        } elseif (!verifyPassword($currentPassword, $user['password'])) {
            $error = 'Password saat ini salah';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password baru minimal 6 karakter';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Konfirmasi password tidak cocok';
        } else {
            $db = getDB();
            $hashedPassword = hashPassword($newPassword);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                $success = 'Password berhasil diubah';
            } else {
                $error = 'Gagal mengubah password';
            }
        }
    }
}

// Get statistics
$db = getDB();
$stmt = $db->prepare("SELECT COUNT(*) as total_orders, SUM(total_amount) as total_spent FROM orders WHERE user_id = ? AND status != 'cancelled'");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

$stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$wishlistCount = $stmt->fetchColumn();

// Get addresses
$stmt = $db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$_SESSION['user_id']]);
$addresses = $stmt->fetchAll();

$pageTitle = 'Akun Saya';

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-user me-2"></i>Akun Saya</h2>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <!-- Profile Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-user fa-2x text-white"></i>
                    </div>
                    <h5 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                    <p class="text-muted small mb-0"><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Pesanan</span>
                        <strong><?= $stats['total_orders'] ?? 0 ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Belanja</span>
                        <strong class="text-primary"><?= formatRupiah($stats['total_spent'] ?? 0) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Wishlist</span>
                        <strong><?= $wishlistCount ?> item</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="accountTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile">
                        <i class="fas fa-user me-1"></i>Profil
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#addresses">
                        <i class="fas fa-map-marker-alt me-1"></i>Alamat
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security">
                        <i class="fas fa-lock me-1"></i>Keamanan
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Informasi Profil</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?= csrfInput() ?>
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Depan *</label>
                                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Belakang *</label>
                                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                        <small class="text-muted">Email tidak dapat diubah</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Telepon</label>
                                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Addresses Tab -->
                <div class="tab-pane fade" id="addresses">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Alamat Tersimpan</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="fas fa-plus me-1"></i>Tambah Alamat
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (empty($addresses)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada alamat tersimpan</p>
                            </div>
                            <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($addresses as $addr): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 <?= $addr['is_default'] ? 'border-primary' : '' ?>">
                                        <div class="card-body">
                                            <?php if ($addr['is_default']): ?>
                                            <span class="badge bg-primary mb-2">Alamat Utama</span>
                                            <?php endif; ?>
                                            <h6><?= htmlspecialchars($addr['first_name'] . ' ' . $addr['last_name']) ?></h6>
                                            <p class="text-muted small mb-0">
                                                <?= htmlspecialchars($addr['address_line_1']) ?><br>
                                                <?= htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' ' . $addr['postal_code']) ?><br>
                                                <?= htmlspecialchars($addr['phone']) ?>
                                            </p>
                                        </div>
                                        <div class="card-footer bg-white border-top-0">
                                            <button class="btn btn-outline-primary btn-sm" onclick="editAddress(<?= $addr['id'] ?>)">Edit</button>
                                            <button class="btn btn-outline-danger btn-sm" onclick="deleteAddress(<?= $addr['id'] ?>)">Hapus</button>
                                            <?php if (!$addr['is_default']): ?>
                                            <button class="btn btn-outline-secondary btn-sm" onclick="setDefaultAddress(<?= $addr['id'] ?>)">Jadikan Utama</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Security Tab -->
                <div class="tab-pane fade" id="security">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Ubah Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?= csrfInput() ?>
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Password Saat Ini *</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password Baru *</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Konfirmasi Password Baru *</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-key me-2"></i>Ubah Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
            showToast(data.message, 'error');
        }
    });
});

function deleteAddress(id) {
    if (!confirm('Hapus alamat ini?')) return;
    
    $.ajax({
        url: SITE_URL + '/api/address.php',
        method: 'POST',
        data: { action: 'delete', id: id },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                showToast(response.message, 'error');
            }
        }
    });
}

function setDefaultAddress(id) {
    $.ajax({
        url: SITE_URL + '/api/address.php',
        method: 'POST',
        data: { action: 'set_default', id: id },
        success: function(response) {
            if (response.success) {
                location.reload();
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
