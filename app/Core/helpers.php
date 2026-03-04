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
