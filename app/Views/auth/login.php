<div class="row justify-content-center align-items-center min-vh-100 py-4 py-lg-5">
    <?php
    $popupItems = [];
    if (!empty($error)) {
        $popupItems[] = [
            'level' => 'danger',
            'message' => (string) $error,
        ];
    }
    ?>

    <?php if (!empty($popupItems)): ?>
        <?php include APP_PATH . '/Views/layouts/floating_popups.php'; ?>
    <?php endif; ?>

    <div class="col-12 col-lg-10 col-xxl-8">
        <div class="card app-panel overflow-hidden rounded-4">
            <div class="row g-0">
                <div
                    class="col-lg-5 bg-primary bg-gradient text-white p-4 p-lg-5 d-none d-lg-flex flex-column justify-content-center position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25"
                        style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.4), transparent 40%), radial-gradient(circle at 80% 80%, rgba(255,255,255,0.4), transparent 40%);">
                    </div>
                    <div class="position-relative z-1">
                        <div class="text-uppercase small fw-semibold opacity-75 mb-2 d-flex align-items-center"><i
                                class="bi bi-shield-check me-2 fs-5"></i><?= htmlspecialchars(t('auth.brand_tagline', 'Uptime Monitoring')) ?>
                        </div>
                        <div class="h3 fw-bold mb-3"><?= htmlspecialchars(t('app.name')) ?></div>
                        <p class="mb-4 opacity-75"><?= htmlspecialchars(t('home.description')) ?></p>
                        <div class="small opacity-75 mb-2"><?= htmlspecialchars(t('auth.register_title')) ?>?</div>
                        <a class="btn btn-light btn-sm rounded-pill align-self-start px-4 shadow-sm fw-medium pt-2 pb-2"
                            href="<?= route_url('register') ?>"><?= htmlspecialchars(t('home.create_account')) ?></a>
                    </div>
                </div>
                <div class="col-12 col-lg-7 p-4 p-lg-5 bg-body">
                    <h1 class="h3 mb-2"><?= htmlspecialchars(t('auth.login_title')) ?></h1>
                    <p class="text-body-secondary mb-4">
                        <?= htmlspecialchars(t('auth.login_subtitle', 'Welcome back, please sign in to continue.')) ?>
                    </p>

                    <form method="post" action="<?= route_url('login') ?>" class="needs-validation"
                        novalidate>
                        <div class="form-floating mb-3">
                            <input class="form-control rounded-3" type="text" name="login_identifier"
                                id="login_identifier" placeholder="Username / Email" autocomplete="username" required>
                            <label for="login_identifier"><i
                                    class="bi bi-person text-secondary me-2"></i><?= htmlspecialchars(t('auth.login_identifier', 'Username / Email')) ?></label>
                        </div>
                        <div class="form-floating mb-4">
                            <input class="form-control rounded-3" type="password" name="password" id="password"
                                placeholder="Password" autocomplete="current-password" required>
                            <label for="password"><i
                                    class="bi bi-lock text-secondary me-2"></i><?= htmlspecialchars(t('auth.password')) ?></label>
                        </div>
                        <button class="btn btn-primary rounded-pill w-100 py-2 shadow-sm fw-medium" type="submit"><i
                                class="bi bi-box-arrow-in-right me-2"></i><?= htmlspecialchars(t('auth.login_button')) ?></button>
                    </form>

                    <div class="text-center mt-4 small text-body-secondary">
                        <?= htmlspecialchars(t('auth.register_title')) ?>?
                        <a href="<?= route_url('register') ?>"
                            class="text-decoration-none fw-semibold ms-1"><?= htmlspecialchars(t('home.create_account')) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>