<?php

declare(strict_types=1);

require_once APP_PATH . '/Models/Setting.php';
require_once APP_PATH . '/Models/User.php';

class SettingsController extends Controller
{
    private Setting $settingModel;
    private User $userModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $setting = $this->settingModel->getOrCreateDefault($userId);
        $user = $this->userModel->findById($userId);

        $this->view('settings/index', [
            'setting' => $setting,
            'user' => $user,
            'languages' => available_languages(),
            'saved' => isset($_GET['saved']),
            'profileSaved' => isset($_GET['profile_saved']),
            'profileError' => $_SESSION['profile_error'] ?? null,
            'hideTopNav' => true,
        ]);

        unset($_SESSION['profile_error']);
    }

    public function save(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings');
        }

        $allowedThemes = ['light', 'dark'];
        $allowedLanguages = available_languages();

        $themeMode = (string) ($_POST['theme_mode'] ?? 'light');
        $languageCode = strtolower((string) ($_POST['language_code'] ?? 'vi'));

        if (!in_array($themeMode, $allowedThemes, true)) {
            $themeMode = 'light';
        }

        if (!in_array($languageCode, $allowedLanguages, true)) {
            $languageCode = 'vi';
        }

        $userId = (int) $_SESSION['user_id'];
        $this->settingModel->save($userId, $languageCode, $themeMode);

        $_SESSION['theme_mode'] = $themeMode;
        $_SESSION['language_code'] = $languageCode;
        set_locale($languageCode);

        header('Location: ' . route_url('settings', ['saved' => 1]));
        exit;
    }

    public function profile(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings');
        }

        $userId = (int) $_SESSION['user_id'];
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $newPassword = trim((string) ($_POST['new_password'] ?? ''));

        if ($username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['profile_error'] = t('profile.invalid_input');
            $this->redirect('settings');
        }

        if ($this->userModel->usernameExistsExceptId($username, $userId)) {
            $_SESSION['profile_error'] = t('profile.username_exists');
            $this->redirect('settings');
        }

        if ($this->userModel->emailExistsExceptId($email, $userId)) {
            $_SESSION['profile_error'] = t('profile.email_exists');
            $this->redirect('settings');
        }

        if ($newPassword !== '' && strlen($newPassword) < 6) {
            $_SESSION['profile_error'] = t('profile.password_too_short');
            $this->redirect('settings');
        }

        $passwordArg = $newPassword !== '' ? $newPassword : null;
        $this->userModel->updateProfile($userId, $username, $email, $passwordArg);
        $_SESSION['username'] = $username;

        header('Location: ' . route_url('settings', ['profile_saved' => 1]));
        exit;
    }
}
