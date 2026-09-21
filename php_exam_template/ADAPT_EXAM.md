# ADAPT EXAM — ĐỔI TEMPLATE THEO ĐỀ THI

Tài liệu này là checklist thao tác. Chỉ giữ chức năng đề yêu cầu.

## 1. Cách đọc đề

Gạch chân đúng 5 thứ:

```text
1. Chủ thể chính là gì?
2. Các thuộc tính là gì?
3. Có bao nhiêu bảng?
4. Có quan hệ giữa các bảng không?
5. Đề yêu cầu chức năng gì?
```

Ví dụ đề: “Xây dựng website quản lý sinh viên. Mỗi sinh viên có mã sinh viên, họ tên, email, lớp. Cho phép thêm, sửa, xóa, tìm kiếm.”

Phân tích:

```text
Entity:  Student
Table:   students
Fields:  student_id, name, email, class_name
Feature: CRUD, Search
Số bảng: 1
```

Không có bảng liên quan nên bỏ Category, Foreign Key và JOIN.

## 2. Bảng đổi chủ đề

| Shop | Sinh viên | Sách | Nhân viên |
|---|---|---|---|
| Product | Student | Book | Employee |
| `products` | `students` | `books` | `employees` |
| `product_id` | `student_id` | `book_id` | `employee_id` |
| `name` | `name` | `title` | `name` |
| `price` | `score` hoặc `birthday` | `price` | `salary` |
| Category | Class | Category | Department |

Code CRUD về bản chất không đổi. Chủ yếu đổi:

```text
Tên bảng
Tên field
Label trên form
Tên biến
JOIN
```

## 3. Quy trình 10 phút đầu khi nhận đề

```text
BƯỚC 1   Xác định entity.
BƯỚC 2   Xác định field.
BƯỚC 3   Xác định số bảng.
BƯỚC 4   Xác định Primary Key / Foreign Key.
BƯỚC 5   Tick feature checklist.
BƯỚC 6   Import rồi sửa database.sql.
BƯỚC 7   Sửa SELECT và tên bảng.
BƯỚC 8   Sửa form Add/Edit.
BƯỚC 9   Sửa INSERT/UPDATE và biến $_POST.
BƯỚC 10  Xóa feature đề không yêu cầu, rồi test CRUD.
```

## 4. Feature checklist

```text
[ ] CRUD
[ ] Search
[ ] Pagination
[ ] Login
[ ] Session
[ ] Cookie
[ ] Upload
[ ] OOP
[ ] JavaScript
[ ] AJAX
[ ] JOIN
[ ] GROUP BY
```

Chỉ giữ mục đề yêu cầu. Đừng thêm chức năng để “cho đẹp” nếu đề không chấm.

## 5. Các cấu hình nhanh

### Bản tối giản khi đề chỉ yêu cầu CRUD

Chỉ cần:

```text
config/database.php
index.php        <- copy products.php rồi đổi tên, bỏ Search/Pagination/Category/Upload
add.php          <- copy product_add.php rồi bỏ Category/Upload
edit.php         <- copy product_edit.php rồi bỏ Category/Upload
delete.php       <- copy product_delete.php
database.sql     <- chỉ giữ một bảng chính
```

Không có Login, Cookie, Upload, OOP, AJAX, Search, Pagination.

### Bản CRUD + Search

Giữ PDO, 4 thao tác CRUD và khối `OPTIONAL FEATURE: SEARCH`. Bỏ Pagination, Login, Upload, AJAX; đổi query danh sách thành SELECT không có `LIMIT/OFFSET`.

### Bản CRUD + Search + Pagination

Giữ PDO, CRUD, Search, Pagination. Đây là bản ưu tiên để ôn. Khi bấm trang 2 phải giữ `keyword` trên URL.

### Bản 2 bảng

Giữ `categories` + `products`, Primary Key, Foreign Key, JOIN, CRUD, Search và Pagination. Form Add/Edit có `<select name="category_id">`.

## 6. Cách bỏ từng feature

### Bỏ Search

1. Xóa form `<form class="search-form" method="get">` trong `products.php`.
2. Xóa `$keyword = ...`.
3. Xóa hai khối `if ($keyword !== '')` khỏi câu COUNT và SELECT.
4. Xóa `bindValue(':keyword', ...)`.
5. Nếu còn Pagination, tạo URL trang chỉ với `page`.

Đổi từ:

```sql
SELECT * FROM products WHERE name LIKE :keyword
```

thành:

```sql
SELECT * FROM products
```

### Bỏ Pagination

1. Bỏ `$limit`, `$page`, `$offset`, `$totalRecords`, `$totalPages` và câu COUNT.
2. Bỏ `LIMIT :limit OFFSET :offset`.
3. Bỏ hai `bindValue` cho `:limit`, `:offset`.
4. Bỏ HTML `<nav class="pagination">` và dòng tổng.
5. Giữ Search nếu đề vẫn yêu cầu.

### Bỏ Login / Session

1. Không dùng thư mục `auth/`.
2. Không thêm `session_start()` hay đoạn kiểm tra `$_SESSION` vào CRUD.
3. Truy cập thẳng `products.php`.

CRUD hiện tại vốn không phụ thuộc Login.

### Bỏ Cookie

Xóa `setcookie(...)`, `$_COOKIE['last_user']` và checkbox “Ghi nhớ”. Session login vẫn hoạt động.

### Bỏ Upload

1. Bỏ cột `image` trong `database.sql`.
2. Bỏ `enctype="multipart/form-data"`.
3. Bỏ `<input type="file" name="image">`.
4. Bỏ khối `$_FILES`, `move_uploaded_file()`.
5. Bỏ `image` khỏi INSERT/UPDATE và mảng `execute`.
6. Bỏ cột Ảnh trong List và thẻ `<img>` trong View/Edit.

Sau khi xóa, INSERT cơ bản là:

```sql
INSERT INTO products (name, price, quantity, category_id)
VALUES (?, ?, ?, ?)
```

### Bỏ Category / chuyển về một bảng

1. Bỏ bảng `categories` và Foreign Key trong SQL.
2. Bỏ cột `category_id` khỏi bảng chính.
3. Bỏ query lấy `$categories` trong Add/Edit.
4. Bỏ `<select name="category_id">`.
5. Bỏ `category_id` khỏi INSERT/UPDATE.
6. Bỏ tên danh mục khỏi table HTML.

Đổi:

```sql
SELECT p.*, c.category_name
FROM products AS p
LEFT JOIN categories AS c ON p.category_id = c.category_id
```

thành:

```sql
SELECT * FROM products
```

### Bỏ AJAX

Xóa `api/`, `ajax_demo.php` và link AJAX trên menu. Giữ form GET, form POST và PHP render HTML. CRUD không gọi API nên vẫn chạy.

### Bỏ OOP

Không dùng thư mục `classes/`. CRUD đang dùng procedural PDO, không cần sửa gì khác.

### Bỏ JavaScript

Xóa `javascript_demo.php`, `ajax_demo.php`, `api/` và link menu. Có thể bỏ `onclick="return confirm(...)"`; CRUD PHP vẫn chạy.

## 7. Ví dụ A — Shop → Sinh viên

Đề mẫu:

```text
students: student_id, name, email, phone, birthday
Feature: CRUD, Search
```

| Cần đổi | Từ | Thành |
|---|---|---|
| Table | `products` | `students` |
| ID | `product_id` | `student_id` |
| Tên | `name` | `name` |
| Giá | `price` | `birthday` |
| Số lượng | `quantity` | `phone` |
| File list | `products.php` | `students.php` |
| File add | `product_add.php` | `student_add.php` |
| File edit | `product_edit.php` | `student_edit.php` |
| File delete | `product_delete.php` | `student_delete.php` |

SQL chính:

```sql
SELECT * FROM students ORDER BY student_id DESC;

INSERT INTO students (name, email, phone, birthday)
VALUES (?, ?, ?, ?);

UPDATE students
SET name = ?, email = ?, phone = ?, birthday = ?
WHERE student_id = ?;
```

Form đổi label thành Họ tên, Email, Điện thoại, Ngày sinh. Search dùng `WHERE name LIKE :keyword`. Bỏ Category/JOIN, Upload, Login, AJAX và Pagination nếu đề không yêu cầu. Giữ CRUD + Search.

## 8. Ví dụ B — Shop → Sách / Thư viện

```text
categories: category_id, category_name
books: book_id, title, author, price, category_id
Feature: CRUD, Search, JOIN
```

- `Product` → `Book`, `products` → `books`, `product_id` → `book_id`.
- `name` → `title`, `quantity` → `author`; giữ `price`, `category_id`.
- Đổi file thành `books.php`, `book_add.php`, `book_edit.php`, `book_delete.php`, `book_view.php`.
- Form có Title, Author, Price, Category.

JOIN mới:

```sql
SELECT b.*, c.category_name
FROM books AS b
LEFT JOIN categories AS c ON b.category_id = c.category_id;
```

Search đổi thành `WHERE b.title LIKE :keyword`. Giữ Category, JOIN, CRUD, Search; bỏ Login/AJAX/Upload nếu đề không yêu cầu.

## 9. Ví dụ C — Shop → Nhân viên + Phòng ban

```text
departments: department_id, department_name
employees: employee_id, name, salary, department_id
Feature: CRUD, JOIN, Pagination
```

- `categories` → `departments`; `category_id` → `department_id`; `category_name` → `department_name`.
- `products` → `employees`; `product_id` → `employee_id`; `price` → `salary`.
- Bỏ `quantity`, `image`; thêm field nếu đề yêu cầu như `email` hoặc `position`.
- File: `employees.php`, `employee_add.php`, `employee_edit.php`, `employee_delete.php`.
- Form: Name, Salary, Department `<select>`.

JOIN:

```sql
SELECT e.*, d.department_name
FROM employees AS e
JOIN departments AS d ON e.department_id = d.department_id;
```

Giữ CRUD, JOIN, Pagination. Chỉ giữ Search nếu đề có từ “tìm kiếm”. Bỏ Upload và Category cũ; thay toàn bộ bằng Department.

## 10. Ví dụ D — Shop → Món ăn + Danh mục món

```text
food_categories: category_id, category_name
foods: food_id, food_name, price, description, category_id
Feature: CRUD, Search, Upload, JOIN
```

- `categories` → `food_categories`; `products` → `foods`.
- `product_id` → `food_id`; `name` → `food_name`; giữ `price`, `category_id`, `image`.
- `quantity` → `description` và đổi input số thành `<textarea name="description">`.
- File: `foods.php`, `food_add.php`, `food_edit.php`, `food_delete.php`, `food_view.php`.

SQL list:

```sql
SELECT f.*, c.category_name
FROM foods AS f
LEFT JOIN food_categories AS c ON f.category_id = c.category_id
WHERE f.food_name LIKE :keyword;
```

Form giữ upload ảnh món và select danh mục. Giữ CRUD, Search, Upload, JOIN; bỏ Login, OOP, AJAX, Pagination nếu đề không yêu cầu.

## 11. Kiểm tra khi đổi sang đề một bảng

Nếu đề chỉ có `students`, cần thấy những thứ sau đã biến mất:

```text
categories
category_id
FOREIGN KEY
JOIN
<select>
```

Cần giữ:

```text
PDO
Prepared Statement
CRUD
Search/Pagination nếu đề yêu cầu
```

## 12. Checklist trước khi nộp bài

```text
[ ] Database đúng tên
[ ] Table đúng field
[ ] Primary Key đúng
[ ] Foreign Key đúng nếu có
[ ] PDO kết nối được
[ ] SELECT chạy
[ ] INSERT chạy
[ ] UPDATE chạy
[ ] DELETE chạy
[ ] Dữ liệu GET/POST đưa vào SQL bằng Prepared Statement
[ ] Form field đúng
[ ] Search chạy nếu đề yêu cầu
[ ] Pagination chạy nếu đề yêu cầu
[ ] JOIN đúng nếu có 2 bảng
[ ] Không còn code chức năng đề không yêu cầu
[ ] Không còn tên Product nếu đề là Student/Book/Employee
[ ] Không còn SQL dùng field cũ
[ ] Test lại toàn bộ trước khi nộp
```

