<div class="home-shell row g-3">
    <aside class="col-12 col-lg-3 col-xl-2">
        <div class="card app-sidebar-card home-sidebar">
            <div class="card-body d-flex flex-column h-100">
                <div class="mb-4 mt-2">
                    <div class="fw-bold fs-4 text-primary d-flex align-items-center gap-2">
                        <i class="bi bi-activity bg-primary-subtle text-primary rounded-circle p-1 d-inline-flex justify-content-center align-items-center shadow-sm app-brand-icon"></i>
                        <?= htmlspecialchars(t('app.name')) ?>
                    </div>
                </div>
                <div class="d-grid gap-3 mb-4">
                    <a class="btn btn-primary rounded-pill fw-medium shadow-sm py-2"
                        href="<?= route_url('login') ?>"><i
                            class="bi bi-box-arrow-in-right me-2"></i><?= htmlspecialchars(t('home.login')) ?></a>
                    <a class="btn btn-outline-primary rounded-pill fw-medium py-2"
                        href="<?= route_url('register') ?>"><i
                            class="bi bi-person-plus me-2"></i><?= htmlspecialchars(t('home.create_account')) ?></a>
                </div>
                <div class="mt-auto small text-secondary">
                    <?= htmlspecialchars(t('home.description')) ?>
                </div>
            </div>
        </div>
    </aside>

    <main class="col-12 col-lg-9 col-xl-10">
        <div class="card app-panel h-100 overflow-hidden position-relative rounded-4">
            <div class="position-absolute top-0 end-0 p-4 opacity-10 pe-none d-none d-md-block home-hero-art">
                <i class="bi bi-hdd-network home-hero-icon"></i>
            </div>
            <div class="card-body p-4 p-lg-5 d-flex flex-column justify-content-center position-relative z-1">
                <h1 class="hero-title fw-bold mb-4 home-hero-gradient">
                    <?= htmlspecialchars(t('home.title')) ?>
                </h1>
                <p class="hero-copy home-hero-copy text-secondary mb-5 fs-5">
                    <?= htmlspecialchars(t('home.description')) ?>
                </p>
                <div class="d-grid gap-3 d-sm-flex">
                    <a class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-2"
                        href="<?= route_url('register') ?>">
                        <i class="bi bi-rocket-takeoff"></i> <?= htmlspecialchars(t('home.create_account')) ?>
                    </a>
                    <a class="btn btn-light rounded-pill px-4 py-2 border fw-medium d-inline-flex align-items-center justify-content-center gap-2"
                        href="<?= route_url('login') ?>">
                        <i class="bi bi-box-arrow-in-right"></i> <?= htmlspecialchars(t('home.login')) ?>
                    </a>
                </div>
            </div>
        </div>  
    </main>
</div>
