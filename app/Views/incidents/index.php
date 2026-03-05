<div class="incidents-shell row g-3">
    <?php
    $activeMenu = 'incidents';
    include APP_PATH . '/Views/layouts/desktop_sidebar.php';
    ?>

    <main class="col-12 col-lg-9 col-xl-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarIncidents" aria-controls="mobileSidebarIncidents">
                    <i class="bi bi-list me-1"></i> <?= htmlspecialchars(t('common.menu')) ?>
                </button>
                <h2 class="h3 mb-0 app-page-title"><?= htmlspecialchars(t('nav.incidents', 'Incidents')) ?></h2>
            </div>
        </div>

        <div class="card app-panel">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th><?= htmlspecialchars(t('table.status')) ?></th>
                                <th><?= htmlspecialchars(t('table.monitor')) ?></th>
                                <th><?= htmlspecialchars(t('table.type')) ?></th>
                                <th><?= htmlspecialchars(t('table.root_cause')) ?></th>
                                <th><?= htmlspecialchars(t('table.started')) ?></th>
                                <th><?= htmlspecialchars(t('table.duration')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($incidents)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4"><?= htmlspecialchars(t('incidents.empty')) ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($incidents as $incident): ?>
                                    <tr>
                                        <td><span class="badge <?= !empty($incident['is_active']) ? 'text-bg-danger' : 'text-bg-success' ?>"><?= htmlspecialchars((string) $incident['status']) ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars((string) $incident['monitor_name']) ?></div>
                                            <div class="small text-secondary text-break"><?= htmlspecialchars((string) $incident['monitor_url']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars((string) $incident['target_type']) ?></td>
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
$offcanvasId = 'mobileSidebarIncidents';
$offcanvasTitle = t('app.name');
$activeMenu = 'incidents';
include APP_PATH . '/Views/layouts/mobile_sidebar_offcanvas.php';
?>
