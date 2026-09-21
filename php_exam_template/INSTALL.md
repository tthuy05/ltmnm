# HƯỚNG DẪN CÀI ĐẶT VÀ CHUYỂN MÁY

Project chạy bằng PHP và MySQL trong Laragon. Visual Studio hoặc VS Code chỉ dùng để mở và sửa code; phpMyAdmin dùng để import, export và xem database.

## 1. Điều kiện tối thiểu

- Windows có Laragon, Apache hoặc Nginx và MySQL đang chạy.
- PHP 7.4 trở lên; khuyến nghị PHP 8.x.
- PHP bật `pdo_mysql`, `session`, `file_uploads` và có hàm `getimagesize` nếu dùng upload.
- MySQL/MariaDB hỗ trợ InnoDB và `utf8mb4`.
- Trình duyệt hiện đại nếu dùng JavaScript/AJAX.

Project không cần Composer, Node.js hoặc Internet sau khi đã tải mã nguồn.

## 2. Đặt đúng thư mục

### Cách A — Chỉ copy project website

Copy thư mục `php_exam_template` vào:

```text
C:\laragon\www\php_exam_template
```

URL tương ứng:

```text
http://localhost/php_exam_template/
```

### Cách B — Clone cả repository

Nếu repository nằm ở:

```text
C:\laragon\www\ltmnm\php_exam_template
```

URL tương ứng:

```text
http://localhost/ltmnm/php_exam_template/
```

Quy tắc: phần đường dẫn sau `localhost/` phải giống các thư mục nằm dưới `C:\laragon\www`.

## 3. Khởi động Laragon

1. Mở Laragon.
2. Chọn **Start All**.
3. Chờ Apache/Nginx và MySQL chuyển sang trạng thái chạy.
4. Chọn **Web** hoặc mở `http://localhost/` để kiểm tra web server.
5. Chọn **Database** để mở phpMyAdmin.

Nếu đổi phiên bản PHP trong Laragon, hãy Stop rồi Start lại dịch vụ. PHP trong Terminal và PHP mà Apache đang dùng có thể khác nhau; trang `setup.php` luôn cho biết phiên bản thực tế của website.

## 4. Import database lần đầu

1. Mở phpMyAdmin.
2. Chọn tab **Import**.
3. Chọn file `php_exam_template/sql/database.sql`.
4. Giữ định dạng SQL, chọn **Import/Go**.
5. Kiểm tra database `php_exam_shop` và các bảng `categories`, `products`, `customers`, `orders`, `order_details`.

`database.sql` dùng `CREATE ... IF NOT EXISTS` và `INSERT IGNORE`. Import lại không chủ động xóa bảng hoặc ghi đè các ID đã tồn tại. Nó không phải công cụ tự nâng cấp một schema cũ; nếu `setup.php` báo thiếu cột, hãy so sánh schema và sao lưu trước khi sửa.

File `sql/reset.sql` xóa toàn bộ các bảng mẫu. Chỉ chạy file này khi chắc chắn muốn làm lại, sau khi đã export dữ liệu cần giữ. Chạy `database.sql` sau reset để tạo lại dữ liệu mẫu.

## 5. Cấu hình MySQL cho từng máy

Cấu hình mặc định:

```text
host:     127.0.0.1
port:     3306
database: php_exam_shop
username: root
password: rỗng
```

Nếu máy trường dùng thông số khác:

1. Copy `config/local.example.php` thành `config/local.php`.
2. Mở `config/local.php` bằng Visual Studio/VS Code.
3. Sửa đúng 5 biến:

```php
<?php
$host = '127.0.0.1';
$port = 3306;
$databaseName = 'php_exam_shop';
$username = 'root';
$password = '';
```

Không sửa `config/database.php` chỉ để đổi mật khẩu/cổng. `local.php` được Git bỏ qua nên cấu hình riêng và mật khẩu không bị push lên repository.

Nếu mật khẩu chứa `$`, giữ dấu nháy đơn như mẫu: `'$abc'`.

## 6. Chạy trang kiểm tra

Mở một trong hai URL, tùy cách đặt thư mục:

```text
http://localhost/php_exam_template/setup.php
http://localhost/ltmnm/php_exam_template/setup.php
```

Trang này chỉ đọc cấu hình và schema, không tạo hoặc xóa dữ liệu. Xử lý tất cả dòng **Cần sửa**. Cảnh báo về session/upload chỉ ảnh hưởng module tương ứng.

Sau khi trang kiểm tra đạt, thử thực tế:

1. Mở danh sách sản phẩm.
2. Thêm sản phẩm có giá `12.34` và không chọn ảnh.
3. Sửa tên, số lượng; kiểm tra ảnh cũ được giữ nếu có.
4. Xóa chính sản phẩm vừa tạo.
5. Tìm kiếm và chuyển trang.
6. Mở AJAX; đăng nhập demo nếu đề yêu cầu.

## 7. Bật/tắt module tùy chọn

Mở `config/features.php`:

```php
$features = [
    'search' => true,
    'pagination' => true,
    'category' => true,
    'upload' => true,
];
```

Đổi `true` thành `false` để tắt module. Khi `category=false`, CRUD không truy vấn bảng `categories` hoặc cột `category_id`. Khi `upload=false`, CRUD không truy vấn cột `image`. Vì vậy có thể dùng schema một bảng tối giản sau khi tắt đúng feature.

## 8. Chuyển project sang máy trường

Nếu chỉ cần dữ liệu mẫu:

1. Copy/clone code.
2. Import `sql/database.sql`.
3. Tạo `config/local.php` nếu cấu hình khác.
4. Mở `setup.php`.

Nếu cần giữ dữ liệu và ảnh đã nhập ở nhà:

1. Tại máy cũ, phpMyAdmin → chọn database → **Export** → SQL → **Go**.
2. Copy file SQL đã export.
3. Copy toàn bộ file bên trong thư mục `uploads/`.
4. Tại máy mới, đặt code trong `www`.
5. Tạo database rỗng đúng tên rồi import bản export của bạn. Không cần import `database.sql` trước bản backup đầy đủ.
6. Copy ảnh vào `uploads/`.
7. Tạo `config/local.php`, chạy `setup.php`, thử CRUD.

Git không mang theo `config/local.php` và ảnh người dùng trong `uploads/`; đây là chủ ý để tránh đẩy mật khẩu và file cá nhân lên GitHub.

## 9. Lỗi thường gặp

| Hiện tượng | Nguyên nhân thường gặp | Cách xử lý |
|---|---|---|
| `404 Not Found` | URL không khớp vị trí dưới `www` | Xem lại mục 2 và tên thư mục |
| Không kết nối MySQL | MySQL chưa chạy, sai host/port | Start All; sửa `config/local.php`; mở `setup.php` |
| `Unknown database` | Chưa import hoặc sai tên database | Import `database.sql` hoặc sửa `$databaseName` |
| `Access denied` | Sai username/password hoặc thiếu quyền | Sửa `local.php`, kiểm tra tài khoản trong phpMyAdmin |
| `could not find driver` | PHP web chưa bật `pdo_mysql` | Laragon → PHP → Extensions → bật PDO MySQL, restart |
| Thiếu bảng/cột | Import nhầm DB hoặc schema cũ | Xem `setup.php`; backup rồi sửa/import đúng SQL |
| Upload báo lỗi | File quá 2 MB, sai nội dung, thư mục không ghi được | Dùng JPG/PNG thật; kiểm tra `uploads`, `upload_max_filesize`, `post_max_size` |
| Đăng nhập không giữ session | `session.save_path` không ghi được | Xem dòng session tại `setup.php`, sửa `php.ini`, restart |
| AJAX báo không tải được | API lỗi database hoặc URL sai | Mở trực tiếp `api/search_products.php?keyword=Laptop`; xem JSON lỗi |
| Xóa sản phẩm báo đang dùng | Sản phẩm có trong `order_details` | Đây là bảo vệ lịch sử đơn; sửa sản phẩm hoặc xóa quan hệ theo yêu cầu đề |
| Sửa `php.ini` nhưng không đổi | Sửa nhầm PHP CLI | Xem đường dẫn `php.ini` tại `setup.php`, restart Laragon |
| Trang trắng | PHP lỗi và tắt hiển thị lỗi | Kiểm tra Apache/PHP error log; chạy lint bằng Terminal Laragon |

## 10. Kiểm tra bằng Terminal (tùy chọn)

Mở Terminal từ Laragon tại thư mục repository:

```text
php -v
php -m
php -l php_exam_template/products.php
php tests/regression.php
```

`tests/regression.php` cần quyền tạo/xóa một database có tên ngẫu nhiên `exam_test_...`. Nó chạy trên bản copy tạm và không sửa `php_exam_shop`. Nếu tài khoản MySQL trường không có quyền tạo database, vẫn có thể dùng project bình thường và kiểm tra thủ công qua `setup.php`.

