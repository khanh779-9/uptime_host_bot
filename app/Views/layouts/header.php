<!DOCTYPE html>
<html lang="<?= htmlspecialchars(current_lang()) ?>" data-bs-theme="<?= htmlspecialchars(current_theme()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uptime Host Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/app.css?v=<?= urlencode((string) APP_ASSET_VERSION) ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/pages.css?v=<?= urlencode((string) APP_ASSET_VERSION) ?>">
</head>
<body class="bg-body-tertiary">
<?php if (empty($hideTopNav)): ?>
    <nav class="navbar navbar-expand-lg bg-body border-bottom mb-4 shadow-sm sticky-top">
        <div class="container-fluid app-shell">
            <a class="navbar-brand fw-bold mb-0 text-primary d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php">
                <i class="bi bi-activity bg-primary-subtle text-primary rounded-circle p-1 d-inline-flex justify-content-center align-items-center app-brand-icon-sm"></i>
                <?= htmlspecialchars(t('app.name')) ?>
            </a>
            <button class="navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto fw-medium">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/index.php?url=monitor/index"><i class="bi bi-display ms-1 me-1 d-inline-block d-lg-none"></i><?= htmlspecialchars(t('nav.monitors')) ?></a></li>
                        <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/index.php?url=settings/index"><i class="bi bi-gear ms-1 me-1 d-inline-block d-lg-none"></i><?= htmlspecialchars(t('nav.settings')) ?></a></li>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                            <a class="btn btn-light rounded-pill px-4 d-inline-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php?url=auth/logout">
                                <i class="bi bi-box-arrow-right"></i>
                                <?= htmlspecialchars(t('nav.logout')) ?> (<?= htmlspecialchars($_SESSION['username']) ?>)
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/index.php?url=auth/login"><?= htmlspecialchars(t('nav.login')) ?></a></li>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a class="btn btn-primary rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php?url=auth/register"><?= htmlspecialchars(t('nav.register')) ?> <i class="bi bi-arrow-right-short fs-5 lh-1"></i></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
<?php endif; ?>
<div class="container-fluid app-shell <?= empty($hideTopNav) ? 'pb-4' : 'py-3' ?>">
