<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/features.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product_helpers.php';

$productId = positive_id($_GET['id'] ?? null);
if (!$productId) {
    show_error('ID sản phẩm phải là số nguyên dương.');
}

// CORE: GET id -> SELECT bản ghi cũ -> form -> POST -> UPDATE.
$stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    show_error('Không tìm thấy sản phẩm.', 404);
}

$pageTitle = 'Sửa sản phẩm #' . $productId;
$submitLabel = 'Cập nhật';
$data = $product;
$errors = [];
$imageName = $product['image'] ?? null;
$categories = [];
if ($features['category']) {
    $categories = $conn->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = read_product_input();
    $errors = validate_product($conn, $data, $features);
    $newImageName = null;
    if (!$errors) {
        try {
            $changes = 'name = ?, price = ?, quantity = ?';
            $values = [$data['name'], $data['price'], (int) $data['quantity']];

            // OPTIONAL: CATEGORY
            if ($features['category']) {
                $changes .= ', category_id = ?';
                $values[] = $data['category_id'] !== '' ? (int) $data['category_id'] : null;
            }
            // OPTIONAL: FILE UPLOAD - giữ ảnh cũ nếu không chọn ảnh mới.
            if ($features['upload']) {
                $newImageName = upload_product_image($_FILES['image'] ?? null);
                if ($newImageName !== null) {
                    $changes .= ', image = ?';
                    $values[] = $newImageName;
                }
            }
            $values[] = $productId;
            $stmt = $conn->prepare("UPDATE products SET $changes WHERE product_id = ?");
            $stmt->execute($values);
            if ($newImageName !== null) {
                remove_product_image($imageName);
            }
            header('Location: products.php?message=updated');
            exit;
        } catch (PDOException $exception) {
            remove_product_image($newImageName);
            $errors[] = 'Chưa cập nhật được sản phẩm. Kiểm tra danh mục và cấu trúc bảng tại setup.php.';
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }
    http_response_code(422);
}
require __DIR__ . '/includes/product_form.php';
