<?php

declare(strict_types=1);

require_once APP_PATH . '/Models/Monitor.php';

class MonitorController extends Controller
{
    private Monitor $monitorModel;

    public function __construct()
    {
        $this->monitorModel = new Monitor();
    }

    public function index(): void
    {
        $this->requireAuth();

        $monitors = $this->monitorModel->allByUser((int) $_SESSION['user_id']);

        $stats = [
            'total' => count($monitors),
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

        foreach ($monitors as $monitor) {
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
            'hideTopNav' => true,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $targetType = trim($_POST['target_type'] ?? 'web');
            $intervalSeconds = (int) ($_POST['check_interval_seconds'] ?? 300);
            $expectedStatus = (int) ($_POST['expected_status'] ?? 200);

            $allowedTypes = ['host', 'web', 'api', 'database'];
            $allowedIntervals = [30, 50, 60, 300, 900, 1800, 3600];

            if (!in_array($targetType, $allowedTypes, true)) {
                $targetType = 'web';
            }

            if (!in_array($intervalSeconds, $allowedIntervals, true)) {
                $intervalSeconds = 300;
            }

            if ($expectedStatus < 100 || $expectedStatus > 599) {
                $expectedStatus = 200;
            }

            $isValidTarget = $targetType === 'database' ? true : (bool) filter_var($url, FILTER_VALIDATE_URL);

            if ($name !== '' && $isValidTarget) {
                if ($targetType === 'database' && $url === '') {
                    $url = 'mysql://local-connection';
                }

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

            $this->redirect('monitor/index');
        }

        $this->redirect('monitor/index');
    }

    public function delete(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['id'] ?? 0);
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

        $this->redirect('monitor/index');
    }

    public function update(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $targetType = trim($_POST['target_type'] ?? 'web');
            $intervalSeconds = (int) ($_POST['check_interval_seconds'] ?? 300);
            $expectedStatus = (int) ($_POST['expected_status'] ?? 200);

            $allowedTypes = ['host', 'web', 'api', 'database'];
            $allowedIntervals = [30, 50, 60, 300, 900, 1800, 3600];

            if (!in_array($targetType, $allowedTypes, true)) {
                $targetType = 'web';
            }

            if (!in_array($intervalSeconds, $allowedIntervals, true)) {
                $intervalSeconds = 300;
            }

            if ($expectedStatus < 100 || $expectedStatus > 599) {
                $expectedStatus = 200;
            }

            $isValidTarget = $targetType === 'database' ? true : (bool) filter_var($url, FILTER_VALIDATE_URL);

            if ($id > 0 && $name !== '' && $isValidTarget) {
                $existingMonitor = $this->monitorModel->findByIdAndUser($id, (int) $_SESSION['user_id']);

                if ($existingMonitor !== null) {
                    if ($targetType === 'database' && $url === '') {
                        $url = 'mysql://local-connection';
                    }

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

        $this->redirect('monitor/index');
    }

    public function toggleActive(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['id'] ?? 0);
        $next = (int) ($_GET['next'] ?? 1);
        $nextIsActive = $next === 1;

        if ($id <= 0) {
            set_flash('monitor_feedback', [
                'level' => 'danger',
                'message' => t('monitor.invalid_input'),
            ]);
            $this->redirect('monitor/index');
        }

        $existing = $this->monitorModel->findByIdAndUser($id, (int) $_SESSION['user_id']);
        if ($existing === null) {
            set_flash('monitor_feedback', [
                'level' => 'warning',
                'message' => t('monitor.not_found'),
            ]);
            $this->redirect('monitor/index');
        }

        $updated = $this->monitorModel->setActiveByIdAndUser($id, (int) $_SESSION['user_id'], $nextIsActive);
        set_flash('monitor_feedback', [
            'level' => $updated ? 'success' : 'danger',
            'message' => $updated
                ? ($nextIsActive ? t('status.resumed', 'Monitor resumed') : t('status.paused', 'Monitor paused'))
                : t('monitor.update_failed'),
        ]);

        $this->redirect('monitor/index');
    }

    public function detail(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            set_flash('monitor_feedback', [
                'level' => 'danger',
                'message' => t('monitor.invalid_input'),
            ]);
            $this->redirect('monitor/index');
        }

        $monitor = $this->monitorModel->findByIdAndUser($id, (int) $_SESSION['user_id']);
        if ($monitor === null) {
            set_flash('monitor_feedback', [
                'level' => 'warning',
                'message' => t('monitor.not_found'),
            ]);
            $this->redirect('monitor/index');
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
        return $this->formatShortDuration($seconds) . ' ago';
    }

}
