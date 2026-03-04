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

            if ($status === null) {
                $stats['unknown']++;
            } elseif ((int) $status >= 200 && (int) $status < 400) {
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

            $allowedTypes = ['host', 'web', 'api', 'database'];
            $allowedIntervals = [30, 50, 60, 300, 900, 1800, 3600];

            if (!in_array($targetType, $allowedTypes, true)) {
                $targetType = 'web';
            }

            if (!in_array($intervalSeconds, $allowedIntervals, true)) {
                $intervalSeconds = 300;
            }

            $isValidTarget = $targetType === 'database' ? true : (bool) filter_var($url, FILTER_VALIDATE_URL);

            if ($name !== '' && $isValidTarget) {
                if ($targetType === 'database' && $url === '') {
                    $url = 'mysql://local-connection';
                }

                $created = $this->monitorModel->create((int) $_SESSION['user_id'], $name, $url, $targetType, $intervalSeconds);
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

            $allowedTypes = ['host', 'web', 'api', 'database'];
            $allowedIntervals = [30, 50, 60, 300, 900, 1800, 3600];

            if (!in_array($targetType, $allowedTypes, true)) {
                $targetType = 'web';
            }

            if (!in_array($intervalSeconds, $allowedIntervals, true)) {
                $intervalSeconds = 300;
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
                        $intervalSeconds
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
        $isUp = $status !== null && (int) $status >= 200 && (int) $status < 400;
        $isDown = $status !== null && !$isUp;
        $statusText = $isUp ? t('status.up', 'Up') : ($isDown ? t('status.down', 'Down') : t('common.na'));

        $this->view('monitor/detail', [
            'monitor' => $monitor,
            'statusText' => $statusText,
            'isUp' => $isUp,
            'isDown' => $isDown,
            'hideTopNav' => true,
        ]);
    }
}
