<?php

// API đặt $isJsonRequest = true trước khi require file này.
// Không xuất lỗi PDO gốc vì có thể chứa tài khoản, tên máy và câu SQL.
function database_error_response($message, $status = 500)
{
    global $isJsonRequest;

    http_response_code($status);
    if (!empty($isJsonRequest)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="vi"><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Cần kiểm tra database</title><body>';
    echo '<h1>Chưa thể xử lý dữ liệu</h1><p>';
    echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo '</p><p>Mở <a href="setup.php">trang kiểm tra cài đặt</a> để xem cách xử lý.</p>';
    echo '<p><a href="index.php">Về trang chủ</a></p></body></html>';
    exit;
}

// Xử lý cả lỗi truy vấn chưa được trang gọi bắt riêng (vd. thiếu bảng/cột).
set_exception_handler(function ($exception) {
    error_log(get_class($exception) . ' in ' . $exception->getFile() . ':' . $exception->getLine()
        . ' (code ' . $exception->getCode() . ')');
    if ($exception instanceof PDOException) {
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);
        if (in_array($driverCode, [1146, 1054], true)) {
            database_error_response('Database thiếu bảng hoặc cột. Xem setup.php, kiểm tra sql/database.sql và config/features.php. Sao lưu dữ liệu trước khi import lại SQL.');
        }
        if ($driverCode === 1451) {
            database_error_response('Bản ghi đang được dữ liệu khác sử dụng nên chưa thể xóa. Kiểm tra các bản ghi liên quan trước khi thử lại.', 409);
        }
        if ($driverCode === 1062) {
            database_error_response('Dữ liệu bị trùng với bản ghi đã có. Hãy kiểm tra lại thông tin vừa nhập.', 409);
        }
        database_error_response('Không thể đọc hoặc ghi dữ liệu. Kiểm tra MySQL trong Laragon, quyền của tài khoản database và trang setup.php.');
    }

    // Không để lỗi chưa được bắt hiển thị stack trace trong trình duyệt/API.
    database_error_response('Có lỗi khi xử lý yêu cầu. Kiểm tra cú pháp các file vừa sửa và error log của PHP trong Laragon.');
});

require_once __DIR__ . '/settings.php';

if (!extension_loaded('pdo_mysql')) {
    database_error_response('PHP chưa bật pdo_mysql. Bật extension này trong php.ini của PHP đang chạy, khởi động lại Laragon rồi mở setup.php.', 503);
}

$dsn = "mysql:host={$host};port={$port};dbname={$databaseName};charset=utf8mb4";
try {
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ]);
} catch (PDOException $exception) {
    $driverCode = (int) ($exception->errorInfo[1] ?? 0);
    if ($driverCode === 1049) {
        database_error_response('Chưa tìm thấy database. Lần cài đầu: import sql/database.sql bằng phpMyAdmin. Nếu đã có dữ liệu, kiểm tra tên database trong config/local.php trước.', 503);
    }
    if (in_array($driverCode, [1044, 1045], true)) {
        database_error_response('Tài khoản MySQL chưa đúng hoặc chưa có quyền. Copy config/local.example.php thành config/local.php và sửa username/password theo máy này.', 503);
    }
    database_error_response('Không kết nối được MySQL. Chọn Start All trong Laragon và kiểm tra host, port, tên database trong config/local.php. Xem setup.php để chẩn đoán.', 503);
}
