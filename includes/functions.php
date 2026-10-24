<?php
/**
 * Helper Functions
 * TeWuNeed E-Commerce Website
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Format price to Indonesian Rupiah
 * @param float $price
 * @return string
 */
function formatRupiah($price) {
    // Handle null, empty, or non-numeric values
    if ($price === null || $price === '' || !is_numeric($price)) {
        $price = 0;
    }
    // Convert to float to ensure proper formatting
    $price = floatval($price);
    return CURRENCY_SYMBOL . number_format($price, 0, ',', '.');
}

/**
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Hash password
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate unique slug
 * @param string $text
 * @return string
 */
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Generate order number
 * @return string
 */
function generateOrderNumber() {
    return 'TWN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 * @return bool
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Require user login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Save only the path, will be combined with SITE_URL during redirect
        $_SESSION['redirect_path'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

/**
 * Require admin login
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Get current admin data
 * @return array|null
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

/**
 * Flash message functions
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $alertClass = '';
        switch ($flash['type']) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
            case 'danger':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            default:
                $alertClass = 'alert-info';
        }
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">'
            . htmlspecialchars($flash['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

/**
 * Get all categories
 * @return array
 */
function getCategories() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get products by category
 * @param int|null $categoryId
 * @param int $limit
 * @param int $offset
 * @return array
 */
function getProducts($categoryId = null, $limit = ITEMS_PER_PAGE, $offset = 0, $sort = 'newest') {
    $db = getDB();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    
    $params = [];
    
    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }
    
    // Sorting
    $orderBy = "p.created_at DESC";
    if ($sort === 'price_low') {
        $orderBy = "COALESCE(p.sale_price, p.price) ASC";
    } elseif ($sort === 'price_high') {
        $orderBy = "COALESCE(p.sale_price, p.price) DESC";
    } elseif ($sort === 'popular') {
        $orderBy = "p.sold_count DESC, p.views DESC, p.created_at DESC";
    } elseif ($sort === 'name') {
        $orderBy = "p.name ASC";
    }
    
    $sql .= " ORDER BY $orderBy LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get featured products
 * @param int $limit
 * @return array
 */
function getFeaturedProducts($limit = 12) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active' AND p.featured = 1 
        ORDER BY p.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get product by ID
 * @param int $id
 * @return array|null
 */
function getProductById($id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get product by slug
 * @param string $slug
 * @return array|null
 */
function getProductBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.slug = ?
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Get product reviews
 * @param int $productId
 * @return array
 */
function getProductReviews($productId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT r.*, u.first_name, u.last_name 
        FROM product_reviews r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? AND r.status = 'approved'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

/**
 * Get product average rating
 * @param int $productId
 * @return float
 */
function getProductRating($productId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT AVG(rating) as avg_rating 
        FROM product_reviews 
        WHERE product_id = ? AND status = 'approved'
    ");
    $stmt->execute([$productId]);
    $result = $stmt->fetch();
    return $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
}

/**
 * Render star rating
 * @param float $rating
 * @return string
 */
function renderStars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return $html . '<span class="ms-1 text-muted">(' . $rating . ')</span>';
}

/**
 * Get cart items count
 * @return int
 */
function getCartCount() {
    if (!isLoggedIn()) {
        return isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get cart items
 * @return array
 */
function getCartItems() {
    if (!isLoggedIn()) {
        $cart = $_SESSION['cart'] ?? [];
        $items = [];
        foreach ($cart as $productId => $item) {
            $product = getProductById($productId);
            if ($product) {
                $items[] = [
                    'product' => $product,
                    'quantity' => $item['quantity']
                ];
            }
        }
        return $items;
    }
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.id, c.product_id, c.quantity, c.created_at,
               p.id as pid, p.name, p.price, p.sale_price, p.image, p.stock_quantity, p.slug 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll();
}

/**
 * Calculate cart total
 * @return float
 */
function getCartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        if (isLoggedIn()) {
            $price = $item['sale_price'] ?? $item['price'];
            $total += $price * $item['quantity'];
        } else {
            $price = $item['product']['sale_price'] ?? $item['product']['price'];
            $total += $price * $item['quantity'];
        }
    }
    return $total;
}

/**
 * Add to cart
 * @param int $productId
 * @param int $quantity
 * @return bool
 */
function addToCart($productId, $quantity = 1) {
    $product = getProductById($productId);
    if (!$product || $product['stock_quantity'] < $quantity) {
        return false;
    }
    
    if (!isLoggedIn()) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = ['quantity' => $quantity];
        }
        return true;
    }
    
    $db = getDB();
    
    // Check if product already in cart
    $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        $stmt = $db->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        return $stmt->execute([$quantity, $existingItem['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$_SESSION['user_id'], $productId, $quantity]);
    }
}

/**
 * Remove from cart
 * @param int $productId
 * @return bool
 */
function removeFromCart($productId) {
    if (!isLoggedIn()) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }
    
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$_SESSION['user_id'], $productId]);
}

/**
 * Update cart quantity
 * @param int $productId
 * @param int $quantity
 * @return bool
 */
function updateCartQuantity($productId, $quantity) {
    if ($quantity <= 0) {
        return removeFromCart($productId);
    }
    
    $product = getProductById($productId);
    if (!$product || $product['stock_quantity'] < $quantity) {
        return false;
    }
    
    if (!isLoggedIn()) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
            return true;
        }
        return false;
    }
    
    $db = getDB();
    $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$quantity, $_SESSION['user_id'], $productId]);
}

/**
 * Clear cart
 * @return bool
 */
function clearCart() {
    if (!isLoggedIn()) {
        $_SESSION['cart'] = [];
        return true;
    }
    
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    return $stmt->execute([$_SESSION['user_id']]);
}

/**
 * Get user orders
 * @param int $userId
 * @return array
 */
function getUserOrders($userId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get order details
 * @param int $orderId
 * @return array|null
 */
function getOrderById($orderId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetch();
}

/**
 * Get order items
 * @param int $orderId
 * @return array
 */
function getOrderItems($orderId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT oi.*, p.name, p.image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

/**
 * Get wishlist items
 * @return array
 */
function getWishlistItems() {
    if (!isLoggedIn()) {
        return [];
    }
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT w.*, p.* 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll();
}

/**
 * Check if product is in wishlist
 * @param int $productId
 * @return bool
 */
function isInWishlist($productId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    return $stmt->fetch() !== false;
}

/**
 * Toggle wishlist item
 * @param int $productId
 * @return bool
 */
function toggleWishlist($productId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $db = getDB();
    
    if (isInWishlist($productId)) {
        $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$_SESSION['user_id'], $productId]);
    } else {
        $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        return $stmt->execute([$_SESSION['user_id'], $productId]);
    }
}

/**
 * JSON response helper
 * @param array $data
 * @param int $statusCode
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get product image URL
 * @param string|null $image
 * @return string
 */
function getProductImage($image) {
    // If no image specified, return placeholder
    if (empty($image)) {
        return SITE_URL . '/Images/placeholder.jpg';
    }
    
    $cleanImage = ltrim($image, '/\\');
    
    // 1. Direct path inside Images/ (e.g. 'home gambar/abc.jpg' or 'products/xyz.jpeg' or 'Elektronik.jpg')
    if (file_exists(__DIR__ . '/../Images/' . $cleanImage)) {
        return SITE_URL . '/Images/' . str_replace('\\', '/', $cleanImage);
    }
    
    // 2. Relative inside Images/products/
    if (file_exists(__DIR__ . '/../Images/products/' . $cleanImage)) {
        return SITE_URL . '/Images/products/' . str_replace('\\', '/', $cleanImage);
    }
    
    // If file not found, return placeholder
    return SITE_URL . '/Images/placeholder.jpg';
}

/**
 * Validate coupon code
 * @param string $code
 * @return array|false
 */
function validateCoupon($code) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM coupons 
        WHERE code = ? 
        AND status = 'active' 
        AND (expires_at IS NULL OR expires_at > NOW())
        AND (max_uses = 0 OR used_count < max_uses)
    ");
    $stmt->execute([$code]);
    return $stmt->fetch();
}

/**
 * Apply coupon discount
 * @param float $total
 * @param array $coupon
 * @return float
 */
function applyCouponDiscount($total, $coupon) {
    if ($total < $coupon['min_amount']) {
        return 0;
    }
    
    if ($coupon['type'] === 'percentage') {
        return $total * ($coupon['value'] / 100);
    } else {
        return min($coupon['value'], $total);
    }
}

/**
 * CSRF token functions
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfInput() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

/**
 * Get payment method instructions & details logically based on selected method
 * @param string $methodName
 * @param float $totalAmount
 * @param string $paymentStatus
 * @return array
 */
function getPaymentMethodDetails($methodName, $totalAmount, $paymentStatus = 'pending') {
    $methodLower = strtolower($methodName ?? '');
    
    // 1. Cash on Delivery (COD)
    if (strpos($methodLower, 'cod') !== false || strpos($methodLower, 'tempat') !== false) {
        return [
            'type' => 'cod',
            'title' => 'COD (Bayar di Tempat)',
            'badge_class' => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
            'card_border' => 'border-info',
            'card_header' => 'bg-info text-white',
            'icon' => 'fa-hand-holding-dollar',
            'headline' => 'Siapkan Uang Tunai Saat Pesanan Tiba',
            'is_transfer_required' => false,
            'amount' => $totalAmount,
            'details' => [
                'Metode Pembayaran' => 'Cash on Delivery (COD)',
                'Total yang Harus Disiapkan' => formatRupiah($totalAmount),
                'Waktu Pembayaran' => 'Saat kurir menyerahkan paket di alamat Anda'
            ],
            'note' => 'Anda memilih metode Bayar di Tempat (COD). Anda <strong>TIDAK PERLU</strong> melakukan transfer bank sekarang. Mohon siapkan uang tunai pas untuk mempermudah transaksi dengan kurir saat pengantaran pesanan.'
        ];
    }
    
    // 2. Transfer Bank Mandiri
    if (strpos($methodLower, 'mandiri') !== false) {
        return [
            'type' => 'bank_transfer',
            'title' => 'Transfer Bank Mandiri',
            'badge_class' => 'bg-warning bg-opacity-10 text-dark border border-warning',
            'card_border' => 'border-primary',
            'card_header' => 'bg-primary text-white',
            'icon' => 'fa-building-columns',
            'headline' => 'Transfer ke Rekening Bank Mandiri Resmi',
            'is_transfer_required' => true,
            'amount' => $totalAmount,
            'details' => [
                'Bank Tujuan' => 'Bank Mandiri',
                'Nomor Rekening' => '137-00-1234567-8',
                'Atas Nama' => 'PT TEWUNEED INDONESIA',
                'Jumlah Transfer' => formatRupiah($totalAmount)
            ],
            'note' => 'Silakan selesaikan pembayaran dalam waktu 24 jam via Livin\' by Mandiri, ATM, atau Internet Banking agar pesanan segera diproses.'
        ];
    }
    
    // 3. Transfer Bank BNI
    if (strpos($methodLower, 'bni') !== false) {
        return [
            'type' => 'bank_transfer',
            'title' => 'Transfer Bank BNI',
            'badge_class' => 'bg-warning bg-opacity-10 text-dark border border-warning',
            'card_border' => 'border-primary',
            'card_header' => 'bg-primary text-white',
            'icon' => 'fa-building-columns',
            'headline' => 'Transfer ke Rekening Bank BNI Resmi',
            'is_transfer_required' => true,
            'amount' => $totalAmount,
            'details' => [
                'Bank Tujuan' => 'Bank BNI',
                'Nomor Rekening' => '089-123-4567',
                'Atas Nama' => 'PT TEWUNEED INDONESIA',
                'Jumlah Transfer' => formatRupiah($totalAmount)
            ],
            'note' => 'Silakan selesaikan pembayaran dalam waktu 24 jam via BNI Mobile Banking, ATM, atau SMS Banking.'
        ];
    }
    
    // 4. Transfer Bank BCA
    if (strpos($methodLower, 'bca') !== false) {
        return [
            'type' => 'bank_transfer',
            'title' => 'Transfer Bank BCA',
            'badge_class' => 'bg-warning bg-opacity-10 text-dark border border-warning',
            'card_border' => 'border-primary',
            'card_header' => 'bg-primary text-white',
            'icon' => 'fa-building-columns',
            'headline' => 'Transfer ke Rekening Bank BCA Resmi',
            'is_transfer_required' => true,
            'amount' => $totalAmount,
            'details' => [
                'Bank Tujuan' => 'Bank Central Asia (BCA)',
                'Nomor Rekening' => '123-456-7890',
                'Atas Nama' => 'PT TEWUNEED INDONESIA',
                'Jumlah Transfer' => formatRupiah($totalAmount)
            ],
            'note' => 'Silakan selesaikan pembayaran dalam waktu 24 jam via myBCA, BCA Mobile, KlikBCA, atau ATM BCA.'
        ];
    }
    
    // 5. GoPay
    if (strpos($methodLower, 'gopay') !== false) {
        return [
            'type' => 'e_wallet',
            'title' => 'Pembayaran GoPay',
            'badge_class' => 'bg-success bg-opacity-10 text-success border border-success',
            'card_border' => 'border-success',
            'card_header' => 'bg-success text-white',
            'icon' => 'fa-wallet',
            'headline' => 'Transfer Saldo GoPay / QRIS Toko',
            'is_transfer_required' => true,
            'amount' => $totalAmount,
            'details' => [
                'Layanan' => 'GoPay Bisnis',
                'Nomor Akun / HP' => '0812-9876-5432',
                'Nama Merchant' => 'TeWuNeed Official Store',
                'Jumlah Bayar' => formatRupiah($totalAmount)
            ],
            'note' => 'Buka aplikasi Gojek / GoPay, pilih Kirim / Transfer Saldo ke nomor merchant di atas atau scan QRIS toko.'
        ];
    }
    
    // 6. OVO
    if (strpos($methodLower, 'ovo') !== false) {
        return [
            'type' => 'e_wallet',
            'title' => 'Pembayaran OVO',
            'badge_class' => 'bg-primary bg-opacity-10 text-primary border border-primary',
            'card_border' => 'border-primary',
            'card_header' => 'bg-primary text-white',
            'icon' => 'fa-wallet',
            'headline' => 'Transfer Saldo OVO Merchant',
            'is_transfer_required' => true,
            'amount' => $totalAmount,
            'details' => [
                'Layanan' => 'OVO Merchant',
                'Nomor OVO' => '0812-9876-5432',
                'Nama Merchant' => 'TeWuNeed Official Store',
                'Jumlah Bayar' => formatRupiah($totalAmount)
            ],
            'note' => 'Buka aplikasi OVO Anda, pilih menu Transfer ke nomor akun merchant di atas dengan nominal yang sesuai.'
        ];
    }
    
    // 7. DANA
    if (strpos($methodLower, 'dana') !== false) {
        return [
            'type' => 'e_wallet',
            'title' => 'Pembayaran DANA',
            'badge_class' => 'bg-info bg-opacity-10 text-info border border-info',
            'card_border' => 'border-info',
            'card_header' => 'bg-info text-white',
            'icon' => 'fa-wallet',
            'headline' => 'Kirim Saldo DANA Bisnis',
            'is_transfer_required' => true,
            'amount' => $totalAmount,
            'details' => [
                'Layanan' => 'DANA Bisnis',
                'Nomor DANA' => '0812-9876-5432',
                'Nama Akun' => 'TeWuNeed Official Store',
                'Jumlah Bayar' => formatRupiah($totalAmount)
            ],
            'note' => 'Buka aplikasi DANA, pilih menu Kirim Uang ke nomor di atas dengan nominal yang sesuai.'
        ];
    }
    
    // Default Fallback
    return [
        'type' => 'bank_transfer',
        'title' => htmlspecialchars($methodName),
        'badge_class' => 'bg-warning bg-opacity-10 text-dark border border-warning',
        'card_border' => 'border-primary',
        'card_header' => 'bg-primary text-white',
        'icon' => 'fa-credit-card',
        'headline' => 'Selesaikan Pembayaran Pesanan',
        'is_transfer_required' => true,
        'amount' => $totalAmount,
        'details' => [
            'Metode Pembayaran' => htmlspecialchars($methodName),
            'Nomor Rekening / Akun' => '123-456-7890',
            'Atas Nama' => 'PT TEWUNEED INDONESIA',
            'Jumlah Pembayaran' => formatRupiah($totalAmount)
        ],
        'note' => 'Silakan lakukan pembayaran sesuai dengan total belanja pesanan Anda.'
    ];
}
?>
