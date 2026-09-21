# ADAPT EXAM — ĐỔI TEMPLATE THEO ĐỀ THI

Mục tiêu là đổi đúng entity, field, kiểu dữ liệu và chức năng được chấm. Không thay tên máy móc rồi giữ validation sai kiểu.

## 1. Gạch chân 5 thứ trong đề

```text
1. Chủ thể chính là gì?
2. Thuộc tính và kiểu dữ liệu là gì?
3. Có bao nhiêu bảng?
4. Primary Key / Foreign Key nằm đâu?
5. Đề yêu cầu chức năng nào?
```

Ví dụ “Quản lý sinh viên: mã, họ tên, email, lớp; thêm/sửa/xóa/tìm kiếm”:

```text
Entity: Student
Table: students
Fields: student_id INT, name VARCHAR, email VARCHAR, class_name VARCHAR
Tables: 1
Features: CRUD + Search
```

Không có bảng liên quan thì đặt `category=false`; không có ảnh thì đặt `upload=false`.

## 2. Quy trình 10 phút đầu

```text
BƯỚC 1  Viết entity và tên bảng.
BƯỚC 2  Viết từng field cùng kiểu SQL.
BƯỚC 3  Đánh dấu PK, AUTO_INCREMENT, UNIQUE, NOT NULL.
BƯỚC 4  Vẽ quan hệ và FK nếu có nhiều bảng.
BƯỚC 5  Tick feature checklist.
BƯỚC 6  Copy project sang thư mục đề mới; giữ bản gốc.
BƯỚC 7  Sửa SQL rồi import vào database mới.
BƯỚC 8  Sửa helper + form + INSERT/UPDATE.
BƯỚC 9  Sửa List/View/Search/JOIN/API.
BƯỚC 10 Test Add → View → Edit → Delete và xóa tên chủ đề cũ.
```

Feature checklist:

```text
[ ] CRUD             [ ] Search          [ ] Pagination
[ ] Category/JOIN    [ ] Upload          [ ] Login/Session/Cookie
[ ] JavaScript       [ ] AJAX/JSON       [ ] OOP
[ ] GROUP BY         [ ] Orders/chi tiết
```

## 3. Chuỗi file phải sửa khi đổi field

```text
sql/database.sql
    ↓
includes/product_helpers.php   read_product_input + validation
    ↓
includes/product_form.php      label + input
    ↓
product_add.php                INSERT
product_edit.php               SELECT cũ + UPDATE
    ↓
products.php                   SELECT + table + search
product_view.php               SELECT + detail
    ↓
api/*                          nếu giữ AJAX
```

Nếu đổi tên `products.php` thành `students.php`, sửa luôn link, redirect và đường dẫn trong các file liên quan.

## 4. Kiểu dữ liệu phải đi cùng input và validation

| Dữ liệu | SQL | HTML thường dùng | PHP kiểm tra |
|---|---|---|---|
| Tên ngắn | `VARCHAR(150)` | `text`, `maxlength` | không rỗng, giới hạn độ dài |
| Email | `VARCHAR(150) UNIQUE` | `email` | `filter_var(..., FILTER_VALIDATE_EMAIL)` |
| Số lượng | `INT` | `number step=1` | số nguyên, min/max |
| Giá/lương | `DECIMAL(12,2)` | `number step=0.01` | chuỗi số, tối đa 2 số lẻ |
| Ngày sinh | `DATE` | `date` | kiểm tra định dạng/ngày hợp lệ |
| Mô tả | `TEXT` | `textarea` | giới hạn độ dài nếu cần |
| Khóa ngoại | `INT` | `select` | ID dương và tồn tại trong bảng cha |
| Ảnh | `VARCHAR(255)` | `file` | size, phần mở rộng, nội dung ảnh |

Không đổi `price` thành `birthday` rồi giữ `type="number"` và validation giá. Đây là lỗi đổi đề phổ biến nhất.

## 5. Các bản cấu hình nhanh

### Chỉ CRUD một bảng

Trong `config/features.php`:

```php
'search' => false,
'pagination' => false,
'category' => false,
'upload' => false,
```

Giữ `config/`, `includes/`, List/Add/Edit/View/Delete, CSS và SQL một bảng. `auth/`, `api/`, `classes/`, demo JavaScript có thể bỏ.

### CRUD + Search

Đặt Search `true`, ba feature còn lại theo đề. Query dùng prepared `LIKE :keyword`.

### CRUD + Search + Pagination

Đặt Search và Pagination `true`. Giữ từ khóa khi tạo URL số trang. Đây là bản nên ôn kỹ nhất.

### Hai bảng 1–N

Đặt Category `true`, nhưng có thể đổi ý nghĩa thành Class, Department hoặc Book Category. Sửa đồng bộ tên bảng, FK, JOIN, `<select>` và câu kiểm tra FK.

## 6. Bỏ từng feature

### Search / Pagination

Đổi feature tương ứng thành `false` và test. Nếu đề yêu cầu code ngắn nhất, sau đó xóa các block comment `OPTIONAL: SEARCH` hoặc `OPTIONAL: PAGINATION` trong `products.php`.

### Category

1. Đặt `category=false`.
2. Test toàn bộ CRUD.
3. Bỏ cột FK, constraint và bảng Category khỏi SQL.
4. Bỏ `categories.php` và link menu nếu nộp bản gọn.

### Upload

1. Đặt `upload=false`.
2. Test CRUD.
3. Bỏ cột `image` khỏi SQL.
4. Có thể bỏ phần upload helper và thư mục `uploads` nếu đề không cần.

### Login / Cookie

Xóa hoặc không dùng `auth/`; CRUD không phụ thuộc login. Nếu đề yêu cầu bảo vệ trang, thêm `session_start()` và kiểm tra session trước HTML.

### AJAX / JavaScript / OOP

- AJAX: bỏ `ajax_demo.php`, `api/` và link menu.
- JavaScript: bỏ hai trang demo; xóa không ảnh hưởng xác nhận Delete vì Delete có trang POST riêng.
- OOP: bỏ `classes/`; CRUD procedural PDO vẫn chạy.

## 7. Ví dụ A — Shop → Sinh viên một bảng

Schema:

```sql
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20),
    birthday DATE
);
```

Đổi file/biến:

```text
products.php       → students.php
product_add.php    → student_add.php
product_edit.php   → student_edit.php
product_view.php   → student_view.php
product_delete.php → student_delete.php
product_id         → student_id
```

Form: text Name, email Email, tel Phone, date Birthday. Helper dùng `FILTER_VALIDATE_EMAIL`; không dùng validation giá/số lượng. INSERT/UPDATE liệt kê bốn field mới. Search theo `name` hoặc `email`. Giữ CRUD + Search; tắt Category/Upload, thường tắt Pagination nếu đề không hỏi.

## 8. Ví dụ B — Shop → Sách + Thể loại

```sql
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);
```

Mapping:

```text
Product/products/product_id/name
→ Book/books/book_id/title
quantity → author (đổi sang text và validation chuỗi)
price → price
Category giữ nguyên ý nghĩa thể loại
```

JOIN:

```sql
SELECT b.*, c.category_name
FROM books AS b
LEFT JOIN categories AS c ON b.category_id = c.category_id;
```

Form: Title, Author, Price, Category. Search theo `b.title`. Giữ CRUD, Search, Category/JOIN; Upload/Pagination theo đề.

## 9. Ví dụ C — Nhân viên + Phòng ban

```sql
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    salary DECIMAL(12,2) NOT NULL,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(department_id)
);
```

Mapping Category → Department và Product → Employee. Form có Name, Salary, Department. Bỏ Quantity/Image. Validation Salary giống Price. JOIN:

```sql
SELECT e.*, d.department_name
FROM employees AS e
LEFT JOIN departments AS d ON e.department_id = d.department_id;
```

Giữ CRUD, JOIN và Pagination nếu đề yêu cầu nhiều bản ghi. Sửa tên function/biến để không còn `$product` trong bài Employee.

## 10. Ví dụ D — Món ăn + Danh mục món

```sql
CREATE TABLE food_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE foods (
    food_id INT AUTO_INCREMENT PRIMARY KEY,
    food_name VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    description TEXT,
    category_id INT,
    image VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES food_categories(category_id)
);
```

Đổi Product → Food, `name` → `food_name`, `quantity` → `description`. `description` phải dùng textarea và validation chuỗi. Giữ Price, Category và Upload. Search theo tên món. API phải đổi field JSON nếu AJAX còn được chấm.

## 11. Kiểm tra tính độc lập

Sau mỗi lần bỏ feature:

```text
[ ] Search off: danh sách vẫn chạy, keyword không lọc
[ ] Pagination off: hiện đủ bản ghi
[ ] Category off: CRUD chạy dù không còn categories/category_id
[ ] Upload off: CRUD chạy dù không còn image
[ ] Bỏ auth: CRUD truy cập trực tiếp
[ ] Bỏ API/JS/OOP: CRUD PHP vẫn chạy
```

Repository có `tests/regression.php` chạy các biến thể này trên database tạm. Trong phòng thi, test thủ công vẫn là bước bắt buộc.

## 12. Checklist trước khi nộp

```text
[ ] Database và table đúng tên đề
[ ] Field đúng tên và đúng kiểu
[ ] PK/AUTO_INCREMENT đúng
[ ] FK/JOIN đúng nếu có hai bảng
[ ] PDO kết nối được trên máy nộp
[ ] GET/POST đưa vào SQL bằng Prepared Statement
[ ] Form input khớp kiểu dữ liệu
[ ] SELECT / INSERT / UPDATE / DELETE đều chạy
[ ] GET Delete không tự xóa; POST xác nhận mới xóa
[ ] Search/Pagination chạy nếu đề yêu cầu
[ ] Upload kiểm tra được nếu đề yêu cầu
[ ] Không còn tên Product/field SQL cũ
[ ] Feature không được yêu cầu đã tắt hoặc bỏ
[ ] setup.php không báo lỗi module đang dùng
[ ] Import SQL vào database trống chạy được
```
