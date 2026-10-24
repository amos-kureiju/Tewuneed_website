<?php
/**
 * Orders API
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu'], 401);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {
    case 'cancel':
        $orderId = intval($_POST['order_id'] ?? 0);
        
        if ($orderId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID pesanan tidak valid'], 400);
        }
        
        // Verify order belongs to user and is pending
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }
        
        if ($order['status'] !== 'pending') {
            jsonResponse(['success' => false, 'message' => 'Pesanan tidak dapat dibatalkan'], 400);
        }
        
        try {
            $db->beginTransaction();
            
            // Update order status
            $stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$orderId]);
            
            // Add status history
            $stmt = $db->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'cancelled', 'Cancelled by customer')");
            $stmt->execute([$orderId]);
            
            // Restore stock
            $items = getOrderItems($orderId);
            foreach ($items as $item) {
                $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            $db->commit();
            
            jsonResponse(['success' => true, 'message' => 'Pesanan berhasil dibatalkan']);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['success' => false, 'message' => 'Gagal membatalkan pesanan'], 500);
        }
        break;
        
    case 'confirm_received':
        $orderId = intval($_POST['order_id'] ?? 0);
        
        if ($orderId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID pesanan tidak valid'], 400);
        }
        
        // Verify order belongs to user
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }
        
        if ($order['status'] !== 'delivered') {
            jsonResponse(['success' => false, 'message' => 'Pesanan belum dikirim'], 400);
        }
        
        // Update order status
        $stmt = $db->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $stmt->execute([$orderId]);
        
        // Add status history
        $stmt = $db->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'completed', 'Confirmed received by customer')");
        $stmt->execute([$orderId]);
        
        jsonResponse(['success' => true, 'message' => 'Pesanan telah selesai']);
        break;
        
    case 'get':
        $orders = getUserOrders($_SESSION['user_id']);
        jsonResponse(['success' => true, 'orders' => $orders]);
        break;
        
    case 'detail':
        $orderId = intval($_GET['id'] ?? 0);
        
        if ($orderId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID pesanan tidak valid'], 400);
        }
        
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }
        
        $items = getOrderItems($orderId);
        
        jsonResponse([
            'success' => true,
            'order' => $order,
            'items' => $items
        ]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak valid'], 400);
}
?>
