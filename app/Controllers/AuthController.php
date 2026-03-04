<?php

declare(strict_types=1);

require_once APP_PATH . '/Models/User.php';
require_once APP_PATH . '/Models/Setting.php';

class AuthController extends Controller
{
    private User $userModel;
    private Setting $settingModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->settingModel = new Setting();
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if ($username !== '' && $password !== '' && $email !== '') {
                $this->userModel->create($username, $password, $email);
                $this->redirect('auth/login');
            }
        }

        $this->view('auth/register', ['hideTopNav' => true]);
    }

    public function login(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginIdentifier = trim($_POST['login_identifier'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $user = $this->userModel->findByLoginIdentifier($loginIdentifier);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];

                $setting = $this->settingModel->getOrCreateDefault((int) $user['id']);
                $_SESSION['language_code'] = $setting['language_code'] ?? 'vi';
                $_SESSION['theme_mode'] = $setting['theme_mode'] ?? 'light';
                set_locale((string) $_SESSION['language_code']);

                $this->redirect('monitor/index');
            }

            $error = t('auth.invalid');
        }

        $this->view('auth/login', ['error' => $error, 'hideTopNav' => true]);
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('auth/login');
    }
}
