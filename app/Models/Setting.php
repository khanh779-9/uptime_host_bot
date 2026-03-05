<?php

declare(strict_types=1);

require_once APP_PATH . '/Core/Model.php';

class Setting extends Model
{
    public function getByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM user_settings WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $setting = $stmt->fetch();

        return $setting ?: null;
    }

    public function getOrCreateDefault(int $userId): array
    {
        $setting = $this->getByUserId($userId);
        if ($setting) {
            return $setting;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO user_settings (user_id, language_code, theme_mode) VALUES (:user_id, :language_code, :theme_mode)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'language_code' => 'en',
            'theme_mode' => 'light',
        ]);

        return $this->getByUserId($userId) ?? [
            'user_id' => $userId,
            'language_code' => 'en',
            'theme_mode' => 'light',
        ];
    }

    public function save(int $userId, string $languageCode, string $themeMode): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO user_settings (user_id, language_code, theme_mode)
             VALUES (:user_id, :language_code, :theme_mode)
             ON DUPLICATE KEY UPDATE language_code = VALUES(language_code), theme_mode = VALUES(theme_mode), updated_at = CURRENT_TIMESTAMP'
        );

        return $stmt->execute([
            'user_id' => $userId,
            'language_code' => $languageCode,
            'theme_mode' => $themeMode,
        ]);
    }
}
