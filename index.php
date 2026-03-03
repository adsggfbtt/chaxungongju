<?php
require __DIR__ . '/lib/bootstrap.php';

$products = getActiveProducts();
$selected = $products[0] ?? null;
$pixelId = pixelId();
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Facebook广告投流课程</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f5f7fb;margin:0;color:#1f2937;}
    .wrap{max-width:1000px;margin:0 auto;padding:24px 16px 40px;}
    .hero{background:#111827;color:#fff;border-radius:16px;padding:24px;}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;}
    .card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 8px 20px rgba(0,0,0,.06);}
    label{display:block;margin:10px 0 6px}
    input,select,button,textarea{width:100%;padding:11px;border-radius:9px;border:1px solid #d1d5db;font-size:15px;}
    button{background:#2563eb;color:#fff;border:none;font-weight:700;cursor:pointer;}
    @media(max-width:860px){.grid{grid-template-columns:1fr;}}
  </style>
  <?php if ($pixelId): ?>
  <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?= htmlspecialchars($pixelId) ?>');
    fbq('track', 'PageView');
  </script>
  <?php endif; ?>
</head>
<body>
<div class="wrap">
  <section class="hero">
    <h1>Facebook广告投流课程（APP / 外贸B2B / 独立站 / 金融 / 游戏）</h1>
    <p>实战导向，含账户搭建、素材策略、受众策略、预算优化、像素和CAPI回传。</p>
  </section>

  <div class="grid">
    <section class="card">
      <h2>课程模块</h2>
      <ul>
        <li>APP安装与事件优化（AEO/VO）</li>
        <li>外贸B2B线索广告 + 表单转化</li>
        <li>独立站转化广告结构（ASC / 重定向）</li>
        <li>金融/游戏素材合规与放量</li>
        <li>像素 + CAPI 数据回传与归因</li>
      </ul>
    </section>

    <section class="card">
      <h2>立即购买</h2>
      <?php if (!$selected): ?>
        <p>暂无可售产品，请稍后再试。</p>
      <?php else: ?>
      <form method="post" action="checkout.php" id="checkout-form">
        <label for="product_id">选择产品</label>
        <select id="product_id" name="product_id" required>
          <?php foreach ($products as $product): ?>
            <option value="<?= (int)$product['id'] ?>"><?= htmlspecialchars($product['name']) ?> - $<?= number_format((float)$product['price_usd'], 2) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="email">接收课程邮箱</label>
        <input id="email" type="email" name="email" placeholder="you@example.com" required>

        <label for="payment_method">支付方式</label>
        <select id="payment_method" name="payment_method" required>
          <option value="usdt">USDT（链上）</option>
          <option value="alipay">支付宝</option>
        </select>

        <button style="margin-top:12px" type="submit">去支付并自动发课</button>
      </form>
      <?php endif; ?>
    </section>
  </div>
</div>
<?php if ($pixelId): ?>
<script>
  var form = document.getElementById('checkout-form');
  if (form) {
    form.addEventListener('submit', function () {
      fbq('track', 'InitiateCheckout');
    });
  }
</script>
<?php endif; ?>
</body>
</html>
