<?php // Form Add/Edit dùng chung: sửa label, input ở một nơi. ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header"><div class="container header-inner">
    <a class="brand" href="index.php">PHP Exam Template</a>
    <nav class="nav"><a href="products.php">← Danh sách sản phẩm</a></nav>
</div></header>
<main class="container narrow page-space">
    <h1><?= e($pageTitle) ?></h1>
    <?php if ($errors): ?>
        <div class="alert error" role="alert">
            <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form class="form-card" method="post" <?= $features['upload'] ? 'enctype="multipart/form-data"' : '' ?>>
        <div class="form-group">
            <label for="name">Tên sản phẩm</label>
            <input id="name" name="name" maxlength="150" value="<?= e($data['name']) ?>" required>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="price">Giá (đ)</label>
                <input id="price" name="price" type="number" min="0" max="9999999999.99" step="0.01" value="<?= e($data['price']) ?>" required>
            </div>
            <div class="form-group">
                <label for="quantity">Số lượng</label>
                <input id="quantity" name="quantity" type="number" min="0" max="2147483647" step="1" value="<?= e($data['quantity']) ?>" required>
            </div>
        </div>
        <!-- OPTIONAL: CATEGORY -->
        <?php if ($features['category']): ?>
            <div class="form-group">
                <label for="category_id">Danh mục</label>
                <select id="category_id" name="category_id">
                    <option value="">-- Chưa phân loại --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['category_id'] ?>" <?= (string) $data['category_id'] === (string) $category['category_id'] ? 'selected' : '' ?>><?= e($category['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <!-- OPTIONAL: FILE UPLOAD -->
        <?php if ($features['upload']): ?>
            <div class="form-group">
                <label for="image">Ảnh JPG, JPEG hoặc PNG (tối đa 2 MB)</label>
                <?php if ($imageName): ?>
                    <img class="current-image" src="uploads/<?= e(rawurlencode($imageName)) ?>" alt="Ảnh hiện tại">
                    <p class="muted">Không chọn ảnh mới sẽ giữ ảnh hiện tại.</p>
                <?php endif; ?>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png">
            </div>
        <?php endif; ?>
        <div class="actions">
            <button class="button primary" type="submit"><?= e($submitLabel) ?></button>
            <a class="button secondary" href="products.php">Hủy</a>
        </div>
    </form>
</main>
</body>
</html>
