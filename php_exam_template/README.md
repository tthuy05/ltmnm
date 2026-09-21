# PHP Exam Template — Mini Shop Management

Project mẫu để ôn và làm bài kiểm tra PHP thuần, MySQL, PDO, CRUD, JavaScript và Fetch API. Các tính năng tùy chọn được đánh dấu rõ trong code để có thể bỏ nhanh khi đề không yêu cầu.

## Yêu cầu

- Laragon có PHP 8.x và MySQL.
- Trình duyệt web.
- phpMyAdmin (đi kèm Laragon).

## Cách chạy

1. Copy thư mục `php_exam_template` vào `C:\laragon\www`.
2. Mở Laragon và chọn **Start All**.
3. Mở phpMyAdmin từ Laragon.
4. Import file `sql/database.sql`.
5. Nếu tài khoản MySQL khác mặc định, sửa `config/database.php`.
6. Mở `http://localhost/php_exam_template/`.

Tài khoản login demo: `admin` / `123`.

## Các trang chính

- `/index.php`: menu tổng quan.
- `/products.php`: CRUD, tìm kiếm và phân trang.
- `/categories.php`: ví dụ bảng liên quan.
- `/javascript_demo.php`: DOM và Event.
- `/ajax_demo.php`: tìm kiếm bằng Fetch API.
- `/auth/login.php`: Session và Cookie.

## Cách học nhanh

1. Đọc `REVIEW.md` để nhớ cú pháp.
2. Theo dõi luồng CRUD trong `products.php`, `product_add.php`, `product_edit.php`, `product_delete.php`.
3. Đọc `FEATURE_MAP.md` để biết phần nào có thể bỏ.
4. Dùng `ADAPT_EXAM.md` khi đổi đề sang Sinh viên, Sách, Nhân viên hoặc Món ăn.

## Lưu ý upload

Thư mục `uploads/` phải có quyền ghi. Chỉ file JPG, JPEG hoặc PNG, tối đa 2 MB, được chấp nhận.

