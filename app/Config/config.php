<?php

declare(strict_types=1);

define('APP_PATH', dirname(__DIR__));
define('BASE_PATH', dirname(APP_PATH));

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$baseSubPath = str_contains($scriptName, '/public/') ? '/public' : '';
define('BASE_URL', $scheme . '://' . $host . $baseSubPath);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'uptimebot_db');
define('DB_USER', 'root');
define('DB_PASS', '');
