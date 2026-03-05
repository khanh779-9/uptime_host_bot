<div class="settings-shell row g-3">
    <?php
    $activeMenu = 'settings';
    include APP_PATH . '/Views/layouts/desktop_sidebar.php';
    ?>

    <main class="col-12 col-lg-9 col-xl-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarSettings" aria-controls="mobileSidebarSettings">
                    <i class="bi bi-list me-1"></i> <?= htmlspecialchars(t('common.menu')) ?>
                </button>
                <h2 class="h3 mb-0 app-page-title"><?= htmlspecialchars(t('settings.title')) ?></h2>
            </div>
        </div>

        <?php if (!empty($saved)): ?>
            <div class="alert alert-success"><?= htmlspecialchars(t('settings.saved')) ?></div>
        <?php endif; ?>

        <?php if (!empty($profileSaved)): ?>
            <div class="alert alert-success"><?= htmlspecialchars(t('profile.saved', 'Profile updated successfully.')) ?></div>
        <?php endif; ?>

        <?php if (!empty($profileError)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars((string) $profileError) ?></div>
        <?php endif; ?>

        <div class="card app-panel mb-3">
            <div class="card-body p-4">
                <h3 class="h5 mb-3"><?= htmlspecialchars(t('settings.title')) ?></h3>
                <form method="post" action="<?= BASE_URL ?>/index.php?url=settings/save" class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label class="form-label"><?= htmlspecialchars(t('settings.language')) ?></label>
                        <select class="form-select" name="language_code" required>
                            <?php foreach ($languages as $code): ?>
                                <option value="<?= htmlspecialchars($code) ?>" <?= ($setting['language_code'] === $code) ? 'selected' : '' ?>>
                                    <?= strtoupper(htmlspecialchars($code)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label"><?= htmlspecialchars(t('settings.theme')) ?></label>
                        <select class="form-select" name="theme_mode" required>
                            <option value="light" <?= ($setting['theme_mode'] === 'light') ? 'selected' : '' ?>><?= htmlspecialchars(t('settings.theme.light')) ?></option>
                            <option value="dark" <?= ($setting['theme_mode'] === 'dark') ? 'selected' : '' ?>><?= htmlspecialchars(t('settings.theme.dark')) ?></option>
                        </select>
                    </div>

                    <div class="col-12 d-grid d-md-block">
                        <button class="btn btn-outline-primary" type="submit"><?= htmlspecialchars(t('settings.save')) ?></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card app-panel">
            <div class="card-body p-4">
                <h3 class="h5 mb-3"><?= htmlspecialchars(t('profile.title', 'Thông tin cá nhân')) ?></h3>
                <form method="post" action="<?= BASE_URL ?>/index.php?url=settings/profile" class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label class="form-label"><?= htmlspecialchars(t('auth.username')) ?></label>
                        <input class="form-control" type="text" name="username" value="<?= htmlspecialchars((string) ($user['username'] ?? '')) ?>" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label"><?= htmlspecialchars(t('auth.email')) ?></label>
                        <input class="form-control" type="email" name="email" value="<?= htmlspecialchars((string) ($user['email'] ?? '')) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label"><?= htmlspecialchars(t('profile.new_password', 'Mật khẩu mới')) ?></label>
                        <input class="form-control" type="password" name="new_password" placeholder="<?= htmlspecialchars(t('profile.password_hint', 'Để trống nếu không đổi')) ?>">
                        <div class="form-text"><?= htmlspecialchars(t('profile.password_rule', 'Tối thiểu 6 ký tự.')) ?></div>
                    </div>

                    <div class="col-12 d-grid d-md-block">
                        <button class="btn btn-outline-primary" type="submit"><?= htmlspecialchars(t('profile.save', 'Lưu hồ sơ')) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <section class="col-12 col-xl-3">
        <div class="stats-stack">
            <div class="card app-panel mb-3">
                <div class="card-body p-4">
                    <div class="fw-semibold mb-3"><?= htmlspecialchars(t('dashboard.current_status', 'Current status')) ?></div>
                    <div class="small text-secondary mb-2"><?= htmlspecialchars(t('auth.username')) ?></div>
                    <div class="fw-semibold mb-3"><?= htmlspecialchars((string) ($_SESSION['username'] ?? '')) ?></div>
                    <div class="small text-secondary mb-2"><?= htmlspecialchars(t('auth.email')) ?></div>
                    <div class="fw-semibold"><?= htmlspecialchars((string) ($user['email'] ?? '')) ?></div>
                </div>
            </div>

            <div class="card app-panel mb-3">
                <div class="card-body p-4">
                    <div class="fw-semibold mb-3"><?= htmlspecialchars(t('settings.language')) ?></div>
                    <span class="badge text-bg-secondary"><?= strtoupper(htmlspecialchars((string) ($setting['language_code'] ?? 'vi'))) ?></span>
                </div>
            </div>

            <div class="card app-panel">
                <div class="card-body p-4">
                    <div class="fw-semibold mb-3"><?= htmlspecialchars(t('settings.theme')) ?></div>
                    <span class="badge text-bg-secondary"><?= htmlspecialchars(t('settings.theme.' . ($setting['theme_mode'] ?? 'light'))) ?></span>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
$offcanvasId = 'mobileSidebarSettings';
$offcanvasTitle = t('app.name');
$activeMenu = 'settings';
include APP_PATH . '/Views/layouts/mobile_sidebar_offcanvas.php';
?>
