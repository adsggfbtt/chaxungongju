<?php

require_once __DIR__ . '/app.php';

$config = app_load_config();
$links = $config['links'] ?? [];

if (!is_array($links) || count($links) === 0) {
    http_response_code(503);
    app_render_header('暂无可用分流链接');
    echo '<div class="card">';
    echo '<h1>暂无可用分流链接</h1>';
    echo '<p class="muted">请先在后台配置分流链接。</p>';
    echo '</div>';
    app_render_footer();
    exit;
}

$lastIndex = isset($config['last_index']) && is_int($config['last_index'])
    ? $config['last_index']
    : -1;

$nextIndex = ($lastIndex + 1) % count($links);
$target = $links[$nextIndex];

$config['last_index'] = $nextIndex;
app_save_config($config);

$stats = app_load_stats();
$stats['total'] = ($stats['total'] ?? 0) + 1;
$stats['by_link'] = $stats['by_link'] ?? [];
$stats['by_link'][$target] = ($stats['by_link'][$target] ?? 0) + 1;
app_save_stats($stats);

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Location: ' . $target, true, 302);
exit;
