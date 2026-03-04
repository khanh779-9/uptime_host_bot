<style>
    .settings-shell {
        min-height: calc(100vh - 2rem);
    }

    .settings-shell .sidebar-card {
        min-height: calc(100vh - 2rem);
    }

    .settings-shell .menu-link {
        border-radius: 0.6rem;
        padding: 0.6rem 0.8rem;
        color: var(--bs-body-color);
        text-decoration: none;
        display: block;
    }

    .settings-shell .menu-link.active {
        background: var(--bs-primary-bg-subtle);
        color: var(--bs-primary-text-emphasis);
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .settings-shell .sidebar-card {
            min-height: auto;
        }
    }
</style>

<div class="settings-shell row g-3">
    <aside class="col-12 col-lg-3 col-xl-2">
        <div class="card border-0 shadow-sm sidebar-card sticky-lg-top" style="top:1rem;">
            <div class="card-body d-flex flex-column h-100">
                <div class="mb-3">
                    <a class="text-decoration-none fw-bold fs-4" href="<?= BASE_URL ?>/index.php?url=monitor/index">
                        <span class="text-success">●</span>
                        <?= htmlspecialchars(t('app.name')) ?>
                    </a>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <a class="menu-link" href="<?= BASE_URL ?>/index.php?url=monitor/index"><?= htmlspecialchars(t('nav.monitors')) ?></a>
                    <a class="menu-link active" href="<?= BASE_URL ?>/index.php?url=settings/index"><?= htmlspecialchars(t('nav.settings')) ?></a>
                    <a class="menu-link" href="#"><?= htmlspecialchars(t('nav.incidents', 'Incidents')) ?></a>
                </div>

                <div class="mt-auto pt-3 border-top">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" data-bs-toggle="dropdown" aria-expanded="false" type="button">
                            <span><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                            <span>⋯</span>
                        </button>
                        <ul class="dropdown-menu w-100">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?url=settings/index"><?= htmlspecialchars(t('nav.settings')) ?></a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?url=auth/logout"><?= htmlspecialchars(t('nav.logout')) ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <main class="col-12 col-lg-9 col-xl-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 mb-0"><?= htmlspecialchars(t('settings.title')) ?></h2>
        </div>

        <?php if (!empty($saved)): ?>
            <div class="alert alert-success"><?= htmlspecialchars(t('settings.saved')) ?></div>
        <?php endif; ?>

        <?php if (!empty($profileSaved)): ?>
            <div class="alert alert-success"><?= htmlspecialchars(t('profile.saved', 'Đã cập nhật thông tin cá nhân.')) ?></div>
        <?php endif; ?>

        <?php if (!empty($profileError)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars((string) $profileError) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-3">
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

        <div class="card shadow-sm border-0">
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
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-3"><?= htmlspecialchars(t('dashboard.current_status', 'Current status')) ?></div>
                <div class="small text-secondary mb-2"><?= htmlspecialchars(t('auth.username')) ?></div>
                <div class="fw-semibold mb-3"><?= htmlspecialchars((string) ($_SESSION['username'] ?? '')) ?></div>
                <div class="small text-secondary mb-2"><?= htmlspecialchars(t('auth.email')) ?></div>
                <div class="fw-semibold"><?= htmlspecialchars((string) ($user['email'] ?? '')) ?></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-3"><?= htmlspecialchars(t('settings.language')) ?></div>
                <span class="badge text-bg-secondary"><?= strtoupper(htmlspecialchars((string) ($setting['language_code'] ?? 'vi'))) ?></span>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-3"><?= htmlspecialchars(t('settings.theme')) ?></div>
                <span class="badge text-bg-secondary"><?= htmlspecialchars(t('settings.theme.' . ($setting['theme_mode'] ?? 'light'))) ?></span>
            </div>
        </div>
    </section>
</div>
