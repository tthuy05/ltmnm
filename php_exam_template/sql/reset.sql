-- NGUY HIỂM: file này XÓA TOÀN BỘ bảng và dữ liệu mẫu của php_exam_shop.
-- Chỉ chạy khi chắc chắn muốn làm lại từ đầu.
-- Nếu cần giữ dữ liệu: phpMyAdmin -> Export trước, và sao lưu thư mục uploads.

USE php_exam_shop;

DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS categories;

-- Sau khi reset, import lại sql/database.sql để tạo bảng và dữ liệu mẫu.
