<div class="row g-3">
    <aside class="col-12 col-lg-3 col-xl-2">
        <div class="card border-0 shadow-sm" style="min-height: calc(100vh - 2rem);">
            <div class="card-body d-flex flex-column h-100">
                <div class="mb-3">
                    <div class="fw-bold fs-4"><span class="text-success">●</span> <?= htmlspecialchars(t('app.name')) ?></div>
                </div>
                <div class="d-grid gap-2 mb-3">
                    <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php?url=auth/login"><?= htmlspecialchars(t('home.login')) ?></a>
                    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/index.php?url=auth/register"><?= htmlspecialchars(t('home.create_account')) ?></a>
                </div>
                <div class="mt-auto small text-secondary">
                    <?= htmlspecialchars(t('home.description')) ?>
                </div>
            </div>
        </div>
    </aside>

    <main class="col-12 col-lg-9 col-xl-10">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 p-lg-5 d-flex flex-column justify-content-center">
                <h1 class="display-6 fw-semibold mb-3"><?= htmlspecialchars(t('home.title')) ?></h1>
                <p class="text-secondary mb-4"><?= htmlspecialchars(t('home.description')) ?></p>
                <div class="d-grid gap-2 d-sm-flex">
                    <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php?url=auth/register"><?= htmlspecialchars(t('home.create_account')) ?></a>
                    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/index.php?url=auth/login"><?= htmlspecialchars(t('home.login')) ?></a>
                </div>
            </div>
        </div>
    </main>
</div>
