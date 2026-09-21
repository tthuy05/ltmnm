# Kiểm thử hồi quy

Chạy tại thư mục chứa `php_exam_template` và `tests`, sau khi bật MySQL trong Laragon:

```powershell
php tests/regression.php
```

Nếu PowerShell không nhận `php`, mở **Terminal** từ Laragon rồi chạy lệnh trên, hoặc gọi đúng đường dẫn PHP đã cài:

```powershell
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' tests/regression.php
```

Tên thư mục phiên bản PHP trên máy bạn có thể khác. Script cần PHP CLI có `pdo_mysql`, `proc_open` và quyền tạo/xóa database kiểm thử. Không cần Composer, Node.js hoặc thư viện bên ngoài.

Nếu MySQL dùng tài khoản, mật khẩu hoặc cổng khác, đặt biến môi trường cho phiên Terminal hiện tại:

```powershell
$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '3306'
$env:DB_USER = 'root'
$env:DB_PASSWORD = ''
php tests/regression.php
```

Script không đọc mật khẩu từ cấu hình cá nhân và không ghi biến này vào repository. Không đưa mật khẩu thực tế vào Git.

Script tạo database có tên ngẫu nhiên dạng `exam_test_...`, copy project vào thư mục tạm, tạo cấu hình riêng cho bản sao và chạy một PHP server tạm trên cổng trống. Mọi thao tác thêm/sửa/xóa, bỏ bảng/cột, xóa module, thử import lại SQL đều thực hiện ở bản sao. Database `php_exam_shop` và ảnh upload đang dùng được giữ nguyên.

Các nhóm kiểm tra gồm CRUD; số tiền có phần thập phân; validation; tìm kiếm và phân trang; input mảng và SQL injection; JSON API; xóa sản phẩm đang có trong đơn hàng; xác minh nội dung ảnh, giữ/thay/xóa ảnh; import SQL hai lần không mất dữ liệu; chuyển vào thư mục con; chạy khi tắt từng module và xóa thật cột/bảng liên quan; lỗi thiếu database/bảng.

Cuối lượt chạy, script in `PASS`/`FAIL`, tổng kết và tự dọn server, database cùng thư mục tạm. Mã thoát `0` nghĩa là tất cả đạt, `1` nghĩa là có lỗi. Nếu bạn cưỡng chế tắt tiến trình giữa chừng, xem tên database được in đầu lượt chạy để tự xóa **đúng database `exam_test_...` đó** trong phpMyAdmin; không xóa database đang học.

Kiểm thử này xác nhận hành vi bằng HTTP thật trên PHP/MySQL đang có. Nó không thay thế việc mở trình duyệt kiểm tra giao diện, và không khẳng định project đã được thử trên mọi phiên bản PHP/MySQL hoặc mọi máy tính.
