<?php
require __DIR__ . '/lib/bootstrap.php';

$orderNo = (string)($_GET['order_no'] ?? '');
$order = $orderNo ? findOrderByNo($orderNo) : null;
$pixelId = pixelId();
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>支付成功</title>
  <?php if ($pixelId && $order): ?>
  <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?= htmlspecialchars($pixelId) ?>');
    fbq('track', 'Purchase', {
      value: <?= json_encode((float)$order['amount']) ?>,
      currency: 'USD',
      content_name: '<?= htmlspecialchars((string)$order['product_name']) ?>'
    }, {
      eventID: '<?= htmlspecialchars($order['order_no']) ?>'
    });
  </script>
  <?php endif; ?>
  <style>body{font-family:Arial;background:#f0fdf4;padding:24px}.card{max-width:680px;background:#fff;padding:20px;border-radius:12px;margin:auto}</style>
</head>
<body>
  <div class="card">
    <h1>支付成功，课程已自动发送</h1>
    <?php if ($order): ?>
      <p>订单号：<?= htmlspecialchars($order['order_no']) ?></p>
      <p>产品名称：<?= htmlspecialchars((string)$order['product_name']) ?></p>
      <p>接收邮箱：<?= htmlspecialchars($order['email']) ?></p>
    <?php endif; ?>
    <p>请前往邮箱查收课程内容（包含视频课链接、资料包、社群入口）。</p>
    <p><a href="index.php">返回首页</a></p>
  </div>
</body>
</html>
