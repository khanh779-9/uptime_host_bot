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
        $monitors = $this->monitorModel->allActive();
        $results = [];

        foreach ($monitors as $monitor) {
            $status = $this->checkTarget($monitor);
            $this->monitorModel->updateCheckResult((int) $monitor['id'], $status);
            $results[] = [
                'id' => (int) $monitor['id'],
                'target_type' => $monitor['target_type'],
                'url' => $monitor['url'],
                'status' => $status,
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['checked' => count($results), 'results' => $results], JSON_UNESCAPED_UNICODE);
    }

    private function checkTarget(array $monitor): int
    {
        $targetType = $monitor['target_type'] ?? 'web';

        if ($targetType === 'database') {
            return $this->checkDatabase();
        }

        return $this->pingHttp($monitor['url']);
    }

    private function pingHttp(string $url): int
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

    private function checkDatabase(): int
    {
        try {
            $db = Database::connection();
            $db->query('SELECT 1');
            return 200;
        } catch (Throwable $e) {
            return 503;
        }
    }
}
