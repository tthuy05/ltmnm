<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJAX Fetch API</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Exam Template</a>
        <nav class="nav" aria-label="Menu chính">
            <a href="products.php">Sản phẩm</a>
            <a href="javascript_demo.php">JavaScript</a>
            <a class="active" href="ajax_demo.php">AJAX</a>
        </nav>
    </div>
</header>

<main class="container page-space">
    <p class="eyebrow">OPTIONAL · FETCH + JSON</p>
    <h1>Tìm sản phẩm bằng AJAX</h1>
    <p class="muted">Nhập từ khóa. Kết quả cập nhật mà không tải lại trang.</p>

    <div class="ajax-search">
        <label for="ajaxKeyword">Tên sản phẩm</label>
        <div class="search-row">
            <input id="ajaxKeyword" type="search" placeholder="Ví dụ: điện thoại">
            <button class="button primary" id="ajaxSearchButton" type="button">Tìm bằng Fetch</button>
        </div>
    </div>

    <p id="ajaxStatus" class="muted" role="status"></p>
    <div id="ajaxResults" class="ajax-results"></div>
</main>

<script>
    // OPTIONAL FEATURE: AJAX / FETCH
    const keywordInput = document.getElementById('ajaxKeyword');
    const searchButton = document.getElementById('ajaxSearchButton');
    const statusText = document.getElementById('ajaxStatus');
    const results = document.getElementById('ajaxResults');

    function renderProducts(products) {
        results.innerHTML = '';

        if (products.length === 0) {
            statusText.innerText = 'Không tìm thấy sản phẩm.';
            return;
        }

        statusText.innerText = 'Tìm thấy ' + products.length + ' sản phẩm.';

        products.forEach(function (product) {
            const card = document.createElement('article');
            card.className = 'ajax-card';

            const name = document.createElement('h2');
            name.innerText = product.name;

            const detail = document.createElement('p');
            detail.innerText = (product.category_name || 'Chưa phân loại')
                + ' · ' + Number(product.price).toLocaleString('vi-VN') + ' đ'
                + ' · Còn ' + product.quantity;

            card.appendChild(name);
            card.appendChild(detail);
            results.appendChild(card);
        });
    }

    function searchProducts() {
        const params = new URLSearchParams({ keyword: keywordInput.value });
        statusText.innerText = 'Đang tải...';

        fetch('api/search_products.php?' + params.toString())
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(renderProducts)
            .catch(function () {
                results.innerHTML = '';
                statusText.innerText = 'Không thể tải dữ liệu. Hãy kiểm tra kết nối PDO.';
            });
    }

    searchButton.addEventListener('click', searchProducts);
    keywordInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            searchProducts();
        }
    });

    searchProducts();
</script>
</body>
</html>

