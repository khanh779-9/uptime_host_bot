<?php

declare(strict_types=1);

class MonitorChecker
{
    public static function checkTarget(array $monitor): array
    {
        $targetType = (string) ($monitor['target_type'] ?? 'web');

        if ($targetType === 'database') {
            return self::checkDatabase();
        }

        return self::pingHttp((string) ($monitor['url'] ?? ''));
    }

    private static function pingHttp(string $url): array
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

    private static function checkDatabase(): array
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
}