<?php
$offcanvasId = isset($offcanvasId) ? (string) $offcanvasId : 'mobileSidebar';
$offcanvasTitle = isset($offcanvasTitle) ? (string) $offcanvasTitle : t('app.name');
$activeMenu = isset($activeMenu) ? (string) $activeMenu : '';
?>

<div class="offcanvas offcanvas-start" tabindex="-1" id="<?= htmlspecialchars($offcanvasId) ?>" aria-labelledby="<?= htmlspecialchars($offcanvasId) ?>Label">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title d-flex align-items-center gap-2" id="<?= htmlspecialchars($offcanvasId) ?>Label">
            <i class="bi bi-activity text-primary"></i>
            <?= htmlspecialchars($offcanvasTitle) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?= htmlspecialchars(t('common.close', 'Close')) ?>"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div class="d-grid gap-2 mb-3">
            <a class="app-menu-link <?= $activeMenu === 'monitor' ? 'active' : '' ?>" href="<?= route_url('monitor') ?>"><i class="bi bi-display me-2 text-primary"></i><?= htmlspecialchars(t('nav.monitors')) ?></a>
            <a class="app-menu-link <?= $activeMenu === 'settings' ? 'active' : '' ?>" href="<?= route_url('settings') ?>"><i class="bi bi-gear me-2 text-secondary"></i><?= htmlspecialchars(t('nav.settings')) ?></a>
            <a class="app-menu-link <?= $activeMenu === 'incidents' ? 'active' : '' ?>" href="<?= route_url('incidents') ?>"><i class="bi bi-exclamation-triangle me-2 text-secondary"></i><?= htmlspecialchars(t('nav.incidents', 'Incidents')) ?></a>
        </div>

        <div class="mt-auto pt-3 border-top">
            <div class="small text-secondary mb-2"><?= htmlspecialchars($_SESSION['username'] ?? t('auth.username', 'User')) ?></div>
            <div class="d-grid gap-2">
                <a class="btn btn-outline-secondary" href="<?= route_url('settings') ?>"><?= htmlspecialchars(t('nav.settings')) ?></a>
                <a class="btn btn-outline-danger" href="<?= route_url('logout') ?>"><?= htmlspecialchars(t('nav.logout')) ?></a>
            </div>
        </div>
    </div>
</div>
