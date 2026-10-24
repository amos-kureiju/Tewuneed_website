<?php
/**
 * Admin Users Management
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

requireAdminLogin();

$db = getDB();

// Handle status change
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] === 'activate') {
        $db->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$id]);
    } elseif ($_GET['action'] === 'ban') {
        $db->prepare("UPDATE users SET status = 'banned' WHERE id = ?")->execute([$id]);
    }
    header('Location: users.php');
    exit;
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$search = sanitize($_GET['search'] ?? '');
$statusFilter = sanitize($_GET['status'] ?? '');

$where = [];
$params = [];

if ($search) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}
if ($statusFilter) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();

$stmt = $db->prepare("SELECT COUNT(*) FROM users $whereClause");
$stmt->execute($params);
$totalUsers = $stmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - Admin <?= SITE_NAME ?></title>
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
                <li class="nav-item"><a class="nav-link active" href="users.php"><i class="fas fa-users"></i>Pelanggan</a></li>
                <li class="nav-item"><a class="nav-link" href="coupons.php"><i class="fas fa-ticket-alt"></i>Kupon</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i>Pengaturan</a></li>
                <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main-content">
        <h4 class="mb-4">Kelola Pelanggan</h4>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                            <option value="banned" <?= $statusFilter === 'banned' ? 'selected' : '' ?>>Banned</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Pelanggan</th>
                            <th class="border-0">Email</th>
                            <th class="border-0">Telepon</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0">Terdaftar</th>
                            <th class="border-0 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3" style="width: 40px; height: 40px;">
                                        <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : ($user['status'] === 'banned' ? 'danger' : 'secondary') ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                            <td class="text-center">
                                <?php if ($user['status'] === 'active'): ?>
                                <a href="?action=ban&id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Ban user ini?')"><i class="fas fa-ban"></i></a>
                                <?php else: ?>
                                <a href="?action=activate&id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada pelanggan</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $statusFilter ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
