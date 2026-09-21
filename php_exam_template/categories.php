<?php
// OPTIONAL: CATEGORY / FOREIGN KEY
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/features.php';
require_once __DIR__ . '/includes/functions.php';

if (!$features['category']) {
    show_error('Chức năng Category đang tắt trong config/features.php.', 404);
}

$error = '';
$categoryName = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = input_text($_POST, 'category_name');
    if (!preg_match('/^.{1,100}$/us', $categoryName)) {
        $error = 'Tên danh mục cần có từ 1 đến 100 ký tự.';
        http_response_code(422);
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO categories (category_name) VALUES (?)');
            $stmt->execute([$categoryName]);
            header('Location: categories.php?message=added');
            exit;
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                $error = 'Tên danh mục đã tồn tại.';
                http_response_code(409);
            } else {
                throw $exception;
            }
        }
    }
}

$sql = 'SELECT c.category_id, c.category_name, COUNT(p.product_id) AS product_count
        FROM categories AS c
        LEFT JOIN products AS p ON c.category_id = p.category_id
        GROUP BY c.category_id, c.category_name
        ORDER BY c.category_id';
$categories = $conn->query($sql)->fetchAll();
$message = input_text($_GET, 'message') === 'added' ? 'Đã thêm danh mục.' : '';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Danh mục sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header"><div class="container header-inner">
    <a class="brand" href="index.php">PHP Exam Template</a>
    <nav class="nav"><a href="products.php">Sản phẩm</a><a class="active" href="categories.php">Danh mục</a></nav>
</div></header>
<main class="container page-space">
    <div class="page-heading"><div><p class="eyebrow">OPTIONAL · 1 — N</p><h1>Danh mục sản phẩm</h1></div></div>
    <?php if ($message !== ''): ?><div class="alert success" role="status"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <div class="split-layout">
        <form class="form-card" method="post">
            <h2>Thêm danh mục</h2>
            <div class="form-group">
                <label for="category_name">Tên danh mục</label>
                <input id="category_name" name="category_name" maxlength="100" value="<?= e($categoryName) ?>" required>
            </div>
            <button class="button primary" type="submit">Lưu danh mục</button>
        </form>
        <div class="table-wrap"><table>
            <thead><tr><th>ID</th><th>Tên danh mục</th><th>Số sản phẩm</th></tr></thead>
            <tbody><?php foreach ($categories as $category): ?><tr>
                <td><?= (int) $category['category_id'] ?></td>
                <td><?= e($category['category_name']) ?></td>
                <td><?= (int) $category['product_count'] ?></td>
            </tr><?php endforeach; ?></tbody>
        </table></div>
    </div>
</main>
</body>
</html>
