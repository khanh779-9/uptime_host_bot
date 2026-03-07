<?php
$statusBadgeClass = $isUp ? 'text-bg-success' : ($isDown ? 'text-bg-danger' : 'text-bg-secondary');
$checkedAt = (string) ($monitor['last_checked_at'] ?? t('common.na'));
$lastStatus = (string) ($monitor['last_status'] ?? t('common.na'));
$responseLabels = array_map(static fn($point) => $point['label'], $responseSeries ?? []);
$responseValues = array_map(static fn($point) => (int) $point['value'], $responseSeries ?? []);
$cronRunUrl = route_url('cron_run', ['token' => (string) CRON_SECRET]);
?>

<div class="monitor-shell row g-3">
    <?php
    $activeMenu = 'monitor';
    include APP_PATH . '/Views/layouts/desktop_sidebar.php';
    ?>

    <main class="col-12 col-lg-9 col-xl-10">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <button class="btn btn-outline-secondary btn-sm d-lg-none mb-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarMonitorDetail" aria-controls="mobileSidebarMonitorDetail">
                    <i class="bi bi-list me-1"></i> <?= htmlspecialchars(t('common.menu')) ?>
                </button>
                <div class="small text-secondary mb-1"><a class="text-decoration-none" href="<?= route_url('monitor') ?>">← <?= htmlspecialchars(t('nav.monitors')) ?></a></div>
                <h2 class="h3 mb-0 app-page-title"><?= htmlspecialchars($monitor['name']) ?></h2>
            </div>
            <div class="monitor-detail-actions d-flex flex-wrap gap-2 justify-content-md-end">
                <a class="btn btn-outline-primary btn-sm rounded-pill" href="<?= htmlspecialchars($cronRunUrl) ?>" target="_blank"><i class="bi bi-play-fill me-1"></i><?= htmlspecialchars(t('monitor.run_check')) ?></a>
                <a class="btn <?= (int) ($monitor['is_active'] ?? 1) === 1 ? 'btn-outline-warning' : 'btn-outline-success' ?> btn-sm rounded-pill" href="<?= route_url('monitor_toggle', ['monitor_id' => (int) $monitor['id'], 'next' => (int) ($monitor['is_active'] ?? 1) === 1 ? 0 : 1]) ?>">
                    <i class="bi <?= (int) ($monitor['is_active'] ?? 1) === 1 ? 'bi-pause-circle' : 'bi-play-circle' ?> me-1"></i><?= (int) ($monitor['is_active'] ?? 1) === 1 ? htmlspecialchars(t('status.paused', 'Pause')) : htmlspecialchars(t('status.resumed', 'Resume')) ?>
                </a>
                <a class="btn btn-outline-danger btn-sm rounded-pill" href="<?= route_url('monitor_delete', ['monitor_id' => (int) $monitor['id']]) ?>" onclick="return confirm('<?= htmlspecialchars(t('monitor.delete_confirm')) ?>')"><i class="bi bi-trash3 me-1"></i><?= htmlspecialchars(t('monitor.delete')) ?></a>
            </div>
        </div>

        <div class="card app-panel mb-3">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between gap-2 p-4">
                <div>
                    <span class="badge <?= $statusBadgeClass ?> mb-2"><?= htmlspecialchars($statusText) ?></span>
                    <div class="small text-secondary mb-1"><?= htmlspecialchars(t('monitor.type.' . $monitor['target_type'], $monitor['target_type'])) ?></div>
                    <div class="fw-semibold mb-1"><?= htmlspecialchars($monitor['url']) ?></div>
                    <div class="small text-secondary"><?= htmlspecialchars(t('dashboard.current_status_label')) ?>: <span class="fw-semibold"><?= htmlspecialchars($statusText) ?></span></div>
                    <div class="small text-secondary"><?= htmlspecialchars(t('dashboard.currently_up_for')) ?> <span class="fw-semibold"><?= htmlspecialchars((string) ($currentlyUpText ?? '0m')) ?></span></div>
                </div>
                <div class="text-lg-end">
                    <div class="small text-secondary mb-1"><?= htmlspecialchars(t('dashboard.last_check')) ?></div>
                    <div class="fw-semibold"><?= htmlspecialchars($checkedAt) ?></div>
                    <div class="small text-secondary mt-2"><?= htmlspecialchars($lastCheckText ?? t('common.na')) ?> · <?= htmlspecialchars(t('dashboard.checked_every')) ?> <?= htmlspecialchars($intervalText ?? '5m') ?></div>
                    <div class="small text-secondary mt-2"><?= htmlspecialchars(t('common.http')) ?> <?= htmlspecialchars($lastStatus) ?></div>
                    <div class="small text-secondary mt-1"><?= htmlspecialchars(t('dashboard.expected_http')) ?> <?= (int) ($monitor['expected_status'] ?? 200) ?></div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-1">
            <div class="col-12 col-md-4">
                <div class="card app-panel h-100">
                    <div class="card-body p-4">
                        <div class="small text-secondary mb-1"><?= htmlspecialchars(t('dashboard.last_24h')) ?></div>
                        <div class="h4 mb-2"><?= number_format((float) ($periodStats['last_24h'] ?? 0), 2) ?>%</div>
                        <div class="uptime-sparkline" title="<?= htmlspecialchars(t('dashboard.status_timeline_24h')) ?>">
                            <?php foreach (($statusBlocks24h ?? []) as $block): ?>
                                <?php
                                $dotStatus = (string) ($block['status'] ?? 'unknown');
                                $dotLabel = (string) ($block['label'] ?? '');
                                $dotStatusText = $dotStatus === 'up' ? t('status.up', 'Up') : ($dotStatus === 'down' ? t('status.down', 'Down') : t('status.unknown', 'Unknown'));
                                ?>
                                <span class="uptime-dot <?= htmlspecialchars($dotStatus) ?>" title="<?= htmlspecialchars($dotLabel . ' · ' . $dotStatusText) ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card app-panel h-100"><div class="card-body p-4"><div class="small text-secondary mb-1"><?= htmlspecialchars(t('dashboard.last_7d')) ?></div><div class="h4 mb-0"><?= number_format((float) ($periodStats['last_7d'] ?? 0), 2) ?>%</div></div></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card app-panel h-100"><div class="card-body p-4"><div class="small text-secondary mb-1"><?= htmlspecialchars(t('dashboard.last_30d')) ?></div><div class="h4 mb-0"><?= number_format((float) ($periodStats['last_30d'] ?? 0), 2) ?>%</div></div></div>
            </div>
        </div>

        <div class="card app-panel mt-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-semibold"><?= htmlspecialchars(t('dashboard.response_time')) ?></div>
                    <div class="small text-secondary"><?= htmlspecialchars(t('dashboard.response_time_desc')) ?></div>
                </div>
                <div class="response-chart-wrap">
                    <canvas id="responseTimeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card app-panel mt-3">
            <div class="card-body p-4">
                <div class="fw-semibold mb-3"><?= htmlspecialchars(t('dashboard.latest_incidents')) ?></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th><?= htmlspecialchars(t('table.status')) ?></th>
                                <th><?= htmlspecialchars(t('table.root_cause')) ?></th>
                                <th><?= htmlspecialchars(t('table.started')) ?></th>
                                <th><?= htmlspecialchars(t('table.duration')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($incidents)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4"><?= htmlspecialchars(t('incidents.empty')) ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($incidents as $incident): ?>
                                    <tr>
                                        <td><span class="badge <?= !empty($incident['is_active']) ? 'text-bg-danger' : 'text-bg-success' ?>"><?= htmlspecialchars((string) $incident['status']) ?></span></td>
                                        <td><?= htmlspecialchars((string) $incident['root_cause']) ?></td>
                                        <td><?= htmlspecialchars((string) $incident['started']) ?></td>
                                        <td><?= htmlspecialchars((string) $incident['duration']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php
$offcanvasId = 'mobileSidebarMonitorDetail';
$offcanvasTitle = t('app.name');
$activeMenu = 'monitor';
include APP_PATH . '/Views/layouts/mobile_sidebar_offcanvas.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (() => {
        const responseTimeCanvas = document.getElementById('responseTimeChart');
        if (!responseTimeCanvas) {
            return;
        }

        const css = getComputedStyle(document.documentElement);
        const lineColor = css.getPropertyValue('--bs-primary').trim() || '#0d6efd';

        new Chart(responseTimeCanvas, {
            type: 'line',
            data: {
                labels: <?= json_encode($responseLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: '<?= addslashes(t('dashboard.response_time')) ?> (ms)',
                    data: <?= json_encode($responseValues, JSON_UNESCAPED_UNICODE) ?>,
                    borderColor: lineColor,
                    fill: false,
                    tension: 0.3,
                    pointRadius: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => value + ' ms'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    })();
</script>
