<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Config/config.php';
require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/Model.php';
require_once APP_PATH . '/Models/Monitor.php';

$monitorModel = new Monitor();
$monitors = $monitorModel->allActive();

function ping_url(string $url): int
{
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
    if ($statusCode === 0) {
        $statusCode = 503;
    }

    curl_close($ch);
    return $statusCode;
}

function ping_database(): int
{
    try {
        $db = Database::connection();
        $db->query('SELECT 1');
        return 200;
    } catch (Throwable $e) {
        return 503;
    }
}

foreach ($monitors as $monitor) {
    $targetType = $monitor['target_type'] ?? 'web';
    $statusCode = $targetType === 'database' ? ping_database() : ping_url($monitor['url']);
    $monitorModel->updateCheckResult((int) $monitor['id'], $statusCode);
    echo '[' . date('Y-m-d H:i:s') . '] [' . $targetType . '] ' . $monitor['url'] . ' => ' . $statusCode . PHP_EOL;
}
