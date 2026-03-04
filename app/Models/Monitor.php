<?php

declare(strict_types=1);

require_once APP_PATH . '/Core/Model.php';

class Monitor extends Model
{
    public function allByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM monitors WHERE user_id = :user_id ORDER BY id DESC');
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function create(int $userId, string $name, string $url, string $targetType, int $intervalSeconds): bool
    {
        $sql = 'INSERT INTO monitors (user_id, name, target_type, url, check_interval_seconds, expected_status)
                VALUES (:user_id, :name, :target_type, :url, :check_interval_seconds, 200)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id' => $userId,
            'name' => $name,
            'target_type' => $targetType,
            'url' => $url,
            'check_interval_seconds' => $intervalSeconds,
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

    public function updateByIdAndUser(int $id, int $userId, string $name, string $url, string $targetType, int $intervalSeconds): bool
    {
        $sql = 'UPDATE monitors
                SET name = :name,
                    target_type = :target_type,
                    url = :url,
                    check_interval_seconds = :check_interval_seconds
                WHERE id = :id AND user_id = :user_id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => $name,
            'target_type' => $targetType,
            'url' => $url,
            'check_interval_seconds' => $intervalSeconds,
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
        $stmt = $this->db->prepare('UPDATE monitors SET last_status = :last_status, last_checked_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'last_status' => $statusCode,
        ]);
    }
}
