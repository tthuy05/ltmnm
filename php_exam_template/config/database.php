<?php

// ===============================
// CORE: PDO CONNECTION
// Sửa 4 biến bên dưới nếu MySQL trên máy dùng cấu hình khác.
// ===============================
$host = 'localhost';
$databaseName = 'php_exam_shop';
$username = 'root';
$password = '';

$dsn = "mysql:host={$host};dbname={$databaseName};charset=utf8mb4";

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $exception) {
    die('Kết nối thất bại: ' . $exception->getMessage());
}

