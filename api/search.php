<?php
/**
 * Search API
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

header('Content-Type: application/json');

$query = sanitize($_GET['q'] ?? '');

if (strlen($query) < 2) {
    jsonResponse(['success' => true, 'results' => []]);
}

$db = getDB();
$stmt = $db->prepare("
    SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.image, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active' AND (p.name LIKE ? OR p.description LIKE ?)
    ORDER BY p.featured DESC, p.sold_count DESC
    LIMIT 10
");
$stmt->execute(["%$query%", "%$query%"]);
$results = $stmt->fetchAll();

$formattedResults = array_map(function($product) {
    return [
        'id' => $product['id'],
        'name' => $product['name'],
        'slug' => $product['slug'],
        'price' => formatRupiah($product['sale_price'] ?? $product['price']),
        'image' => getProductImage($product['image']),
        'category' => $product['category_name'] ?? 'Uncategorized',
        'url' => SITE_URL . '/product.php?slug=' . $product['slug']
    ];
}, $results);

jsonResponse(['success' => true, 'results' => $formattedResults]);
?>
