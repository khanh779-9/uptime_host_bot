<?php

declare(strict_types=1);

require_once APP_PATH . '/Models/Monitor.php';

class CronController extends Controller
{
    private Monitor $monitorModel;

    public function __construct()
    {
        $this->monitorModel = new Monitor();
    }

    public function run(): void
    {
        if (!$this->isCronAuthorized()) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $monitors = $this->monitorModel->allActive();
        $results = [];

        foreach ($monitors as $monitor) {
            $checkResult = $this->checkTarget($monitor);
            $this->monitorModel->updateCheckResultWithLatency(
                (int) $monitor['id'],
                (int) $checkResult['status'],
                $checkResult['response_time_ms'],
                (int) ($monitor['expected_status'] ?? 200)
            );
            $results[] = [
                'id' => (int) $monitor['id'],
                'target_type' => $monitor['target_type'],
                'url' => $monitor['url'],
                'status' => (int) $checkResult['status'],
                'response_time_ms' => $checkResult['response_time_ms'],
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['checked' => count($results), 'results' => $results], JSON_UNESCAPED_UNICODE);
    }

    private function isCronAuthorized(): bool
    {
        $configuredSecret = defined('CRON_SECRET') ? (string) CRON_SECRET : '';
        if ($configuredSecret === '' || $configuredSecret === 'replace_with_a_long_random_secret') {
            return false;
        }

        $token = (string) ($_GET['token'] ?? '');
        if ($token === '' && isset($_SERVER['HTTP_X_CRON_TOKEN'])) {
            $token = (string) $_SERVER['HTTP_X_CRON_TOKEN'];
        }

        if ($token === '') {
            return false;
        }

        return hash_equals($configuredSecret, $token);
    }

    private function checkTarget(array $monitor): array
    {
        $targetType = $monitor['target_type'] ?? 'web';

        if ($targetType === 'database') {
            return $this->checkDatabase();
        }

        return $this->pingHttp($monitor['url']);
    }

    private function pingHttp(string $url): array
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

    private function checkDatabase(): array
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
