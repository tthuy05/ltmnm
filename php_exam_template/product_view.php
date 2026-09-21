<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/features.php';
require_once __DIR__ . '/includes/functions.php';

$productId = positive_id($_GET['id'] ?? null);
if (!$productId) {
    show_error('ID sản phẩm phải là số nguyên dương.');
}

$selectParts = ['p.product_id', 'p.name', 'p.price', 'p.quantity'];
$joinSql = '';
if ($features['category']) {
    $selectParts[] = 'c.category_name';
    $joinSql = ' LEFT JOIN categories AS c ON p.category_id = c.category_id';
}
if ($features['upload']) {
    $selectParts[] = 'p.image';
}
$stmt = $conn->prepare('SELECT ' . implode(', ', $selectParts)
    . ' FROM products AS p' . $joinSql . ' WHERE p.product_id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    show_error('Không tìm thấy sản phẩm.', 404);
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($product['name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header"><div class="container header-inner">
    <a class="brand" href="index.php">PHP Exam Template</a>
    <nav class="nav"><a href="products.php">← Danh sách sản phẩm</a></nav>
</div></header>
<main class="container narrow page-space">
    <p class="eyebrow">PRODUCT #<?= (int) $product['product_id'] ?></p>
    <section class="detail-card">
        <?php if ($features['upload'] && !empty($product['image'])): ?>
            <img class="product-detail-image" src="uploads/<?= e(rawurlencode($product['image'])) ?>" alt="Ảnh <?= e($product['name']) ?>">
        <?php endif; ?>
        <div><p class="detail-label">Tên sản phẩm</p><h1><?= e($product['name']) ?></h1></div>
        <dl class="detail-list">
            <div><dt>Giá</dt><dd><?= e(money($product['price'])) ?></dd></div>
            <div><dt>Số lượng</dt><dd><?= (int) $product['quantity'] ?></dd></div>
            <?php if ($features['category']): ?><div><dt>Danh mục</dt><dd><?= e($product['category_name'] ?? 'Chưa phân loại') ?></dd></div><?php endif; ?>
        </dl>
        <div class="actions">
            <a class="button primary" href="product_edit.php?id=<?= (int) $productId ?>">Sửa sản phẩm</a>
            <a class="button secondary" href="products.php">Quay lại</a>
        </div>
    </section>
</main>
</body>
</html>
