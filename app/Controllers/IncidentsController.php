<?php

declare(strict_types=1);

require_once APP_PATH . '/Models/Monitor.php';

class IncidentsController extends Controller
{
    private Monitor $monitorModel;

    public function __construct()
    {
        $this->monitorModel = new Monitor();
    }

    public function index(): void
    {
        $this->requireAuth();

        $rows = $this->monitorModel->incidentsByUser((int) $_SESSION['user_id'], 120);
        $now = time();

        $incidents = array_map(function (array $row) use ($now): array {
            $startedAt = (string) ($row['started_at'] ?? '');
            $endedAt = !empty($row['ended_at']) ? (string) $row['ended_at'] : null;
            $durationSeconds = $row['duration_seconds'] !== null
                ? (int) $row['duration_seconds']
                : ($startedAt !== '' ? max(0, $now - (int) strtotime($startedAt)) : 0);

            return [
                'status' => ((string) ($row['status'] ?? '')) === 'down'
                    ? t('status.down', 'Down')
                    : t('status.resolved', 'Resolved'),
                'is_active' => ((string) ($row['status'] ?? '')) === 'down' && $endedAt === null,
                'monitor_name' => (string) ($row['monitor_name'] ?? ''),
                'monitor_url' => (string) ($row['monitor_url'] ?? ''),
                'target_type' => (string) ($row['target_type'] ?? ''),
                'root_cause' => (string) ($row['root_cause'] ?? t('status.unknown', 'Unknown')),
                'started' => $startedAt !== '' ? date('Y-m-d H:i:s', strtotime($startedAt)) : t('common.na'),
                'duration' => $this->formatShortDuration($durationSeconds),
            ];
        }, $rows);

        $this->view('incidents/index', [
            'incidents' => $incidents,
            'hideTopNav' => true,
        ]);
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
}
