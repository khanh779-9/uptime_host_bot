<?php

declare(strict_types=1);

require_once APP_PATH . '/Models/Monitor.php';
require_once APP_PATH . '/Core/MonitorChecker.php';

class MonitorController extends Controller
{
    private const DEFAULT_TARGET_TYPE = 'web';
    private const DEFAULT_INTERVAL_SECONDS = 300;
    private const DEFAULT_EXPECTED_STATUS = 200;
    private const DATABASE_PLACEHOLDER_URL = 'mysql://local-connection';
    private const ALLOWED_TYPES = ['host', 'web', 'api', 'database'];
    private const ALLOWED_INTERVALS = [10, 30, 50, 60, 300, 900, 1800, 3600];

    private Monitor $monitorModel;

    public function __construct()
    {
        $this->monitorModel = new Monitor();
    }

    public function index(): void
    {
        $this->requireAuth();

        $allMonitors = $this->monitorModel->allByUser((int) $_SESSION['user_id']);
        $totalMonitors = count($allMonitors);
        $minimumItemsPerPage = 10;
        $maximumItemsOption = max($minimumItemsPerPage, $totalMonitors);
        $itemsPerPageOptions = [];

        for ($option = $minimumItemsPerPage; $option <= $maximumItemsOption; $option += 10) {
            $itemsPerPageOptions[] = $option;
        }

        if (empty($itemsPerPageOptions) || end($itemsPerPageOptions) !== $maximumItemsOption) {
            $itemsPerPageOptions[] = $maximumItemsOption;
        }

        $requestedItemsPerPage = (int) ($_GET['per_page'] ?? $minimumItemsPerPage);
        $itemsPerPage = in_array($requestedItemsPerPage, $itemsPerPageOptions, true)
            ? $requestedItemsPerPage
            : $minimumItemsPerPage;

        $currentPage = max(1, (int) ($_GET['page'] ?? 1));
        $totalPages = max(1, (int) ceil($totalMonitors / $itemsPerPage));

        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        $offset = ($currentPage - 1) * $itemsPerPage;
        $monitors = array_slice($allMonitors, $offset, $itemsPerPage);

        $stats = [
            'total' => $totalMonitors,
            'up' => 0,
            'down' => 0,
            'paused' => 0,
            'unknown' => 0,
            'checked' => 0,
            'uptime_percent' => 0,
        ];

        $typeCounts = [
            'host' => 0,
            'web' => 0,
            'api' => 0,
            'database' => 0,
        ];

        foreach ($allMonitors as $monitor) {
            if ((int) ($monitor['is_active'] ?? 1) === 0) {
                $stats['paused']++;
            }

            $status = $monitor['last_status'];
            $expectedStatus = (int) ($monitor['expected_status'] ?? 200);

            if ($status === null) {
                $stats['unknown']++;
            } elseif ((int) $status === $expectedStatus) {
                $stats['up']++;
            } else {
                $stats['down']++;
            }

            $type = (string) ($monitor['target_type'] ?? 'web');
            if (isset($typeCounts[$type])) {
                $typeCounts[$type]++;
            }
        }

        $stats['checked'] = $stats['up'] + $stats['down'];
        if ($stats['checked'] > 0) {
            $stats['uptime_percent'] = round(($stats['up'] / $stats['checked']) * 100, 2);
        }

        $this->view('monitor/index', [
            'monitors' => $monitors,
            'stats' => $stats,
            'typeCounts' => $typeCounts,
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'items_per_page' => $itemsPerPage,
                'items_per_page_options' => $itemsPerPageOptions,
                'total_items' => $totalMonitors,
                'start_item' => $totalMonitors > 0 ? ($offset + 1) : 0,
                'end_item' => min($offset + $itemsPerPage, $totalMonitors),
            ],
            'hideTopNav' => true,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $targetType = $this->normalizeTargetType((string) ($_POST['target_type'] ?? self::DEFAULT_TARGET_TYPE));
            $intervalSeconds = $this->normalizeInterval((int) ($_POST['check_interval_seconds'] ?? self::DEFAULT_INTERVAL_SECONDS));
            $expectedStatus = $this->normalizeExpectedStatus((int) ($_POST['expected_status'] ?? self::DEFAULT_EXPECTED_STATUS));

            $isValidTarget = $this->isValidTarget($targetType, $url);

            if ($name !== '' && $isValidTarget) {
                $url = $this->normalizeTargetUrl($targetType, $url);

                $created = $this->monitorModel->create((int) $_SESSION['user_id'], $name, $url, $targetType, $intervalSeconds, $expectedStatus);
                set_flash('monitor_feedback', [
                    'level' => $created ? 'success' : 'danger',
                    'message' => $created ? t('monitor.create_success') : t('monitor.create_failed'),
                ]);
            } else {
                set_flash('monitor_feedback', [
                    'level' => 'danger',
                    'message' => t('monitor.invalid_input'),
                ]);
            }

            $this->redirect('monitor');
        }

        $this->redirect('monitor');
    }

    public function delete(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['monitor_id'] ?? ($_GET['id'] ?? 0));
        if ($id > 0) {
            $existingMonitor = $this->monitorModel->findByIdAndUser($id, (int) $_SESSION['user_id']);

            if ($existingMonitor !== null) {
                $deleted = $this->monitorModel->deleteByIdAndUser($id, (int) $_SESSION['user_id']);
                set_flash('monitor_feedback', [
                    'level' => $deleted ? 'success' : 'danger',
                    'message' => $deleted ? t('monitor.delete_success') : t('monitor.delete_failed'),
                ]);
            } else {
                set_flash('monitor_feedback', [
                    'level' => 'warning',
                    'message' => t('monitor.not_found'),
                ]);
            }
        } else {
            set_flash('monitor_feedback', [
                'level' => 'danger',
                'message' => t('monitor.invalid_input'),
            ]);
        }

        $this->redirect('monitor');
    }

    public function update(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $targetType = $this->normalizeTargetType((string) ($_POST['target_type'] ?? self::DEFAULT_TARGET_TYPE));
            $intervalSeconds = $this->normalizeInterval((int) ($_POST['check_interval_seconds'] ?? self::DEFAULT_INTERVAL_SECONDS));
            $expectedStatus = $this->normalizeExpectedStatus((int) ($_POST['expected_status'] ?? self::DEFAULT_EXPECTED_STATUS));

            $isValidTarget = $this->isValidTarget($targetType, $url);

            if ($id > 0 && $name !== '' && $isValidTarget) {
                $existingMonitor = $this->monitorModel->findByIdAndUser($id, (int) $_SESSION['user_id']);

                if ($existingMonitor !== null) {
                    $url = $this->normalizeTargetUrl($targetType, $url);

                    $updated = $this->monitorModel->updateByIdAndUser(
                        $id,
                        (int) $_SESSION['user_id'],
                        $name,
                        $url,
                        $targetType,
                        $intervalSeconds,
                        $expectedStatus
                    );

                    set_flash('monitor_feedback', [
                        'level' => $updated ? 'success' : 'danger',
                        'message' => $updated ? t('monitor.update_success') : t('monitor.update_failed'),
                    ]);
                } else {
                    set_flash('monitor_feedback', [
                        'level' => 'warning',
                        'message' => t('monitor.not_found'),
                    ]);
                }
            } else {
                set_flash('monitor_feedback', [
                    'level' => 'danger',
                    'message' => t('monitor.invalid_input'),
                ]);
            }
        }

        $this->redirect('monitor');
    }

    public function toggleActive(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['monitor_id'] ?? ($_GET['id'] ?? 0));
        $next = (int) ($_GET['next'] ?? 1);
        $nextIsActive = $next === 1;

        if ($id <= 0) {
            set_flash('monitor_feedback', [
                'level' => 'danger',
                'message' => t('monitor.invalid_input'),
            ]);
            $this->redirect('monitor');
        }

        $existing = $this->monitorModel->findByIdAndUser($id, (int) $_SESSION['user_id']);
        if ($existing === null) {
            set_flash('monitor_feedback', [
                'level' => 'warning',
                'message' => t('monitor.not_found'),
            ]);
            $this->redirect('monitor');
        }

        $updated = $this->monitorModel->setActiveByIdAndUser($id, (int) $_SESSION['user_id'], $nextIsActive);
        set_flash('monitor_feedback', [
            'level' => $updated ? 'success' : 'danger',
            'message' => $updated
                ? ($nextIsActive ? t('status.resumed', 'Monitor resumed') : t('status.paused', 'Monitor paused'))
                : t('monitor.update_failed'),
        ]);

        $this->redirect('monitor');
    }

    public function detail(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['monitor_id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            set_flash('monitor_feedback', [
                'level' => 'danger',
                'message' => t('monitor.invalid_input'),
            ]);
            $this->redirect('monitor');
        }

        $monitor = $this->monitorModel->findByIdAndUser($id, (int) $_SESSION['user_id']);
        if ($monitor === null) {
            set_flash('monitor_feedback', [
                'level' => 'warning',
                'message' => t('monitor.not_found'),
            ]);
            $this->redirect('monitor');
        }

        $status = $monitor['last_status'];
        $expectedStatus = (int) ($monitor['expected_status'] ?? 200);
        $isUp = $status !== null && (int) $status === $expectedStatus;
        $isDown = $status !== null && !$isUp;
        $statusText = $isUp ? t('status.up', 'Up') : ($isDown ? t('status.down', 'Down') : t('common.na'));

        $now = time();
        $checkedAtTs = !empty($monitor['last_checked_at']) ? strtotime((string) $monitor['last_checked_at']) : null;
        $createdAtTs = !empty($monitor['created_at']) ? strtotime((string) $monitor['created_at']) : null;
        $secondsSinceLastCheck = $checkedAtTs ? max(0, $now - $checkedAtTs) : null;
        $currentlyUpSeconds = ($isUp && $createdAtTs) ? max(0, $now - $createdAtTs) : 0;
        $intervalSeconds = (int) ($monitor['check_interval_seconds'] ?? 300);

        $periodStats = [
            'last_24h' => $this->monitorModel->uptimePercentByPeriod((int) $monitor['id'], 1, $expectedStatus),
            'last_7d' => $this->monitorModel->uptimePercentByPeriod((int) $monitor['id'], 7, $expectedStatus),
            'last_30d' => $this->monitorModel->uptimePercentByPeriod((int) $monitor['id'], 30, $expectedStatus),
        ];

        $responseSeries = $this->monitorModel->responseSeries((int) $monitor['id'], 24, 24);
        $statusBlocks24h = $this->monitorModel->statusBlocks24h((int) $monitor['id'], 48, $expectedStatus);
        $incidentsRaw = $this->monitorModel->latestIncidents((int) $monitor['id'], 8);

        $incidents = array_map(function (array $incident) use ($now): array {
            $startedAt = (string) ($incident['started_at'] ?? '');
            $endedAt = !empty($incident['ended_at']) ? (string) $incident['ended_at'] : null;
            $durationSeconds = $incident['duration_seconds'] !== null
                ? (int) $incident['duration_seconds']
                : ($startedAt !== '' ? max(0, $now - (int) strtotime($startedAt)) : 0);

            return [
                'status' => ((string) ($incident['status'] ?? '')) === 'down'
                    ? t('status.down', 'Down')
                    : t('status.resolved', 'Resolved'),
                'root_cause' => (string) ($incident['root_cause'] ?? t('status.unknown', 'Unknown')),
                'started' => $startedAt !== '' ? date('Y-m-d H:i:s', strtotime($startedAt)) : t('common.na'),
                'duration' => $this->formatShortDuration($durationSeconds),
                'is_active' => ((string) ($incident['status'] ?? '')) === 'down' && $endedAt === null,
            ];
        }, $incidentsRaw);

        $this->view('monitor/detail', [
            'monitor' => $monitor,
            'statusText' => $statusText,
            'isUp' => $isUp,
            'isDown' => $isDown,
            'secondsSinceLastCheck' => $secondsSinceLastCheck,
            'currentlyUpSeconds' => $currentlyUpSeconds,
            'currentlyUpText' => $currentlyUpSeconds > 0 ? $this->formatLongDuration($currentlyUpSeconds) : '0m',
            'intervalText' => $this->formatShortDuration($intervalSeconds),
            'lastCheckText' => $secondsSinceLastCheck !== null ? $this->formatAgo($secondsSinceLastCheck) : t('common.na'),
            'periodStats' => $periodStats,
            'responseSeries' => $responseSeries,
            'statusBlocks24h' => $statusBlocks24h,
            'incidents' => $incidents,
            'hideTopNav' => true,
        ]);
    }

    public function check(): void
    {
        $this->requireAuth();

        header('Content-Type: application/json; charset=utf-8');

        $id = (int) ($_GET['monitor_id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'message' => t('monitor.invalid_input'),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $monitor = $this->monitorModel->findByIdAndUser($id, (int) $_SESSION['user_id']);
        if ($monitor === null) {
            http_response_code(404);
            echo json_encode([
                'ok' => false,
                'message' => t('monitor.not_found'),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ((int) ($monitor['is_active'] ?? 1) !== 1) {
            echo json_encode([
                'ok' => true,
                'skipped' => true,
                'monitor' => $this->buildMonitorRealtimePayload($monitor),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $lastCheckedAt = !empty($monitor['last_checked_at']) ? strtotime((string) $monitor['last_checked_at']) : null;
        $intervalSeconds = max(1, (int) ($monitor['check_interval_seconds'] ?? 300));
        $secondsSinceLast = $lastCheckedAt ? max(0, time() - $lastCheckedAt) : $intervalSeconds;

        if ($secondsSinceLast < $intervalSeconds) {
            echo json_encode([
                'ok' => true,
                'skipped' => true,
                'monitor' => $this->buildMonitorRealtimePayload($monitor),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $checkResult = MonitorChecker::checkTarget($monitor);
        $updated = $this->monitorModel->updateCheckResultWithLatency(
            (int) $monitor['id'],
            (int) $checkResult['status'],
            $checkResult['response_time_ms'],
            (int) ($monitor['expected_status'] ?? 200)
        );

        if (!$updated) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'message' => t('monitor.update_failed'),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $freshMonitor = $this->monitorModel->findByIdAndUser($id, (int) $_SESSION['user_id']);
        if ($freshMonitor === null) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'message' => t('monitor.not_found'),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'ok' => true,
            'skipped' => false,
            'monitor' => $this->buildMonitorRealtimePayload($freshMonitor),
        ], JSON_UNESCAPED_UNICODE);
    }

    private function buildMonitorRealtimePayload(array $monitor): array
    {
        $expectedStatus = (int) ($monitor['expected_status'] ?? 200);
        $status = $monitor['last_status'] !== null ? (int) $monitor['last_status'] : null;
        $isUp = $status !== null && $status === $expectedStatus;
        $isDown = $status !== null && !$isUp;
        $state = $isUp ? 'up' : ($isDown ? 'down' : 'unknown');
        $healthPercent = $isUp ? 100 : ($isDown ? 30 : 10);
        $checkedAtRaw = !empty($monitor['last_checked_at']) ? (string) $monitor['last_checked_at'] : null;
        $checkedAtTs = $checkedAtRaw !== null ? strtotime($checkedAtRaw) : null;
        $intervalSeconds = max(1, (int) ($monitor['check_interval_seconds'] ?? 300));
        $secondsSinceLast = $checkedAtTs ? max(0, time() - $checkedAtTs) : $intervalSeconds;
        $nextDueSeconds = max(0, $intervalSeconds - $secondsSinceLast);

        return [
            'id' => (int) $monitor['id'],
            'state' => $state,
            'status_code' => $status,
            'status_text' => $isUp
                ? t('status.up', 'Up')
                : ($isDown ? t('status.down', 'Down') : t('common.na')),
            'health_percent' => $healthPercent,
            'last_checked_at' => $checkedAtRaw ?? t('common.na'),
            'last_checked_ts' => $checkedAtTs ?: null,
            'interval_seconds' => $intervalSeconds,
            'next_due_seconds' => $nextDueSeconds,
            'expected_status' => $expectedStatus,
            'is_active' => (int) ($monitor['is_active'] ?? 1) === 1,
        ];
    }

    private function normalizeTargetType(string $targetType): string
    {
        $normalized = trim($targetType);
        return in_array($normalized, self::ALLOWED_TYPES, true) ? $normalized : self::DEFAULT_TARGET_TYPE;
    }

    private function normalizeInterval(int $intervalSeconds): int
    {
        return in_array($intervalSeconds, self::ALLOWED_INTERVALS, true)
            ? $intervalSeconds
            : self::DEFAULT_INTERVAL_SECONDS;
    }

    private function normalizeExpectedStatus(int $expectedStatus): int
    {
        if ($expectedStatus < 100 || $expectedStatus > 599) {
            return self::DEFAULT_EXPECTED_STATUS;
        }

        return $expectedStatus;
    }

    private function isValidTarget(string $targetType, string $url): bool
    {
        if ($targetType === 'database') {
            return true;
        }

        return (bool) filter_var($url, FILTER_VALIDATE_URL);
    }

    private function normalizeTargetUrl(string $targetType, string $url): string
    {
        if ($targetType === 'database' && $url === '') {
            return self::DATABASE_PLACEHOLDER_URL;
        }

        return $url;
    }

    private function formatLongDuration(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . 'd';
        }
        if ($hours > 0 || !empty($parts)) {
            $parts[] = $hours . 'h';
        }
        $parts[] = $minutes . 'm';

        return implode(' ', $parts);
    }

    private function formatShortDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        if ($minutes > 0) {
            return $minutes . 'm' . ($secs > 0 ? ' ' . $secs . 's' : '');
        }

        return $secs . 's';
    }

    private function formatAgo(int $seconds): string
    {
        return $this->formatShortDuration($seconds) . ' ' . t('common.ago', 'ago');
    }

}
