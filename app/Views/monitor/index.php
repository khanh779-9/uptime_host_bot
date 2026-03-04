<?php
$uptimePercent = (float) ($stats['uptime_percent'] ?? 0);
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

    .monitor-shell .monitor-item {
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        padding: 0.9rem;
        background: var(--bs-body-bg);
    }

    .monitor-shell .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .monitor-shell .status-dot.up {
        background: var(--bs-success);
    }

    .monitor-shell .status-dot.down {
        background: var(--bs-danger);
    }

    .monitor-shell .status-dot.unknown {
        background: var(--bs-warning);
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

    <main class="col-12 col-lg-9 col-xl-7">
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-3">
            <h2 class="h3 mb-0"><?= htmlspecialchars(t('monitor.list_title')) ?></h2>
            <div class="d-flex gap-2">
                <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/index.php?url=cron/run" target="_blank"><?= htmlspecialchars(t('monitor.run_check')) ?></a>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>/index.php?url=monitor/create" class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 col-xl-4">
                        <label class="form-label mb-1"><?= htmlspecialchars(t('monitor.name')) ?></label>
                        <input class="form-control" type="text" name="name" placeholder="<?= htmlspecialchars(t('monitor.name_placeholder')) ?>" required>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label mb-1"><?= htmlspecialchars(t('monitor.type')) ?></label>
                        <select class="form-select" name="target_type" required>
                            <option value="host"><?= htmlspecialchars(t('monitor.type.host')) ?></option>
                            <option value="web" selected><?= htmlspecialchars(t('monitor.type.web')) ?></option>
                            <option value="api"><?= htmlspecialchars(t('monitor.type.api')) ?></option>
                            <option value="database"><?= htmlspecialchars(t('monitor.type.database')) ?></option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label mb-1"><?= htmlspecialchars(t('monitor.interval')) ?></label>
                        <select class="form-select" name="check_interval_seconds" required>
                            <option value="30"><?= htmlspecialchars(t('interval.30')) ?></option>
                            <option value="50"><?= htmlspecialchars(t('interval.50')) ?></option>
                            <option value="60"><?= htmlspecialchars(t('interval.60')) ?></option>
                            <option value="300" selected><?= htmlspecialchars(t('interval.300')) ?></option>
                            <option value="900"><?= htmlspecialchars(t('interval.900')) ?></option>
                            <option value="1800"><?= htmlspecialchars(t('interval.1800')) ?></option>
                            <option value="3600"><?= htmlspecialchars(t('interval.3600')) ?></option>
                        </select>
                    </div>
                    <div class="col-12 col-xl-4">
                        <label class="form-label mb-1"><?= htmlspecialchars(t('monitor.url')) ?></label>
                        <input class="form-control" type="text" name="url" placeholder="<?= htmlspecialchars(t('monitor.url_placeholder')) ?>">
                    </div>
                    <div class="col-12 d-grid d-md-block">
                        <button class="btn btn-outline-primary" type="submit"><?= htmlspecialchars(t('monitor.add_button')) ?></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-grid gap-2">
            <?php if (empty($monitors)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-secondary text-center"><?= htmlspecialchars(t('monitor.empty')) ?></div>
                </div>
            <?php else: ?>
                <?php foreach ($monitors as $monitor): ?>
                    <?php
                    $isUp = $monitor['last_status'] !== null && (int) $monitor['last_status'] >= 200 && (int) $monitor['last_status'] < 400;
                    $isDown = $monitor['last_status'] !== null && !$isUp;
                    $statusClass = $isUp ? 'up' : ($isDown ? 'down' : 'unknown');
                    $statusText = $isUp ? t('status.up', 'Up') : ($isDown ? t('status.down', 'Down') : t('common.na'));
                    $healthPercent = $isUp ? 100 : ($isDown ? 30 : 10);
                    ?>
                    <div class="monitor-item">
                        <div class="d-flex flex-column flex-lg-row gap-3 justify-content-between">
                            <div class="d-flex gap-2 align-items-start">
                                <span class="status-dot <?= $statusClass ?> mt-1"></span>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($monitor['name']) ?></div>
                                    <div class="small text-secondary">
                                        <?= htmlspecialchars(t('monitor.type.' . $monitor['target_type'], $monitor['target_type'])) ?> •
                                        <?= htmlspecialchars($statusText) ?> •
                                        <?= htmlspecialchars((string) ($monitor['last_status'] ?? t('common.na'))) ?>
                                    </div>
                                    <div class="small mt-1">
                                        <?php if (str_starts_with((string) $monitor['url'], 'http://') || str_starts_with((string) $monitor['url'], 'https://')): ?>
                                            <a href="<?= htmlspecialchars($monitor['url']) ?>" target="_blank"><?= htmlspecialchars($monitor['url']) ?></a>
                                        <?php else: ?>
                                            <?= htmlspecialchars($monitor['url']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="text-lg-end">
                                <div class="small text-secondary mb-1"><?= htmlspecialchars(t('table.interval')) ?>: <?= (int) $monitor['check_interval_seconds'] ?>s</div>
                                <div class="progress" style="height:7px; min-width:160px;">
                                    <div class="progress-bar <?= $isUp ? 'bg-success' : ($isDown ? 'bg-danger' : 'bg-warning') ?>" style="width: <?= $healthPercent ?>%"></div>
                                </div>
                                <div class="small text-secondary mt-1"><?= htmlspecialchars((string) ($monitor['last_checked_at'] ?? t('common.na'))) ?></div>
                                <a class="btn btn-sm btn-outline-danger mt-2" href="<?= BASE_URL ?>/index.php?url=monitor/delete&id=<?= (int) $monitor['id'] ?>"
                                   onclick="return confirm('<?= htmlspecialchars(t('monitor.delete_confirm')) ?>')"><?= htmlspecialchars(t('monitor.delete')) ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <section class="col-12 col-xl-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-3"><?= htmlspecialchars(t('dashboard.current_status', 'Current status')) ?></div>
                <div class="row text-center g-2">
                    <div class="col-4">
                        <div class="fs-5 fw-semibold text-danger"><?= (int) ($stats['down'] ?? 0) ?></div>
                        <div class="small text-secondary"><?= htmlspecialchars(t('status.down', 'Down')) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="fs-5 fw-semibold text-success"><?= (int) ($stats['up'] ?? 0) ?></div>
                        <div class="small text-secondary"><?= htmlspecialchars(t('status.up', 'Up')) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="fs-5 fw-semibold text-warning"><?= (int) ($stats['paused'] ?? 0) ?></div>
                        <div class="small text-secondary"><?= htmlspecialchars(t('status.paused', 'Paused')) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-3"><?= htmlspecialchars(t('dashboard.last_24h', 'Last 24 hours')) ?></div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary"><?= htmlspecialchars(t('dashboard.overall_uptime', 'Overall uptime')) ?></span>
                    <span class="fw-semibold"><?= number_format($uptimePercent, 2) ?>%</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary"><?= htmlspecialchars(t('dashboard.checked', 'Checked')) ?></span>
                    <span class="fw-semibold"><?= (int) ($stats['checked'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary"><?= htmlspecialchars(t('dashboard.incidents', 'Incidents')) ?></span>
                    <span class="fw-semibold"><?= (int) ($stats['down'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-3"><?= htmlspecialchars(t('dashboard.by_type', 'Phân bổ theo loại')) ?></div>
                <canvas id="typeChart" height="220"></canvas>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(() => {
    const ctx = document.getElementById('typeChart');
    if (!ctx) {
        return;
    }

    const labels = [
        '<?= addslashes(t('monitor.type.host')) ?>',
        '<?= addslashes(t('monitor.type.web')) ?>',
        '<?= addslashes(t('monitor.type.api')) ?>',
        '<?= addslashes(t('monitor.type.database')) ?>'
    ];

    const values = [
        <?= (int) ($typeCounts['host'] ?? 0) ?>,
        <?= (int) ($typeCounts['web'] ?? 0) ?>,
        <?= (int) ($typeCounts['api'] ?? 0) ?>,
        <?= (int) ($typeCounts['database'] ?? 0) ?>
    ];

    const css = getComputedStyle(document.documentElement);
    const colors = [
        css.getPropertyValue('--bs-primary').trim(),
        css.getPropertyValue('--bs-success').trim(),
        css.getPropertyValue('--bs-warning').trim(),
        css.getPropertyValue('--bs-info').trim()
    ];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
})();
</script>
