<?php
/**
 * Admin Categories Management
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

requireAdminLogin();

$db = getDB();
$message = '';
$error = '';

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $db->prepare("UPDATE categories SET status = 'inactive' WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = 'Kategori berhasil dihapus';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = intval($_POST['category_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');
    
    if (empty($name)) {
        $error = 'Nama kategori wajib diisi';
    } else {
        $slug = generateSlug($name);
        
        if ($categoryId > 0) {
            $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, status = ? WHERE id = ?");
            if ($stmt->execute([$name, $slug, $description, $status, $categoryId])) {
                $message = 'Kategori berhasil diperbarui';
            }
        } else {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, description, status) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $slug, $description, $status])) {
                $message = 'Kategori berhasil ditambahkan';
            }
        }
    }
}

// Get categories
$stmt = $db->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 'active') as product_count FROM categories c ORDER BY c.name");
$categories = $stmt->fetchAll();

// Edit mode
$editCategory = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([intval($_GET['id'])]);
    $editCategory = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - Admin <?= SITE_NAME ?></title>
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
                <li class="nav-item"><a class="nav-link active" href="categories.php"><i class="fas fa-tags"></i>Kategori</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php"><i class="fas fa-shopping-cart"></i>Pesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i>Pelanggan</a></li>
                <li class="nav-item"><a class="nav-link" href="coupons.php"><i class="fas fa-ticket-alt"></i>Kupon</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i>Pengaturan</a></li>
                <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Kelola Kategori</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="fas fa-plus me-2"></i>Tambah Kategori
            </button>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Nama</th>
                            <th class="border-0">Slug</th>
                            <th class="border-0">Deskripsi</th>
                            <th class="border-0 text-center">Produk</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($cat['name']) ?></td>
                            <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                            <td><small class="text-muted"><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 50)) ?>...</small></td>
                            <td class="text-center"><span class="badge bg-primary"><?= $cat['product_count'] ?></span></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $cat['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($cat['status']) ?></span>
                            </td>
                            <td class="text-center">
                                <a href="?action=edit&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <a href="?action=delete&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="category_id" value="<?= $editCategory['id'] ?? '' ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= $editCategory ? 'Edit' : 'Tambah' ?> Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editCategory['description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($editCategory['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                <option value="inactive" <?= ($editCategory['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
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
    <?php if ($editCategory): ?>
    <script>new bootstrap.Modal(document.getElementById('categoryModal')).show();</script>
    <?php endif; ?>
</body>
</html>
