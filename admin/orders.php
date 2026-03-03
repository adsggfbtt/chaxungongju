<?php
require __DIR__ . '/../lib/bootstrap.php';
adminRequireLogin();
$orders = getAllOrders();
?>
<!doctype html>
<html lang="zh-CN">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>订单列表</title>
<style>body{font-family:Arial;background:#f8fafc;padding:20px}table{width:100%;border-collapse:collapse;background:#fff}th,td{border:1px solid #e5e7eb;padding:8px;text-align:left}.paid{color:#059669;font-weight:bold}.pending{color:#dc2626;font-weight:bold}</style>
</head>
<body>
<h1>订单列表</h1>
<?php require __DIR__ . '/_nav.php'; ?>
<table>
  <tr><th>订单号</th><th>产品</th><th>邮箱</th><th>支付方式</th><th>金额</th><th>状态</th><th>交易号</th><th>创建时间</th></tr>
  <?php foreach ($orders as $o): ?>
  <tr>
    <td><?= htmlspecialchars($o['order_no']) ?></td>
    <td><?= htmlspecialchars((string)$o['product_name']) ?></td>
    <td><?= htmlspecialchars($o['email']) ?></td>
    <td><?= htmlspecialchars($o['payment_method']) ?></td>
    <td>$<?= number_format((float)$o['amount'], 2) ?></td>
    <td class="<?= $o['status'] === 'paid' ? 'paid' : 'pending' ?>"><?= htmlspecialchars($o['status']) ?></td>
    <td><?= htmlspecialchars((string)$o['tx_id']) ?></td>
    <td><?= htmlspecialchars($o['created_at']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
</body>
</html>
