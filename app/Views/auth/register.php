<div class="row justify-content-center align-items-center min-vh-100 py-4 py-lg-5">
    <div class="col-12 col-lg-10 col-xxl-8">
        <div class="card app-panel overflow-hidden rounded-4">
            <div class="row g-0">
                <div
                    class="col-lg-5 bg-primary bg-gradient text-white p-4 p-lg-5 d-none d-lg-flex flex-column justify-content-center position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25"
                        style="background-image: radial-gradient(circle at 20% 40%, rgba(255,255,255,0.4), transparent 40%), radial-gradient(circle at 80% 60%, rgba(255,255,255,0.4), transparent 40%);">
                    </div>
                    <div class="position-relative z-1">
                        <div class="text-uppercase small fw-semibold opacity-75 mb-2 d-flex align-items-center"><i
                                class="bi bi-shield-check me-2 fs-5"></i><?= htmlspecialchars(t('auth.brand_tagline', 'Uptime Monitoring')) ?>
                        </div>
                        <div class="h3 fw-bold mb-3"><?= htmlspecialchars(t('app.name')) ?></div>
                        <p class="mb-4 opacity-75"><?= htmlspecialchars(t('home.description')) ?></p>
                        <div class="small opacity-75 mb-2"><?= htmlspecialchars(t('auth.login_title')) ?>?</div>
                        <a class="btn btn-light btn-sm rounded-pill align-self-start px-4 shadow-sm fw-medium pt-2 pb-2"
                            href="<?= route_url('login') ?>"><?= htmlspecialchars(t('home.login')) ?></a>
                    </div>
                </div>
                <div class="col-12 col-lg-7 p-4 p-lg-5 bg-body">
                    <h1 class="h3 mb-2"><?= htmlspecialchars(t('auth.register_title')) ?></h1>
                    <p class="text-body-secondary mb-4">
                        <?= htmlspecialchars(t('auth.register_subtitle', 'Create your account to start monitoring uptime in minutes.')) ?>
                    </p>

                    <form method="post" action="<?= route_url('register') ?>" class="needs-validation"
                        novalidate>
                        <div class="form-floating mb-3">
                            <input class="form-control rounded-3" type="text" name="username" id="username"
                                placeholder="Username" autocomplete="username" required>
                            <label for="username"><i
                                    class="bi bi-person text-secondary me-2"></i><?= htmlspecialchars(t('auth.username')) ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control rounded-3" type="email" name="email" id="email"
                                placeholder="Email" autocomplete="email" required>
                            <label for="email"><i
                                    class="bi bi-envelope text-secondary me-2"></i><?= htmlspecialchars(t('auth.email')) ?></label>
                        </div>
                        <div class="form-floating mb-4">
                            <input class="form-control rounded-3" type="password" name="password" id="password"
                                placeholder="Password" autocomplete="new-password" minlength="6" required>
                            <label for="password"><i
                                    class="bi bi-lock text-secondary me-2"></i><?= htmlspecialchars(t('auth.password')) ?></label>
                        </div>
                        <button class="btn btn-primary rounded-pill w-100 py-2 shadow-sm fw-medium" type="submit"><i
                                class="bi bi-person-plus me-2"></i><?= htmlspecialchars(t('auth.register_button')) ?></button>
                    </form>

                    <div class="text-center mt-4 small text-body-secondary">
                        <?= htmlspecialchars(t('auth.login_title')) ?>?
                        <a href="<?= route_url('login') ?>"
                            class="text-decoration-none fw-semibold ms-1"><?= htmlspecialchars(t('home.login')) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>