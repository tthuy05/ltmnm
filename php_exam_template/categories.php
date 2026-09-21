<?php

// ===============================
// OPTIONAL FEATURE: CATEGORY / FOREIGN KEY
// Có thể bỏ toàn bộ file này nếu đề chỉ có một bảng.
// ===============================
require_once __DIR__ . '/config/database.php';

$error = '';
$categoryName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name'] ?? '');

    if ($categoryName === '') {
        $error = 'Tên danh mục không được để trống.';
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO categories (category_name) VALUES (?)');
            $stmt->execute([$categoryName]);
            header('Location: categories.php?message=' . urlencode('Thêm danh mục thành công.'));
            exit;
        } catch (PDOException $exception) {
            $error = $exception->getCode() === '23000'
                ? 'Tên danh mục đã tồn tại.'
                : 'Không thể thêm danh mục.';
        }
    }
}

$sql = 'SELECT c.category_id, c.category_name, COUNT(p.product_id) AS product_count
        FROM categories AS c
        LEFT JOIN products AS p ON c.category_id = p.category_id
        GROUP BY c.category_id, c.category_name
        ORDER BY c.category_id';
$categories = $conn->query($sql)->fetchAll();
$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Exam Template</a>
        <nav class="nav" aria-label="Menu chính">
            <a href="products.php">Sản phẩm</a>
            <a class="active" href="categories.php">Danh mục</a>
        </nav>
    </div>
</header>

<main class="container page-space">
    <div class="page-heading">
        <div>
            <p class="eyebrow">OPTIONAL · 1 — N</p>
            <h1>Danh mục sản phẩm</h1>
        </div>
    </div>

    <?php if ($message !== ''): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="split-layout">
        <form class="form-card" method="post">
            <h2>Thêm danh mục</h2>
            <div class="form-group">
                <label for="category_name">Tên danh mục</label>
                <input id="category_name" name="category_name" type="text" value="<?= htmlspecialchars($categoryName) ?>" required>
            </div>
            <button class="button primary" type="submit">Lưu danh mục</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Tên danh mục</th><th>Số sản phẩm</th></tr></thead>
                <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= (int) $category['category_id'] ?></td>
                        <td><?= htmlspecialchars($category['category_name']) ?></td>
                        <td><?= (int) $category['product_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>

