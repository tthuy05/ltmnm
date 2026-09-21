<?php

require_once __DIR__ . '/config/database.php';

// OPTIONAL FEATURE: CATEGORY
$categoryStmt = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_name');
$categories = $categoryStmt->fetchAll();

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$productId) {
    http_response_code(400);
    die('ID sản phẩm không hợp lệ.');
}

// CORE: SELECT dữ liệu cũ bằng Prepared Statement.
$stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    die('Không tìm thấy sản phẩm.');
}

$errors = [];
$productName = $product['name'];
$productPrice = $product['price'];
$productQuantity = $product['quantity'];
$categoryId = $product['category_id'] ?? '';
$imageName = $product['image'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['name'] ?? '');
    $productPrice = trim($_POST['price'] ?? '');
    $productQuantity = trim($_POST['quantity'] ?? '');
    $categoryId = trim($_POST['category_id'] ?? '');

    if ($productName === '') {
        $errors[] = 'Tên sản phẩm không được để trống.';
    }
    if (!is_numeric($productPrice) || (float) $productPrice < 0) {
        $errors[] = 'Giá phải là số lớn hơn hoặc bằng 0.';
    }
    if (filter_var($productQuantity, FILTER_VALIDATE_INT) === false || (int) $productQuantity < 0) {
        $errors[] = 'Số lượng phải là số nguyên lớn hơn hoặc bằng 0.';
    }

    // ===============================
    // OPTIONAL FEATURE: FILE UPLOAD
    // Nếu không chọn ảnh mới thì giữ nguyên ảnh cũ.
    // ===============================
    $imageFile = $_FILES['image'] ?? null;
    $newImageName = $imageName;

    if ($imageFile && $imageFile['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($imageFile['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Có lỗi khi tải ảnh lên.';
        } elseif ($imageFile['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ảnh không được lớn hơn 2 MB.';
        } else {
            $extension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (!in_array($extension, $allowedExtensions, true)) {
                $errors[] = 'Ảnh chỉ được có định dạng JPG, JPEG hoặc PNG.';
            } else {
                $newImageName = uniqid('product_', true) . '.' . $extension;
            }
        }
    }

    if (empty($errors) && $newImageName !== $imageName) {
        $uploadPath = __DIR__ . '/uploads/' . $newImageName;
        if (!move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
            $errors[] = 'Không thể lưu ảnh vào thư mục uploads.';
        }
    }

    if (empty($errors)) {
        // CORE: UPDATE bằng Prepared Statement.
        // OPTIONAL FEATURE: CATEGORY - bỏ category_id nếu đề chỉ có một bảng.
        // OPTIONAL FEATURE: FILE UPLOAD - bỏ image nếu đề không yêu cầu.
        $sql = 'UPDATE products
                SET name = ?, price = ?, quantity = ?, category_id = ?, image = ?
                WHERE product_id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $productName,
            $productPrice,
            $productQuantity,
            $categoryId !== '' ? (int) $categoryId : null,
            $newImageName,
            $productId,
        ]);

        header('Location: products.php?message=' . urlencode('Cập nhật sản phẩm thành công.'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sản phẩm</title>
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
    <p class="eyebrow">UPDATE · ID <?= (int) $productId ?></p>
    <h1>Sửa sản phẩm</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- OPTIONAL FEATURE: FILE UPLOAD -->
    <form class="form-card" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Tên sản phẩm</label>
            <input id="name" name="name" type="text" value="<?= htmlspecialchars($productName) ?>" required>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="price">Giá</label>
                <input id="price" name="price" type="number" min="0" step="1000" value="<?= htmlspecialchars((string) $productPrice) ?>" required>
            </div>
            <div class="form-group">
                <label for="quantity">Số lượng</label>
                <input id="quantity" name="quantity" type="number" min="0" value="<?= htmlspecialchars((string) $productQuantity) ?>" required>
            </div>
        </div>
        <!-- OPTIONAL FEATURE: CATEGORY -->
        <div class="form-group">
            <label for="category_id">Danh mục</label>
            <select id="category_id" name="category_id">
                <option value="">-- Chưa phân loại --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['category_id'] ?>"
                        <?= (string) $categoryId === (string) $category['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- OPTIONAL FEATURE: FILE UPLOAD -->
        <div class="form-group">
            <label for="image">Đổi ảnh (JPG, JPEG, PNG — tối đa 2 MB)</label>
            <?php if ($imageName): ?>
                <img class="current-image" src="uploads/<?= rawurlencode($imageName) ?>" alt="Ảnh hiện tại">
            <?php endif; ?>
            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
        </div>
        <div class="actions">
            <button class="button primary" type="submit">Cập nhật</button>
            <a class="button secondary" href="products.php">Hủy</a>
        </div>
    </form>
</main>
</body>
</html>
