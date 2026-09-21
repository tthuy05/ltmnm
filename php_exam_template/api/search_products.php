<?php
// OPTIONAL: AJAX / FETCH API
$isJsonRequest = true;
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/features.php';
require_once __DIR__ . '/../includes/functions.php';

$keyword = input_text($_GET, 'keyword');
if ($keyword !== '' && preg_match('//u', $keyword) !== 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Từ khóa phải dùng bảng mã UTF-8.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$select = 'p.product_id, p.name, p.price, p.quantity';
$join = '';
if ($features['category']) {
    $select .= ', c.category_name';
    $join = ' LEFT JOIN categories AS c ON p.category_id = c.category_id';
} else {
    $select .= ', NULL AS category_name';
}
$stmt = $conn->prepare('SELECT ' . $select . ' FROM products AS p' . $join
    . ' WHERE p.name LIKE :keyword ORDER BY p.name LIMIT 20');
$stmt->execute([':keyword' => '%' . $keyword . '%']);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
