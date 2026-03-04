<div class="row justify-content-center align-items-center min-vh-100 py-4 py-lg-5">
    <div class="col-12 col-lg-10 col-xxl-8">
        <div class="card border-0 shadow-lg overflow-hidden rounded-4">
            <div class="row g-0">
                <div class="col-lg-5 bg-primary text-white p-4 p-lg-5 d-flex flex-column justify-content-center">
                    <div class="text-uppercase small fw-semibold opacity-75 mb-2"><?= htmlspecialchars(t('auth.brand_tagline', 'Uptime Monitoring')) ?></div>
                    <div class="h3 fw-bold mb-3"><?= htmlspecialchars(t('app.name')) ?></div>
                    <p class="mb-4 opacity-75"><?= htmlspecialchars(t('home.description')) ?></p>
                    <div class="small opacity-75 mb-2"><?= htmlspecialchars(t('auth.register_title')) ?>?</div>
                    <a class="btn btn-light btn-sm align-self-start px-3" href="<?= BASE_URL ?>/index.php?url=auth/register"><?= htmlspecialchars(t('home.create_account')) ?></a>
                </div>
                <div class="col-lg-7 p-4 p-lg-5 bg-body">
                    <h1 class="h3 mb-2"><?= htmlspecialchars(t('auth.login_title')) ?></h1>
                    <p class="text-body-secondary mb-4"><?= htmlspecialchars(t('auth.login_subtitle', 'Welcome back, please sign in to continue.')) ?></p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/index.php?url=auth/login" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <input class="form-control" type="text" name="login_identifier" id="login_identifier" placeholder="Username / Email" autocomplete="username" required>
                            <label for="login_identifier"><?= htmlspecialchars(t('auth.login_identifier', 'Username / Email')) ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" type="password" name="password" id="password" placeholder="Password" autocomplete="current-password" required>
                            <label for="password"><?= htmlspecialchars(t('auth.password')) ?></label>
                        </div>
                        <button class="btn btn-primary w-100 py-2" type="submit"><?= htmlspecialchars(t('auth.login_button')) ?></button>
                    </form>

                    <div class="text-center mt-4 small text-body-secondary">
                        <?= htmlspecialchars(t('auth.register_title')) ?>?
                        <a href="<?= BASE_URL ?>/index.php?url=auth/register" class="text-decoration-none fw-semibold ms-1"><?= htmlspecialchars(t('home.create_account')) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
