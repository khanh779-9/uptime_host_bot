<!DOCTYPE html>
<html lang="<?= htmlspecialchars(current_lang()) ?>" data-bs-theme="<?= htmlspecialchars(current_theme()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uptime Host Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .app-shell {
            width: 100%;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        @media (min-width: 576px) {
            .app-shell {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        @media (min-width: 992px) {
            .app-shell {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
    </style>
</head>
<body class="bg-body-tertiary">
<?php if (empty($hideTopNav)): ?>
    <nav class="navbar navbar-expand-lg bg-body border-bottom mb-4">
        <div class="container-fluid app-shell">
            <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/index.php"><?= htmlspecialchars(t('app.name')) ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php?url=monitor/index"><?= htmlspecialchars(t('nav.monitors')) ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php?url=settings/index"><?= htmlspecialchars(t('nav.settings')) ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php?url=auth/logout"><?= htmlspecialchars(t('nav.logout')) ?> (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php?url=auth/login"><?= htmlspecialchars(t('nav.login')) ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php?url=auth/register"><?= htmlspecialchars(t('nav.register')) ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
<?php endif; ?>
<div class="container-fluid app-shell <?= empty($hideTopNav) ? 'pb-4' : 'py-3' ?>">
