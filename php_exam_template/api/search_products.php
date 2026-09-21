<?php

// ===============================
// OPTIONAL FEATURE: AJAX / FETCH API
// File này độc lập với CRUD HTML truyền thống.
// ===============================
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

$keyword = trim($_GET['keyword'] ?? '');
$sql = 'SELECT p.product_id, p.name, p.price, p.quantity, c.category_name
        FROM products AS p
        LEFT JOIN categories AS c ON p.category_id = c.category_id
        WHERE p.name LIKE :keyword
        ORDER BY p.name
        LIMIT 20';

$stmt = $conn->prepare($sql);
$stmt->execute([':keyword' => '%' . $keyword . '%']);
$products = $stmt->fetchAll();

echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

