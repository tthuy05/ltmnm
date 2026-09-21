<?php
// Chỉ gom phần Add/Edit bị lặp. INSERT/UPDATE vẫn nằm trong từng trang.
function read_product_input()
{
    return [
        'name' => input_text($_POST, 'name'),
        'price' => input_text($_POST, 'price'),
        'quantity' => input_text($_POST, 'quantity'),
        'category_id' => input_text($_POST, 'category_id'),
    ];
}

function validate_product($conn, $data, $features)
{
    $errors = [];
    if (array_key_exists('category_id', $_POST) && !is_string($_POST['category_id'])) {
        $errors[] = 'Danh mục gửi lên không hợp lệ.';
    }
    if (!preg_match('/^.{1,150}$/us', $data['name'])) {
        $errors[] = 'Tên sản phẩm cần có từ 1 đến 150 ký tự.';
    }
    // Khớp DECIMAL(12,2); không nhận số âm, số mũ hoặc hơn 2 chữ số lẻ.
    if (!preg_match('/^\\d{1,10}(\\.\\d{1,2})?$/D', $data['price'])) {
        $errors[] = 'Giá từ 0 đến 9999999999.99; dùng dấu chấm và tối đa 2 chữ số lẻ.';
    }
    if (!preg_match('/^\\d{1,10}$/D', $data['quantity']) || (float) $data['quantity'] > 2147483647) {
        $errors[] = 'Số lượng phải là số nguyên từ 0 đến 2147483647.';
    }
    // OPTIONAL: CATEGORY - không truy vấn categories khi tính năng tắt.
    if ($features['category'] && $data['category_id'] !== '') {
        $categoryId = positive_id($data['category_id']);
        $stmt = $conn->prepare('SELECT category_id FROM categories WHERE category_id = ?');
        $stmt->execute([$categoryId ?: 0]);
        if (!$stmt->fetch()) {
            $errors[] = 'Danh mục không tồn tại. Hãy chọn lại danh mục.';
        }
    }
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0 && empty($_POST) && empty($_FILES)) {
        $errors[] = 'Form vượt giới hạn post_max_size của PHP. Chọn ảnh nhỏ hơn hoặc xem setup.php.';
    }
    return $errors;
}

// OPTIONAL: FILE UPLOAD. Trả tên file mới hoặc null khi không chọn ảnh.
function upload_product_image($file)
{
    if ($file === null) {
        return null;
    }
    if (!is_array($file) || !isset($file['error']) || !is_int($file['error'])) {
        throw new RuntimeException('Dữ liệu ảnh không hợp lệ.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('PHP không nhận được ảnh. Kiểm tra giới hạn upload và thư mục tạm tại setup.php.');
    }
    if (!isset($file['tmp_name'], $file['name']) || !is_string($file['tmp_name']) || !is_string($file['name'])
        || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('File upload không hợp lệ.');
    }
    $fileSize = filesize($file['tmp_name']);
    if ($fileSize === false) {
        throw new RuntimeException('Không đọc được kích thước ảnh tạm. Kiểm tra upload_tmp_dir trong setup.php.');
    }
    if ($fileSize > 2 * 1024 * 1024) {
        throw new RuntimeException('Ảnh không được lớn hơn 2 MB.');
    }
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
    $imageInfo = @getimagesize($file['tmp_name']);
    if (!isset($allowedTypes[$extension]) || !$imageInfo || $imageInfo['mime'] !== $allowedTypes[$extension]) {
        throw new RuntimeException('Chỉ nhận ảnh JPG, JPEG hoặc PNG thật; đổi đuôi file không đủ.');
    }
    $directory = __DIR__ . '/../uploads';
    if (!is_dir($directory) || !is_writable($directory)) {
        throw new RuntimeException('Thư mục uploads chưa tồn tại hoặc chưa có quyền ghi. Xem setup.php.');
    }
    $imageName = 'product_' . bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $imageName)) {
        throw new RuntimeException('Không lưu được ảnh. Kiểm tra quyền ghi và dung lượng ổ đĩa.');
    }
    return $imageName;
}

function remove_product_image($name)
{
    // Chỉ xóa tên ảnh do app tạo; không cho đường dẫn đi ra ngoài uploads.
    if (!is_string($name) || !preg_match('/^product_[a-z0-9.]+\\.(jpg|jpeg|png)$/D', $name)) {
        return;
    }
    $path = __DIR__ . '/../uploads/' . $name;
    if (is_file($path) && !is_link($path) && !@unlink($path)) {
        error_log('Không thể dọn ảnh cũ trong uploads: ' . $name);
    }
}
