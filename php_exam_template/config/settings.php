<?php

// Cấu hình dùng chung. Không cần sửa file này khi chuyển máy.
// Máy có mật khẩu/cổng khác: copy local.example.php thành local.php rồi sửa.
$host = '127.0.0.1';
$port = 3306;
$databaseName = 'php_exam_shop';
$username = 'root';
$password = '';

$localConfigFile = __DIR__ . '/local.php';
if (is_file($localConfigFile)) {
    require $localConfigFile;
}
