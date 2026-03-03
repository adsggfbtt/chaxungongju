<?php
require __DIR__ . '/../lib/bootstrap.php';
adminRequireLogin();

$keys = [
    'payment_usdt_wallet' => 'USDT收款地址/账号',
    'payment_usdt_api_key' => 'USDT API Key',
    'payment_alipay_account' => '支付宝收款账号',
    'payment_alipay_app_id' => '支付宝 App ID',
    'payment_alipay_private_key' => '支付宝私钥',
    'payment_alipay_public_key' => '支付宝公钥',
    'facebook_pixel_id' => 'Facebook Pixel ID',
    'facebook_access_token' => 'Facebook CAPI Access Token',
    'facebook_test_event_code' => 'Facebook Test Event Code',
    'from_email' => '发件邮箱',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($keys) as $key) {
        setSetting($key, trim((string)($_POST[$key] ?? '')));
    }
    header('Location: settings.php?saved=1');
    exit;
}
?>
<!doctype html>
<html lang="zh-CN">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>支付与像素配置</title>
<style>body{font-family:Arial;background:#f8fafc;padding:20px}.card{background:#fff;border-radius:12px;padding:16px}input,textarea{width:100%;padding:8px;margin:6px 0 12px}</style>
</head>
<body>
<h1>支付与像素配置</h1>
<?php require __DIR__ . '/_nav.php'; ?>
<div class="card">
  <?php if (isset($_GET['saved'])): ?><p style="color:#059669">保存成功</p><?php endif; ?>
  <form method="post">
    <?php foreach ($keys as $key => $label): ?>
      <label><?= htmlspecialchars($label) ?></label>
      <textarea name="<?= htmlspecialchars($key) ?>" rows="2"><?= htmlspecialchars(getSetting($key, '')) ?></textarea>
    <?php endforeach; ?>
    <button type="submit">保存配置</button>
  </form>
</div>
</body>
</html>
