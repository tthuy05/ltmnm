<?php

// Chỉ đọc cấu hình và schema: trang này KHÔNG tạo, xóa hay import dữ liệu.
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/features.php';

$checks = [];
function setup_check($name, $status, $detail, $group = 'core')
{
    global $checks;
    $checks[] = compact('name', 'status', 'detail', 'group');
}

function setup_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Tên bảng/cột được viết cố định ở dưới, không nhận từ URL hay form.
function setup_columns($connection, $table, $required, $group = 'core')
{
    try {
        $rows = $connection->query('SHOW COLUMNS FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC);
        $missing = array_diff($required, array_column($rows, 'Field'));
        setup_check('Bảng ' . $table, $missing ? 'fail' : 'pass', $missing
            ? 'Thiếu cột: ' . implode(', ', $missing) . '. So sánh sql/database.sql; sao lưu dữ liệu trước khi sửa schema hoặc import lại.'
            : 'Đủ cột: ' . implode(', ', $required) . '.', $group);
    } catch (PDOException $exception) {
        setup_check('Bảng ' . $table, 'fail', 'Chưa có bảng hoặc tài khoản không được đọc schema. Lần cài đầu: import sql/database.sql. Nếu đã có dữ liệu: kiểm tra database/quyền trước khi import.', $group);
    }
}

setup_check('Phiên bản PHP', PHP_VERSION_ID >= 70400 ? 'pass' : 'fail',
    'Đang chạy PHP ' . PHP_VERSION . '. Yêu cầu PHP 7.4 trở lên; khuyến nghị PHP 8.x. Chọn phiên bản trong Laragon rồi khởi động lại dịch vụ.');
setup_check('Driver pdo_mysql', extension_loaded('pdo_mysql') ? 'pass' : 'fail',
    extension_loaded('pdo_mysql') ? 'Đã bật driver kết nối MySQL/MariaDB.' : 'Bật pdo_mysql trong php.ini của PHP đang chạy rồi khởi động lại Laragon.');

$connection = null;
if (extension_loaded('pdo_mysql')) {
    try {
        $connection = new PDO("mysql:host={$host};port={$port};dbname={$databaseName};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $connection->query('SELECT 1');
        setup_check('Kết nối database', 'pass', 'Kết nối và đọc được database đã chọn. Không kiểm tra quyền ghi bằng cách tạo dữ liệu thử.');
    } catch (PDOException $exception) {
        $connection = null;
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);
        $hint = 'Mở Laragon → Start All. Nếu MySQL dùng cổng hoặc tài khoản khác, copy config/local.example.php thành config/local.php và sửa theo máy này.';
        if ($driverCode === 1049) {
            $hint = 'Chưa có database đã cấu hình. Lần cài đầu: import sql/database.sql bằng phpMyAdmin. Nếu đã có dữ liệu, kiểm tra tên database trong config/local.php trước.';
        } elseif (in_array($driverCode, [1044, 1045], true)) {
            $hint = 'Tài khoản hoặc quyền MySQL chưa đúng. Kiểm tra username/password trong config/local.php và quyền truy cập database.';
        }
        setup_check('Kết nối database', 'fail', $hint);
    }
}

if ($connection !== null) {
    setup_columns($connection, 'products', ['product_id', 'name', 'price', 'quantity']);
    if (!empty($features['category'])) {
        setup_columns($connection, 'categories', ['category_id', 'category_name'], 'optional');
        setup_columns($connection, 'products', ['category_id'], 'optional');
    }
    if (!empty($features['upload'])) {
        setup_columns($connection, 'products', ['image'], 'optional');
    }
} else {
    setup_check('Bảng và cột', 'skip', 'Chưa kiểm tra được schema. Sửa kết nối database rồi tải lại trang này.');
}

setup_check('Module tùy chọn', 'info', 'Category: ' . (!empty($features['category']) ? 'bật' : 'tắt')
    . '; Upload: ' . (!empty($features['upload']) ? 'bật' : 'tắt')
    . '. Thiếu bảng/cột của module đang bật vẫn gây lỗi. Có thể tắt module trong config/features.php nếu đề không yêu cầu.', 'optional');

if (!empty($features['upload'])) {
    $uploadDirectory = __DIR__ . '/uploads';
    setup_check('Thư mục uploads', is_dir($uploadDirectory) && is_writable($uploadDirectory) ? 'pass' : 'fail',
        'Cần có thư mục uploads và tài khoản chạy PHP được ghi vào đó. Khi chuyển máy, copy cả ảnh trong thư mục này.', 'optional');
    setup_check('PHP cho phép upload', filter_var(ini_get('file_uploads'), FILTER_VALIDATE_BOOLEAN) ? 'pass' : 'fail',
        'file_uploads = ' . ini_get('file_uploads') . '. Nếu tắt, đặt file_uploads = On trong php.ini rồi khởi động lại Laragon.', 'optional');
    setup_check('Nhận dạng ảnh', function_exists('getimagesize') ? 'pass' : 'fail',
        'Cần hàm getimagesize để kiểm tra nội dung file ảnh.', 'optional');
    setup_check('Giới hạn ảnh', 'info', 'Ứng dụng nhận ảnh tối đa 2 MB; PHP upload_max_filesize = '
        . ini_get('upload_max_filesize') . ', post_max_size = ' . ini_get('post_max_size')
        . '. Nên đặt upload_max_filesize = 2M trở lên, post_max_size = 8M trở lên để đủ cả dữ liệu form.', 'optional');
    $uploadTempDirectory = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
    setup_check('Thư mục upload tạm', is_dir($uploadTempDirectory) && is_writable($uploadTempDirectory) ? 'pass' : 'warn',
        $uploadTempDirectory . '. Nếu báo lỗi upload, kiểm tra thư mục tồn tại và quyền ghi hoặc cấu hình upload_tmp_dir trong php.ini.', 'optional');
}

if (extension_loaded('session')) {
    $sessionHandler = ini_get('session.save_handler');
    if ($sessionHandler === 'files') {
        // save_path có thể ở dạng "2;/duong/dan"; phần cuối mới là thư mục.
        $sessionPathParts = explode(';', (string) ini_get('session.save_path'));
        $sessionDirectory = end($sessionPathParts) ?: sys_get_temp_dir();
        setup_check('Nơi lưu session đăng nhập', is_dir($sessionDirectory) && is_writable($sessionDirectory) ? 'pass' : 'warn',
            $sessionDirectory . '. Đăng nhập cần PHP được ghi session. Nếu báo Permission denied, sửa session.save_path trong php.ini sang thư mục tồn tại và được phép ghi.', 'optional');
    } else {
        setup_check('Nơi lưu session đăng nhập', 'info', 'session.save_handler = ' . $sessionHandler . '. Không dùng thư mục session; cần tự kiểm tra cấu hình handler này.', 'optional');
    }
} else {
    setup_check('Session đăng nhập', 'warn', 'PHP chưa có session. CRUD vẫn dùng được nhưng phần đăng nhập cần bật extension session.', 'optional');
}

$failures = array_filter($checks, function ($check) { return $check['status'] === 'fail'; });
$warnings = array_filter($checks, function ($check) { return $check['status'] === 'warn'; });
$statusLabels = ['pass' => 'Đạt', 'fail' => 'Cần sửa', 'warn' => 'Cảnh báo', 'info' => 'Thông tin', 'skip' => 'Chưa kiểm tra'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra cài đặt - PHP Exam Template</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Exam Template</a>
        <nav class="nav" aria-label="Menu chính"><a href="products.php">Sản phẩm</a><a href="setup.php" class="active">Kiểm tra cài đặt</a></nav>
    </div>
</header>
<main class="container page-space">
    <p class="eyebrow">CÀI ĐẶT VÀ CHUYỂN MÁY</p>
    <h1>Kiểm tra môi trường</h1>
    <p>Trang này chỉ đọc cấu hình và cấu trúc database, không import hay thay đổi dữ liệu.</p>
    <?php if ($failures): ?>
        <div class="alert error">Có <?= count($failures) ?> mục cần sửa. Các mục thuộc module tùy chọn đang bật cũng cần sửa hoặc tắt module trước khi sử dụng.</div>
    <?php else: ?>
        <div class="alert success">Các kiểm tra cấu hình bắt buộc đều đạt. Tiếp theo hãy thử thêm, sửa, xóa một sản phẩm mới; upload ảnh và đăng nhập nếu cần.</div>
    <?php endif; ?>
    <?php if ($warnings): ?><p>Còn <?= count($warnings) ?> cảnh báo cho chức năng tùy chọn; xem hướng dẫn trong bảng.</p><?php endif; ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Hạng mục</th><th>Kết quả</th><th>Chi tiết và cách xử lý</th></tr></thead>
            <tbody>
            <?php foreach ($checks as $check): ?>
                <tr>
                    <td><?= setup_escape($check['name']) ?><br><small class="muted"><?= $check['group'] === 'core' ? 'CRUD cơ bản' : 'Module tùy chọn' ?></small></td>
                    <td><strong><?= setup_escape($statusLabels[$check['status']]) ?></strong></td>
                    <td><?= setup_escape($check['detail']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <section class="form-card actions">
        <div>
            <h2>Cài lần đầu bằng Laragon</h2>
            <ol>
                <li>Đặt thư mục php_exam_template trong thư mục www của Laragon rồi chọn Start All.</li>
                <li>Mở phpMyAdmin, tạo/chọn database nếu cần và import sql/database.sql. File mẫu dùng tên php_exam_shop.</li>
                <li>Nếu cấu hình MySQL khác mặc định, copy config/local.example.php thành config/local.php rồi sửa host, port, tên database, username và password.</li>
                <li>Tải lại trang này, xử lý các mục “Cần sửa”, rồi mở danh sách sản phẩm.</li>
            </ol>
            <p><strong>Nếu project đã có dữ liệu:</strong> export database bằng phpMyAdmin trước khi chuyển máy và copy cả thư mục uploads. File sql/database.sql không xóa bảng; file sql/reset.sql mới là file xóa dữ liệu và chỉ dùng khi thật sự muốn làm lại.</p>
            <p>PHP đang đọc cấu hình từ: <code><?= setup_escape(php_ini_loaded_file() ?: 'Chưa nạp php.ini') ?></code>. Sau khi đổi php.ini, khởi động lại dịch vụ Laragon.</p>
            <p>Đọc README.md trong trình soạn thảo để xem hướng dẫn cài đặt, sử dụng và sửa lỗi đầy đủ.</p>
        </div>
    </section>
</main>
</body>
</html>
