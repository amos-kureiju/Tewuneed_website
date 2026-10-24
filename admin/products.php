<?php
/**
 * Admin Products Management
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

requireAdminLogin();

$db = getDB();
$admin = getCurrentAdmin();

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$error = '';

// Delete product
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $db->prepare("UPDATE products SET status = 'inactive' WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = 'Produk berhasil dihapus';
    } else {
        $error = 'Gagal menghapus produk';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $salePrice = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $stock = intval($_POST['stock_quantity'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $shortDescription = sanitize($_POST['short_description'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? 'active');
    
    if (empty($name) || $price <= 0) {
        $error = 'Nama produk dan harga wajib diisi';
    } else {
        $slug = generateSlug($name);
        
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ALLOWED_EXTENSIONS)) {
                $image = uniqid('product_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], '../Images/products/' . $image);
            }
        }
        
        if ($productId > 0) {
            // Update
            $sql = "UPDATE products SET name = ?, slug = ?, category_id = ?, price = ?, sale_price = ?, stock_quantity = ?, description = ?, short_description = ?, featured = ?, status = ?";
            $params = [$name, $slug, $categoryId ?: null, $price, $salePrice, $stock, $description, $shortDescription, $featured, $status];
            
            if ($image) {
                $sql .= ", image = ?";
                $params[] = $image;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $productId;
            
            $stmt = $db->prepare($sql);
            if ($stmt->execute($params)) {
                $message = 'Produk berhasil diperbarui';
            } else {
                $error = 'Gagal memperbarui produk';
            }
        } else {
            // Insert
            $stmt = $db->prepare("INSERT INTO products (name, slug, category_id, price, sale_price, stock_quantity, description, short_description, image, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $slug, $categoryId ?: null, $price, $salePrice, $stock, $description, $shortDescription, $image, $featured, $status])) {
                $message = 'Produk berhasil ditambahkan';
            } else {
                $error = 'Gagal menambahkan produk';
            }
        }
    }
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

// Search & Filter
$search = sanitize($_GET['search'] ?? '');
$categoryFilter = intval($_GET['category'] ?? 0);
$statusFilter = sanitize($_GET['status'] ?? '');

$where = [];
$params = [];

if ($search) {
    $where[] = "(name LIKE ? OR sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($categoryFilter > 0) {
    $where[] = "category_id = ?";
    $params[] = $categoryFilter;
}
if ($statusFilter) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$stmt = $db->prepare("SELECT COUNT(*) FROM products $whereClause");
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Get products
$stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for dropdown
$categories = getCategories();

// Edit mode
$editProduct = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([intval($_GET['id'])]);
    $editProduct = $stmt->fetch();
}

$pageTitle = 'Kelola Produk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Admin <?= SITE_NAME ?></title>
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
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="products.php"><i class="fas fa-box"></i>Produk</a></li>
                <li class="nav-item"><a class="nav-link" href="categories.php"><i class="fas fa-tags"></i>Kategori</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php"><i class="fas fa-shopping-cart"></i>Pesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i>Pelanggan</a></li>
                <li class="nav-item"><a class="nav-link" href="coupons.php"><i class="fas fa-ticket-alt"></i>Kupon</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i>Pengaturan</a></li>
                <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Kelola Produk</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                <i class="fas fa-plus me-2"></i>Tambah Produk
            </button>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                            <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Products Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">Produk</th>
                                <th class="border-0">Kategori</th>
                                <th class="border-0 text-end">Harga</th>
                                <th class="border-0 text-center">Stok</th>
                                <th class="border-0 text-center">Status</th>
                                <th class="border-0 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= getProductImage($product['image']) ?>" alt="" class="product-img me-3">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($product['name']) ?></div>
                                            <small class="text-muted"><?= $product['sku'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                                <td class="text-end">
                                    <?php if ($product['sale_price']): ?>
                                    <span class="text-danger"><?= formatRupiah($product['sale_price']) ?></span>
                                    <br><small class="text-muted text-decoration-line-through"><?= formatRupiah($product['price']) ?></small>
                                    <?php else: ?>
                                    <?= formatRupiah($product['price']) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $product['stock_quantity'] > 10 ? 'success' : ($product['stock_quantity'] > 0 ? 'warning' : 'danger') ?>">
                                        <?= $product['stock_quantity'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $product['status'] === 'active' ? 'success' : ($product['status'] === 'draft' ? 'secondary' : 'danger') ?>">
                                        <?= ucfirst($product['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="?action=edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <a href="?action=delete&id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus produk ini?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada produk</td>
                            </tr>
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
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $categoryFilter ?>&status=<?= $statusFilter ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $editProduct ? 'Edit Produk' : 'Tambah Produk' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfInput() ?>
                    <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?? '' ?>">
                    
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nama Produk *</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Harga *</label>
                                <input type="number" name="price" class="form-control" value="<?= $editProduct['price'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Harga Diskon</label>
                                <input type="number" name="sale_price" class="form-control" value="<?= $editProduct['sale_price'] ?? '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stok *</label>
                                <input type="number" name="stock_quantity" class="form-control" value="<?= $editProduct['stock_quantity'] ?? 0 ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi Singkat</label>
                                <textarea name="short_description" class="form-control" rows="2"><?= htmlspecialchars($editProduct['short_description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi Lengkap</label>
                                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gambar Produk</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= ($editProduct['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="draft" <?= ($editProduct['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="inactive" <?= ($editProduct['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="checkbox" name="featured" class="form-check-input" id="featured" <?= ($editProduct['featured'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="featured">Produk Unggulan</label>
                                </div>
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
    <?php if ($editProduct): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('productModal')).show();
        });
    </script>
    <?php endif; ?>
</body>
</html>
