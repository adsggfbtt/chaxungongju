<?php

require_once __DIR__ . '/app.php';

$config = app_load_config();
$stats = app_load_stats();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawLinks = $_POST['links'] ?? '';
    $links = app_normalize_links($rawLinks);

    if (count($links) === 0) {
        $error = '请至少输入一个有效的跳转链接。';
    } else {
        $config['links'] = $links;
        if (!isset($config['last_index']) || !is_int($config['last_index'])) {
            $config['last_index'] = -1;
        }

        $stats['total'] = $stats['total'] ?? 0;
        $stats['by_link'] = $stats['by_link'] ?? [];

        foreach ($links as $link) {
            if (!array_key_exists($link, $stats['by_link'])) {
                $stats['by_link'][$link] = 0;
            }
        }

        foreach (array_keys($stats['by_link']) as $existing) {
            if (!in_array($existing, $links, true)) {
                unset($stats['by_link'][$existing]);
            }
        }

        app_save_config($config);
        app_save_stats($stats);
        $message = '配置已保存，分流链接已更新。';
    }
}

$linksText = implode("\n", $config['links'] ?? []);

app_render_header('分流链接配置');
?>

<div class="card">
  <h1>分流链接配置</h1>
  <p class="muted">配置多个分流链接后，每次访问入口地址将按顺序轮流跳转，适合广告投放场景。</p>

  <?php if ($message): ?>
    <div class="notice"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="notice error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>

  <form method="post">
    <label for="links"><strong>分流链接列表</strong></label>
    <textarea id="links" name="links" placeholder="https://bb.com\nhttps://cc.com"><?php echo htmlspecialchars($linksText, ENT_QUOTES, 'UTF-8'); ?></textarea>
    <p class="muted">每行一个链接，支持自动补全 https://。</p>
    <button type="submit">保存配置</button>
  </form>
</div>

<div class="card">
  <h1>点击统计</h1>
  <p class="muted">实时统计每个分流链接的点击次数。</p>
  <table>
    <thead>
      <tr>
        <th>链接</th>
        <th>点击次数</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($stats['by_link'])): ?>
        <?php foreach ($stats['by_link'] as $link => $count): ?>
          <tr>
            <td><?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo (int) $count; ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="2" class="muted">暂无统计数据。</td>
        </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <th>总点击</th>
        <th><?php echo (int) ($stats['total'] ?? 0); ?></th>
      </tr>
    </tfoot>
  </table>
</div>

<?php
app_render_footer();
