# HƯỚNG DẪN SỬ DỤNG PROJECT

## 1. Trang nào dùng để làm gì?

| Trang | Chức năng |
|---|---|
| `index.php` | Menu các phần học |
| `setup.php` | Kiểm tra PHP, PDO, database, schema, upload và session |
| `products.php` | Danh sách, tìm kiếm, phân trang |
| `product_add.php` | Thêm sản phẩm bằng POST + INSERT |
| `product_view.php` | Xem một bản ghi bằng prepared SELECT |
| `product_edit.php` | GET dữ liệu cũ, POST để UPDATE |
| `product_delete.php` | GET xác nhận, POST mới DELETE |
| `categories.php` | Thêm danh mục và xem quan hệ 1–N |
| `javascript_demo.php` | DOM, Event, tạo phần tử |
| `ajax_demo.php` | Fetch API và JSON không tải lại trang |
| `auth/login.php` | Session + Cookie demo |

## 2. Thực hành CRUD theo đúng thứ tự

### Thêm

1. Mở **Sản phẩm → Thêm sản phẩm**.
2. Điền tên, giá, số lượng.
3. Danh mục và ảnh là tùy chọn.
4. Bấm **Lưu sản phẩm**.

Giá cho phép tối đa 2 chữ số thập phân, ví dụ `125000.50`. Không dùng dấu chấm để phân cách hàng nghìn trong input; nhập `125000`, không nhập `125.000`.

### Xem

Tại danh sách, bấm **Xem**. ID trên URL được kiểm tra là số nguyên dương và được đưa vào prepared statement.

### Sửa

1. Bấm **Sửa**.
2. Trang tải dữ liệu cũ từ database.
3. Sửa field cần thiết.
4. Không chọn ảnh mới thì giữ ảnh cũ; chọn ảnh mới thì ảnh cũ được dọn sau khi UPDATE thành công.

### Xóa

1. Bấm **Xóa** để mở trang xác nhận. GET không xóa dữ liệu.
2. Bấm **Xác nhận xóa** để gửi POST.
3. Nếu sản phẩm đã xuất hiện trong `order_details`, project trả thông báo và giữ dữ liệu lịch sử.

Để kiểm tra xóa thành công, hãy xóa một sản phẩm vừa tự thêm thay vì sản phẩm mẫu đang nằm trong đơn hàng.

## 3. Search và Pagination

- Search dùng GET: từ khóa xuất hiện trên URL và query dùng `LIKE :keyword`.
- Pagination mặc định 5 sản phẩm/trang.
- Khi tìm kiếm rồi chuyển trang, từ khóa được giữ lại.
- Trang âm, trang không hợp lệ được đưa về trang 1; trang quá lớn được đưa về trang cuối.

Tắt từng phần trong `config/features.php`:

```php
'search' => false,
'pagination' => false,
```

## 4. Category và JOIN

Quan hệ mặc định:

```text
categories (1) ---- (N) products
```

`products.php` và `product_view.php` dùng `LEFT JOIN`, vì vậy sản phẩm chưa phân loại vẫn hiển thị. Form Add/Edit kiểm tra ID danh mục có thật trước khi ghi.

Nếu đề chỉ có một bảng:

1. Đặt `'category' => false` trong `config/features.php`.
2. CRUD lập tức ngừng truy vấn bảng/cột category.
3. Có thể bỏ `category_id`, foreign key và bảng `categories` khỏi SQL của đề mới.
4. Có thể xóa `categories.php` và link menu nếu muốn nộp bản tối giản.

## 5. Upload ảnh

Project nhận JPG/JPEG/PNG thật, tối đa 2 MiB. Nó kiểm tra cả phần mở rộng và nội dung ảnh, tạo tên ngẫu nhiên và lưu trong `uploads/`.

Nếu đề không yêu cầu:

1. Đặt `'upload' => false`.
2. Input file, cột ảnh và xử lý `$_FILES` sẽ không chạy.
3. Có thể bỏ cột `image` khỏi schema đề mới.

Khi chuyển máy và muốn giữ ảnh, phải copy nội dung `uploads/` riêng vì Git đang bỏ qua ảnh người dùng.

## 6. Login, Session và Cookie

Tài khoản demo:

```text
username: admin
password: 123
```

Checkbox ghi nhớ chỉ lưu username vào Cookie trong 1 giờ. CRUD mặc định không bắt buộc đăng nhập, để có thể bỏ thư mục `auth/` mà không làm hỏng bài chính.

Nếu đề yêu cầu bảo vệ CRUD, đặt đoạn sau trước mọi HTML ở trang cần bảo vệ và sửa đường dẫn login theo vị trí file:

```php
session_start();
if (empty($_SESSION['user'])) {
    header('Location: auth/login.php');
    exit;
}
```

## 7. JavaScript và AJAX

`javascript_demo.php` minh họa `getElementById`, `querySelector`, `value`, `innerText`, `innerHTML`, `style`, `createElement`, `appendChild`, `addEventListener`.

`ajax_demo.php` gọi:

```text
api/search_products.php?keyword=Laptop
```

API trả JSON. Có thể mở URL trực tiếp để phân biệt lỗi JavaScript với lỗi PHP/database. `api/products_json.php` trả danh sách đầy đủ. API tự điều chỉnh khi tắt Category hoặc Upload.

AJAX độc lập với CRUD HTML. Xóa `ajax_demo.php` và `api/` không làm hỏng Add/Edit/Delete.

## 8. OOP demo

Các file trong `classes/` chỉ dùng để học class, property, method, constructor, inheritance, abstract class và interface. CRUD không require các class này.

Ví dụ trong Terminal Laragon:

```text
php -r "require 'php_exam_template/classes/Product.php'; echo (new Product('Laptop', 15000000))->printInfo();"
```

Nếu đang đứng bên trong thư mục `php_exam_template`, bỏ phần `php_exam_template/` khỏi đường dẫn.

## 9. Muốn sửa field sản phẩm

Luôn sửa theo chuỗi này để tránh sót:

```text
sql/database.sql
    ↓
includes/product_helpers.php   đọc + validation
    ↓
includes/product_form.php      label + input
    ↓
product_add.php / product_edit.php
    ↓
products.php / product_view.php
    ↓
api/* nếu vẫn dùng AJAX
```

Ví dụ đổi `quantity` thành `description`: đổi kiểu cột sang `TEXT`, đổi validation số thành validation chuỗi, đổi `<input type="number">` thành `<textarea>`, rồi đổi INSERT/UPDATE/List/View. Không chỉ đổi tên biến; kiểu input và validation cũng phải khớp kiểu dữ liệu.

## 10. Quy trình tự kiểm tra trước khi nộp

1. Mở `setup.php`, không còn dòng **Cần sửa** cho module đang bật.
2. Thêm một bản ghi có dữ liệu dễ nhận biết.
3. Xem đúng bản ghi.
4. Sửa và kiểm tra database thay đổi.
5. Xóa bản ghi vừa tạo.
6. Test Search/Pagination/Category/Upload nếu đề yêu cầu.
7. Tắt hoặc xóa module đề không yêu cầu.
8. Tìm toàn project xem còn tên bảng/field/chủ đề cũ không.
9. Export SQL cần nộp và mở lại để kiểm tra có `CREATE TABLE` cùng dữ liệu mẫu.
10. Chạy lại từ một database trống nếu còn thời gian.

Đọc `ADAPT_EXAM.md` để xem bốn ví dụ đổi đề hoàn chỉnh và `REVIEW.md` để ôn nhanh cú pháp.

