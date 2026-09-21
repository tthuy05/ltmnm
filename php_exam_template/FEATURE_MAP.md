# FEATURE MAP — BẬT, TẮT VÀ PHỤ THUỘC

## Cấu hình nhanh

Bốn module được bật/tắt tại `config/features.php`:

```php
$features = [
    'search' => true,
    'pagination' => true,
    'category' => true,
    'upload' => true,
];
```

Đổi `true` thành `false` trước. Chạy lại CRUD. Chỉ xóa file/cột/bảng sau khi bản tắt đã chạy đúng.

| Chức năng | File chính | Phụ thuộc database/filesystem | Tắt bằng config? |
|---|---|---|---:|
| Kết nối PDO | `config/settings.php`, `config/database.php` | MySQL + `pdo_mysql` | Không |
| Cấu hình từng máy | `config/local.php` | File tự tạo, Git bỏ qua | Không |
| Kiểm tra cài đặt | `setup.php` | Chỉ đọc cấu hình/schema | Không |
| Hàm input/output chung | `includes/functions.php` | Không | Không |
| Validation/upload | `includes/product_helpers.php` | `uploads/` khi bật Upload | Không |
| Form Add/Edit | `includes/product_form.php` | Nhận `$features` | Không |
| List/Add/Edit/View/Delete | `products.php`, `product_*.php` | Bảng `products` và 4 cột core | Không |
| Search | `products.php` | PDO | Có: `search` |
| Pagination | `products.php` | PDO | Có: `pagination` |
| Category/JOIN | CRUD, `categories.php`, API | `categories`, `products.category_id` | Có: `category` |
| Upload | Add/Edit/List/View/Delete | `products.image`, `uploads/` | Có: `upload` |
| Login/Session/Cookie | `auth/*` | PHP session | Xóa thư mục nếu không dùng |
| AJAX/JSON | `ajax_demo.php`, `api/*` | JavaScript + PDO | Xóa nếu không dùng |
| JavaScript demo | `javascript_demo.php` | Trình duyệt | Xóa nếu không dùng |
| OOP demo | `classes/*` | Không; CRUD không require | Xóa nếu không dùng |
| SQL orders | `orders`, `order_details` | Products + Customers | Bỏ khỏi SQL nếu đề không hỏi |

## Bốn cột core

CRUD luôn cần:

```text
product_id
name
price
quantity
```

Category thêm `category_id`; Upload thêm `image`. `setup.php` kiểm tra đúng theo các feature đang bật.

## Sơ đồ phụ thuộc

```text
config/settings.php <- config/local.php (nếu có)
        |
config/database.php -> $conn
        |
        +-- products.php / product_*.php (CORE)
        |      +-- includes/functions.php
        |      +-- includes/product_helpers.php
        |      +-- includes/product_form.php
        |      +-- categories + category_id (OPTIONAL)
        |      +-- image + uploads/ (OPTIONAL)
        |
        +-- categories.php (OPTIONAL)
        +-- api/* (OPTIONAL)

auth/*, classes/* và javascript_demo.php độc lập với CRUD.
```

## Thứ tự bỏ module an toàn

### Search

1. Đặt `search=false`.
2. Kiểm tra `products.php?keyword=Laptop` vẫn hiện danh sách không lọc.
3. Khi nộp bản gọn, có thể xóa các block `OPTIONAL: SEARCH`.

### Pagination

1. Đặt `pagination=false`.
2. Kiểm tra danh sách hiện mọi bản ghi và không có số trang.
3. Có thể xóa COUNT/page/LIMIT cùng HTML pagination nếu đề yêu cầu code thật tối giản.

### Category

1. Đặt `category=false`.
2. Test List/Add/Edit/View/Delete.
3. Bỏ `category_id` và foreign key khỏi bảng chính; bỏ bảng `categories`.
4. Xóa `categories.php` và các block `OPTIONAL: CATEGORY` nếu muốn.

### Upload

1. Đặt `upload=false`.
2. Test CRUD khi bảng vẫn còn cột `image`.
3. Bỏ cột `image`, test lại.
4. Xóa block `OPTIONAL: FILE UPLOAD` và thư mục ảnh nếu đề không cần.

### Login, AJAX, JavaScript, OOP

Các phần này không được CRUD require. Có thể bỏ thư mục/file tương ứng; nhớ xóa link menu dẫn đến file đã bỏ.

## Khi thêm một field mới

Sửa đủ các nơi theo thứ tự:

```text
SQL schema
→ read_product_input + validate_product
→ product_form
→ INSERT/UPDATE
→ List/View
→ JSON API (nếu giữ)
```

Sau mỗi thay đổi, mở `setup.php`, rồi test thêm–xem–sửa–xóa một bản ghi mới.
