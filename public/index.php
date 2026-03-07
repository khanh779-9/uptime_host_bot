<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Core/Controller.php';

if (!empty($_SESSION['user_id']) && (empty($_SESSION['language_code']) || empty($_SESSION['theme_mode']))) {
    try {
        $stmt = Database::connection()->prepare('SELECT language_code, theme_mode FROM user_settings WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => (int) $_SESSION['user_id']]);
        $settings = $stmt->fetch();

        if ($settings) {
            $_SESSION['language_code'] = $settings['language_code'];
            $_SESSION['theme_mode'] = $settings['theme_mode'];
        }
    } catch (Throwable $e) {
    }
}

if (empty($_SESSION['language_code'])) {
    $_SESSION['language_code'] = 'en';
}

if (empty($_SESSION['theme_mode'])) {
    $_SESSION['theme_mode'] = 'light';
}

set_locale((string) $_SESSION['language_code']);

$routes = app_action_routes();

$actionKey = trim((string) ($_GET['action'] ?? ''));
if ($actionKey === '') {
    $actionKey = 'home';
}

if (!isset($routes[$actionKey])) {
    http_response_code(404);
    echo htmlspecialchars(t('error.action_not_found', 'Action not found'));
    exit;
}

$controllerName = $routes[$actionKey]['controller'];
$action = $routes[$actionKey]['method'];

if (isset($_GET['monitor_id']) && !isset($_GET['id'])) {
    $_GET['id'] = $_GET['monitor_id'];
}

$controllerFile = APP_PATH . '/Controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo htmlspecialchars(t('error.controller_not_found', 'Controller not found'));
    exit;
}

require_once $controllerFile;

if (!class_exists($controllerName)) {
    http_response_code(404);
    echo htmlspecialchars(t('error.controller_class_not_found', 'Controller class not found'));
    exit;
}

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo htmlspecialchars(t('error.action_not_found', 'Action not found'));
    exit;
}

$controller->{$action}();
