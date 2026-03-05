<?php
$uptimePercent = (float) ($stats['uptime_percent'] ?? 0);
$feedback = get_flash('monitor_feedback');
$cronRunUrl = BASE_URL . '/index.php?url=cron/run&token=' . urlencode((string) CRON_SECRET);
?>

<div class="monitor-shell align-start row g-3">
    <?php
    $activeMenu = 'monitor';
    include APP_PATH . '/Views/layouts/desktop_sidebar.php';
    ?>

    <main class="col-12 col-lg-9 col-xl-7">
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-4">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarMonitor" aria-controls="mobileSidebarMonitor">
                    <i class="bi bi-list me-1"></i> <?= htmlspecialchars(t('common.menu')) ?>
                </button>
                <h2 class="h3 mb-0 app-page-title"><?= htmlspecialchars(t('monitor.list_title')) ?></h2>
            </div>
            <div class="monitor-list-actions d-flex flex-wrap gap-2 justify-content-md-end">
                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-2 d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileStatsMonitor" aria-controls="mobileStatsMonitor">
                    <i class="bi bi-bar-chart"></i> <?= htmlspecialchars(t('common.stats')) ?>
                </button>
                <button class="btn btn-primary btn-sm rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#addMonitorModal">
                    <i class="bi bi-plus-lg"></i> <?= htmlspecialchars(t('monitor.add_button')) ?>
                </button>
                <a class="btn btn-outline-primary btn-sm rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-2" href="<?= htmlspecialchars($cronRunUrl) ?>" target="_blank">
                    <i class="bi bi-play-fill"></i> <?= htmlspecialchars(t('monitor.run_check')) ?>
                </a>
            </div>
        </div>

        <?php if (!empty($feedback['message'])): ?>
            <div class="alert alert-<?= htmlspecialchars((string) ($feedback['level'] ?? 'info')) ?> mb-3" role="alert">
                <?= htmlspecialchars((string) $feedback['message']) ?>
            </div>
        <?php endif; ?>

        <div class="d-grid gap-2">
            <?php if (empty($monitors)): ?>
                <div class="card app-panel">
                    <div class="card-body text-secondary text-center py-5"><?= htmlspecialchars(t('monitor.empty')) ?></div>
                </div>
            <?php else: ?>
                <?php foreach ($monitors as $monitor): ?>
                    <?php
                    $expectedStatus = (int) ($monitor['expected_status'] ?? 200);
                    $isUp = $monitor['last_status'] !== null && (int) $monitor['last_status'] === $expectedStatus;
                    $isDown = $monitor['last_status'] !== null && !$isUp;
                    $statusClass = $isUp ? 'up' : ($isDown ? 'down' : 'unknown');
                    $statusText = $isUp ? t('status.up', 'Up') : ($isDown ? t('status.down', 'Down') : t('common.na'));
                    $healthPercent = $isUp ? 100 : ($isDown ? 30 : 10);
                    $editModalId = 'editMonitorModal' . (int) $monitor['id'];
                    $isActive = (int) ($monitor['is_active'] ?? 1) === 1;
                    ?>
                    <div class="monitor-item position-relative overflow-hidden mb-3">
                        <div class="bg-<?= $isUp ? 'success' : ($isDown ? 'danger' : 'warning') ?> position-absolute top-0 start-0 h-100" style="width: 4px;"></div>
                        <div class="ps-2">
                            <div class="item-head">
                                <span class="status-dot <?= $statusClass ?>"></span>
                                <div class="fw-bold text-dark fs-5 flex-grow-1"><?= htmlspecialchars($monitor['name']) ?></div>
                                <span class="status-pill <?= $isUp ? 'bg-success-subtle text-success' : ($isDown ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') ?>"><?= htmlspecialchars($statusText) ?></span>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="meta-chip"><i class="bi bi-hdd-network"></i><?= htmlspecialchars(t('monitor.type.' . $monitor['target_type'], $monitor['target_type'])) ?></span>
                                <span class="meta-chip"><i class="bi bi-arrow-return-right"></i><?= htmlspecialchars((string) ($monitor['last_status'] ?? t('common.na'))) ?></span>
                                <span class="meta-chip"><i class="bi bi-bullseye"></i><?= htmlspecialchars(t('common.expected')) ?> <?= (int) ($monitor['expected_status'] ?? 200) ?></span>
                                <span class="meta-chip"><i class="bi bi-clock-history"></i><?= (int) $monitor['check_interval_seconds'] ?>s</span>
                                <span class="meta-chip"><i class="bi bi-calendar2-check"></i><?= htmlspecialchars((string) ($monitor['last_checked_at'] ?? t('common.na'))) ?></span>
                            </div>

                            <div class="small fw-medium font-monospace text-primary position-relative mb-2 monitor-url">
                                <?php if (str_starts_with((string) $monitor['url'], 'http://') || str_starts_with((string) $monitor['url'], 'https://')): ?>
                                    <a href="<?= htmlspecialchars($monitor['url']) ?>" target="_blank" class="text-decoration-none"><i class="bi bi-box-arrow-up-right me-1"></i><?= htmlspecialchars($monitor['url']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($monitor['url']) ?>
                                <?php endif; ?>
                            </div>

                            <div class="progress rounded-pill bg-light" style="height:8px; border: 1px solid rgba(0,0,0,0.05);">
                                <div class="progress-bar rounded-pill <?= $isUp ? 'bg-success' : ($isDown ? 'bg-danger' : 'bg-warning') ?>" style="width: <?= $healthPercent ?>%"></div>
                            </div>

                            <div class="item-actions d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                <div class="small text-secondary"><i class="bi bi-activity me-1"></i><?= htmlspecialchars(t('common.health_score')) ?>: <?= (int) $healthPercent ?>%</div>
                                <div class="monitor-card-actions d-flex flex-wrap gap-2 justify-content-md-end">
                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="<?= BASE_URL ?>/index.php?url=monitor/detail&id=<?= (int) $monitor['id'] ?>"><i class="bi bi-eye me-1"></i><?= htmlspecialchars(t('common.view', 'View details')) ?></a>
                                    <a class="btn btn-sm <?= $isActive ? 'btn-outline-warning' : 'btn-outline-success' ?> rounded-pill px-3" href="<?= BASE_URL ?>/index.php?url=monitor/toggleActive&id=<?= (int) $monitor['id'] ?>&next=<?= $isActive ? 0 : 1 ?>">
                                        <i class="bi <?= $isActive ? 'bi-pause-circle' : 'bi-play-circle' ?> me-1"></i><?= $isActive ? htmlspecialchars(t('status.paused', 'Pause')) : htmlspecialchars(t('status.resumed', 'Resume')) ?>
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" type="button" data-bs-toggle="modal" data-bs-target="#<?= htmlspecialchars($editModalId) ?>"><i class="bi bi-pencil-square me-1"></i><?= htmlspecialchars(t('common.edit', 'Edit')) ?></button>
                                    <a class="btn btn-sm btn-outline-danger rounded-pill px-3" href="<?= BASE_URL ?>/index.php?url=monitor/delete&id=<?= (int) $monitor['id'] ?>" onclick="return confirm('<?= htmlspecialchars(t('monitor.delete_confirm')) ?>')"><i class="bi bi-trash3 me-1"></i><?= htmlspecialchars(t('monitor.delete')) ?></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="<?= htmlspecialchars($editModalId) ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title"><?= htmlspecialchars(t('common.edit', 'Edit')) ?>: <?= htmlspecialchars($monitor['name']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-3">
                                    <form method="post" action="<?= BASE_URL ?>/index.php?url=monitor/update" class="row g-3 align-items-end">
                                        <input type="hidden" name="id" value="<?= (int) $monitor['id'] ?>">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.name')) ?></label>
                                            <input class="form-control" type="text" name="name" value="<?= htmlspecialchars((string) $monitor['name']) ?>" required>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.type')) ?></label>
                                            <select class="form-select" name="target_type" required>
                                                <option value="host" <?= $monitor['target_type'] === 'host' ? 'selected' : '' ?>><?= htmlspecialchars(t('monitor.type.host')) ?></option>
                                                <option value="web" <?= $monitor['target_type'] === 'web' ? 'selected' : '' ?>><?= htmlspecialchars(t('monitor.type.web')) ?></option>
                                                <option value="api" <?= $monitor['target_type'] === 'api' ? 'selected' : '' ?>><?= htmlspecialchars(t('monitor.type.api')) ?></option>
                                                <option value="database" <?= $monitor['target_type'] === 'database' ? 'selected' : '' ?>><?= htmlspecialchars(t('monitor.type.database')) ?></option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.interval')) ?></label>
                                            <select class="form-select" name="check_interval_seconds" required>
                                                <option value="30" <?= (int) $monitor['check_interval_seconds'] === 30 ? 'selected' : '' ?>><?= htmlspecialchars(t('interval.30')) ?></option>
                                                <option value="50" <?= (int) $monitor['check_interval_seconds'] === 50 ? 'selected' : '' ?>><?= htmlspecialchars(t('interval.50')) ?></option>
                                                <option value="60" <?= (int) $monitor['check_interval_seconds'] === 60 ? 'selected' : '' ?>><?= htmlspecialchars(t('interval.60')) ?></option>
                                                <option value="300" <?= (int) $monitor['check_interval_seconds'] === 300 ? 'selected' : '' ?>><?= htmlspecialchars(t('interval.300')) ?></option>
                                                <option value="900" <?= (int) $monitor['check_interval_seconds'] === 900 ? 'selected' : '' ?>><?= htmlspecialchars(t('interval.900')) ?></option>
                                                <option value="1800" <?= (int) $monitor['check_interval_seconds'] === 1800 ? 'selected' : '' ?>><?= htmlspecialchars(t('interval.1800')) ?></option>
                                                <option value="3600" <?= (int) $monitor['check_interval_seconds'] === 3600 ? 'selected' : '' ?>><?= htmlspecialchars(t('interval.3600')) ?></option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.url')) ?></label>
                                            <input class="form-control" type="text" name="url" value="<?= htmlspecialchars((string) $monitor['url']) ?>" placeholder="<?= htmlspecialchars(t('monitor.url_placeholder')) ?>">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.expected_status')) ?></label>
                                            <input class="form-control" type="number" name="expected_status" min="100" max="599" value="<?= (int) ($monitor['expected_status'] ?? 200) ?>" required>
                                        </div>
                                        <div class="col-12 text-end mt-2">
                                            <button class="btn btn-primary rounded-pill px-4" type="submit"><i class="bi bi-check2 me-1"></i><?= htmlspecialchars(t('common.save', 'Save')) ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <section class="d-none d-xl-block col-xl-3">
        <div class="stats-stack">
            <div class="card app-panel mb-3 rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold d-flex align-items-center text-dark"><i class="bi bi-stars text-primary me-2 fs-5"></i><?= htmlspecialchars(t('dashboard.snapshot')) ?></div>
                        <span class="badge rounded-pill text-bg-light border"><?= htmlspecialchars(t('common.live')) ?></span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="metric-tile">
                                <div class="small text-secondary mb-1"><?= htmlspecialchars(t('dashboard.total_monitors_label')) ?></div>
                                <div class="h5 mb-0"><?= (int) ($stats['total'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-tile">
                                <div class="small text-secondary mb-1"><?= htmlspecialchars(t('dashboard.overall_uptime')) ?></div>
                                <div class="h5 mb-0 text-success"><?= number_format($uptimePercent, 2) ?>%</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-tile">
                                <div class="small text-secondary mb-1"><i class="bi bi-arrow-up-circle-fill text-success me-1"></i><?= htmlspecialchars(t('status.up', 'Up')) ?></div>
                                <div class="h6 mb-0"><?= (int) ($stats['up'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-tile">
                                <div class="small text-secondary mb-1"><i class="bi bi-arrow-down-circle-fill text-danger me-1"></i><?= htmlspecialchars(t('status.down', 'Down')) ?></div>
                                <div class="h6 mb-0"><?= (int) ($stats['down'] ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card app-panel mb-3 rounded-4">
                <div class="card-body p-3">
                    <div class="fw-bold mb-3 d-flex align-items-center text-dark"><i class="bi bi-broadcast text-info me-2 fs-5"></i><?= htmlspecialchars(t('dashboard.health_pulse')) ?></div>
                    <div class="chart-wrap mb-2">
                        <canvas id="healthChart"></canvas>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary pt-1">
                        <span><?= htmlspecialchars(t('dashboard.checked')) ?>: <span class="fw-semibold text-body"><?= (int) ($stats['checked'] ?? 0) ?></span></span>
                        <span><?= htmlspecialchars(t('dashboard.incidents')) ?>: <span class="fw-semibold text-danger"><?= (int) ($stats['down'] ?? 0) ?></span></span>
                    </div>
                </div>
            </div>

            <div class="card app-panel mb-4 rounded-4">
                <div class="card-body p-3">
                    <div class="fw-bold mb-2 d-flex align-items-center text-dark"><i class="bi bi-pie-chart-fill text-warning me-2 fs-5"></i><?= htmlspecialchars(t('dashboard.by_type', 'Distribution by type')) ?></div>
                    <div class="chart-wrap">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
$offcanvasId = 'mobileSidebarMonitor';
$offcanvasTitle = t('app.name');
$activeMenu = 'monitor';
include APP_PATH . '/Views/layouts/mobile_sidebar_offcanvas.php';
?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileStatsMonitor" aria-labelledby="mobileStatsMonitorLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title d-flex align-items-center gap-2" id="mobileStatsMonitorLabel">
            <i class="bi bi-bar-chart-line text-primary"></i>
            <?= htmlspecialchars(t('dashboard.monitor_stats')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="card app-panel mb-2 rounded-4">
            <div class="card-body p-3">
                <div class="fw-bold mb-2 d-flex align-items-center text-dark"><i class="bi bi-stars text-primary me-2"></i><?= htmlspecialchars(t('dashboard.snapshot')) ?></div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="metric-tile">
                            <div class="small text-secondary mb-0"><?= htmlspecialchars(t('dashboard.total_monitors_label')) ?></div>
                            <div class="h5 mb-0"><?= (int) ($stats['total'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric-tile">
                            <div class="small text-secondary mb-0"><?= htmlspecialchars(t('dashboard.overall_uptime')) ?></div>
                            <div class="h5 mb-0 text-success"><?= number_format($uptimePercent, 2) ?>%</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric-tile">
                            <div class="small text-secondary mb-0"><?= htmlspecialchars(t('status.up', 'Up')) ?></div>
                            <div class="h6 mb-0"><?= (int) ($stats['up'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric-tile">
                            <div class="small text-secondary mb-0"><?= htmlspecialchars(t('status.down', 'Down')) ?></div>
                            <div class="h6 mb-0"><?= (int) ($stats['down'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card app-panel rounded-4">
            <div class="card-body p-3">
                <div class="fw-bold mb-3 d-flex align-items-center text-dark"><i class="bi bi-info-circle text-info me-2"></i><?= htmlspecialchars(t('common.more')) ?></div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-secondary"><?= htmlspecialchars(t('dashboard.checked')) ?></span>
                    <span class="fw-semibold"><?= (int) ($stats['checked'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-secondary"><?= htmlspecialchars(t('status.paused')) ?></span>
                    <span class="fw-semibold"><?= (int) ($stats['paused'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-secondary"><?= htmlspecialchars(t('status.unknown')) ?></span>
                    <span class="fw-semibold"><?= (int) ($stats['unknown'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addMonitorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title"><?= htmlspecialchars(t('monitor.add_button')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form method="post" action="<?= BASE_URL ?>/index.php?url=monitor/create" class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.name')) ?></label>
                        <input class="form-control" type="text" name="name" placeholder="<?= htmlspecialchars(t('monitor.name_placeholder')) ?>" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.type')) ?></label>
                        <select class="form-select" name="target_type" required>
                            <option value="host"><?= htmlspecialchars(t('monitor.type.host')) ?></option>
                            <option value="web" selected><?= htmlspecialchars(t('monitor.type.web')) ?></option>
                            <option value="api"><?= htmlspecialchars(t('monitor.type.api')) ?></option>
                            <option value="database"><?= htmlspecialchars(t('monitor.type.database')) ?></option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.interval')) ?></label>
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
                    <div class="col-12">
                        <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.url')) ?></label>
                        <input class="form-control" type="text" name="url" placeholder="<?= htmlspecialchars(t('monitor.url_placeholder')) ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-medium mb-1"><?= htmlspecialchars(t('monitor.expected_status')) ?></label>
                        <input class="form-control" type="number" name="expected_status" min="100" max="599" value="200" required>
                    </div>
                    <div class="col-12 text-end mt-2">
                        <button class="btn btn-primary rounded-pill px-4" type="submit"><i class="bi bi-plus-lg me-1"></i><?= htmlspecialchars(t('monitor.add_button')) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (() => {
        const css = getComputedStyle(document.documentElement);
        const colorPrimary = css.getPropertyValue('--bs-primary').trim();
        const colorSuccess = css.getPropertyValue('--bs-success').trim();
        const colorDanger = css.getPropertyValue('--bs-danger').trim();
        const colorWarning = css.getPropertyValue('--bs-warning').trim();
        const colorInfo = css.getPropertyValue('--bs-info').trim();
        const gridColor = css.getPropertyValue('--bs-border-color').trim();

        const healthCtx = document.getElementById('healthChart');
        if (healthCtx) {
            new Chart(healthCtx, {
                type: 'radar',
                data: {
                    labels: [
                        '<?= addslashes(t('status.up', 'Up')) ?>',
                        '<?= addslashes(t('status.down', 'Down')) ?>',
                        '<?= addslashes(t('status.paused', 'Paused')) ?>',
                        '<?= addslashes(t('status.unknown', 'Unknown')) ?>',
                        '<?= addslashes(t('dashboard.checked', 'Checked')) ?>'
                    ],
                    datasets: [{
                        data: [
                            <?= (int) ($stats['up'] ?? 0) ?>,
                            <?= (int) ($stats['down'] ?? 0) ?>,
                            <?= (int) ($stats['paused'] ?? 0) ?>,
                            <?= (int) ($stats['unknown'] ?? 0) ?>,
                            <?= (int) ($stats['checked'] ?? 0) ?>
                        ],
                        borderColor: colorPrimary,
                        backgroundColor: colorPrimary + '2A',
                        borderWidth: 2,
                        pointRadius: 2,
                        pointBackgroundColor: colorPrimary
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        r: {
                            grid: {
                                color: gridColor
                            },
                            angleLines: {
                                color: gridColor
                            },
                            pointLabels: {
                                font: {
                                    size: 10
                                }
                            },
                            ticks: {
                                display: false
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        const typeCtx = document.getElementById('typeChart');
        if (!typeCtx) {
            return;
        }

        const typeLabels = [
            '<?= addslashes(t('monitor.type.host')) ?>',
            '<?= addslashes(t('monitor.type.web')) ?>',
            '<?= addslashes(t('monitor.type.api')) ?>',
            '<?= addslashes(t('monitor.type.database')) ?>'
        ];

        const typeValues = [
            <?= (int) ($typeCounts['host'] ?? 0) ?>,
            <?= (int) ($typeCounts['web'] ?? 0) ?>,
            <?= (int) ($typeCounts['api'] ?? 0) ?>,
            <?= (int) ($typeCounts['database'] ?? 0) ?>
        ];

        new Chart(typeCtx, {
            type: 'polarArea',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: [
                        colorPrimary + 'CC',
                        colorSuccess + 'CC',
                        colorWarning + 'CC',
                        colorInfo + 'CC'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    r: {
                        grid: {
                            color: gridColor
                        },
                        angleLines: {
                            color: gridColor
                        },
                        ticks: {
                            display: false
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    })();
</script>