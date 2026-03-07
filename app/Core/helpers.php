<?php

declare(strict_types=1);

function available_languages(): array
{
    $langDir = APP_PATH . '/Lang';
    if (!is_dir($langDir)) {
        return ['vi'];
    }

    $files = glob($langDir . '/*.json') ?: [];
    $codes = [];

    foreach ($files as $file) {
        $codes[] = basename($file, '.json');
    }

    sort($codes);
    return !empty($codes) ? $codes : ['vi'];
}

function load_language_messages(string $languageCode): array
{
    $languageCode = strtolower(trim($languageCode));
    $available = available_languages();

    if (!in_array($languageCode, $available, true)) {
        $languageCode = 'vi';
    }

    $langPath = APP_PATH . '/Lang/' . $languageCode . '.json';
    $raw = file_exists($langPath) ? (string) file_get_contents($langPath) : '{}';
    $parsed = json_decode($raw, true);

    return is_array($parsed) ? $parsed : [];
}

function set_locale(string $languageCode): void
{
    $messages = load_language_messages($languageCode);
    $GLOBALS['lang_code'] = strtolower($languageCode);
    $GLOBALS['lang_messages'] = $messages;
}

function t(string $key, ?string $default = null): string
{
    $messages = $GLOBALS['lang_messages'] ?? [];

    if (isset($messages[$key]) && is_string($messages[$key])) {
        return $messages[$key];
    }

    return $default ?? $key;
}

function current_lang(): string
{
    return (string) ($GLOBALS['lang_code'] ?? ($_SESSION['language_code'] ?? 'vi'));
}

function current_theme(): string
{
    $theme = (string) ($_SESSION['theme_mode'] ?? 'light');
    return in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
}

function app_action_routes(): array
{
    return [
        'home' => ['controller' => 'HomeController', 'method' => 'index'],
        'login' => ['controller' => 'AuthController', 'method' => 'login'],
        'register' => ['controller' => 'AuthController', 'method' => 'register'],
        'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
        'monitor' => ['controller' => 'MonitorController', 'method' => 'index'],
        'monitor_detail' => ['controller' => 'MonitorController', 'method' => 'detail'],
        'monitor_check' => ['controller' => 'MonitorController', 'method' => 'check'],
        'monitor_create' => ['controller' => 'MonitorController', 'method' => 'create'],
        'monitor_update' => ['controller' => 'MonitorController', 'method' => 'update'],
        'monitor_delete' => ['controller' => 'MonitorController', 'method' => 'delete'],
        'monitor_toggle' => ['controller' => 'MonitorController', 'method' => 'toggleActive'],
        'settings' => ['controller' => 'SettingsController', 'method' => 'index'],
        'settings_save' => ['controller' => 'SettingsController', 'method' => 'save'],
        'settings_profile' => ['controller' => 'SettingsController', 'method' => 'profile'],
        'incidents' => ['controller' => 'IncidentsController', 'method' => 'index'],
        'cron_run' => ['controller' => 'CronController', 'method' => 'run'],
    ];
}

function route_url(string $action, array $params = []): string
{
    $query = array_merge(['action' => trim($action)], $params);
    return BASE_URL . '/index.php?' . http_build_query($query);
}

function set_flash(string $key, array $payload): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }

    $_SESSION['_flash'][$key] = $payload;
}

function get_flash(string $key): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return null;
    }

    if (empty($_SESSION['_flash']) || !is_array($_SESSION['_flash']) || !isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $payload = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return is_array($payload) ? $payload : null;
}
