<?php
/**
 * Coupon API
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

header('Content-Type: application/json');

$code = sanitize($_POST['code'] ?? $_GET['code'] ?? '');

if (empty($code)) {
    jsonResponse(['success' => false, 'message' => 'Kode kupon diperlukan'], 400);
}

$coupon = validateCoupon($code);

if (!$coupon) {
    jsonResponse(['success' => false, 'message' => 'Kode kupon tidak valid atau sudah kadaluarsa'], 404);
}

$cartTotal = getCartTotal();

if ($cartTotal < $coupon['min_amount']) {
    jsonResponse([
        'success' => false, 
        'message' => 'Minimum pembelian ' . formatRupiah($coupon['min_amount']) . ' untuk menggunakan kupon ini'
    ], 400);
}

$discount = applyCouponDiscount($cartTotal, $coupon);

// Store coupon in session
$_SESSION['applied_coupon'] = [
    'id' => $coupon['id'],
    'code' => $coupon['code'],
    'discount' => $discount
];

jsonResponse([
    'success' => true,
    'message' => 'Kupon berhasil diterapkan! Anda mendapat diskon ' . formatRupiah($discount),
    'coupon' => [
        'code' => $coupon['code'],
        'type' => $coupon['type'],
        'value' => $coupon['value']
    ],
    'discount' => $discount,
    'new_total' => $cartTotal - $discount
]);
?>
