<?php
require_once __DIR__ . '/config/features.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Trang chủ';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - PHP Exam Template</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Exam Template</a>
        <nav class="nav" aria-label="Menu chính">
            <a href="products.php">Sản phẩm</a>
            <?php if ($features['category']): ?><a href="categories.php">Danh mục</a><?php endif; ?>
            <a href="javascript_demo.php">JavaScript</a>
            <a href="ajax_demo.php">AJAX</a>
            <a href="auth/login.php">Đăng nhập</a>
            <a href="setup.php">Kiểm tra cài đặt</a>
        </nav>
    </div>
</header>

<main class="container page-space">
    <section class="intro-panel">
        <p class="eyebrow">MINI SHOP MANAGEMENT</p>
        <h1>Project mẫu ôn thi PHP + MySQL + PDO</h1>
        <p>Chọn một phần bên dưới để xem code mẫu. CRUD Product hoạt động độc lập; các phần Search, Pagination, Upload, Login, JavaScript và AJAX có thể bỏ khi đề không yêu cầu.</p>
        <div class="actions">
            <a class="button primary" href="products.php">Mở danh sách sản phẩm</a>
            <a class="button secondary" href="product_add.php">Thêm sản phẩm</a>
        </div>
    </section>

    <section class="card-grid" aria-label="Các phần ôn tập">
        <article class="card">
            <span class="card-number">01</span>
            <h2>CRUD + PDO</h2>
            <p>SELECT, INSERT, UPDATE, DELETE bằng prepared statement.</p>
            <a href="products.php">Xem CRUD →</a>
        </article>
        <?php if ($features['category']): ?><article class="card">
            <span class="card-number">02</span>
            <h2>Category + JOIN</h2>
            <p>Quan hệ 1–N giữa danh mục và sản phẩm.</p>
            <a href="categories.php">Xem danh mục →</a>
        </article><?php endif; ?>
        <article class="card">
            <span class="card-number">03</span>
            <h2>JavaScript DOM</h2>
            <p>DOM, Event, createElement và thay đổi nội dung.</p>
            <a href="javascript_demo.php">Xem JavaScript →</a>
        </article>
        <article class="card">
            <span class="card-number">04</span>
            <h2>Fetch API</h2>
            <p>Tìm sản phẩm không tải lại trang và nhận JSON từ PHP.</p>
            <a href="ajax_demo.php">Xem AJAX →</a>
        </article>
    </section>
</main>
</body>
</html>
