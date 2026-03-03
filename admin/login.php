<?php
require __DIR__ . '/../lib/bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = (string)($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }

    $error = '账号或密码错误';
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>后台登录</title>
  <style>body{font-family:Arial;background:#f3f4f6;padding:20px}.card{max-width:420px;margin:auto;background:#fff;padding:20px;border-radius:12px}input,button{width:100%;padding:10px;margin-top:10px}button{background:#2563eb;color:#fff;border:none}</style>
</head>
<body>
<div class="card">
  <h2>后台管理登录</h2>
  <?php if ($error): ?><p style="color:#dc2626"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  <form method="post">
    <input type="text" name="username" placeholder="用户名" required>
    <input type="password" name="password" placeholder="密码" required>
    <button type="submit">登录</button>
  </form>
</div>
</body>
</html>
