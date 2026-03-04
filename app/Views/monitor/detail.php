<?php
$statusBadgeClass = $isUp ? 'text-bg-success' : ($isDown ? 'text-bg-danger' : 'text-bg-secondary');
$checkedAt = (string) ($monitor['last_checked_at'] ?? t('common.na'));
$lastStatus = (string) ($monitor['last_status'] ?? t('common.na'));
$uptimePercent = $isUp ? 100 : ($isDown ? 30 : 0);
?>

<style>
    .monitor-shell {
        min-height: calc(100vh - 2rem);
    }

    .monitor-shell .sidebar-card {
        min-height: calc(100vh - 2rem);
    }

    .monitor-shell .menu-link {
        border-radius: 0.6rem;
        padding: 0.6rem 0.8rem;
        color: var(--bs-body-color);
        text-decoration: none;
        display: block;
    }

    .monitor-shell .menu-link.active {
        background: var(--bs-primary-bg-subtle);
        color: var(--bs-primary-text-emphasis);
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .monitor-shell .sidebar-card {
            min-height: auto;
        }
    }
</style>

<div class="monitor-shell row g-3">
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
                    <a class="menu-link active" href="<?= BASE_URL ?>/index.php?url=monitor/index"><?= htmlspecialchars(t('nav.monitors')) ?></a>
                    <a class="menu-link" href="<?= BASE_URL ?>/index.php?url=settings/index"><?= htmlspecialchars(t('nav.settings')) ?></a>
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

    <main class="col-12 col-lg-9 col-xl-10">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <div class="small text-secondary mb-1">
                    <a class="text-decoration-none" href="<?= BASE_URL ?>/index.php?url=monitor/index">← <?= htmlspecialchars(t('nav.monitors')) ?></a>
                </div>
                <h2 class="h3 mb-0"><?= htmlspecialchars($monitor['name']) ?></h2>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>/index.php?url=cron/run" target="_blank"><?= htmlspecialchars(t('monitor.run_check')) ?></a>
                <a class="btn btn-outline-danger btn-sm" href="<?= BASE_URL ?>/index.php?url=monitor/delete&id=<?= (int) $monitor['id'] ?>"
                   onclick="return confirm('<?= htmlspecialchars(t('monitor.delete_confirm')) ?>')"><?= htmlspecialchars(t('monitor.delete')) ?></a>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <span class="badge <?= $statusBadgeClass ?> mb-2"><?= htmlspecialchars($statusText) ?></span>
                    <div class="small text-secondary mb-1"><?= htmlspecialchars(t('monitor.type.' . $monitor['target_type'], $monitor['target_type'])) ?></div>
                    <div class="fw-semibold mb-2"><?= htmlspecialchars($monitor['url']) ?></div>
                    <div class="small text-secondary"><?= htmlspecialchars(t('dashboard.last_24h', 'Last 24 hours')) ?>: <?= number_format((float) $uptimePercent, 2) ?>%</div>
                </div>
                <div class="text-lg-end">
                    <div class="small text-secondary"><?= htmlspecialchars(t('table.interval')) ?></div>
                    <div class="fs-5 fw-semibold"><?= (int) $monitor['check_interval_seconds'] ?>s</div>
                    <div class="small text-secondary mt-2"><?= htmlspecialchars(t('dashboard.checked', 'Checked')) ?></div>
                    <div class="fw-semibold"><?= htmlspecialchars($checkedAt) ?></div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-3"><?= htmlspecialchars(t('dashboard.current_status', 'Current status')) ?></div>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="small text-secondary mb-1">HTTP Code</div>
                                    <div class="fs-4 fw-semibold"><?= htmlspecialchars($lastStatus) ?></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="small text-secondary mb-1"><?= htmlspecialchars(t('monitor.interval')) ?></div>
                                    <div class="fs-4 fw-semibold"><?= (int) $monitor['check_interval_seconds'] ?>s</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="small text-secondary mb-1"><?= htmlspecialchars(t('dashboard.overall_uptime', 'Overall uptime')) ?></div>
                                    <div class="fs-4 fw-semibold"><?= number_format((float) $uptimePercent, 2) ?>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-3"><?= htmlspecialchars(t('monitor.list_title')) ?></div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">ID</span>
                                <span class="fw-semibold">#<?= (int) $monitor['id'] ?></span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">Expected Status</span>
                                <span class="fw-semibold"><?= (int) $monitor['expected_status'] ?></span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">Created</span>
                                <span class="fw-semibold"><?= htmlspecialchars((string) ($monitor['created_at'] ?? t('common.na'))) ?></span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">Active</span>
                                <span class="fw-semibold"><?= (int) ($monitor['is_active'] ?? 0) === 1 ? 'Yes' : 'No' ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
