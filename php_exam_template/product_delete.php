<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/features.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product_helpers.php';

// GET chỉ hiển thị xác nhận; POST mới thay đổi dữ liệu. Không cần JavaScript.
$productId = positive_id($_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null));
if (!$productId) {
    show_error('ID sản phẩm phải là số nguyên dương.');
}
$stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    show_error('Không tìm thấy sản phẩm.', 404);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // CORE: DELETE bằng Prepared Statement.
        $stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
        $stmt->execute([$productId]);
        if ($features['upload']) {
            remove_product_image($product['image'] ?? null);
        }
        header('Location: products.php?message=deleted');
        exit;
    } catch (PDOException $exception) {
        if ((int) ($exception->errorInfo[1] ?? 0) === 1451) {
            http_response_code(409);
            $error = 'Không thể xóa: sản phẩm đã có trong đơn hàng. Giữ sản phẩm để bảo toàn lịch sử đơn hàng; bạn vẫn có thể sửa thông tin.';
        } else {
            throw $exception;
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xóa sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container narrow page-space">
    <h1>Xóa sản phẩm</h1>
    <?php if ($error !== ''): ?><p class="alert error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <div class="form-card">
        <p>Bạn muốn xóa <strong><?= e($product['name']) ?></strong> (ID <?= (int) $productId ?>)?</p>
        <p>Dữ liệu sản phẩm và ảnh của sản phẩm sẽ bị xóa khi xác nhận.</p>
        <form method="post">
            <input type="hidden" name="id" value="<?= (int) $productId ?>">
            <div class="actions">
                <?php if ($error === ''): ?><button class="button danger" type="submit">Xác nhận xóa</button><?php endif; ?>
                <a class="button secondary" href="products.php">Quay lại</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
