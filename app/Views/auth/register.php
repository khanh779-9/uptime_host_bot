<div class="row justify-content-center align-items-center min-vh-100 py-4 py-lg-5">
    <div class="col-12 col-lg-10 col-xxl-8">
        <div class="card border-0 shadow-lg overflow-hidden rounded-4">
            <div class="row g-0">
                <div class="col-lg-5 bg-primary text-white p-4 p-lg-5 d-flex flex-column justify-content-center">
                    <div class="text-uppercase small fw-semibold opacity-75 mb-2"><?= htmlspecialchars(t('auth.brand_tagline', 'Uptime Monitoring')) ?></div>
                    <div class="h3 fw-bold mb-3"><?= htmlspecialchars(t('app.name')) ?></div>
                    <p class="mb-4 opacity-75"><?= htmlspecialchars(t('home.description')) ?></p>
                    <div class="small opacity-75 mb-2"><?= htmlspecialchars(t('auth.login_title')) ?>?</div>
                    <a class="btn btn-light btn-sm align-self-start px-3" href="<?= BASE_URL ?>/index.php?url=auth/login"><?= htmlspecialchars(t('home.login')) ?></a>
                </div>
                <div class="col-lg-7 p-4 p-lg-5 bg-body">
                    <h1 class="h3 mb-2"><?= htmlspecialchars(t('auth.register_title')) ?></h1>
                    <p class="text-body-secondary mb-4"><?= htmlspecialchars(t('auth.register_subtitle', 'Create your account to start monitoring uptime in minutes.')) ?></p>

                    <form method="post" action="<?= BASE_URL ?>/index.php?url=auth/register" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <input class="form-control" type="text" name="username" id="username" placeholder="Username" autocomplete="username" required>
                            <label for="username"><?= htmlspecialchars(t('auth.username')) ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" type="email" name="email" id="email" placeholder="Email" autocomplete="email" required>
                            <label for="email"><?= htmlspecialchars(t('auth.email')) ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" type="password" name="password" id="password" placeholder="Password" autocomplete="new-password" minlength="6" required>
                            <label for="password"><?= htmlspecialchars(t('auth.password')) ?></label>
                        </div>
                        <button class="btn btn-primary w-100 py-2" type="submit"><?= htmlspecialchars(t('auth.register_button')) ?></button>
                    </form>

                    <div class="text-center mt-4 small text-body-secondary">
                        <?= htmlspecialchars(t('auth.login_title')) ?>?
                        <a href="<?= BASE_URL ?>/index.php?url=auth/login" class="text-decoration-none fw-semibold ms-1"><?= htmlspecialchars(t('home.login')) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
