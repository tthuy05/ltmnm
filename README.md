# PHP Exam Template

Project PHP thuần để ôn CRUD, PDO, MySQL, JavaScript và AJAX. Mã nguồn website nằm trong **[php_exam_template/](php_exam_template/)**.

## Chạy bằng Laragon ở trường

1. Tải repository và giải nén.
2. Copy thư mục con `php_exam_template` vào `C:\laragon\www`.
3. Mở Laragon → **Start All**.
4. Mở phpMyAdmin và import `php_exam_template/sql/database.sql`.
5. Mở [trang kiểm tra môi trường](http://localhost/php_exam_template/setup.php), sau đó mở [website](http://localhost/php_exam_template/).

Nếu clone **cả repository** vào `C:\laragon\www\ltmnm`, URL là [http://localhost/ltmnm/php_exam_template/](http://localhost/ltmnm/php_exam_template/). Đường dẫn trên trình duyệt phải khớp vị trí thư mục trong `www`.

Thông tin mặc định: MySQL `127.0.0.1:3306`, database `php_exam_shop`, tài khoản `root`, mật khẩu rỗng. Máy có cấu hình khác: copy `config/local.example.php` thành `config/local.php` trong thư mục project rồi sửa bản riêng đó.

## Tài liệu

| Cần làm gì? | Đọc file |
|---|---|
| Cài lần đầu, sửa lỗi Laragon, chuyển máy, sao lưu | [INSTALL.md](php_exam_template/INSTALL.md) |
| Dùng từng chức năng và thực hành | [USAGE.md](php_exam_template/USAGE.md) |
| Hiểu cấu trúc code | [README.md của project](php_exam_template/README.md) |
| Bật/tắt hoặc bỏ một chức năng | [FEATURE_MAP.md](php_exam_template/FEATURE_MAP.md) |
| Đổi đề thành sinh viên, sách, nhân viên, món ăn | [ADAPT_EXAM.md](php_exam_template/ADAPT_EXAM.md) |
| Ôn nhanh cú pháp | [REVIEW.md](php_exam_template/REVIEW.md) |

Đăng nhập demo: `admin` / `123`. CRUD có thể dùng trực tiếp, không bắt buộc đăng nhập.

Đây là template học tập chạy cục bộ. Project không cần Composer, Node.js hay Internet sau khi đã có code, PHP và MySQL. Khi chuyển máy, cần chuyển **code + database đã export + ảnh trong `uploads/`**; Git chỉ mang theo mã nguồn và dữ liệu mẫu.
