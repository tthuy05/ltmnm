<?php

require_once __DIR__ . '/config/database.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$productId) {
    header('Location: products.php?message=' . urlencode('ID sản phẩm không hợp lệ.'));
    exit;
}

// CORE: DELETE bằng Prepared Statement.
$stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
$stmt->execute([$productId]);

$message = $stmt->rowCount() > 0
    ? 'Xóa sản phẩm thành công.'
    : 'Không tìm thấy sản phẩm để xóa.';

header('Location: products.php?message=' . urlencode($message));
exit;

