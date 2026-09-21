<?php

// ===============================
// CORE: PDO CONNECTION
// ===============================
require_once __DIR__ . '/config/database.php';

// ===============================
// OPTIONAL FEATURE: SEARCH
// Xóa biến $keyword, form tìm kiếm, WHERE và execute nếu đề không yêu cầu.
// ===============================
$keyword = trim($_GET['keyword'] ?? '');

// ===============================
// OPTIONAL FEATURE: PAGINATION
// Xóa khối COUNT, $limit, $page, $offset và LIMIT/OFFSET nếu không cần.
// ===============================
$limit = 5;
$page = max(1, (int) ($_GET['page'] ?? 1));

$countSql = 'SELECT COUNT(*) FROM products AS p';
if ($keyword !== '') {
    // OPTIONAL FEATURE: SEARCH trong câu đếm.
    $countSql .= ' WHERE p.name LIKE :keyword';
}

$countStmt = $conn->prepare($countSql);
if ($keyword !== '') {
    $countStmt->execute([':keyword' => '%' . $keyword . '%']);
} else {
    $countStmt->execute();
}

$totalRecords = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRecords / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

// ===============================
// CORE: PRODUCT LIST
// OPTIONAL FEATURE: CATEGORY / JOIN
// Bỏ c.category_name, LEFT JOIN và cột Danh mục nếu đề chỉ có một bảng.
// ===============================
$sql = 'SELECT
            p.product_id,
            p.name,
            p.price,
            p.quantity,
            p.category_id,
            p.image,
            c.category_name
        FROM products AS p
        LEFT JOIN categories AS c ON p.category_id = c.category_id';

// OPTIONAL FEATURE: SEARCH
if ($keyword !== '') {
    $sql .= ' WHERE p.name LIKE :keyword';
}

$sql .= ' ORDER BY p.product_id DESC';

// OPTIONAL FEATURE: PAGINATION
$sql .= ' LIMIT :limit OFFSET :offset';

$stmt = $conn->prepare($sql);

// OPTIONAL FEATURE: SEARCH
if ($keyword !== '') {
    $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
}

// OPTIONAL FEATURE: PAGINATION
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$products = $stmt->fetchAll();

$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Exam Template</a>
        <nav class="nav" aria-label="Menu chính">
            <a class="active" href="products.php">Sản phẩm</a>
            <a href="categories.php">Danh mục</a>
            <a href="javascript_demo.php">JavaScript</a>
            <a href="ajax_demo.php">AJAX</a>
            <a href="auth/login.php">Login demo</a>
            <a href="auth/logout.php">Logout demo</a>
        </nav>
    </div>
</header>

<main class="container page-space">
    <div class="page-heading">
        <div>
            <p class="eyebrow">CRUD CORE</p>
            <h1>Danh sách sản phẩm</h1>
        </div>
        <a class="button primary" href="product_add.php">+ Thêm sản phẩm</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- OPTIONAL FEATURE: SEARCH -->
    <form class="search-form" method="get">
        <div class="form-group">
            <label for="keyword">Tìm theo tên sản phẩm</label>
            <div class="search-row">
                <input id="keyword" name="keyword" type="search" value="<?= htmlspecialchars($keyword) ?>" placeholder="Ví dụ: laptop">
                <button class="button primary" type="submit">Tìm kiếm</button>
                <?php if ($keyword !== ''): ?>
                    <a class="button secondary" href="products.php">Xóa lọc</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <!-- OPTIONAL: FILE UPLOAD -->
                <th>Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <!-- OPTIONAL: CATEGORY / JOIN -->
                <th>Danh mục</th>
                <th>Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= (int) $product['product_id'] ?></td>
                    <!-- OPTIONAL: FILE UPLOAD -->
                    <td>
                        <?php if (!empty($product['image'])): ?>
                            <img class="product-thumb" src="uploads/<?= rawurlencode($product['image']) ?>" alt="Ảnh <?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <span class="image-empty">Không có</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= number_format((float) $product['price'], 0, ',', '.') ?> đ</td>
                    <td><?= (int) $product['quantity'] ?></td>
                    <!-- OPTIONAL: CATEGORY / JOIN -->
                    <td><?= htmlspecialchars($product['category_name'] ?? 'Chưa phân loại') ?></td>
                    <td class="row-actions">
                        <a href="product_view.php?id=<?= (int) $product['product_id'] ?>">Xem</a>
                        <a href="product_edit.php?id=<?= (int) $product['product_id'] ?>">Sửa</a>
                        <!-- OPTIONAL: JavaScript confirm delete -->
                        <a class="danger-link"
                           href="product_delete.php?id=<?= (int) $product['product_id'] ?>"
                           onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <?= $keyword !== '' ? 'Không tìm thấy sản phẩm phù hợp.' : 'Chưa có sản phẩm.' ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- OPTIONAL FEATURE: PAGINATION -->
    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Phân trang sản phẩm">
            <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                <?php
                $pageUrl = 'products.php?' . http_build_query([
                    'keyword' => $keyword,
                    'page' => $pageNumber,
                ]);
                ?>
                <a class="<?= $pageNumber === $page ? 'current' : '' ?>"
                   href="<?= htmlspecialchars($pageUrl) ?>"
                   <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>>
                    <?= $pageNumber ?>
                </a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>

    <p class="result-summary">
        Tổng: <?= $totalRecords ?> sản phẩm<?= $keyword !== '' ? ' phù hợp' : '' ?>.
    </p>
</main>
</body>
</html>
