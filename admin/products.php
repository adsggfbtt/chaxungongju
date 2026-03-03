<?php
require __DIR__ . '/../lib/bootstrap.php';
adminRequireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    saveProduct([
        'id' => (int)($_POST['id'] ?? 0),
        'name' => trim((string)($_POST['name'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'price_usd' => (float)($_POST['price_usd'] ?? 0),
        'delivery_text' => trim((string)($_POST['delivery_text'] ?? '')),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ]);
    header('Location: products.php');
    exit;
}

$editId = (int)($_GET['edit'] ?? 0);
$editProduct = $editId > 0 ? findProduct($editId) : null;
$products = getAllProducts();
?>
<!doctype html>
<html lang="zh-CN">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>产品管理</title>
<style>body{font-family:Arial;background:#f8fafc;padding:20px}.card{background:#fff;border-radius:12px;padding:16px;margin-bottom:16px}input,textarea{width:100%;padding:8px;margin-top:8px}table{width:100%;border-collapse:collapse;background:#fff}th,td{border:1px solid #e5e7eb;padding:8px;text-align:left}</style>
</head>
<body>
<h1>产品管理</h1>
<?php require __DIR__ . '/_nav.php'; ?>
<div class="card">
  <h3><?= $editProduct ? '编辑产品' : '新增产品' ?></h3>
  <form method="post">
    <input type="hidden" name="id" value="<?= (int)($editProduct['id'] ?? 0) ?>">
    <label>产品名称</label>
    <input name="name" required value="<?= htmlspecialchars((string)($editProduct['name'] ?? '')) ?>">
    <label>产品简介</label>
    <textarea name="description" required><?= htmlspecialchars((string)($editProduct['description'] ?? '')) ?></textarea>
    <label>价格（USD）</label>
    <input type="number" step="0.01" name="price_usd" required value="<?= htmlspecialchars((string)($editProduct['price_usd'] ?? '299')) ?>">
    <label>发课内容</label>
    <textarea name="delivery_text" rows="6" required><?= htmlspecialchars((string)($editProduct['delivery_text'] ?? '')) ?></textarea>
    <label><input type="checkbox" name="is_active" <?= (!isset($editProduct['is_active']) || (int)$editProduct['is_active'] === 1) ? 'checked' : '' ?>> 上架</label>
    <button type="submit">保存</button>
  </form>
</div>

<table>
  <tr><th>ID</th><th>名称</th><th>价格</th><th>状态</th><th>操作</th></tr>
  <?php foreach ($products as $p): ?>
    <tr>
      <td><?= (int)$p['id'] ?></td>
      <td><?= htmlspecialchars($p['name']) ?></td>
      <td>$<?= number_format((float)$p['price_usd'], 2) ?></td>
      <td><?= (int)$p['is_active'] === 1 ? '上架' : '下架' ?></td>
      <td><a href="products.php?edit=<?= (int)$p['id'] ?>">编辑</a></td>
    </tr>
  <?php endforeach; ?>
</table>
</body>
</html>
