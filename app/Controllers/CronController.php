<?php

declare(strict_types=1);

require_once APP_PATH . '/Models/Monitor.php';
require_once APP_PATH . '/Core/MonitorChecker.php';

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
            $checkResult = MonitorChecker::checkTarget($monitor);
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

}
