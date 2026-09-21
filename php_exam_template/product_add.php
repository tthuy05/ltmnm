<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/features.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product_helpers.php';

$pageTitle = 'Thêm sản phẩm';
$submitLabel = 'Lưu sản phẩm';
$data = ['name' => '', 'price' => '', 'quantity' => '', 'category_id' => ''];
$errors = [];
$imageName = null;
$categories = [];

// OPTIONAL: CATEGORY
if ($features['category']) {
    $categories = $conn->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = read_product_input();
    $errors = validate_product($conn, $data, $features);
    if (!$errors) {
        try {
            // CORE: tên cột viết cố định, không lấy từ GET/POST.
            $columns = 'name, price, quantity';
            $placeholders = '?, ?, ?';
            $values = [$data['name'], $data['price'], (int) $data['quantity']];

            // OPTIONAL: CATEGORY
            if ($features['category']) {
                $columns .= ', category_id';
                $placeholders .= ', ?';
                $values[] = $data['category_id'] !== '' ? (int) $data['category_id'] : null;
            }
            // OPTIONAL: FILE UPLOAD
            if ($features['upload']) {
                $imageName = upload_product_image($_FILES['image'] ?? null);
                $columns .= ', image';
                $placeholders .= ', ?';
                $values[] = $imageName;
            }
            $stmt = $conn->prepare("INSERT INTO products ($columns) VALUES ($placeholders)");
            $stmt->execute($values);
            header('Location: products.php?message=added');
            exit;
        } catch (PDOException $exception) {
            remove_product_image($imageName);
            $imageName = null;
            $errors[] = 'Chưa lưu được sản phẩm. Kiểm tra danh mục còn tồn tại và cấu trúc bảng tại setup.php.';
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }
    http_response_code(422);
}
require __DIR__ . '/includes/product_form.php';
