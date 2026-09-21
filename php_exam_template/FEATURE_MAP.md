# FEATURE MAP

Nhìn bảng này trước khi xóa chức năng. “Có” nghĩa là có thể bỏ khi đề không yêu cầu.

| Chức năng | File | Có thể bỏ? | Phụ thuộc |
|---|---|---:|---|
| PDO Connection | `config/database.php` | Không | MySQL, PDO MySQL |
| Trang chủ | `index.php` | Có | Không |
| Product List | `products.php` | Không | PDO, bảng `products` |
| Product View | `product_view.php` | Có | PDO, bảng `products` |
| Add | `product_add.php` | Nếu đề không yêu cầu | PDO |
| Edit | `product_edit.php` | Nếu đề không yêu cầu | PDO |
| Delete | `product_delete.php` | Nếu đề không yêu cầu | PDO |
| Search | Khối `OPTIONAL FEATURE: SEARCH` trong `products.php` | Có | PDO |
| Pagination | Khối `OPTIONAL FEATURE: PAGINATION` trong `products.php` | Có | PDO |
| Category / JOIN | `categories.php` và các khối CATEGORY trong CRUD | Có | Bảng `categories`, `category_id` |
| Login | `auth/login.php`, `auth/logout.php` | Có | Session |
| Cookie | Khối COOKIE trong `auth/login.php` | Có | Trình duyệt |
| Upload | Khối FILE UPLOAD trong Add/Edit/List/View | Có | Form multipart, thư mục `uploads/` |
| AJAX Search | `ajax_demo.php`, `api/search_products.php` | Có | JavaScript, PDO |
| JSON List | `api/products_json.php` | Có | PDO |
| OOP Demo | `classes/*` | Có | Không; CRUD không dùng class |
| JavaScript Demo | `javascript_demo.php` | Có | Trình duyệt |
| CSS | `style.css` | Có thể thay | HTML vẫn chạy nếu bỏ CSS |
| SQL học JOIN Orders | `orders`, `order_details` trong SQL | Có | Customers, Products |

## Quan hệ module

```text
config/database.php
       |
       +-- CRUD Product (CORE)
       +-- Category (OPTIONAL)
       +-- API JSON (OPTIONAL)

auth/*, classes/*, javascript_demo.php
không phải dependency của CRUD.
```

