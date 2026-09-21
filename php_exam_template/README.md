# PHP Exam Template — Mini Shop Management

Template học PHP thuần, MySQL, PDO, CRUD, Session/Cookie, JavaScript và Fetch API. Mỗi trang xử lý một nhiệm vụ; form thêm/sửa và các bước kiểm tra dữ liệu được dùng chung để dễ chỉnh khi đổi đề.

## Bắt đầu ở đây

1. Copy thư mục `php_exam_template` vào `C:\laragon\www`.
2. Laragon → **Start All**.
3. Mở phpMyAdmin → import `sql/database.sql`.
4. Mở [setup.php](http://localhost/php_exam_template/setup.php) để kiểm tra PHP, kết nối database và môi trường.
5. Mở [trang chủ](http://localhost/php_exam_template/) và thử thêm một sản phẩm.

Máy dùng thông số MySQL khác: copy `config/local.example.php` thành `config/local.php`, sửa host/port/tên database/tài khoản/mật khẩu theo máy đó. Không cần sửa các trang CRUD.

Đọc **[INSTALL.md](INSTALL.md)** nếu chưa quen Laragon, nếu clone cả repository, hoặc nếu cần chuyển dữ liệu sang máy trường. Đọc **[USAGE.md](USAGE.md)** để dùng từng chức năng.

## Môi trường

- PHP 7.4 trở lên (khuyến nghị PHP 8.x) có PDO MySQL; upload ảnh cần hàm `getimagesize`, PHP cho phép upload và thư mục `uploads` có quyền ghi.
- MySQL hoặc một môi trường tương thích MySQL được cấu hình đúng.
- Apache/Nginx của Laragon, hoặc PHP development server để thực hành cục bộ.
- Trình duyệt; JavaScript chỉ cần cho các trang demo JavaScript/AJAX.

Bản trước đã được chạy với PHP 8.3 và MySQL 8.4. Đây là mốc kiểm thử, không phải cam kết đã thử mọi phiên bản PHP/MySQL, MariaDB hay mọi bản Laragon. `setup.php` giúp kiểm tra điều kiện trên máy mới; vẫn cần thử CRUD sau khi cài.

Visual Studio/VS Code là trình soạn code. Laragon mới là phần chạy PHP/MySQL; phpMyAdmin dùng để quản lý và import/export database. Project không cần Node.js, Composer hoặc tải thư viện qua mạng khi sử dụng.

## Cấu trúc dễ sửa

```text
php_exam_template/
├── config/
│   ├── settings.php          Cấu hình mặc định dùng chung
│   ├── local.example.php     Mẫu cấu hình cho máy khác
│   ├── local.php             Tự tạo nếu cần; Git bỏ qua file này
│   ├── features.php          Bật/tắt search, pagination, category, upload
│   └── database.php          Tạo kết nối PDO
├── includes/
│   ├── functions.php         Hàm dùng chung: đọc input, escape HTML...
│   ├── product_helpers.php   Kiểm tra dữ liệu sản phẩm, xử lý ảnh
│   └── product_form.php      HTML form dùng chung cho thêm và sửa
├── products.php             Danh sách, tìm kiếm, phân trang
├── product_add.php          Nhận POST và INSERT
├── product_edit.php         Lấy bản ghi, nhận POST và UPDATE
├── product_view.php         Xem một sản phẩm
├── product_delete.php       GET xác nhận; POST mới xóa
├── categories.php           Thêm/danh sách danh mục, đếm sản phẩm
├── setup.php                Chẩn đoán; không import hay reset dữ liệu
├── auth/                    Demo Session và Cookie
├── api/                     JSON cho AJAX và ví dụ API
├── classes/                 Ví dụ OOP độc lập với CRUD
├── uploads/                 Ảnh do người dùng tải lên
├── sql/
│   ├── database.sql          Tạo bảng và bổ sung dữ liệu mẫu
│   ├── reset.sql             Xóa bảng mẫu; chỉ dùng khi muốn làm lại
│   └── sample_queries.sql    Bài tập SELECT/JOIN/CRUD
├── javascript_demo.php      Ví dụ DOM và Event
├── ajax_demo.php            Tìm kiếm bằng Fetch
└── style.css                CSS chung
```

## Muốn đổi gì thì sửa đâu?

| Thay đổi | File cần đọc/sửa |
|---|---|
| Mật khẩu hoặc port MySQL của máy hiện tại | `config/local.php` |
| Bật/tắt bốn chức năng tùy chọn | `config/features.php` |
| Label, input của form thêm/sửa | `includes/product_form.php` |
| Quy tắc kiểm tra tên/giá/số lượng hoặc upload | `includes/product_helpers.php` |
| Tên bảng/cột và câu INSERT/UPDATE | `product_add.php`, `product_edit.php` và SQL |
| Danh sách, tìm kiếm, số dòng mỗi trang | `products.php` |
| Cột trên trang chi tiết | `product_view.php` |
| Cách xác nhận và xử lý xóa | `product_delete.php` |
| Màu sắc, khoảng cách, giao diện nhỏ | `style.css` |

Khi thêm một field, cần sửa đồng bộ **schema → đọc/kiểm tra input → form → INSERT/UPDATE → danh sách/chi tiết → API nếu giữ API**. [ADAPT_EXAM.md](ADAPT_EXAM.md) có ví dụ cụ thể.

## Những điểm cần nhớ

- Tài khoản demo: `admin` / `123`; đăng nhập không bảo vệ CRUD mặc định.
- Ảnh: JPG/JPEG/PNG, tối đa 2 MiB mỗi file; PHP cũng có giới hạn upload riêng.
- Sản phẩm được dùng trong chi tiết đơn hàng không thể xóa; dữ liệu lịch sử được giữ lại và trang báo lý do.
- `database.sql` không xóa bảng có sẵn. Import lại có thể thêm lại dòng mẫu còn thiếu; không phải công cụ nâng cấp schema hoặc khôi phục bản sao lưu.
- `reset.sql` là thao tác xóa dữ liệu. Sao lưu trước khi dùng; sau reset phải import lại `database.sql`.
- `config/local.php` và ảnh upload không được đưa vào Git. Chuyển máy phải mang bản sao database và ảnh nếu cần giữ dữ liệu thực hành.

## Tài liệu học và sử dụng

- [INSTALL.md](INSTALL.md): cài Laragon, cấu hình, kiểm tra, chuyển máy, xử lý lỗi.
- [USAGE.md](USAGE.md): hướng dẫn thao tác và bài thực hành từ đầu đến cuối.
- [FEATURE_MAP.md](FEATURE_MAP.md): các phần phụ thuộc nhau và cách tắt module.
- [ADAPT_EXAM.md](ADAPT_EXAM.md): đổi chủ đề, bỏ chức năng và checklist nộp bài.
- [REVIEW.md](REVIEW.md): bảng nhớ nhanh PHP, PDO, SQL, OOP và JavaScript.
