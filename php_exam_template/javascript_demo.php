<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JavaScript DOM và Event</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Exam Template</a>
        <nav class="nav" aria-label="Menu chính">
            <a href="products.php">Sản phẩm</a>
            <a class="active" href="javascript_demo.php">JavaScript</a>
            <a href="ajax_demo.php">AJAX</a>
        </nav>
    </div>
</header>

<main class="container narrow page-space">
    <p class="eyebrow">OPTIONAL · DOM + EVENT</p>
    <h1>JavaScript cơ bản</h1>

    <section class="demo-panel">
        <div class="form-group">
            <label for="demoInput">Nhập nội dung</label>
            <input id="demoInput" type="text" value="Xin chào PHP">
        </div>
        <div class="actions">
            <button class="button primary" id="showInputButton" type="button">Hiển thị input</button>
            <button class="button secondary" id="changeColorButton" type="button">Đổi màu</button>
            <button class="button secondary" id="addElementButton" type="button">Thêm phần tử</button>
            <button class="button secondary" id="showHtmlButton" type="button">Dùng innerHTML</button>
        </div>

        <p id="outputText" class="demo-output">Kết quả sẽ hiện ở đây.</p>
        <ul id="itemList" class="demo-list"></ul>
    </section>
</main>

<script>
    // OPTIONAL FEATURE: JAVASCRIPT / DOM / EVENT
    const input = document.getElementById('demoInput');
    const output = document.querySelector('#outputText');
    const itemList = document.getElementById('itemList');
    let itemNumber = 0;

    document.getElementById('showInputButton').addEventListener('click', function () {
        output.innerText = 'Bạn đã nhập: ' + input.value;
    });

    document.getElementById('changeColorButton').addEventListener('click', function () {
        output.style.color = output.style.color === 'rgb(180, 35, 24)' ? '#175cd3' : '#b42318';
    });

    document.getElementById('addElementButton').addEventListener('click', function () {
        itemNumber++;
        const newItem = document.createElement('li');
        newItem.innerText = 'Phần tử mới số ' + itemNumber;
        itemList.appendChild(newItem);
    });

    document.getElementById('showHtmlButton').addEventListener('click', function () {
        // Chuỗi HTML cố định để minh họa innerHTML, không chèn dữ liệu người dùng.
        output.innerHTML = '<strong>innerHTML</strong> có thể tạo thẻ HTML.';
    });
</script>
</body>
</html>

