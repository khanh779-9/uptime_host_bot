<?php
$uptimePercent = (float) ($stats['uptime_percent'] ?? 0);
$feedback = get_flash('monitor_feedback');
$cronRunUrl = route_url('cron_run', ['token' => (string) CRON_SECRET]);
$pagination = is_array($pagination ?? null) ? $pagination : [];
$currentPage = max(1, (int) ($pagination['current_page'] ?? 1));
$totalPages = max(1, (int) ($pagination['total_pages'] ?? 1));
$itemsPerPage = max(10, (int) ($pagination['items_per_page'] ?? 10));
$itemsPerPageOptions = is_array($pagination['items_per_page_options'] ?? null) ? $pagination['items_per_page_options'] : [10];
$baseIndexUrl = route_url('monitor', ['per_page' => $itemsPerPage]);

$formatIntervalLabel = static function (int $seconds): string {
    $seconds = max(0, $seconds);
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $remainingSeconds = $seconds % 60;

    if ($hours > 0) {
        return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '');
    }

    if ($minutes > 0) {
        return $minutes . 'm' . ($remainingSeconds > 0 ? ' ' . $remainingSeconds . 's' : '');
    }

    return $remainingSeconds . 's';
};

$feedbackItems = [];
if (is_array($feedback) && isset($feedback[0]) && is_array($feedback[0])) {
    $feedbackItems = $feedback;
} elseif (is_array($feedback)) {
    $feedbackItems[] = $feedback;
}
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
                <form method="get" class="d-flex align-items-center gap-2 monitor-per-page-form">
                    <input type="hidden" name="action" value="monitor">
                    <input type="hidden" name="page" value="1">
                    <label for="perPageSelect" class="small text-secondary mb-0">Items / page</label>
                    <select id="perPageSelect" name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($itemsPerPageOptions as $option): ?>
                            <?php $optionValue = max(10, (int) $option); ?>
                            <option value="<?= $optionValue ?>" <?= $optionValue === $itemsPerPage ? 'selected' : '' ?>><?= $optionValue ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-2 d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileStatsMonitor" aria-controls="mobileStatsMonitor">
                    <i class="bi bi-bar-chart"></i> <?= htmlspecialchars(t('common.stats')) ?>
                </button>
                <button class="btn btn-primary btn-sm rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#addMonitorModal">
                    <i class="bi bi-plus-lg"></i> <?= htmlspecialchars(t('monitor.add_button')) ?>
                </button>
                <button id="runCheckBtn" class="btn btn-outline-primary btn-sm rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#runCheckModal" data-url="<?= htmlspecialchars($cronRunUrl) ?>">
                    <i class="bi bi-play-fill"></i> <?= htmlspecialchars(t('monitor.run_check')) ?>
                </button>
            </div>
        </div>

        <?php if (!empty($feedbackItems)): ?>
            <?php
            $popupItems = $feedbackItems;
            include APP_PATH . '/Views/layouts/floating_popups.php';
            ?>
        <?php endif; ?>

        <div class="d-grid gap-2 monitor-items-section">
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
                    $intervalSeconds = (int) ($monitor['check_interval_seconds'] ?? 0);
                    $intervalLabel = $formatIntervalLabel($intervalSeconds);
                    $checkUrl = route_url('monitor_check', ['monitor_id' => (int) $monitor['id']]);
                    ?>
                    <div class="monitor-item mb-0 overflow-hidden"
                        data-monitor-id="<?= (int) $monitor['id'] ?>"
                        data-interval-seconds="<?= $intervalSeconds ?>"
                        data-is-active="<?= $isActive ? '1' : '0' ?>"
                        data-check-url="<?= htmlspecialchars($checkUrl) ?>">
                        <div class="monitor-state-bar bg-<?= $isUp ? 'success' : ($isDown ? 'danger' : 'warning') ?> position-absolute top-0 start-0 h-100" style="width: 4px;"></div>
                        <div class="ps-2">
                            <div class="item-head">
                                <span class="status-dot monitor-status-dot <?= $statusClass ?>"></span>
                                <div class="fw-bold text-dark fs-5 flex-grow-1 monitor-item-title"><?= htmlspecialchars($monitor['name']) ?></div>
                                <span class="status-pill monitor-status-pill <?= $isUp ? 'bg-success-subtle text-success' : ($isDown ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') ?>"><?= htmlspecialchars($statusText) ?></span>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="meta-chip"><i class="bi bi-hdd-network"></i><?= htmlspecialchars(t('monitor.type.' . $monitor['target_type'], $monitor['target_type'])) ?></span>
                                <span class="meta-chip"><i class="bi bi-arrow-return-right"></i><span class="monitor-last-status"><?= htmlspecialchars((string) ($monitor['last_status'] ?? t('common.na'))) ?></span></span>
                                <span class="meta-chip"><i class="bi bi-bullseye"></i><?= htmlspecialchars(t('common.expected')) ?> <?= (int) ($monitor['expected_status'] ?? 200) ?></span>
                                <span class="meta-chip"><i class="bi bi-clock-history"></i><?= htmlspecialchars($intervalLabel) ?></span>
                                <span class="meta-chip"><i class="bi bi-calendar2-check"></i><span class="monitor-last-checked"><?= htmlspecialchars((string) ($monitor['last_checked_at'] ?? t('common.na'))) ?></span></span>
                            </div>

                            <div class="small fw-medium font-monospace text-primary position-relative mb-2 monitor-url">
                                <?php if (str_starts_with((string) $monitor['url'], 'http://') || str_starts_with((string) $monitor['url'], 'https://')): ?>
                                    <a href="<?= htmlspecialchars($monitor['url']) ?>" target="_blank" class="text-decoration-none"><i class="bi bi-box-arrow-up-right me-1"></i><?= htmlspecialchars($monitor['url']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($monitor['url']) ?>
                                <?php endif; ?>
                            </div>

                            <div class="progress rounded-pill bg-light" style="height:8px; border: 1px solid rgba(0,0,0,0.05);">
                                <div class="progress-bar rounded-pill monitor-health-bar <?= $isUp ? 'bg-success' : ($isDown ? 'bg-danger' : 'bg-warning') ?>" style="width: <?= $healthPercent ?>%"></div>
                            </div>

                            <div class="item-actions d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2 small text-secondary">
                                    <span><i class="bi bi-activity me-1"></i><?= htmlspecialchars(t('common.health_score')) ?>: <span class="monitor-health-score"><?= (int) $healthPercent ?></span>%</span>
                                    <span class="badge rounded-pill monitor-live-badge <?= $isActive ? 'text-bg-secondary' : 'text-bg-dark' ?>" data-live-state="<?= $isActive ? 'idle' : 'paused' ?>">
                                        <i class="bi <?= $isActive ? 'bi-broadcast-pin' : 'bi-pause-circle' ?> me-1"></i><?= htmlspecialchars($isActive ? t('common.live', 'Live') : t('status.paused', 'Paused')) ?>
                                    </span>
                                </div>
                                <div class="monitor-card-actions dropdown monitor-card-actions-menu mt-0">
                                    <button class="btn btn-sm rounded-pill px-3 monitor-action-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                        <i class="bi bi-three-dots me-1"></i><?= htmlspecialchars(t('common.more', 'More')) ?>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm monitor-action-menu">
                                        <li>
                                            <a class="dropdown-item" href="<?= route_url('monitor_detail', ['monitor_id' => (int) $monitor['id']]) ?>">
                                                <i class="bi bi-eye me-2"></i><?= htmlspecialchars(t('common.view', 'View details')) ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= route_url('monitor_toggle', ['monitor_id' => (int) $monitor['id'], 'next' => $isActive ? 0 : 1]) ?>">
                                                <i class="bi <?= $isActive ? 'bi-pause-circle' : 'bi-play-circle' ?> me-2"></i><?= $isActive ? htmlspecialchars(t('status.paused', 'Pause')) : htmlspecialchars(t('status.resumed', 'Resume')) ?>
                                            </a>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#<?= htmlspecialchars($editModalId) ?>">
                                                <i class="bi bi-pencil-square me-2"></i><?= htmlspecialchars(t('common.edit', 'Edit')) ?>
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item monitor-action-danger" href="<?= route_url('monitor_delete', ['monitor_id' => (int) $monitor['id']]) ?>" onclick="return confirm('<?= htmlspecialchars(t('monitor.delete_confirm')) ?>')">
                                                <i class="bi bi-trash3 me-2"></i><?= htmlspecialchars(t('monitor.delete')) ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="<?= htmlspecialchars($editModalId) ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title"><?= htmlspecialchars(t('common.edit', 'Edit')) ?>: <?= htmlspecialchars($monitor['name']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(t('common.close', 'Close')) ?>"></button>
                                </div>
                                <div class="modal-body pt-3">
                                    <form method="post" action="<?= route_url('monitor_update') ?>" class="row g-3 align-items-end">
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
                                                <option value="10" <?= (int) $monitor['check_interval_seconds'] === 10 ? 'selected' : '' ?>><?= htmlspecialchars(t('interval.10')) ?></option>
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

        <?php if ($totalPages > 1): ?>
            <div class="card app-panel mt-3">
                <div class="card-body py-2 px-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div class="small text-secondary">
                        <?= htmlspecialchars(t('monitor.pagination_showing', 'Showing')) ?>
                        <span class="fw-semibold text-body"><?= (int) ($pagination['start_item'] ?? 0) ?></span>
                        -
                        <span class="fw-semibold text-body"><?= (int) ($pagination['end_item'] ?? 0) ?></span>
                        /
                        <span class="fw-semibold text-body"><?= (int) ($pagination['total_items'] ?? 0) ?></span>
                    </div>

                    <nav aria-label="<?= htmlspecialchars(t('monitor.list_title')) ?>">
                        <ul class="pagination pagination-sm mb-0 monitor-pagination">
                            <?php $isPrevDisabled = $currentPage <= 1; ?>
                            <li class="page-item <?= $isPrevDisabled ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $isPrevDisabled ? '#' : htmlspecialchars($baseIndexUrl . '&page=' . ($currentPage - 1)) ?>" aria-label="<?= htmlspecialchars(t('common.previous', 'Previous')) ?>"><?= htmlspecialchars(t('common.previous', 'Previous')) ?></a>
                            </li>

                            <?php
                            $windowRadius = 1;
                            $windowStart = max(1, $currentPage - $windowRadius);
                            $windowEnd = min($totalPages, $currentPage + $windowRadius);

                            if ($windowStart <= 2) {
                                $windowStart = 1;
                                $windowEnd = min($totalPages, 3);
                            }

                            if ($windowEnd >= $totalPages - 1) {
                                $windowEnd = $totalPages;
                                $windowStart = max(1, $totalPages - 2);
                            }

                            if ($windowStart > 1): ?>
                                <li class="page-item <?= $currentPage === 1 ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= htmlspecialchars($baseIndexUrl . '&page=1') ?>">1</a>
                                </li>
                                <?php if ($windowStart > 2): ?>
                                    <li class="page-item disabled" aria-hidden="true"><span class="page-link">…</span></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($page = $windowStart; $page <= $windowEnd; $page++): ?>
                                <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= htmlspecialchars($baseIndexUrl . '&page=' . $page) ?>"><?= $page ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($windowEnd < $totalPages): ?>
                                <?php if ($windowEnd < $totalPages - 1): ?>
                                    <li class="page-item disabled" aria-hidden="true"><span class="page-link">…</span></li>
                                <?php endif; ?>
                                <li class="page-item <?= $currentPage === $totalPages ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= htmlspecialchars($baseIndexUrl . '&page=' . $totalPages) ?>"><?= $totalPages ?></a>
                                </li>
                            <?php endif; ?>

                            <?php $isNextDisabled = $currentPage >= $totalPages; ?>
                            <li class="page-item <?= $isNextDisabled ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $isNextDisabled ? '#' : htmlspecialchars($baseIndexUrl . '&page=' . ($currentPage + 1)) ?>" aria-label="<?= htmlspecialchars(t('common.next', 'Next')) ?>"><?= htmlspecialchars(t('common.next', 'Next')) ?></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
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
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?= htmlspecialchars(t('common.close', 'Close')) ?>"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(t('common.close', 'Close')) ?>"></button>
            </div>
            <div class="modal-body pt-3">
                <form method="post" action="<?= route_url('monitor_create') ?>" class="row g-3 align-items-end">
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
                            <option value="10"><?= htmlspecialchars(t('interval.10')) ?></option>
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

<div class="modal fade" id="runCheckModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bi bi-play-circle text-primary"></i>
                    <?= htmlspecialchars(t('monitor.run_check', 'Run check')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(t('common.close', 'Close')) ?>"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="runCheckLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                    <div class="small text-secondary mt-2"><?= htmlspecialchars(t('monitor.checking_now', 'Checking monitors...')) ?></div>
                </div>

                <div id="runCheckError" class="run-check-error d-none mb-0" role="alert"></div>

                <div id="runCheckResult" class="d-none">
                    <div id="runCheckSummary" class="small fw-semibold text-primary mb-2"></div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle run-check-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= htmlspecialchars(t('table.type', 'Type')) ?></th>
                                    <th><?= htmlspecialchars(t('table.url', 'URL')) ?></th>
                                    <th><?= htmlspecialchars(t('table.status', 'Status')) ?></th>
                                    <th><?= htmlspecialchars(t('dashboard.response_time', 'Response time')) ?></th>
                                </tr>
                            </thead>
                            <tbody id="runCheckResultsBody"></tbody>
                        </table>
                    </div>

                    <div class="small fw-semibold mb-1"><?= htmlspecialchars(t('monitor.raw_response', 'Raw response')) ?></div>
                    <pre id="runCheckRaw" class="run-check-raw mb-0"></pre>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button id="runCheckRetryBtn" type="button" class="btn btn-outline-primary d-none"><?= htmlspecialchars(t('common.retry', 'Retry')) ?></button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= htmlspecialchars(t('common.close', 'Close')) ?></button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (() => {
        const runCheckModal = document.getElementById('runCheckModal');
        const runCheckButton = document.getElementById('runCheckBtn');
        const loadingPanel = document.getElementById('runCheckLoading');
        const errorPanel = document.getElementById('runCheckError');
        const resultPanel = document.getElementById('runCheckResult');
        const summaryEl = document.getElementById('runCheckSummary');
        const resultBody = document.getElementById('runCheckResultsBody');
        const rawEl = document.getElementById('runCheckRaw');
        const retryButton = document.getElementById('runCheckRetryBtn');

        if (!runCheckModal || !runCheckButton || !loadingPanel || !errorPanel || !resultPanel || !summaryEl || !resultBody || !rawEl || !retryButton) {
            return;
        }

        const setLoadingState = () => {
            loadingPanel.classList.remove('d-none');
            errorPanel.classList.add('d-none');
            resultPanel.classList.add('d-none');
            retryButton.classList.add('d-none');
            errorPanel.textContent = '';
            summaryEl.textContent = '';
            resultBody.innerHTML = '';
            rawEl.textContent = '';
        };

        const setErrorState = (message) => {
            loadingPanel.classList.add('d-none');
            resultPanel.classList.add('d-none');
            errorPanel.classList.remove('d-none');
            errorPanel.textContent = message;
            retryButton.classList.remove('d-none');
        };

        const renderResult = (payload) => {
            loadingPanel.classList.add('d-none');
            errorPanel.classList.add('d-none');
            resultPanel.classList.remove('d-none');
            retryButton.classList.add('d-none');

            const checked = Number.parseInt(payload.checked ?? 0, 10) || 0;
            const results = Array.isArray(payload.results) ? payload.results : [];

            summaryEl.textContent = `<?= addslashes(t('dashboard.checked', 'Checked')) ?>: ${checked} | <?= addslashes(t('dashboard.total_monitors_label', 'Total monitors')) ?>: ${results.length}`;
            resultBody.innerHTML = '';

            if (results.length === 0) {
                const row = document.createElement('tr');
                const col = document.createElement('td');
                col.colSpan = 5;
                col.className = 'text-center text-secondary py-3';
                col.textContent = '<?= addslashes(t('monitor.empty_result', 'No monitor check result returned.')) ?>';
                row.appendChild(col);
                resultBody.appendChild(row);
            } else {
                results.forEach((item) => {
                    const row = document.createElement('tr');

                    const idCell = document.createElement('td');
                    idCell.textContent = String(item.id ?? '');

                    const typeCell = document.createElement('td');
                    typeCell.textContent = String(item.target_type ?? '');

                    const urlCell = document.createElement('td');
                    urlCell.className = 'run-check-url';
                    urlCell.textContent = String(item.url ?? '');

                    const statusCell = document.createElement('td');
                    const status = Number.parseInt(item.status ?? 0, 10) || 0;
                    const statusBadge = document.createElement('span');
                    statusBadge.className = `badge ${status >= 200 && status < 400 ? 'text-bg-success' : 'text-bg-danger'}`;
                    statusBadge.textContent = String(status);
                    statusCell.appendChild(statusBadge);

                    const responseCell = document.createElement('td');
                    responseCell.textContent = `${Number.parseInt(item.response_time_ms ?? 0, 10) || 0}ms`;

                    row.appendChild(idCell);
                    row.appendChild(typeCell);
                    row.appendChild(urlCell);
                    row.appendChild(statusCell);
                    row.appendChild(responseCell);
                    resultBody.appendChild(row);
                });
            }

            rawEl.textContent = JSON.stringify(payload, null, 2);
        };

        const runCheckRequest = async () => {
            setLoadingState();

            const endpoint = runCheckButton.dataset.url || '';
            if (endpoint === '') {
                setErrorState('<?= addslashes(t('monitor.run_check_failed', 'Unable to run check right now.')) ?>');
                return;
            }

            try {
                const response = await fetch(endpoint, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let payload = null;
                try {
                    payload = await response.json();
                } catch (parseError) {
                    payload = null;
                }

                if (!response.ok || payload === null || typeof payload !== 'object') {
                    throw new Error('<?= addslashes(t('monitor.run_check_failed', 'Unable to run check right now.')) ?>');
                }

                renderResult(payload);
            } catch (error) {
                setErrorState(error instanceof Error ? error.message : '<?= addslashes(t('monitor.run_check_failed', 'Unable to run check right now.')) ?>');
            }
        };

        runCheckModal.addEventListener('show.bs.modal', runCheckRequest);
        retryButton.addEventListener('click', runCheckRequest);
    })();

    (() => {
        const cards = Array.from(document.querySelectorAll('.monitor-item[data-monitor-id][data-check-url]'));
        if (cards.length === 0) {
            return;
        }

        const MAX_CONCURRENT_CHECKS = Math.min(8, Math.max(3, Math.ceil(cards.length / 6)));
        const MIN_DELAY_MS = 1000;
        const LOOP_INTERVAL_MS = 1000;
        const MAX_RETRY_DELAY_MS = 10000;
        const queue = [];
        const queuedSet = new Set();
        const elapsedSecondsMap = new WeakMap();
        const syncingSet = new Set();
        let activeRequests = 0;

        const getIntervalMs = (card) => {
            const intervalSeconds = Number.parseInt(card.dataset.intervalSeconds || '0', 10);
            return Math.max(MIN_DELAY_MS, (Number.isFinite(intervalSeconds) ? intervalSeconds : 0) * 1000);
        };

        const getIntervalSeconds = (card) => {
            const intervalSeconds = Number.parseInt(card.dataset.intervalSeconds || '0', 10);
            return Math.max(1, Number.isFinite(intervalSeconds) ? intervalSeconds : 1);
        };

        const setLiveBadge = (card, state, nextDueSeconds = null) => {
            const badge = card.querySelector('.monitor-live-badge');
            if (!badge) {
                return;
            }

            const formatWaitLabel = (seconds) => {
                const totalSeconds = Math.max(0, Number.isFinite(seconds) ? Math.floor(seconds) : 0);
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const remainingSeconds = totalSeconds % 60;

                if (hours > 0) {
                    return `${hours}h${minutes > 0 ? ` ${minutes}m` : ''}`;
                }

                if (minutes > 0) {
                    return `${minutes}m${remainingSeconds > 0 ? ` ${remainingSeconds}s` : ''}`;
                }

                return `${remainingSeconds}s`;
            };

            badge.className = 'badge rounded-pill monitor-live-badge';
            let iconClass = 'bi-broadcast-pin';
            let label = '<?= addslashes(t('common.live', 'Live')) ?>';

            if (state === 'syncing') {
                badge.classList.add('text-bg-primary');
                iconClass = 'bi-arrow-repeat monitor-live-spin';
                label = '<?= addslashes(t('monitor.syncing', 'Syncing')) ?>';
            } else if (state === 'paused') {
                badge.classList.add('text-bg-dark');
                iconClass = 'bi-pause-circle';
                label = '<?= addslashes(t('status.paused', 'Paused')) ?>';
            } else if (state === 'retry') {
                badge.classList.add('text-bg-danger');
                iconClass = 'bi-exclamation-triangle';
                label = '<?= addslashes(t('common.retry', 'Retry')) ?>';
            } else {
                badge.classList.add('text-bg-secondary');
                iconClass = 'bi-broadcast-pin';
                label = '<?= addslashes(t('common.live', 'Live')) ?>';
                if (Number.isFinite(nextDueSeconds) && nextDueSeconds !== null) {
                    label = `${label} ${formatWaitLabel(nextDueSeconds)}`;
                }
            }

            badge.dataset.liveState = state;
            badge.innerHTML = `<i class="bi ${iconClass} me-1"></i>${label}`;
        };

        const syncElapsedByNextDue = (card, nextDueSeconds) => {
            const intervalSeconds = getIntervalSeconds(card);
            const dueSeconds = Number.isFinite(nextDueSeconds)
                ? Math.max(0, Math.floor(nextDueSeconds))
                : intervalSeconds;

            const syncedElapsed = Math.max(0, intervalSeconds - dueSeconds);
            elapsedSecondsMap.set(card, Math.min(intervalSeconds, syncedElapsed));

            if (card.dataset.isActive === '1') {
                setLiveBadge(card, 'idle', dueSeconds);
            }
        };

        const scheduleRetry = (card) => {
            const retryDelaySeconds = Math.max(
                1,
                Math.ceil(Math.min(MAX_RETRY_DELAY_MS, getIntervalMs(card)) / 1000)
            );
            const intervalSeconds = getIntervalSeconds(card);
            elapsedSecondsMap.set(card, Math.max(0, intervalSeconds - retryDelaySeconds));
            setLiveBadge(card, 'retry', retryDelaySeconds);
        };

        const enqueueCard = (card) => {
            if (!card || card.dataset.isActive !== '1') {
                if (card) {
                    setLiveBadge(card, 'paused');
                }
                return;
            }

            if (syncingSet.has(card)) {
                return;
            }

            if (!queuedSet.has(card)) {
                queuedSet.add(card);
                queue.push(card);
            }

            processQueue();
        };

        const statusClasses = {
            up: {
                dot: 'up',
                bar: 'bg-success',
                pill: 'bg-success-subtle text-success',
            },
            down: {
                dot: 'down',
                bar: 'bg-danger',
                pill: 'bg-danger-subtle text-danger',
            },
            unknown: {
                dot: 'unknown',
                bar: 'bg-warning',
                pill: 'bg-warning-subtle text-warning',
            },
        };

        const applyPayloadToCard = (card, payload) => {
            if (!payload || typeof payload !== 'object') {
                return;
            }

            const state = typeof payload.state === 'string' && statusClasses[payload.state] ? payload.state : 'unknown';
            const stateConfig = statusClasses[state];
            const healthPercent = Number.parseInt(payload.health_percent ?? 0, 10);

            const stateBar = card.querySelector('.monitor-state-bar');
            if (stateBar) {
                stateBar.classList.remove('bg-success', 'bg-danger', 'bg-warning');
                stateBar.classList.add(stateConfig.bar);
            }

            const statusDot = card.querySelector('.monitor-status-dot');
            if (statusDot) {
                statusDot.classList.remove('up', 'down', 'unknown');
                statusDot.classList.add(stateConfig.dot);
            }

            const statusPill = card.querySelector('.monitor-status-pill');
            if (statusPill) {
                statusPill.className = `status-pill monitor-status-pill ${stateConfig.pill}`;
                statusPill.textContent = String(payload.status_text ?? '');
            }

            const statusCodeEl = card.querySelector('.monitor-last-status');
            if (statusCodeEl) {
                const statusCode = payload.status_code;
                statusCodeEl.textContent = statusCode === null || statusCode === undefined ? '<?= addslashes(t('common.na')) ?>' : String(statusCode);
            }

            const checkedEl = card.querySelector('.monitor-last-checked');
            if (checkedEl) {
                checkedEl.textContent = String(payload.last_checked_at ?? '<?= addslashes(t('common.na')) ?>');
            }

            const healthScoreEl = card.querySelector('.monitor-health-score');
            if (healthScoreEl) {
                healthScoreEl.textContent = String(Number.isFinite(healthPercent) ? healthPercent : 0);
            }

            const healthBar = card.querySelector('.monitor-health-bar');
            if (healthBar) {
                healthBar.classList.remove('bg-success', 'bg-danger', 'bg-warning');
                healthBar.classList.add(stateConfig.bar);
                healthBar.style.width = `${Math.max(0, Math.min(100, Number.isFinite(healthPercent) ? healthPercent : 0))}%`;
            }

            if (typeof payload.interval_seconds === 'number' && payload.interval_seconds > 0) {
                card.dataset.intervalSeconds = String(payload.interval_seconds);
            }

            if (typeof payload.is_active === 'boolean') {
                card.dataset.isActive = payload.is_active ? '1' : '0';
            }
        };

        const checkCard = async (card) => {
            const endpoint = card.dataset.checkUrl || '';
            if (endpoint === '') {
                return;
            }

            syncingSet.add(card);
            setLiveBadge(card, 'syncing');

            try {
                const response = await fetch(endpoint, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json();
                if (!response.ok || !payload || payload.ok !== true || !payload.monitor) {
                    throw new Error('request_failed');
                }

                applyPayloadToCard(card, payload.monitor);

                const isSkipped = payload.skipped === true;
                if (isSkipped) {
                    const nextDueSeconds = Number.parseInt(payload.monitor.next_due_seconds ?? '', 10);
                    syncElapsedByNextDue(card, nextDueSeconds);
                } else {
                    const intervalSeconds = getIntervalSeconds(card);
                    elapsedSecondsMap.set(card, 0);
                    setLiveBadge(card, 'idle', intervalSeconds);
                }
            } catch (error) {
                scheduleRetry(card);
            } finally {
                syncingSet.delete(card);
            }
        };

        const processQueue = () => {
            if (queue.length > 1) {
                queue.sort((leftCard, rightCard) => {
                    const leftInterval = getIntervalSeconds(leftCard);
                    const rightInterval = getIntervalSeconds(rightCard);
                    const leftElapsed = Number(elapsedSecondsMap.get(leftCard) ?? 0);
                    const rightElapsed = Number(elapsedSecondsMap.get(rightCard) ?? 0);
                    const leftOverdue = leftElapsed - leftInterval;
                    const rightOverdue = rightElapsed - rightInterval;

                    return rightOverdue - leftOverdue;
                });
            }

            while (activeRequests < MAX_CONCURRENT_CHECKS && queue.length > 0) {
                const card = queue.shift();
                if (!card) {
                    continue;
                }
                queuedSet.delete(card);

                activeRequests++;
                checkCard(card)
                    .finally(() => {
                        activeRequests = Math.max(0, activeRequests - 1);
                        processQueue();
                    });
            }
        };

        const tick = () => {
            cards.forEach((card) => {
                if (card.dataset.isActive !== '1') {
                    setLiveBadge(card, 'paused');
                    return;
                }

                if (syncingSet.has(card)) {
                    return;
                }

                const intervalSeconds = getIntervalSeconds(card);
                const previousElapsed = Number(elapsedSecondsMap.get(card) ?? 0);
                const nextElapsed = Math.min(intervalSeconds, Math.max(0, previousElapsed + 1));
                elapsedSecondsMap.set(card, nextElapsed);

                if (nextElapsed >= intervalSeconds) {
                    enqueueCard(card);
                    return;
                }

                if (!queuedSet.has(card)) {
                    setLiveBadge(card, 'idle', Math.max(0, intervalSeconds - nextElapsed));
                }
            });

            processQueue();
        };

        let schedulerStopped = false;
        let schedulerTimerId = null;

        const runMainLoop = () => {
            if (schedulerStopped) {
                return;
            }

            tick();
            schedulerTimerId = window.setTimeout(runMainLoop, LOOP_INTERVAL_MS);
        };

        cards.forEach((card) => {
            if (card.dataset.isActive !== '1') {
                setLiveBadge(card, 'paused');
                return;
            }

            elapsedSecondsMap.set(card, 0);
            setLiveBadge(card, 'idle', getIntervalSeconds(card));
        });

        const teardownDropdownPortals = [];

        document.querySelectorAll('.monitor-card-actions-menu').forEach((wrapper) => {
            const toggle = wrapper.querySelector('[data-bs-toggle="dropdown"]');
            const menu = wrapper.querySelector('.dropdown-menu');
            if (!toggle || !menu) {
                return;
            }

            let isPortaled = false;
            let placeholder = null;

            const placeMenu = () => {
                if (!isPortaled) {
                    return;
                }

                const toggleRect = toggle.getBoundingClientRect();
                const menuWidth = menu.offsetWidth || 200;
                const menuHeight = menu.offsetHeight || 0;

                let left = toggleRect.right - menuWidth;
                left = Math.max(8, Math.min(left, window.innerWidth - menuWidth - 8));

                let top = toggleRect.bottom + 6;
                if (top + menuHeight > window.innerHeight - 8) {
                    top = Math.max(8, toggleRect.top - menuHeight - 6);
                }

                menu.style.left = `${left}px`;
                menu.style.top = `${top}px`;
            };

            const onShown = () => {
                if (isPortaled) {
                    return;
                }

                placeholder = document.createComment('monitor-dropdown-placeholder');
                menu.parentNode?.insertBefore(placeholder, menu);
                document.body.appendChild(menu);
                menu.classList.add('monitor-dropdown-portal');
                menu.style.position = 'fixed';
                menu.style.inset = 'auto';
                isPortaled = true;

                placeMenu();
                window.addEventListener('resize', placeMenu);
                window.addEventListener('scroll', placeMenu, true);
            };

            const onHidden = () => {
                if (!isPortaled) {
                    return;
                }

                window.removeEventListener('resize', placeMenu);
                window.removeEventListener('scroll', placeMenu, true);

                menu.classList.remove('monitor-dropdown-portal');
                menu.style.position = '';
                menu.style.inset = '';
                menu.style.left = '';
                menu.style.top = '';

                if (placeholder && placeholder.parentNode) {
                    placeholder.parentNode.insertBefore(menu, placeholder);
                    placeholder.remove();
                }

                placeholder = null;
                isPortaled = false;
            };

            wrapper.addEventListener('shown.bs.dropdown', onShown);
            wrapper.addEventListener('hidden.bs.dropdown', onHidden);

            teardownDropdownPortals.push(() => {
                wrapper.removeEventListener('shown.bs.dropdown', onShown);
                wrapper.removeEventListener('hidden.bs.dropdown', onHidden);
                onHidden();
            });
        });

        runMainLoop();

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                return;
            }

            tick();
        });

        window.addEventListener('beforeunload', () => {
            schedulerStopped = true;
            if (schedulerTimerId !== null) {
                window.clearTimeout(schedulerTimerId);
            }

            teardownDropdownPortals.forEach((teardown) => teardown());
        });
    })();

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