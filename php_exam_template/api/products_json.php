<?php
// OPTIONAL: JSON API
$isJsonRequest = true;
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/features.php';

$columns = ['product_id', 'name', 'price', 'quantity'];
if ($features['category']) {
    $columns[] = 'category_id';
}
if ($features['upload']) {
    $columns[] = 'image';
}
$stmt = $conn->query('SELECT ' . implode(', ', $columns) . ' FROM products ORDER BY product_id DESC');
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
