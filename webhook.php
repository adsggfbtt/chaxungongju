<?php
require __DIR__ . '/lib/bootstrap.php';

// 实际生产请验证签名，确保回调来自真实支付网关。
$orderNo = (string)($_POST['order_no'] ?? '');
$status = (string)($_POST['status'] ?? '');
$txId = (string)($_POST['tx_id'] ?? '');

if (!$orderNo || $status !== 'paid') {
    http_response_code(422);
    exit('invalid payload');
}

try {
    $order = markOrderPaid($orderNo, $txId);
    sendCourseEmail($order['email'], $order['order_no']);
    reportFacebookPurchase($order);

    header('Location: ' . BASE_URL . '/success.php?order_no=' . urlencode($orderNo));
} catch (Throwable $e) {
    http_response_code(500);
    echo 'webhook failed: ' . htmlspecialchars($e->getMessage());
}
