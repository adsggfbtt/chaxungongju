<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = require __DIR__ . '/config.php';

define('BASE_URL', rtrim((string)$config['app']['base_url'], '/'));
define('DEFAULT_FROM_EMAIL', (string)$config['app']['from_email']);
define('DEMO_MODE', (bool)$config['payment']['demo_mode']);
define('ADMIN_USERNAME', (string)$config['admin']['username']);
define('ADMIN_PASSWORD', (string)$config['admin']['password']);

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = __DIR__ . '/../data/orders.sqlite';
    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec('CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT NOT NULL,
        price_usd REAL NOT NULL,
        delivery_text TEXT NOT NULL,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_no TEXT NOT NULL UNIQUE,
        product_id INTEGER,
        email TEXT NOT NULL,
        payment_method TEXT NOT NULL,
        amount REAL NOT NULL,
        status TEXT NOT NULL DEFAULT "pending",
        tx_id TEXT,
        paid_at TEXT,
        created_at TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
    )');

    ensureOrderColumns($pdo);
    seedDefaultProduct($pdo);

    return $pdo;
}

function ensureOrderColumns(PDO $pdo): void
{
    $columns = $pdo->query('PRAGMA table_info(orders)')->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');

    if (!in_array('product_id', $columnNames, true)) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN product_id INTEGER');
    }
}

function seedDefaultProduct(PDO $pdo): void
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO products (name, description, price_usd, delivery_text, is_active, created_at, updated_at)
        VALUES (:name, :description, :price_usd, :delivery_text, 1, :created_at, :updated_at)');
    $now = gmdate('c');
    $stmt->execute([
        ':name' => 'Facebook广告投流实战课（APP / 外贸B2B / 独立站 / 金融 / 游戏）',
        ':description' => '覆盖账户搭建、素材策略、受众策略、预算优化、像素与CAPI回传、冷启动放量等模块。',
        ':price_usd' => 299,
        ':delivery_text' => "感谢购买！\n\n课程入口：\n1) 视频课：https://example.com/video\n2) 资料包：https://example.com/files\n3) 社群入口：https://example.com/group",
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
}

function getSetting(string $key, string $default = ''): string
{
    $stmt = db()->prepare('SELECT value FROM settings WHERE key = :key LIMIT 1');
    $stmt->execute([':key' => $key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : (string)$value;
}

function setSetting(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO settings (key, value) VALUES (:key, :value)
        ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    $stmt->execute([':key' => $key, ':value' => $value]);
}

function getActiveProducts(): array
{
    $stmt = db()->query('SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllProducts(): array
{
    $stmt = db()->query('SELECT * FROM products ORDER BY id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function findProduct(int $productId): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function saveProduct(array $data): void
{
    $now = gmdate('c');
    $id = (int)($data['id'] ?? 0);

    if ($id > 0) {
        $stmt = db()->prepare('UPDATE products SET name=:name, description=:description, price_usd=:price_usd, delivery_text=:delivery_text, is_active=:is_active, updated_at=:updated_at WHERE id=:id');
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price_usd' => (float)$data['price_usd'],
            ':delivery_text' => $data['delivery_text'],
            ':is_active' => (int)$data['is_active'],
            ':updated_at' => $now,
        ]);
        return;
    }

    $stmt = db()->prepare('INSERT INTO products (name, description, price_usd, delivery_text, is_active, created_at, updated_at)
        VALUES (:name, :description, :price_usd, :delivery_text, :is_active, :created_at, :updated_at)');
    $stmt->execute([
        ':name' => $data['name'],
        ':description' => $data['description'],
        ':price_usd' => (float)$data['price_usd'],
        ':delivery_text' => $data['delivery_text'],
        ':is_active' => (int)$data['is_active'],
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
}

function createOrder(string $email, string $paymentMethod, int $productId): array
{
    $product = findProduct($productId);
    if (!$product || (int)$product['is_active'] !== 1) {
        throw new RuntimeException('Product unavailable.');
    }

    $orderNo = 'FB' . date('YmdHis') . random_int(1000, 9999);
    $stmt = db()->prepare('INSERT INTO orders (order_no, product_id, email, payment_method, amount, created_at) VALUES (:order_no, :product_id, :email, :payment_method, :amount, :created_at)');
    $stmt->execute([
        ':order_no' => $orderNo,
        ':product_id' => $productId,
        ':email' => $email,
        ':payment_method' => $paymentMethod,
        ':amount' => (float)$product['price_usd'],
        ':created_at' => gmdate('c'),
    ]);

    return findOrderByNo($orderNo);
}

function findOrderByNo(string $orderNo): array
{
    $stmt = db()->prepare('SELECT o.*, p.name AS product_name, p.delivery_text AS product_delivery_text
        FROM orders o
        LEFT JOIN products p ON p.id = o.product_id
        WHERE o.order_no = :order_no LIMIT 1');
    $stmt->execute([':order_no' => $orderNo]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        throw new RuntimeException('Order not found.');
    }
    return $order;
}

function getAllOrders(): array
{
    $stmt = db()->query('SELECT o.*, p.name AS product_name FROM orders o LEFT JOIN products p ON p.id = o.product_id ORDER BY o.id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markOrderPaid(string $orderNo, string $txId = ''): array
{
    $order = findOrderByNo($orderNo);
    if ($order['status'] === 'paid') {
        return $order;
    }

    $stmt = db()->prepare('UPDATE orders SET status = "paid", tx_id = :tx_id, paid_at = :paid_at WHERE order_no = :order_no');
    $stmt->execute([
        ':tx_id' => $txId,
        ':paid_at' => gmdate('c'),
        ':order_no' => $orderNo,
    ]);

    return findOrderByNo($orderNo);
}

function createPaymentUrl(array $order): string
{
    if (DEMO_MODE) {
        return BASE_URL . '/mock_payment.php?order_no=' . urlencode($order['order_no']);
    }

    throw new RuntimeException('请在 lib/bootstrap.php:createPaymentUrl() 中接入真实支付网关。');
}

function fromEmail(): string
{
    return getSetting('from_email', DEFAULT_FROM_EMAIL);
}

function sendCourseEmail(string $toEmail, string $orderNo): bool
{
    $order = findOrderByNo($orderNo);
    $subject = '课程购买成功 - ' . ($order['product_name'] ?: '课程');
    $deliveryText = (string)($order['product_delivery_text'] ?: '课程资料将在稍后发送。');
    $content = "您好，\n\n您的订单已支付成功。\n订单号：{$orderNo}\n\n{$deliveryText}\n\n祝学习顺利！";
    $headers = 'From: ' . fromEmail() . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
    return mail($toEmail, '=?UTF-8?B?' . base64_encode($subject) . '?=', $content, $headers);
}

function pixelId(): string
{
    return getSetting('facebook_pixel_id', '');
}

function reportFacebookPurchase(array $order): void
{
    $pixelId = pixelId();
    $accessToken = getSetting('facebook_access_token', '');
    $testEventCode = getSetting('facebook_test_event_code', '');

    if (!$pixelId || !$accessToken) {
        return;
    }

    $payload = [
        'data' => [[
            'event_name' => 'Purchase',
            'event_time' => time(),
            'action_source' => 'website',
            'event_id' => $order['order_no'],
            'user_data' => [
                'em' => [hash('sha256', strtolower(trim((string)$order['email'])))],
            ],
            'custom_data' => [
                'currency' => 'USD',
                'value' => (float)$order['amount'],
                'content_name' => (string)($order['product_name'] ?: '课程'),
            ],
        ]],
    ];

    if ($testEventCode) {
        $payload['test_event_code'] = $testEventCode;
    }

    $ch = curl_init('https://graph.facebook.com/v20.0/' . $pixelId . '/events?access_token=' . urlencode($accessToken));
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function adminRequireLogin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}
