<?php
/**
 * Address API
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
    case 'add':
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address_line_1'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $state = sanitize($_POST['state'] ?? '');
        $postalCode = sanitize($_POST['postal_code'] ?? '');
        $type = sanitize($_POST['type'] ?? 'home');
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        
        if (empty($firstName) || empty($lastName) || empty($phone) || empty($address) || empty($city) || empty($state) || empty($postalCode)) {
            jsonResponse(['success' => false, 'message' => 'Semua field wajib diisi'], 400);
        }
        
        // If setting as default, unset other defaults
        if ($isDefault) {
            $stmt = $db->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        }
        
        $stmt = $db->prepare("
            INSERT INTO user_addresses (user_id, type, first_name, last_name, phone, address_line_1, city, state, postal_code, is_default) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $type, $firstName, $lastName, $phone, $address, $city, $state, $postalCode, $isDefault])) {
            jsonResponse(['success' => true, 'message' => 'Alamat berhasil ditambahkan', 'id' => $db->lastInsertId()]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal menambahkan alamat'], 500);
        }
        break;
        
    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address_line_1'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $state = sanitize($_POST['state'] ?? '');
        $postalCode = sanitize($_POST['postal_code'] ?? '');
        $type = sanitize($_POST['type'] ?? 'home');
        
        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID alamat tidak valid'], 400);
        }
        
        $stmt = $db->prepare("
            UPDATE user_addresses 
            SET type = ?, first_name = ?, last_name = ?, phone = ?, address_line_1 = ?, city = ?, state = ?, postal_code = ?
            WHERE id = ? AND user_id = ?
        ");
        
        if ($stmt->execute([$type, $firstName, $lastName, $phone, $address, $city, $state, $postalCode, $id, $_SESSION['user_id']])) {
            jsonResponse(['success' => true, 'message' => 'Alamat berhasil diperbarui']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal memperbarui alamat'], 500);
        }
        break;
        
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID alamat tidak valid'], 400);
        }
        
        $stmt = $db->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        
        if ($stmt->execute([$id, $_SESSION['user_id']])) {
            jsonResponse(['success' => true, 'message' => 'Alamat berhasil dihapus']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal menghapus alamat'], 500);
        }
        break;
        
    case 'set_default':
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID alamat tidak valid'], 400);
        }
        
        // Unset all defaults
        $stmt = $db->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Set new default
        $stmt = $db->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
        
        if ($stmt->execute([$id, $_SESSION['user_id']])) {
            jsonResponse(['success' => true, 'message' => 'Alamat utama berhasil diubah']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal mengubah alamat utama'], 500);
        }
        break;
        
    case 'get':
        $stmt = $db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $addresses = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'addresses' => $addresses]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak valid'], 400);
}
?>
