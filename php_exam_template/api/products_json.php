<?php

// OPTIONAL FEATURE: JSON API
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

$stmt = $conn->query(
    'SELECT product_id, name, price, quantity, category_id, image
     FROM products
     ORDER BY product_id DESC'
);

echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

