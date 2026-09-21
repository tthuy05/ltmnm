<?php
// Các hàm nhỏ dùng chung. Không cần framework hoặc Composer.
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// URL có thể chứa name[]=...; chỉ nhận chuỗi để tránh lỗi TypeError.
function input_text($source, $key, $default = '')
{
    return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : $default;
}

function positive_id($value)
{
    if (!is_string($value) && !is_int($value)) {
        return false;
    }
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}

function money($amount)
{
    return number_format((float) $amount, 2, ',', '.') . ' đ';
}

function show_error($message, $status = 400)
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="vi"><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Thông báo</title><h1>Không thể thực hiện</h1><p>' . e($message) . '</p>';
    echo '<p><a href="products.php">Quay lại danh sách</a></p></html>';
    exit;
}
