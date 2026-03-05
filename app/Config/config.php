<?php

declare(strict_types=1);

define('APP_PATH', dirname(__DIR__));
define('BASE_PATH', dirname(APP_PATH));

if (!function_exists('load_env_file')) {
	function load_env_file(string $path): void
	{
		if (!is_readable($path)) {
			return;
		}

		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($lines === false) {
			return;
		}

		foreach ($lines as $line) {
			$trimmed = trim($line);
			if ($trimmed === '' || str_starts_with($trimmed, '#')) {
				continue;
			}

			$delimiterPos = strpos($trimmed, '=');
			if ($delimiterPos === false) {
				continue;
			}

			$key = trim(substr($trimmed, 0, $delimiterPos));
			$value = trim(substr($trimmed, $delimiterPos + 1));

			if ($key === '') {
				continue;
			}

			$isQuoted =
				(str_starts_with($value, '"') && str_ends_with($value, '"')) ||
				(str_starts_with($value, "'") && str_ends_with($value, "'"));

			if ($isQuoted) {
				$value = substr($value, 1, -1);
			}

			putenv($key . '=' . $value);
			$_ENV[$key] = $value;
			$_SERVER[$key] = $value;
		}
	}
}

if (!function_exists('env_value')) {
	function env_value(string $key, ?string $default = null): ?string
	{
		$value = getenv($key);

		if ($value === false) {
			$value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
		}

		if ($value === null || $value === '') {
			return $default;
		}

		return (string) $value;
	}
}

load_env_file(BASE_PATH . '/.env');

$configuredAppUrl = rtrim((string) env_value('APP_URL', ''), '/');

if ($configuredAppUrl !== '') {
	define('BASE_URL', $configuredAppUrl);
} else {
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
	$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
	$baseSubPath = str_contains($scriptName, '/public/') ? '/public' : '';
	define('BASE_URL', $scheme . '://' . $host . $baseSubPath);
}

define('DB_HOST', (string) env_value('DB_HOST', '127.0.0.1'));
define('DB_PORT', (string) env_value('DB_PORT', '3306'));
define('DB_NAME', (string) env_value('DB_NAME', 'uptimebot_db'));
define('DB_USER', (string) env_value('DB_USER', 'root'));
define('DB_PASS', (string) env_value('DB_PASS', ''));

define('APP_ASSET_VERSION', (string) env_value('APP_ASSET_VERSION', '20260305-1'));
define('CRON_SECRET', (string) env_value('CRON_SECRET', 'replace_with_a_long_random_secret'));
