<?php

declare(strict_types=1);

session_start();

$config = require __DIR__ . '/config.php';
require __DIR__ . '/lib/storage.php';

$dataDir = $config['data_dir'];
ensure_data_dir($dataDir);

$linksFile = $dataDir . '/links.json';
$statsFile = $dataDir . '/stats.json';
$stateFile = $dataDir . '/state.json';
$lockFile = $dataDir . '/lock';

function is_authenticated(): bool
{
    return !empty($_SESSION['admin_authenticated']);
}

$errors = [];
$success = null;

if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!is_authenticated() && isset($_POST['password'])) {
    if (hash_equals($config['admin_password'], (string)$_POST['password'])) {
        $_SESSION['admin_authenticated'] = true;
        header('Location: admin.php');
        exit;
    }

    $errors[] = '密码错误。';
}

if (is_authenticated() && isset($_POST['links'])) {
    $links = sanitize_links((string)$_POST['links']);
    if (count($links) === 0) {
        $errors[] = '请至少填写一个分流链接。';
    } else {
        $lockHandle = fopen($lockFile, 'c+');
        if ($lockHandle === false) {
            $errors[] = '无法写入配置。';
        } else {
            flock($lockHandle, LOCK_EX);
            write_json_file($linksFile, $links);
            write_json_file($stateFile, ['last_index' => -1]);
            flock($lockHandle, LOCK_UN);
            $success = '分流链接已更新。';
        }
    }
}

if (is_authenticated() && isset($_POST['reset_stats'])) {
    $lockHandle = fopen($lockFile, 'c+');
    if ($lockHandle === false) {
        $errors[] = '无法重置统计。';
    } else {
        flock($lockHandle, LOCK_EX);
        write_json_file($statsFile, []);
        flock($lockHandle, LOCK_UN);
        $success = '统计已清空。';
    }
}

$links = normalize_link_list(read_json_file($linksFile, []));
$stats = read_json_file($statsFile, []);
$totalClicks = $stats['_total'] ?? 0;
unset($stats['_total']);

$linksText = implode("\n", $links);

?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>分流管理</title>
    <style>
        body { font-family: "Helvetica Neue", Arial, sans-serif; margin: 24px; background: #f5f6f8; }
        .card { background: #fff; padding: 20px; border-radius: 12px; max-width: 720px; margin: 0 auto 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; font-size: 24px; }
        textarea { width: 100%; min-height: 180px; padding: 12px; font-size: 14px; }
        button { padding: 10px 16px; font-size: 14px; border: none; border-radius: 6px; cursor: pointer; }
        .primary { background: #1a73e8; color: #fff; }
        .danger { background: #d93025; color: #fff; }
        .muted { color: #666; font-size: 13px; }
        .list { margin: 0; padding: 0; list-style: none; }
        .list li { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #eee; }
        .badge { background: #eef2ff; color: #3f51b5; padding: 2px 8px; border-radius: 999px; font-size: 12px; }
        .error { background: #fdecea; color: #b71c1c; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        .success { background: #e6f4ea; color: #1e7e34; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        form + form { margin-top: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>分流系统管理</h1>
        <p class="muted">用于 Facebook 广告投流的分流链接轮询跳转。每次访问主链接都会按顺序跳转到下一个目标。</p>

        <?php if (!is_authenticated()): ?>
            <?php foreach ($errors as $error): ?>
                <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endforeach; ?>
            <form method="post">
                <label>管理员密码</label>
                <input type="password" name="password" style="width:100%; padding:10px; margin-top:8px;">
                <button class="primary" type="submit" style="margin-top:12px;">登录</button>
            </form>
        <?php else: ?>
            <?php foreach ($errors as $error): ?>
                <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endforeach; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post">
                <label>分流链接（每行一个）</label>
                <textarea name="links" placeholder="https://bb.com\nhttps://cc.com"><?php echo htmlspecialchars($linksText, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <button class="primary" type="submit">保存配置</button>
            </form>

            <form method="post">
                <input type="hidden" name="reset_stats" value="1">
                <button class="danger" type="submit">清空统计</button>
            </form>

            <h2>点击统计</h2>
            <p class="muted">总点击：<?php echo (int)$totalClicks; ?></p>
            <ul class="list">
                <?php if (count($stats) === 0): ?>
                    <li><span class="muted">暂无统计</span></li>
                <?php else: ?>
                    <?php foreach ($stats as $link => $count): ?>
                        <li>
                            <span><?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="badge"><?php echo (int)$count; ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <form method="post">
                <input type="hidden" name="logout" value="1">
                <button type="submit">退出登录</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
