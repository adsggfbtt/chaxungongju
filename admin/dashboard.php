<?php
require __DIR__ . '/../lib/bootstrap.php';
adminRequireLogin();

$orders = getAllOrders();
$total = count($orders);
$paid = count(array_filter($orders, static fn($o) => $o['status'] === 'paid'));
$revenue = array_sum(array_map(static fn($o) => $o['status'] === 'paid' ? (float)$o['amount'] : 0, $orders));
?>
<!doctype html>
<html lang="zh-CN">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>后台概览</title>
<style>body{font-family:Arial;background:#f8fafc;padding:20px}.box{background:#fff;border-radius:10px;padding:14px;display:inline-block;margin-right:10px}</style>
</head>
<body>
<h1>后台管理</h1>
<?php require __DIR__ . '/_nav.php'; ?>
<div class="box">总订单：<?= $total ?></div>
<div class="box">已支付：<?= $paid ?></div>
<div class="box">已支付金额：$<?= number_format($revenue, 2) ?></div>
</body>
</html>
