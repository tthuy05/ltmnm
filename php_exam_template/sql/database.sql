-- =========================================================
-- DATABASE MẪU: MINI SHOP MANAGEMENT - CÀI AN TOÀN
-- Có thể import lại: không DROP bảng và không ghi đè ID đã tồn tại.
-- Muốn xóa dữ liệu mẫu để làm lại, đọc kỹ rồi chạy sql/reset.sql trước.
-- =========================================================

CREATE DATABASE IF NOT EXISTS php_exam_shop
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE php_exam_shop;

CREATE TABLE IF NOT EXISTS categories (
    -- Khóa chính
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
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

CREATE TABLE IF NOT EXISTS customers (
    -- Khóa chính
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL
) ENGINE=InnoDB;

-- Hai bảng dưới chỉ phục vụ ôn JOIN, không ảnh hưởng CRUD Product.
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE NOT NULL,
    customer_id INT NOT NULL,
    CONSTRAINT fk_orders_customers
        FOREIGN KEY (customer_id)
        REFERENCES customers(customer_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_details (
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
INSERT IGNORE INTO categories (category_id, category_name) VALUES
(1, 'Điện thoại'),
(2, 'Laptop'),
(3, 'Phụ kiện');

-- 8 sản phẩm mẫu
INSERT IGNORE INTO products (product_id, name, price, quantity, category_id, image) VALUES
(1, 'Điện thoại A1', 6990000, 12, 1, NULL),
(2, 'Điện thoại B2', 8990000, 8, 1, NULL),
(3, 'Điện thoại C3', 4990000, 20, 1, NULL),
(4, 'Laptop Văn phòng L1', 14990000, 6, 2, NULL),
(5, 'Laptop Đồ họa L2', 24990000, 4, 2, NULL),
(6, 'Chuột không dây', 350000, 30, 3, NULL),
(7, 'Bàn phím cơ', 1190000, 15, 3, NULL),
(8, 'Tai nghe Bluetooth', 790000, 18, 3, NULL);

-- 4 khách hàng mẫu
INSERT IGNORE INTO customers (customer_id, name, email, phone) VALUES
(1, 'Nguyễn An', 'an@example.com', '0901000001'),
(2, 'Trần Bình', 'binh@example.com', '0901000002'),
(3, 'Lê Chi', 'chi@example.com', '0901000003'),
(4, 'Phạm Dũng', 'dung@example.com', '0901000004');

-- Dữ liệu đơn hàng chỉ để chạy thử câu JOIN.
INSERT IGNORE INTO orders (order_id, order_date, customer_id) VALUES
(1, '2026-09-01', 1),
(2, '2026-09-02', 2),
(3, '2026-09-03', 1);

INSERT IGNORE INTO order_details (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 6990000),
(1, 6, 2, 350000),
(2, 4, 1, 14990000),
(3, 7, 1, 1190000);
