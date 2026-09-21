-- =========================================================
-- CÁC CÂU SQL MẪU ĐỂ ÔN TẬP
-- Chọn từng câu trong phpMyAdmin rồi bấm Run để xem kết quả.
-- =========================================================

USE php_exam_shop;

-- SELECT: lấy toàn bộ sản phẩm
SELECT *
FROM products;

-- WHERE: lọc theo điều kiện
SELECT *
FROM products
WHERE price > 1000000;

-- LIKE: tìm tên chứa từ khóa
SELECT *
FROM products
WHERE name LIKE '%Laptop%';

-- ORDER BY: sắp xếp giá giảm dần
SELECT *
FROM products
ORDER BY price DESC;

-- GROUP BY: đếm sản phẩm theo danh mục
SELECT
    category_id,
    COUNT(*) AS total
FROM products
GROUP BY category_id;

-- JOIN: lấy tên sản phẩm và tên danh mục
SELECT
    p.name,
    p.price,
    c.category_name
FROM products AS p
JOIN categories AS c
    ON p.category_id = c.category_id;

-- LEFT JOIN: vẫn lấy sản phẩm chưa có danh mục
SELECT
    p.name,
    c.category_name
FROM products AS p
LEFT JOIN categories AS c
    ON p.category_id = c.category_id;

-- JOIN nhiều bảng: xem chi tiết đơn hàng
SELECT
    o.order_id,
    o.order_date,
    cu.name AS customer_name,
    p.name AS product_name,
    od.quantity,
    od.price
FROM orders AS o
JOIN customers AS cu ON o.customer_id = cu.customer_id
JOIN order_details AS od ON o.order_id = od.order_id
JOIN products AS p ON od.product_id = p.product_id
ORDER BY o.order_id;

-- LIMIT + OFFSET: trang 2, mỗi trang 5 sản phẩm
SELECT *
FROM products
ORDER BY product_id
LIMIT 5 OFFSET 5;

-- INSERT / UPDATE / DELETE mẫu.
-- ROLLBACK ở cuối nên dữ liệu mẫu không bị thay đổi khi chạy cả khối.
START TRANSACTION;

INSERT INTO products (name, price, quantity, category_id)
VALUES ('Sản phẩm SQL mẫu', 500000, 10, 3);

SET @new_product_id = LAST_INSERT_ID();

UPDATE products
SET name = 'Sản phẩm đã sửa', price = 550000, quantity = 8
WHERE product_id = @new_product_id;

DELETE FROM products
WHERE product_id = @new_product_id;

ROLLBACK;

