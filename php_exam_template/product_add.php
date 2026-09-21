<?php

require_once __DIR__ . '/config/database.php';

$errors = [];
$productName = '';
$productPrice = '';
$productQuantity = '';
$categoryId = '';
$imageName = null;

// ===============================
// OPTIONAL FEATURE: CATEGORY
// Bỏ query này và thẻ select nếu đề chỉ có một bảng.
// ===============================
$categoryStmt = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_name');
$categories = $categoryStmt->fetchAll();

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
    // Chỉ nhận JPG, JPEG, PNG; tối đa 2 MB.
    // ===============================
    $imageFile = $_FILES['image'] ?? null;
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
                $imageName = uniqid('product_', true) . '.' . $extension;
            }
        }
    }

    if (empty($errors)) {
        // OPTIONAL FEATURE: FILE UPLOAD
        if ($imageName !== null) {
            $uploadPath = __DIR__ . '/uploads/' . $imageName;
            if (!move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
                $errors[] = 'Không thể lưu ảnh vào thư mục uploads.';
            }
        }
    }

    if (empty($errors)) {
        // CORE: INSERT bằng Prepared Statement.
        // OPTIONAL FEATURE: CATEGORY - bỏ category_id nếu đề chỉ có một bảng.
        // OPTIONAL FEATURE: FILE UPLOAD - bỏ cột image nếu đề không yêu cầu.
        $sql = 'INSERT INTO products (name, price, quantity, category_id, image)
                VALUES (:name, :price, :quantity, :category_id, :image)';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $productName,
            ':price' => $productPrice,
            ':quantity' => $productQuantity,
            ':category_id' => $categoryId !== '' ? (int) $categoryId : null,
            ':image' => $imageName,
        ]);

        header('Location: products.php?message=' . urlencode('Thêm sản phẩm thành công.'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm</title>
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
    <p class="eyebrow">CREATE</p>
    <h1>Thêm sản phẩm</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- OPTIONAL FEATURE: FILE UPLOAD - bỏ enctype nếu bỏ upload -->
    <form class="form-card" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Tên sản phẩm</label>
            <input id="name" name="name" type="text" value="<?= htmlspecialchars($productName) ?>" required>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="price">Giá</label>
                <input id="price" name="price" type="number" min="0" step="1000" value="<?= htmlspecialchars($productPrice) ?>" required>
            </div>
            <div class="form-group">
                <label for="quantity">Số lượng</label>
                <input id="quantity" name="quantity" type="number" min="0" value="<?= htmlspecialchars($productQuantity) ?>" required>
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
            <label for="image">Ảnh sản phẩm (JPG, JPEG, PNG — tối đa 2 MB)</label>
            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
        </div>
        <div class="actions">
            <button class="button primary" type="submit">Lưu sản phẩm</button>
            <a class="button secondary" href="products.php">Hủy</a>
        </div>
    </form>
</main>
</body>
</html>
