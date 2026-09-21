<?php

// ===============================
// OPTIONAL FEATURE: SESSION LOGIN
// Bỏ toàn bộ thư mục auth nếu đề không yêu cầu đăng nhập.
// ===============================
session_start();

// Nếu đề yêu cầu bảo vệ trang CRUD, copy đoạn sau lên đầu trang đó
// và sửa đường dẫn cho đúng vị trí file:
// if (empty($_SESSION['user'])) {
//     header('Location: auth/login.php');
//     exit;
// }

if (!empty($_SESSION['user'])) {
    header('Location: ../products.php');
    exit;
}

$error = '';
$savedUsername = $_COOKIE['last_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === '123') {
        session_regenerate_id(true);
        $_SESSION['user'] = 'admin';

        // OPTIONAL FEATURE: COOKIE - nhớ username trong 1 giờ.
        if (!empty($_POST['remember'])) {
            setcookie('last_user', $username, time() + 3600, '/');
        }

        header('Location: ../products.php');
        exit;
    }

    $error = 'Sai username hoặc password.';
    $savedUsername = $username;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập demo</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="../index.php">PHP Exam Template</a>
        <nav class="nav" aria-label="Menu chính"><a href="../products.php">Vào CRUD không cần login</a></nav>
    </div>
</header>

<main class="container login-wrap page-space">
    <p class="eyebrow">OPTIONAL · SESSION + COOKIE</p>
    <h1>Đăng nhập demo</h1>
    <p class="muted">Tài khoản: <strong>admin</strong> · Mật khẩu: <strong>123</strong></p>

    <?php if ($error !== ''): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['logged_out'])): ?><div class="alert success">Đã đăng xuất.</div><?php endif; ?>

    <form class="form-card" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="<?= htmlspecialchars($savedUsername) ?>" autocomplete="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <!-- OPTIONAL FEATURE: COOKIE -->
        <label class="check-row"><input name="remember" type="checkbox" value="1"> Ghi nhớ username bằng Cookie</label>
        <div class="actions">
            <button class="button primary" type="submit">Đăng nhập</button>
            <a class="button secondary" href="../index.php">Trang chủ</a>
        </div>
    </form>
</main>
</body>
</html>
