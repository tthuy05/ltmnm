# REVIEW — PHP Exam Cheat Sheet

## PHP

| Cú pháp | Dùng để làm gì |
|---|---|
| `$_GET['id']` | Nhận dữ liệu trên URL |
| `$_POST['name']` | Nhận dữ liệu form POST |
| `$_FILES['image']` | Nhận file upload |
| `$_SESSION['user']` | Lưu dữ liệu theo phiên |
| `$_COOKIE['last_user']` | Đọc Cookie |
| `header('Location: ...')` | Chuyển trang; luôn gọi `exit` sau đó |
| `htmlspecialchars($value)` | Hiển thị dữ liệu an toàn trong HTML |

Luồng form thường dùng:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
}
```

## PDO

```php
$conn = new PDO($dsn, $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

```php
// Không có dữ liệu người dùng: có thể dùng query()
$stmt = $conn->query('SELECT * FROM products');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

```php
// Có dữ liệu GET/POST: luôn prepare() + execute()
$stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
```

Từ khóa cần nhớ: `new PDO`, `query`, `prepare`, `execute`, `fetch`, `fetchAll`, `fetchColumn`, `PDO::FETCH_ASSOC`.

## CRUD

```sql
SELECT * FROM products;

INSERT INTO products (name, price, quantity)
VALUES (?, ?, ?);

UPDATE products
SET name = ?, price = ?, quantity = ?
WHERE product_id = ?;

DELETE FROM products
WHERE product_id = ?;
```

## SQL

`SELECT` · `INSERT` · `UPDATE` · `DELETE` · `WHERE` · `LIKE` · `ORDER BY` · `GROUP BY` · `JOIN` · `LIMIT` · `OFFSET`

```sql
SELECT p.*, c.category_name
FROM products AS p
LEFT JOIN categories AS c ON p.category_id = c.category_id
WHERE p.name LIKE :keyword
ORDER BY p.product_id DESC
LIMIT :limit OFFSET :offset;
```

## OOP

`class` · `object` · `$this` · `new` · `public` · `private` · `protected` · `extends` · `abstract` · `interface` · `implements`

```php
$product = new Product('Laptop', 15000000);
echo $product->getName();
```

## JavaScript

`getElementById` · `querySelector` · `value` · `innerText` · `innerHTML` · `style` · `createElement` · `appendChild` · `addEventListener` · `fetch` · `FormData`

```javascript
document.getElementById('button').addEventListener('click', function () {
    const value = document.querySelector('#input').value;
    document.querySelector('#output').innerText = value;
});
```

```javascript
fetch('api/search_products.php?keyword=laptop')
    .then(response => response.json())
    .then(data => console.log(data));
```

## 5 lỗi hay gặp

1. Quên `name` trên input nên PHP không nhận được dữ liệu.
2. Quên `enctype="multipart/form-data"` khi upload.
3. Số dấu `?` không khớp số phần tử trong `execute([...])`.
4. Gọi `header()` sau khi đã in HTML.
5. Đổi tên field trong form nhưng quên đổi SQL hoặc `$_POST`.

