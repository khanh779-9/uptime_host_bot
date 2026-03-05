<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Config/config.php';
require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/Model.php';
require_once APP_PATH . '/Models/Monitor.php';

$monitorModel = new Monitor();
$monitors = $monitorModel->allActive();

function ping_url(string $url): array
{
    $startedAt = microtime(true);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'UptimeHostBot/1.0',
    ]);

    curl_exec($ch);
    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);
    if ($statusCode === 0) {
        $statusCode = 503;
    }

    curl_close($ch);
    return [
        'status' => $statusCode,
        'response_time_ms' => max(1, $elapsedMs),
    ];
}

function ping_database(): array
{
    $startedAt = microtime(true);

    try {
        $db = Database::connection();
        $db->query('SELECT 1');
        $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);
        return [
            'status' => 200,
            'response_time_ms' => max(1, $elapsedMs),
        ];
    } catch (Throwable $e) {
        $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);
        return [
            'status' => 503,
            'response_time_ms' => max(1, $elapsedMs),
        ];
    }
}

foreach ($monitors as $monitor) {
    $targetType = $monitor['target_type'] ?? 'web';
    $checkResult = $targetType === 'database' ? ping_database() : ping_url($monitor['url']);
    $monitorModel->updateCheckResultWithLatency(
        (int) $monitor['id'],
        (int) $checkResult['status'],
        $checkResult['response_time_ms'],
        (int) ($monitor['expected_status'] ?? 200)
    );

    echo '[' . date('Y-m-d H:i:s') . '] [' . $targetType . '] ' . $monitor['url']
        . ' => ' . (int) $checkResult['status'] . ' (' . (int) $checkResult['response_time_ms'] . 'ms)' . PHP_EOL;
}
