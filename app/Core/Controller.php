<?php

declare(strict_types=1);

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = APP_PATH . '/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo htmlspecialchars(t('error.view_not_found', 'View not found'));
            return;
        }

        include APP_PATH . '/Views/layouts/header.php';
        include $viewPath;
        include APP_PATH . '/Views/layouts/footer.php';
    }

    protected function redirect(string $action): void
    {
        header('Location: ' . route_url($action));
        exit;
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('login');
        }
    }
}
