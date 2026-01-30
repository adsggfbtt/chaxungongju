<?php

function app_data_dir(): string
{
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

function app_config_path(): string
{
    return app_data_dir() . '/config.json';
}

function app_stats_path(): string
{
    return app_data_dir() . '/stats.json';
}

function app_load_json(string $path, array $default): array
{
    if (!file_exists($path)) {
        return $default;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        return $default;
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return $default;
    }

    return $decoded;
}

function app_save_json(string $path, array $data): void
{
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($path, $encoded . "\n", LOCK_EX);
}

function app_load_config(): array
{
    return app_load_json(app_config_path(), [
        'links' => [],
        'last_index' => -1,
    ]);
}

function app_save_config(array $config): void
{
    app_save_json(app_config_path(), $config);
}

function app_load_stats(): array
{
    return app_load_json(app_stats_path(), [
        'total' => 0,
        'by_link' => [],
    ]);
}

function app_save_stats(array $stats): void
{
    app_save_json(app_stats_path(), $stats);
}

function app_normalize_links(string $raw): array
{
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $links = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            continue;
        }

        if (!preg_match('/^https?:\/\//i', $trimmed)) {
            $trimmed = 'https://' . $trimmed;
        }

        if (!filter_var($trimmed, FILTER_VALIDATE_URL)) {
            continue;
        }

        $links[] = $trimmed;
    }

    return array_values(array_unique($links));
}

function app_render_header(string $title): void
{
    echo "<!doctype html>\n";
    echo "<html lang=\"zh\">\n";
    echo "<head>\n";
    echo "  <meta charset=\"utf-8\">\n";
    echo "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
    echo "  <title>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title>\n";
    echo "  <style>\n";
    echo "    body { font-family: 'Segoe UI', Arial, sans-serif; margin: 40px; color: #1f2937; background: #f8fafc; }\n";
    echo "    .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); max-width: 900px; margin: 0 auto 24px; }\n";
    echo "    h1 { margin-top: 0; font-size: 24px; }\n";
    echo "    textarea { width: 100%; min-height: 160px; padding: 12px; border-radius: 8px; border: 1px solid #cbd5f5; font-size: 14px; }\n";
    echo "    button { background: #2563eb; color: #fff; border: none; padding: 10px 16px; border-radius: 8px; font-size: 14px; cursor: pointer; }\n";
    echo "    table { width: 100%; border-collapse: collapse; }\n";
    echo "    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }\n";
    echo "    .muted { color: #64748b; font-size: 13px; }\n";
    echo "    .notice { padding: 10px 12px; background: #e0f2fe; border-radius: 8px; margin-bottom: 16px; }\n";
    echo "    .error { background: #fee2e2; }\n";
    echo "  </style>\n";
    echo "</head>\n";
    echo "<body>\n";
}

function app_render_footer(): void
{
    echo "</body>\n</html>\n";
}
