<?php

declare(strict_types=1);

require_once APP_PATH . '/Core/Model.php';

class Monitor extends Model
{
    private static bool $historySchemaEnsured = false;

    public function allByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM monitors WHERE user_id = :user_id ORDER BY id DESC');
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function create(int $userId, string $name, string $url, string $targetType, int $intervalSeconds, int $expectedStatus): bool
    {
        $sql = 'INSERT INTO monitors (user_id, name, target_type, url, check_interval_seconds, expected_status)
                VALUES (:user_id, :name, :target_type, :url, :check_interval_seconds, :expected_status)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id' => $userId,
            'name' => $name,
            'target_type' => $targetType,
            'url' => $url,
            'check_interval_seconds' => $intervalSeconds,
            'expected_status' => $expectedStatus,
        ]);
    }

    public function findByIdAndUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM monitors WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $monitor = $stmt->fetch();
        return $monitor ?: null;
    }

    public function updateByIdAndUser(int $id, int $userId, string $name, string $url, string $targetType, int $intervalSeconds, int $expectedStatus): bool
    {
        $sql = 'UPDATE monitors
                SET name = :name,
                    target_type = :target_type,
                    url = :url,
                    check_interval_seconds = :check_interval_seconds,
                    expected_status = :expected_status
                WHERE id = :id AND user_id = :user_id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => $name,
            'target_type' => $targetType,
            'url' => $url,
            'check_interval_seconds' => $intervalSeconds,
            'expected_status' => $expectedStatus,
        ]);
    }

    public function setActiveByIdAndUser(int $id, int $userId, bool $isActive): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE monitors SET is_active = :is_active WHERE id = :id AND user_id = :user_id LIMIT 1'
        );

        return $stmt->execute([
            'is_active' => $isActive ? 1 : 0,
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function deleteByIdAndUser(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM monitors WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function allActive(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM monitors
             WHERE is_active = 1
               AND (last_checked_at IS NULL OR TIMESTAMPDIFF(SECOND, last_checked_at, NOW()) >= check_interval_seconds)'
        );
        return $stmt->fetchAll();
    }

    public function updateCheckResult(int $id, int $statusCode): bool
    {
        return $this->updateCheckResultWithLatency($id, $statusCode, null, 200);
    }

    public function updateCheckResultWithLatency(int $id, int $statusCode, ?int $responseTimeMs, int $expectedStatus = 200): bool
    {
        $this->ensureHistorySchema();

        try {
            $this->db->beginTransaction();

            $prevStmt = $this->db->prepare('SELECT last_status, expected_status FROM monitors WHERE id = :id LIMIT 1 FOR UPDATE');
            $prevStmt->execute(['id' => $id]);
            $previous = $prevStmt->fetch();
            if (!$previous) {
                $this->db->rollBack();
                return false;
            }

            $previousStatus = $previous['last_status'] !== null ? (int) $previous['last_status'] : null;
            $resolvedExpectedStatus = (int) ($previous['expected_status'] ?? $expectedStatus);

            $updateStmt = $this->db->prepare('UPDATE monitors SET last_status = :last_status, last_checked_at = NOW() WHERE id = :id');
            $updated = $updateStmt->execute([
                'id' => $id,
                'last_status' => $statusCode,
            ]);

            if (!$updated) {
                $this->db->rollBack();
                return false;
            }

            $insertCheck = $this->db->prepare(
                'INSERT INTO monitor_checks (monitor_id, status_code, response_time_ms, checked_at)
                 VALUES (:monitor_id, :status_code, :response_time_ms, NOW())'
            );

            $insertCheck->execute([
                'monitor_id' => $id,
                'status_code' => $statusCode,
                'response_time_ms' => $responseTimeMs,
            ]);

            $isCurrentUp = $statusCode === $resolvedExpectedStatus;
            $wasUp = $previousStatus !== null && $previousStatus === $resolvedExpectedStatus;

            if (!$isCurrentUp && ($previousStatus === null || $wasUp)) {
                $this->openIncident($id, $statusCode);
            }

            if ($isCurrentUp && $previousStatus !== null && !$wasUp) {
                $this->resolveLatestIncident($id);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }

    public function uptimePercentByPeriod(int $monitorId, int $days, int $expectedStatus = 200): float
    {
        $this->ensureHistorySchema();

        $cutoff = date('Y-m-d H:i:s', time() - ($days * 86400));

        $stmt = $this->db->prepare(
            'SELECT
                                SUM(CASE WHEN status_code = :expected_status THEN 1 ELSE 0 END) AS up_count,
                COUNT(*) AS total_count
             FROM monitor_checks
             WHERE monitor_id = :monitor_id
               AND checked_at >= :cutoff'
        );
                $stmt->bindValue(':expected_status', $expectedStatus, PDO::PARAM_INT);
        $stmt->bindValue(':monitor_id', $monitorId, PDO::PARAM_INT);
        $stmt->bindValue(':cutoff', $cutoff);
        $stmt->execute();

        $row = $stmt->fetch();
        $total = (int) ($row['total_count'] ?? 0);
        $up = (int) ($row['up_count'] ?? 0);

        if ($total === 0) {
            return 0;
        }

        return round(($up / $total) * 100, 2);
    }

    public function responseSeries(int $monitorId, int $hours = 24, int $maxPoints = 24): array
    {
        $this->ensureHistorySchema();

                $cutoff = date('Y-m-d H:i:s', time() - ($hours * 3600));

        $stmt = $this->db->prepare(
            'SELECT response_time_ms, checked_at
             FROM monitor_checks
             WHERE monitor_id = :monitor_id
                             AND checked_at >= :cutoff
             ORDER BY checked_at ASC
             LIMIT 500'
        );
        $stmt->bindValue(':monitor_id', $monitorId, PDO::PARAM_INT);
                $stmt->bindValue(':cutoff', $cutoff);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (empty($rows)) {
            return [];
        }

        $count = count($rows);
        $step = max(1, (int) ceil($count / $maxPoints));
        $series = [];

        for ($i = 0; $i < $count; $i += $step) {
            $row = $rows[$i];
            $series[] = [
                'label' => date('H:i', strtotime((string) $row['checked_at'])),
                'value' => max(0, (int) ($row['response_time_ms'] ?? 0)),
            ];
        }

        return $series;
    }

    public function latestIncidents(int $monitorId, int $limit = 10): array
    {
        $this->ensureHistorySchema();

        $stmt = $this->db->prepare(
            'SELECT
                status,
                root_cause,
                started_at,
                ended_at,
                duration_seconds
             FROM monitor_incidents
             WHERE monitor_id = :monitor_id
             ORDER BY started_at DESC
             LIMIT :limit_rows'
        );
        $stmt->bindValue(':monitor_id', $monitorId, PDO::PARAM_INT);
        $stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function statusBlocks24h(int $monitorId, int $blocks = 48, int $expectedStatus = 200): array
    {
        $this->ensureHistorySchema();

        $blocks = max(12, min(144, $blocks));
        $now = time();
        $windowSeconds = 24 * 3600;
        $bucketSeconds = (int) floor($windowSeconds / $blocks);
        if ($bucketSeconds <= 0) {
            $bucketSeconds = 1800;
        }

        $cutoff = date('Y-m-d H:i:s', $now - $windowSeconds);
        $stmt = $this->db->prepare(
            'SELECT status_code, checked_at
             FROM monitor_checks
             WHERE monitor_id = :monitor_id
               AND checked_at >= :cutoff
             ORDER BY checked_at ASC'
        );
        $stmt->bindValue(':monitor_id', $monitorId, PDO::PARAM_INT);
        $stmt->bindValue(':cutoff', $cutoff);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        $statuses = [];
        $baseTime = $now - $windowSeconds;

        for ($i = 0; $i < $blocks; $i++) {
            $fromTs = $baseTime + ($i * $bucketSeconds);
            $toTs = min($now, $fromTs + $bucketSeconds);
            $statuses[$i] = [
                'status' => 'unknown',
                'label' => date('H:i', $fromTs) . ' - ' . date('H:i', $toTs),
            ];
        }

        foreach ($rows as $row) {
            $checkedAt = strtotime((string) ($row['checked_at'] ?? ''));
            if ($checkedAt === false) {
                continue;
            }

            $idx = (int) floor(($checkedAt - $baseTime) / $bucketSeconds);
            if ($idx < 0) {
                $idx = 0;
            }
            if ($idx >= $blocks) {
                $idx = $blocks - 1;
            }

            $statusCode = (int) ($row['status_code'] ?? 0);
            $statuses[$idx]['status'] = ($statusCode === $expectedStatus) ? 'up' : 'down';
        }

        return $statuses;
    }

    private function openIncident(int $monitorId, int $statusCode): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO monitor_incidents (monitor_id, status, root_cause, started_at)
             VALUES (:monitor_id, :status, :root_cause, NOW())'
        );

        $stmt->execute([
            'monitor_id' => $monitorId,
            'status' => 'down',
            'root_cause' => $this->mapRootCause($statusCode),
        ]);
    }

    private function resolveLatestIncident(int $monitorId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE monitor_incidents
             SET status = :resolved,
                 ended_at = NOW(),
                 duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW())
             WHERE monitor_id = :monitor_id
               AND status = :down
               AND ended_at IS NULL
             ORDER BY started_at DESC
             LIMIT 1'
        );

        $stmt->execute([
            'monitor_id' => $monitorId,
            'resolved' => 'resolved',
            'down' => 'down',
        ]);
    }

    private function mapRootCause(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'Server unavailable';
        }

        if ($statusCode === 429) {
            return 'Rate limited';
        }

        if ($statusCode >= 400) {
            return 'HTTP ' . $statusCode . ' response';
        }

        return 'Connection timeout';
    }

    private function ensureHistorySchema(): void
    {
        if (self::$historySchemaEnsured) {
            return;
        }

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS monitor_checks (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                monitor_id INT NOT NULL,
                status_code SMALLINT NOT NULL,
                response_time_ms INT NULL,
                checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_monitor_checks_monitor FOREIGN KEY (monitor_id)
                    REFERENCES monitors(id) ON DELETE CASCADE,
                INDEX idx_monitor_checks_monitor_checked_at (monitor_id, checked_at),
                INDEX idx_monitor_checks_checked_at (checked_at)
            ) ENGINE=InnoDB'
        );

        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS monitor_incidents (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                monitor_id INT NOT NULL,
                status ENUM('down','resolved') NOT NULL DEFAULT 'down',
                root_cause VARCHAR(255) NOT NULL,
                started_at DATETIME NOT NULL,
                ended_at DATETIME NULL,
                duration_seconds INT NULL,
                CONSTRAINT fk_monitor_incidents_monitor FOREIGN KEY (monitor_id)
                    REFERENCES monitors(id) ON DELETE CASCADE,
                INDEX idx_monitor_incidents_monitor_started (monitor_id, started_at),
                INDEX idx_monitor_incidents_monitor_status (monitor_id, status)
            ) ENGINE=InnoDB"
        );

        self::$historySchemaEnsured = true;
    }

    public function incidentsByUser(int $userId, int $limit = 100): array
    {
        $this->ensureHistorySchema();

        $stmt = $this->db->prepare(
            'SELECT
                i.id,
                i.monitor_id,
                i.status,
                i.root_cause,
                i.started_at,
                i.ended_at,
                i.duration_seconds,
                m.name AS monitor_name,
                m.url AS monitor_url,
                m.target_type
             FROM monitor_incidents i
             INNER JOIN monitors m ON m.id = i.monitor_id
             WHERE m.user_id = :user_id
             ORDER BY i.started_at DESC
             LIMIT :limit_rows'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
