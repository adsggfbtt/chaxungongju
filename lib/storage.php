<?php

declare(strict_types=1);

function ensure_data_dir(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function read_json_file(string $path, array $default = []): array
{
    if (!file_exists($path)) {
        return $default;
    }

    $contents = file_get_contents($path);
    if ($contents === false || $contents === '') {
        return $default;
    }

    $data = json_decode($contents, true);
    return is_array($data) ? $data : $default;
}

function write_json_file(string $path, array $data): void
{
    $tmpPath = $path . '.tmp';
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        $encoded = '{}';
    }
    file_put_contents($tmpPath, $encoded);
    rename($tmpPath, $path);
}

function sanitize_links(string $raw): array
{
    $lines = preg_split('/\r?\n/', $raw);
    $links = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $links[] = $line;
    }

    return array_values(array_unique($links));
}

function normalize_link_list(array $links): array
{
    $normalized = [];
    foreach ($links as $link) {
        if (!is_string($link)) {
            continue;
        }
        $link = trim($link);
        if ($link === '') {
            continue;
        }
        $normalized[] = $link;
    }

    return array_values(array_unique($normalized));
}
