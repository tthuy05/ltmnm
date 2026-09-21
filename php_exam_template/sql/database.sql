-- =========================================================
-- DATABASE MẪU: MINI SHOP MANAGEMENT
-- Có thể import toàn bộ file này bằng phpMyAdmin.
-- =========================================================

CREATE DATABASE IF NOT EXISTS php_exam_shop
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE php_exam_shop;

-- Xóa bảng con trước bảng cha để có thể import lại file.
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS categories;

CREATE TABLE categories (
    -- Khóa chính
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE products (
    -- Khóa chính
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(12, 2) NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 0,
    category_id INT NULL,
    image VARCHAR(255) NULL,

    -- Khóa ngoại liên kết products với categories
    CONSTRAINT fk_products_categories
        FOREIGN KEY (category_id)
        REFERENCES categories(category_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE customers (
    -- Khóa chính
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL
) ENGINE=InnoDB;

-- Hai bảng dưới chỉ phục vụ ôn JOIN, không ảnh hưởng CRUD Product.
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE NOT NULL,
    customer_id INT NOT NULL,
    CONSTRAINT fk_orders_customers
        FOREIGN KEY (customer_id)
        REFERENCES customers(customer_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE order_details (
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    PRIMARY KEY (order_id, product_id),
    CONSTRAINT fk_order_details_orders
        FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_order_details_products
        FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3 danh mục mẫu
INSERT INTO categories (category_name) VALUES
('Điện thoại'),
('Laptop'),
('Phụ kiện');

-- 8 sản phẩm mẫu
INSERT INTO products (name, price, quantity, category_id, image) VALUES
('Điện thoại A1', 6990000, 12, 1, NULL),
('Điện thoại B2', 8990000, 8, 1, NULL),
('Điện thoại C3', 4990000, 20, 1, NULL),
('Laptop Văn phòng L1', 14990000, 6, 2, NULL),
('Laptop Đồ họa L2', 24990000, 4, 2, NULL),
('Chuột không dây', 350000, 30, 3, NULL),
('Bàn phím cơ', 1190000, 15, 3, NULL),
('Tai nghe Bluetooth', 790000, 18, 3, NULL);

-- 4 khách hàng mẫu
INSERT INTO customers (name, email, phone) VALUES
('Nguyễn An', 'an@example.com', '0901000001'),
('Trần Bình', 'binh@example.com', '0901000002'),
('Lê Chi', 'chi@example.com', '0901000003'),
('Phạm Dũng', 'dung@example.com', '0901000004');

-- Dữ liệu đơn hàng chỉ để chạy thử câu JOIN.
INSERT INTO orders (order_date, customer_id) VALUES
('2026-09-01', 1),
('2026-09-02', 2),
('2026-09-03', 1);

INSERT INTO order_details (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 6990000),
(1, 6, 2, 350000),
(2, 4, 1, 14990000),
(3, 7, 1, 1190000);

