<?php
$activeMenu = isset($activeMenu) ? (string) $activeMenu : '';
?>

<aside class="col-12 col-lg-3 col-xl-2 d-none d-lg-block">
    <div class="card app-sidebar-card sidebar-card sticky-lg-top app-sidebar-sticky">
        <div class="card-body d-flex flex-column h-100 p-4">
            <div class="mb-4">
                <a class="text-decoration-none fw-bold fs-4 d-flex align-items-center gap-2" href="<?= route_url('monitor') ?>">
                    <i class="bi bi-activity bg-success-subtle text-success rounded-circle p-1 d-inline-flex justify-content-center align-items-center app-brand-icon"></i>
                    <span class="text-body"><?= htmlspecialchars(t('app.name')) ?></span>
                </a>
            </div>

            <div class="d-grid gap-2 mb-3">
                <a class="app-menu-link <?= $activeMenu === 'monitor' ? 'active' : '' ?>" href="<?= route_url('monitor') ?>"><i class="bi bi-display me-2 text-primary"></i><?= htmlspecialchars(t('nav.monitors')) ?></a>
                <a class="app-menu-link <?= $activeMenu === 'settings' ? 'active' : '' ?>" href="<?= route_url('settings') ?>"><i class="bi bi-gear me-2 text-secondary"></i><?= htmlspecialchars(t('nav.settings')) ?></a>
                <a class="app-menu-link <?= $activeMenu === 'incidents' ? 'active' : '' ?>" href="<?= route_url('incidents') ?>"><i class="bi bi-exclamation-triangle me-2 text-secondary"></i><?= htmlspecialchars(t('nav.incidents', 'Incidents')) ?></a>
            </div>

            <div class="mt-auto pt-3 border-top">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" data-bs-toggle="dropdown" aria-expanded="false" type="button">
                        <span><?= htmlspecialchars($_SESSION['username'] ?? t('auth.username', 'User')) ?></span>
                        <span>⋯</span>
                    </button>
                    <ul class="dropdown-menu w-100">
                        <li><a class="dropdown-item" href="<?= route_url('settings') ?>"><?= htmlspecialchars(t('nav.settings')) ?></a></li>
                        <li><a class="dropdown-item" href="<?= route_url('logout') ?>"><?= htmlspecialchars(t('nav.logout')) ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</aside>
