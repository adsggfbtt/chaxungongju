<?php
require __DIR__ . '/lib/bootstrap.php';

$orderNo = (string)($_GET['order_no'] ?? '');
if (!$orderNo) {
    http_response_code(422);
    exit('缺少订单号');
}

$order = findOrderByNo($orderNo);
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>模拟支付</title>
  <style>body{font-family:Arial;background:#f5f7fb;padding:24px}.card{max-width:520px;background:#fff;padding:20px;border-radius:12px;margin:auto}button{padding:10px 14px;border:none;background:#059669;color:#fff;border-radius:8px;cursor:pointer}</style>
</head>
<body>
<div class="card">
  <h2>模拟支付页面（Demo）</h2>
  <p>订单号：<?= htmlspecialchars($order['order_no']) ?></p>
  <p>产品：<?= htmlspecialchars((string)$order['product_name']) ?></p>
  <p>邮箱：<?= htmlspecialchars($order['email']) ?></p>
  <p>金额：$<?= number_format((float)$order['amount'], 2) ?></p>
  <form method="post" action="webhook.php">
    <input type="hidden" name="order_no" value="<?= htmlspecialchars($order['order_no']) ?>">
    <input type="hidden" name="status" value="paid">
    <input type="hidden" name="tx_id" value="DEMO-TX-<?= time() ?>">
    <button type="submit">模拟支付成功并回调</button>
  </form>
</div>
</body>
</html>
