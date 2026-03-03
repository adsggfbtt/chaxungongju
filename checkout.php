<?php
require __DIR__ . '/lib/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$paymentMethod = (string)($_POST['payment_method'] ?? '');
$productId = (int)($_POST['product_id'] ?? 0);

if (!$email) {
    http_response_code(422);
    exit('邮箱格式不正确');
}

if (!in_array($paymentMethod, ['usdt', 'alipay'], true)) {
    http_response_code(422);
    exit('支付方式不支持');
}

if ($productId <= 0) {
    http_response_code(422);
    exit('产品无效');
}

try {
    $order = createOrder($email, $paymentMethod, $productId);
    header('Location: ' . createPaymentUrl($order));
} catch (Throwable $e) {
    http_response_code(500);
    echo '创建订单失败：' . htmlspecialchars($e->getMessage());
}
