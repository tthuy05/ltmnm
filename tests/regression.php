<?php

/*
 * Chay bang PHP CLI co pdo_mysql: php tests/regression.php
 * Tuy chon: DB_HOST, DB_PORT, DB_USER, DB_PASSWORD (bien moi truong).
 * Chi tao database exam_test_<ngau nhien> va ban sao trong thu muc temp.
 * Khong import, sua hay xoa database php_exam_shop dang su dung.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$passed = 0;
$failed = 0;
$server = null;
$admin = null;
$db = null;
$tempRoot = null;
$databaseName = 'exam_test_' . bin2hex(random_bytes(6));
$connection = [
    'host' => getenv('DB_HOST') !== false ? getenv('DB_HOST') : '127.0.0.1',
    'port' => getenv('DB_PORT') !== false ? getenv('DB_PORT') : '3306',
    'username' => getenv('DB_USER') !== false ? getenv('DB_USER') : 'root',
    'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
];

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function runCase($name, callable $test)
{
    global $passed, $failed;
    try {
        $test();
        $passed++;
        echo "PASS {$name}\n";
    } catch (Throwable $exception) {
        $failed++;
        echo "FAIL {$name}: " . $exception->getMessage() . "\n";
    }
}

function copyProject($source, $destination)
{
    if (!is_dir($destination)) {
        mkdir($destination, 0700, true);
    }
    foreach (new DirectoryIterator($source) as $entry) {
        if ($entry->isDot() || $entry->isLink()) {
            continue;
        }
        // Khong mang cau hinh hay anh ca nhan sang ban kiem thu.
        if ($entry->getFilename() === 'local.php') {
            continue;
        }
        $target = $destination . DIRECTORY_SEPARATOR . $entry->getFilename();
        if ($entry->isDir()) {
            if ($entry->getFilename() === 'uploads') {
                mkdir($target, 0700, true);
            } else {
                copyProject($entry->getPathname(), $target);
            }
        } else {
            copy($entry->getPathname(), $target);
        }
    }
}

function removeTestPath($path)
{
    global $tempRoot;
    $resolved = realpath($path);
    $root = $tempRoot === null ? false : realpath($tempRoot);
    check($resolved !== false && $root !== false, 'Cannot resolve temporary cleanup path.');
    check($resolved === $root || strpos($resolved, $root . DIRECTORY_SEPARATOR) === 0,
        'Refusing cleanup outside generated temporary directory.');
    if (is_link($path) || !is_dir($path)) {
        unlink($path);
        return;
    }
    foreach (new DirectoryIterator($path) as $entry) {
        if (!$entry->isDot()) {
            if ($entry->isLink()) {
                unlink($entry->getPathname());
            } else {
                removeTestPath($entry->getPathname());
            }
        }
    }
    rmdir($path);
}

function writeLocalConfig($copy, $name)
{
    global $connection;
    $values = $connection;
    $values['databaseName'] = $name;
    $php = "<?php\n";
    foreach ($values as $key => $value) {
        $php .= '$' . $key . ' = ' . var_export($value, true) . ";\n";
    }
    file_put_contents($copy . '/config/local.php', $php);
    clearstatcache();
}

function setFeatures($copy, array $overrides)
{
    $features = array_merge(['search' => true, 'pagination' => true, 'category' => true, 'upload' => true], $overrides);
    file_put_contents($copy . '/config/features.php', '<?php $features = ' . var_export($features, true) . ";\n");
    clearstatcache();
}

function request($path, array $fields = null, array $upload = null)
{
    global $baseUrl;
    $headers = ["Connection: close"];
    $body = '';
    if ($upload !== null) {
        $boundary = 'examBoundary' . bin2hex(random_bytes(10));
        $headers[] = 'Content-Type: multipart/form-data; boundary=' . $boundary;
        foreach ($fields as $name => $value) {
            $body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$name}\"\r\n\r\n{$value}\r\n";
        }
        $body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"image\"; filename=\"{$upload['name']}\"\r\n";
        $body .= "Content-Type: {$upload['type']}\r\n\r\n{$upload['data']}\r\n--{$boundary}--\r\n";
    } elseif ($fields !== null) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $body = http_build_query($fields);
    }
    $context = stream_context_create(['http' => [
        'method' => $fields === null ? 'GET' : 'POST',
        'header' => implode("\r\n", $headers),
        'content' => $body,
        'ignore_errors' => true,
        'follow_location' => 0,
        'timeout' => 10,
    ]]);
    $http_response_header = [];
    $result = @file_get_contents($baseUrl . $path, false, $context);
    check($result !== false, 'No HTTP response: ' . $path);
    preg_match('/HTTP\/\S+\s+(\d+)/', $http_response_header[0] ?? '', $matches);
    $status = (int) ($matches[1] ?? 0);
    check(!preg_match('/(?:Fatal error|Warning:|Notice:|Uncaught |Stack trace:)/i', $result),
        'PHP diagnostic exposed in ' . $path);
    return ['status' => $status, 'body' => $result, 'headers' => implode("\n", $http_response_header)];
}

function expectStatus(array $response, array $statuses, $context)
{
    check(in_array($response['status'], $statuses, true),
        $context . ': expected HTTP ' . implode('/', $statuses) . ', got ' . $response['status']);
}

function productIds($html)
{
    preg_match_all('/product_view\.php\?id=(\d+)/', $html, $matches);
    return array_values(array_unique(array_map('intval', $matches[1])));
}

function productCount()
{
    global $db;
    return (int) $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
}

function findProduct($name)
{
    global $db;
    $stmt = $db->prepare('SELECT * FROM products WHERE name = ? ORDER BY product_id DESC LIMIT 1');
    $stmt->execute([$name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function validFields($name)
{
    return ['name' => $name, 'price' => '12.34', 'quantity' => '3', 'category_id' => '1'];
}

function coreCrud($label)
{
    $fields = validFields('Regression ' . $label);
    $response = request('product_add.php', $fields);
    expectStatus($response, [302, 303], 'Create ' . $label);
    $product = findProduct($fields['name']);
    check($product !== false && $product['price'] === '12.34', 'Create did not preserve cents.');
    $id = (int) $product['product_id'];
    $view = request('product_view.php?id=' . $id);
    expectStatus($view, [200], 'Read ' . $label);
    check(strpos($view['body'], $fields['name']) !== false, 'View missing product name.');
    $fields['name'] .= ' edited';
    $fields['price'] = '98.76';
    expectStatus(request('product_edit.php?id=' . $id, $fields), [302, 303], 'Update ' . $label);
    $product = findProduct($fields['name']);
    check($product !== false && $product['price'] === '98.76', 'Update did not preserve cents.');
    expectStatus(request('product_delete.php?id=' . $id), [200], 'Delete confirmation ' . $label);
    check(findProduct($fields['name']) !== false, 'GET unexpectedly deleted product.');
    expectStatus(request('product_delete.php?id=' . $id, ['id' => (string) $id]), [302, 303], 'Delete ' . $label);
    check(findProduct($fields['name']) === false, 'POST did not delete product.');
}

function verifyJson(array $response)
{
    check(stripos($response['headers'], 'application/json') !== false, 'Response is not application/json.');
    $decoded = json_decode($response['body'], true);
    check(json_last_error() === JSON_ERROR_NONE, 'Response contains invalid JSON.');
    return $decoded;
}

try {
    check(PHP_SAPI === 'cli', 'Run this script from PHP CLI.');
    check(in_array('mysql', PDO::getAvailableDrivers(), true), 'Enable pdo_mysql in CLI php.ini.');
    check(function_exists('proc_open'), 'PHP proc_open is required to start the isolated HTTP server.');
    $admin = new PDO('mysql:host=' . $connection['host'] . ';port=' . $connection['port'] . ';charset=utf8mb4',
        $connection['username'], $connection['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    check((bool) preg_match('/^exam_test_[a-f0-9]{12}$/D', $databaseName), 'Invalid generated test database name.');
    $admin->exec('CREATE DATABASE `' . $databaseName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $db = new PDO('mysql:host=' . $connection['host'] . ';port=' . $connection['port'] . ';dbname=' . $databaseName . ';charset=utf8mb4',
        $connection['username'], $connection['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $databaseName;
    check(!file_exists($tempRoot), 'Temporary path already exists.');
    mkdir($tempRoot, 0700);
    $webRoot = $tempRoot . '/web';
    $copy = $webRoot . '/relocated/exam-project';
    copyProject(dirname(__DIR__) . '/php_exam_template', $copy);
    writeLocalConfig($copy, $databaseName);
    $schema = str_replace('php_exam_shop', $databaseName, file_get_contents($copy . '/sql/database.sql'));
    $db->exec($schema);

    $socket = stream_socket_server('tcp://127.0.0.1:0', $socketError, $socketMessage);
    check($socket !== false, 'Cannot reserve local HTTP port: ' . $socketMessage);
    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    $baseUrl = 'http://' . $address . '/relocated/exam-project/';
    $log = $tempRoot . '/server.log';
    $server = proc_open([PHP_BINARY, '-d', 'display_errors=1', '-d', 'log_errors=1', '-d', 'opcache.enable=0',
        '-S', $address, '-t', $webRoot], [['pipe', 'r'], ['file', $log, 'a'], ['file', $log, 'a']], $pipes, $webRoot);
    check(is_resource($server), 'Cannot start PHP built-in server.');
    fclose($pipes[0]);
    $started = false;
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $probe = @stream_socket_client('tcp://' . $address, $probeError, $probeMessage, 0.1);
        if ($probe !== false) {
            fclose($probe);
            $started = true;
            break;
        }
        usleep(100000);
    }
    check($started, 'HTTP server did not start.');
    echo 'Runtime: PHP ' . PHP_VERSION . '; MySQL ' . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    echo 'Isolated database: ' . $databaseName . "\n";

    runCase('Nested folder relocation and core/demo pages', function () {
        foreach (['index.php', 'products.php', 'product_add.php', 'product_view.php?id=1', 'product_edit.php?id=1',
            'categories.php', 'javascript_demo.php', 'ajax_demo.php', 'auth/login.php', 'setup.php', 'style.css'] as $page) {
            expectStatus(request($page), [200], $page);
        }
    });

    runCase('CRUD, decimal prices and non-mutating GET delete', function () {
        coreCrud('default');
    });

    runCase('Import twice preserves existing data', function () use ($db, $schema) {
        $db->exec("INSERT INTO products (name, price, quantity) VALUES ('Regression retained data', 1.25, 1)");
        $before = productCount();
        $db->exec($schema);
        check(productCount() === $before, 'Reimport duplicated or erased data.');
        check(findProduct('Regression retained data') !== false, 'Reimport erased user-created product.');
        $db->exec("DELETE FROM products WHERE name = 'Regression retained data'");
    });

    runCase('Foreign-key delete returns useful conflict', function () {
        $before = productCount();
        expectStatus(request('product_delete.php?id=1', ['id' => '1']), [409], 'Referenced product');
        check(productCount() === $before, 'Referenced product was deleted.');
    });

    runCase('Invalid IDs and missing records', function () {
        foreach (['product_view.php', 'product_edit.php', 'product_delete.php'] as $page) {
            foreach (['-1', '0', 'abc', '1%20OR%201=1', '1.5'] as $id) {
                expectStatus(request($page . '?id=' . $id), [400], $page . ' invalid id');
            }
            expectStatus(request($page . '?id%5B%5D=1'), [400], $page . ' array id');
            expectStatus(request($page . '?id=2147483647'), [404], $page . ' missing record');
        }
    });

    runCase('Validation rejects malformed arrays, invalid amounts and categories', function () {
        $before = productCount();
        $badFields = [
            ['name' => ''], ['name' => ['not-a-string']], ['name' => str_repeat('x', 151)],
            ['price' => '-1'], ['price' => '1.234'], ['price' => '1e3'], ['price' => '10000000000'], ['price' => ['1']],
            ['quantity' => '-1'], ['quantity' => '1.5'], ['quantity' => '2147483648'], ['quantity' => ['1']],
            ['category_id' => '2147483647'], ['category_id' => 'abc'], ['category_id' => ['1']],
        ];
        foreach ($badFields as $bad) {
            $fields = array_merge(validFields('Regression invalid'), $bad);
            $response = request('product_add.php', $fields);
            expectStatus($response, [200, 400, 422], 'Invalid form ' . json_encode($bad));
            check(productCount() === $before, 'Invalid form inserted a product: ' . json_encode($bad));
        }
    });

    runCase('HTML escaping in product names', function () {
        $name = '<script>alert(1)</script>';
        expectStatus(request('product_add.php', validFields($name)), [302, 303], 'Create HTML-like name');
        $product = findProduct($name);
        check($product !== false, 'Missing HTML-like product.');
        $view = request('product_view.php?id=' . $product['product_id']);
        check(strpos($view['body'], '&lt;script&gt;alert(1)&lt;/script&gt;') !== false, 'Name not escaped.');
        check(strpos($view['body'], $name) === false, 'Raw script tag rendered.');
        expectStatus(request('product_delete.php', ['id' => $product['product_id']]), [302, 303], 'Cleanup escaped name');
    });

    runCase('Search, injection input and page boundaries', function () {
        $first = request('products.php');
        check(count(productIds($first['body'])) === 5, 'Expected five products on first page.');
        $last = request('products.php?page=99999999999999999999999');
        expectStatus($last, [200], 'Very large page');
        check(count(productIds($last['body'])) > 0, 'Out-of-range page was not clamped.');
        check(count(productIds(request('products.php?page=-5')['body'])) === 5, 'Negative page was not clamped.');
        $search = request('products.php?keyword=Laptop');
        check(count(productIds($search['body'])) === 2, 'Laptop search did not return the two seed products.');
        $injection = request('products.php?keyword=' . rawurlencode("' OR 1=1 -- "));
        check(count(productIds($injection['body'])) === 0, 'Search input changed SQL meaning.');
        expectStatus(request('products.php?keyword%5B%5D=x&page%5B%5D=1&message%5B%5D=x'), [200, 400], 'Array query params');
        expectStatus(request('products.php?keyword=%FF'), [200, 400], 'Invalid UTF-8 query');
    });

    runCase('JSON API, search and malformed query input', function () {
        $response = request('api/products_json.php');
        expectStatus($response, [200], 'Products API');
        check(count(verifyJson($response)) === productCount(), 'Products API count mismatch.');
        $response = request('api/search_products.php?keyword=Laptop');
        expectStatus($response, [200], 'Search API');
        check(count(verifyJson($response)) === 2, 'Search API count mismatch.');
        foreach (['keyword%5B%5D=x', 'keyword=%FF'] as $query) {
            $response = request('api/search_products.php?' . $query);
            expectStatus($response, [200, 400, 422], 'Malformed API query');
            verifyJson($response);
        }
    });

    runCase('Reject fake or extension-mismatched image content', function () {
        $before = productCount();
        $files = [
            ['name' => 'fake.jpg', 'type' => 'image/jpeg', 'data' => '<?php echo "not an image";'],
            ['name' => 'mismatch.jpg', 'type' => 'image/jpeg', 'data' => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a8p8AAAAASUVORK5CYII=')],
        ];
        foreach ($files as $file) {
            expectStatus(request('product_add.php', validFields('Regression bad upload'), $file), [200, 400, 422], 'Invalid upload');
            check(productCount() === $before, 'Invalid image was accepted.');
        }
    });

    runCase('Upload, edit preserves image, replace and delete clean files', function () use ($copy) {
        $file = ['name' => 'sample.png', 'type' => 'image/png', 'data' => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a8p8AAAAASUVORK5CYII=')];
        $fields = validFields('Regression image');
        expectStatus(request('product_add.php', $fields, $file), [302, 303], 'Upload valid PNG');
        $product = findProduct($fields['name']);
        check($product !== false && !empty($product['image']), 'Image not saved to database.');
        $id = $product['product_id'];
        $oldImage = $product['image'];
        check(is_file($copy . '/uploads/' . $oldImage), 'Image file missing.');
        $fields['quantity'] = '9';
        expectStatus(request('product_edit.php?id=' . $id, $fields), [302, 303], 'Edit without image');
        check(findProduct($fields['name'])['image'] === $oldImage, 'Edit without upload lost old image.');
        expectStatus(request('product_edit.php?id=' . $id, $fields, $file), [302, 303], 'Replace image');
        $newImage = findProduct($fields['name'])['image'];
        check($newImage !== $oldImage && is_file($copy . '/uploads/' . $newImage), 'Replacement image missing.');
        clearstatcache();
        check(!is_file($copy . '/uploads/' . $oldImage), 'Replaced image was left orphaned.');
        expectStatus(request('product_delete.php', ['id' => $id]), [302, 303], 'Delete with image');
        clearstatcache();
        check(!is_file($copy . '/uploads/' . $newImage), 'Deleted product left orphan image.');
    });

    runCase('Search disabled ignores filter and removes input', function () use ($copy) {
        setFeatures($copy, ['search' => false]);
        $response = request('products.php?keyword=does-not-exist');
        expectStatus($response, [200], 'Search disabled');
        check(count(productIds($response['body'])) === 5, 'Disabled search still filters.');
        check(strpos($response['body'], 'name="keyword"') === false, 'Disabled search input is visible.');
        setFeatures($copy, []);
    });

    runCase('Pagination disabled lists every product', function () use ($copy) {
        setFeatures($copy, ['pagination' => false]);
        $response = request('products.php?page=2');
        expectStatus($response, [200], 'Pagination disabled');
        check(count(productIds($response['body'])) === productCount(), 'Disabled pagination still limits rows.');
        check(strpos($response['body'], 'class="pagination"') === false, 'Pagination controls still visible.');
        setFeatures($copy, []);
    });

    runCase('Core CRUD without auth, API, JavaScript or OOP files', function () use ($copy) {
        foreach (['auth', 'api', 'classes', 'ajax_demo.php', 'javascript_demo.php', 'script.js'] as $path) {
            if (file_exists($copy . '/' . $path)) {
                removeTestPath($copy . '/' . $path);
            }
        }
        expectStatus(request('index.php'), [200], 'Core-only index');
        expectStatus(request('products.php'), [200], 'Core-only list');
        coreCrud('without demo modules');
    });

    runCase('Category disabled after removing actual table and column', function () use ($copy, $db) {
        setFeatures($copy, ['category' => false]);
        $db->exec('ALTER TABLE products DROP FOREIGN KEY fk_products_categories');
        $db->exec('ALTER TABLE products DROP COLUMN category_id');
        $db->exec('DROP TABLE categories');
        expectStatus(request('products.php'), [200], 'List without category schema');
        coreCrud('without category schema');
    });

    runCase('Upload disabled after removing actual image column', function () use ($copy, $db) {
        setFeatures($copy, ['category' => false, 'upload' => false]);
        $db->exec('ALTER TABLE products DROP COLUMN image');
        expectStatus(request('products.php'), [200], 'List without image schema');
        coreCrud('without image schema');
    });

    runCase('Minimum template with every optional feature off', function () use ($copy) {
        setFeatures($copy, ['search' => false, 'pagination' => false, 'category' => false, 'upload' => false]);
        $response = request('products.php?keyword=does-not-exist&page=99');
        expectStatus($response, [200], 'Minimum list');
        check(count(productIds($response['body'])) === productCount(), 'Minimum list missing records.');
        coreCrud('minimum');
    });

    // Phuc hoi API tu source vao BAN SAO de kiem tra loi JSON ket noi.
    copyProject(dirname(__DIR__) . '/php_exam_template/api', $copy . '/api');

    runCase('Missing database yields setup guidance and JSON error', function () use ($copy, $databaseName) {
        writeLocalConfig($copy, $databaseName . '_missing');
        $response = request('products.php');
        expectStatus($response, [500, 503], 'Missing DB page');
        check(strpos($response['body'], 'setup.php') !== false, 'Missing DB page has no setup link.');
        $response = request('api/products_json.php');
        expectStatus($response, [500, 503], 'Missing DB API');
        verifyJson($response);
        expectStatus(request('setup.php'), [200, 503], 'Setup with missing DB');
        writeLocalConfig($copy, $databaseName);
    });

    runCase('Missing tables yield controlled HTML and JSON errors', function () use ($db) {
        $db->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach ($db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $db->exec('DROP TABLE `' . str_replace('`', '``', $table) . '`');
        }
        $db->exec('SET FOREIGN_KEY_CHECKS=1');
        expectStatus(request('products.php'), [500, 503], 'Missing table page');
        $response = request('api/products_json.php');
        expectStatus($response, [500, 503], 'Missing table API');
        verifyJson($response);
        expectStatus(request('setup.php'), [200, 503], 'Setup with missing tables');
    });

    echo "\nResults: {$passed} passed, {$failed} failed.\n";
} catch (Throwable $exception) {
    $failed++;
    fwrite(STDERR, 'SETUP FAILED: ' . $exception->getMessage() . "\n");
} finally {
    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
    }
    $db = null;
    if ($admin instanceof PDO && preg_match('/^exam_test_[a-f0-9]{12}$/D', $databaseName)) {
        try {
            $admin->exec('DROP DATABASE IF EXISTS `' . $databaseName . '`');
            echo 'Removed isolated test database: ' . $databaseName . "\n";
        } catch (Throwable $exception) {
            $failed++;
            fwrite(STDERR, 'Cleanup failed for ' . $databaseName . ': ' . $exception->getMessage() . "\n");
        }
    }
    if ($tempRoot !== null && is_dir($tempRoot)) {
        try {
            removeTestPath($tempRoot);
            echo "Removed isolated test files.\n";
        } catch (Throwable $exception) {
            $failed++;
            fwrite(STDERR, 'Cleanup failed for ' . $tempRoot . ': ' . $exception->getMessage() . "\n");
        }
    }
}

exit($failed === 0 ? 0 : 1);
