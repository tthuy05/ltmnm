<?php

require_once __DIR__ . '/config/database.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$productId) {
    http_response_code(400);
    die('ID sản phẩm không hợp lệ.');
}

// CORE: READ một bản ghi bằng Prepared Statement.
// OPTIONAL FEATURE: CATEGORY / JOIN
$stmt = $conn->prepare(
    'SELECT p.*, c.category_name
     FROM products AS p
     LEFT JOIN categories AS c ON p.category_id = c.category_id
     WHERE p.product_id = ?'
);
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    die('Không tìm thấy sản phẩm.');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Exam Template</a>
        <nav class="nav" aria-label="Menu chính"><a href="products.php">← Danh sách sản phẩm</a></nav>
    </div>
</header>

<main class="container narrow page-space">
    <p class="eyebrow">PRODUCT #<?= (int) $product['product_id'] ?></p>
    <section class="detail-card">
        <!-- OPTIONAL FEATURE: FILE UPLOAD -->
        <?php if (!empty($product['image'])): ?>
            <img class="product-detail-image" src="uploads/<?= rawurlencode($product['image']) ?>" alt="Ảnh <?= htmlspecialchars($product['name']) ?>">
        <?php endif; ?>
        <div>
            <p class="detail-label">Tên sản phẩm</p>
            <h1><?= htmlspecialchars($product['name']) ?></h1>
        </div>
        <dl class="detail-list">
            <div><dt>Giá</dt><dd><?= number_format((float) $product['price'], 0, ',', '.') ?> đ</dd></div>
            <div><dt>Số lượng</dt><dd><?= (int) $product['quantity'] ?></dd></div>
            <!-- OPTIONAL FEATURE: CATEGORY / JOIN -->
            <div><dt>Danh mục</dt><dd><?= htmlspecialchars($product['category_name'] ?? 'Chưa phân loại') ?></dd></div>
        </dl>
        <div class="actions">
            <a class="button primary" href="product_edit.php?id=<?= (int) $product['product_id'] ?>">Sửa sản phẩm</a>
            <a class="button secondary" href="products.php">Quay lại</a>
        </div>
    </section>
</main>
</body>
</html>
