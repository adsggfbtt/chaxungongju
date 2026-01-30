<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require __DIR__ . '/lib/storage.php';

$dataDir = $config['data_dir'];
ensure_data_dir($dataDir);

$linksFile = $dataDir . '/links.json';
$stateFile = $dataDir . '/state.json';
$statsFile = $dataDir . '/stats.json';
$lockFile = $dataDir . '/lock';

$links = normalize_link_list(read_json_file($linksFile, []));

if (count($links) === 0) {
    http_response_code(503);
    echo 'No redirect links configured.';
    exit;
}

$lockHandle = fopen($lockFile, 'c+');
if ($lockHandle === false) {
    http_response_code(500);
    echo 'Unable to acquire lock.';
    exit;
}

flock($lockHandle, LOCK_EX);

$state = read_json_file($stateFile, ['last_index' => -1]);
$stats = read_json_file($statsFile, []);

$lastIndex = is_int($state['last_index'] ?? null) ? $state['last_index'] : -1;
$nextIndex = ($lastIndex + 1) % count($links);
$target = $links[$nextIndex];

$state['last_index'] = $nextIndex;
$stats[$target] = ($stats[$target] ?? 0) + 1;
$stats['_total'] = ($stats['_total'] ?? 0) + 1;

write_json_file($stateFile, $state);
write_json_file($statsFile, $stats);

flock($lockHandle, LOCK_UN);

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Location: ' . $target, true, 302);
exit;
