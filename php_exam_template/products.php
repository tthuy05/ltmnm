<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/features.php';
require_once __DIR__ . '/includes/functions.php';

// OPTIONAL: SEARCH
$keyword = $features['search'] ? input_text($_GET, 'keyword') : '';
if ($keyword !== '' && preg_match('//u', $keyword) !== 1) {
    show_error('Từ khóa tìm kiếm không đúng bảng mã UTF-8.');
}

// OPTIONAL: PAGINATION
$limit = 5;
$pageValue = input_text($_GET, 'page', '1');
$page = ctype_digit($pageValue) && $pageValue !== '0' ? (int) $pageValue : 1;

$whereSql = $keyword !== '' ? ' WHERE p.name LIKE :keyword' : '';
$countStmt = $conn->prepare('SELECT COUNT(*) FROM products AS p' . $whereSql);
if ($keyword !== '') {
    $countStmt->execute([':keyword' => '%' . $keyword . '%']);
} else {
    $countStmt->execute();
}
$totalRecords = (int) $countStmt->fetchColumn();
$totalPages = $features['pagination'] ? max(1, (int) ceil($totalRecords / $limit)) : 1;
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

// CORE: ba cột đầu luôn tồn tại. Các cột tùy chọn chỉ xuất hiện khi bật feature.
$selectParts = ['p.product_id', 'p.name', 'p.price', 'p.quantity'];
$joinSql = '';
if ($features['category']) {
    $selectParts[] = 'p.category_id';
    $selectParts[] = 'c.category_name';
    $joinSql = ' LEFT JOIN categories AS c ON p.category_id = c.category_id';
}
if ($features['upload']) {
    $selectParts[] = 'p.image';
}
$sql = 'SELECT ' . implode(', ', $selectParts)
    . ' FROM products AS p' . $joinSql . $whereSql
    . ' ORDER BY p.product_id DESC';
if ($features['pagination']) {
    $sql .= ' LIMIT :limit OFFSET :offset';
}

$stmt = $conn->prepare($sql);
if ($keyword !== '') {
    $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
}
if ($features['pagination']) {
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
}
$stmt->execute();
$products = $stmt->fetchAll();

$messageKey = input_text($_GET, 'message');
$messages = [
    'added' => 'Đã thêm sản phẩm.',
    'updated' => 'Đã cập nhật sản phẩm.',
    'deleted' => 'Đã xóa sản phẩm.',
];
$message = $messages[$messageKey] ?? '';
$columnCount = 5 + ($features['upload'] ? 1 : 0) + ($features['category'] ? 1 : 0);
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Danh sách sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header"><div class="container header-inner">
    <a class="brand" href="index.php">PHP Exam Template</a>
    <nav class="nav" aria-label="Menu chính">
        <a class="active" href="products.php">Sản phẩm</a>
        <?php if ($features['category']): ?><a href="categories.php">Danh mục</a><?php endif; ?>
        <a href="javascript_demo.php">JavaScript</a>
        <a href="ajax_demo.php">AJAX</a>
        <a href="setup.php">Kiểm tra cài đặt</a>
    </nav>
</div></header>

<main class="container page-space">
    <div class="page-heading">
        <div><p class="eyebrow">CRUD CORE</p><h1>Danh sách sản phẩm</h1></div>
        <a class="button primary" href="product_add.php">+ Thêm sản phẩm</a>
    </div>
    <?php if ($message !== ''): ?><div class="alert success" role="status"><?= e($message) ?></div><?php endif; ?>

    <!-- OPTIONAL: SEARCH -->
    <?php if ($features['search']): ?>
        <form class="search-form" method="get">
            <div class="form-group">
                <label for="keyword">Tìm theo tên sản phẩm</label>
                <div class="search-row">
                    <input id="keyword" name="keyword" type="search" value="<?= e($keyword) ?>" placeholder="Ví dụ: laptop">
                    <button class="button primary" type="submit">Tìm kiếm</button>
                    <?php if ($keyword !== ''): ?><a class="button secondary" href="products.php">Xóa lọc</a><?php endif; ?>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <div class="table-wrap"><table>
        <thead><tr>
            <th>ID</th>
            <?php if ($features['upload']): ?><th>Ảnh</th><?php endif; ?>
            <th>Tên sản phẩm</th><th>Giá</th><th>Số lượng</th>
            <?php if ($features['category']): ?><th>Danh mục</th><?php endif; ?>
            <th>Thao tác</th>
        </tr></thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= (int) $product['product_id'] ?></td>
                <?php if ($features['upload']): ?>
                    <td><?php if (!empty($product['image'])): ?>
                        <img class="product-thumb" src="uploads/<?= e(rawurlencode($product['image'])) ?>" alt="Ảnh <?= e($product['name']) ?>">
                    <?php else: ?><span class="image-empty">Không có</span><?php endif; ?></td>
                <?php endif; ?>
                <td><?= e($product['name']) ?></td>
                <td><?= e(money($product['price'])) ?></td>
                <td><?= (int) $product['quantity'] ?></td>
                <?php if ($features['category']): ?><td><?= e($product['category_name'] ?? 'Chưa phân loại') ?></td><?php endif; ?>
                <td class="row-actions">
                    <a href="product_view.php?id=<?= (int) $product['product_id'] ?>">Xem</a>
                    <a href="product_edit.php?id=<?= (int) $product['product_id'] ?>">Sửa</a>
                    <a class="danger-link" href="product_delete.php?id=<?= (int) $product['product_id'] ?>">Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="<?= $columnCount ?>" class="empty-state"><?= $keyword !== '' ? 'Không tìm thấy sản phẩm phù hợp.' : 'Chưa có sản phẩm.' ?></td></tr><?php endif; ?>
        </tbody>
    </table></div>

    <!-- OPTIONAL: PAGINATION -->
    <?php if ($features['pagination'] && $totalPages > 1): ?>
        <nav class="pagination" aria-label="Phân trang">
        <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++):
            $pageUrl = 'products.php?' . http_build_query(['keyword' => $keyword, 'page' => $pageNumber]); ?>
            <a class="<?= $pageNumber === $page ? 'current' : '' ?>" href="<?= e($pageUrl) ?>" <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>><?= $pageNumber ?></a>
        <?php endfor; ?>
        </nav>
    <?php endif; ?>
    <p class="result-summary">Tổng: <?= $totalRecords ?> sản phẩm<?= $keyword !== '' ? ' phù hợp' : '' ?>.</p>
</main>
</body>
</html>
