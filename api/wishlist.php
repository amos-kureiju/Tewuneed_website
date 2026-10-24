<?php
/**
 * Wishlist API
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isLoggedIn()) {
    jsonResponse([
        'success' => false, 
        'message' => 'Silakan login terlebih dahulu',
        'redirect' => true
    ], 401);
}

switch ($action) {
    case 'toggle':
        $productId = intval($_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
        }
        
        $wasInWishlist = isInWishlist($productId);
        
        if (toggleWishlist($productId)) {
            jsonResponse([
                'success' => true,
                'message' => $wasInWishlist ? 'Produk dihapus dari wishlist' : 'Produk ditambahkan ke wishlist',
                'in_wishlist' => !$wasInWishlist
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal memperbarui wishlist'], 500);
        }
        break;
        
    case 'add':
        $productId = intval($_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
        }
        
        if (isInWishlist($productId)) {
            jsonResponse(['success' => true, 'message' => 'Produk sudah ada di wishlist']);
        }
        
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        
        if ($stmt->execute([$_SESSION['user_id'], $productId])) {
            jsonResponse(['success' => true, 'message' => 'Produk ditambahkan ke wishlist']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal menambahkan ke wishlist'], 500);
        }
        break;
        
    case 'remove':
        $productId = intval($_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
        }
        
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        
        if ($stmt->execute([$_SESSION['user_id'], $productId])) {
            jsonResponse(['success' => true, 'message' => 'Produk dihapus dari wishlist']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal menghapus dari wishlist'], 500);
        }
        break;
        
    case 'get':
        $items = getWishlistItems();
        jsonResponse([
            'success' => true,
            'items' => $items,
            'count' => count($items)
        ]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak valid'], 400);
}
?>
