<?php
/**
 * Cart API
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        
        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
        }
        
        $product = getProductById($productId);
        if (!$product) {
            jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }
        
        // Check if product is active
        if ($product['status'] !== 'active') {
            jsonResponse(['success' => false, 'message' => 'Produk tidak tersedia'], 400);
        }
        
        // Check stock availability
        if ($product['stock_quantity'] <= 0) {
            jsonResponse(['success' => false, 'message' => 'Produk habis'], 400);
        }
        
        if ($product['stock_quantity'] < $quantity) {
            jsonResponse(['success' => false, 'message' => 'Stok tersisa: ' . $product['stock_quantity']], 400);
        }
        
        if (addToCart($productId, $quantity)) {
            jsonResponse([
                'success' => true, 
                'message' => 'Produk berhasil ditambahkan ke keranjang',
                'cart_count' => getCartCount()
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal menambahkan ke keranjang'], 500);
        }
        break;
        
    case 'update':
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
        }
        
        if (updateCartQuantity($productId, $quantity)) {
            jsonResponse([
                'success' => true,
                'message' => 'Jumlah berhasil diperbarui',
                'cart_count' => getCartCount(),
                'cart_total' => getCartTotal()
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal memperbarui jumlah'], 500);
        }
        break;
        
    case 'remove':
        $productId = intval($_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
        }
        
        if (removeFromCart($productId)) {
            jsonResponse([
                'success' => true,
                'message' => 'Produk dihapus dari keranjang',
                'cart_count' => getCartCount(),
                'cart_total' => getCartTotal()
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal menghapus produk'], 500);
        }
        break;
        
    case 'clear':
        if (clearCart()) {
            jsonResponse([
                'success' => true,
                'message' => 'Keranjang dikosongkan',
                'cart_count' => 0
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal mengosongkan keranjang'], 500);
        }
        break;
        
    case 'get':
        $items = getCartItems();
        jsonResponse([
            'success' => true,
            'items' => $items,
            'cart_count' => getCartCount(),
            'cart_total' => getCartTotal()
        ]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak valid'], 400);
}
?>
