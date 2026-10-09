<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth_helpers.php';

$pageTitle = $pageTitle ?? APP_NAME;
$flash = getFlash();
$firstName = currentUserName() ? explode(' ', currentUserName())[0] : null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php"><?= APP_NAME ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/reservation.php">Reservation</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (!isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm me-2" href="<?= BASE_URL ?>/auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm" href="<?= BASE_URL ?>/auth/register.php">Register</a>
                    </li>
                <?php elseif (isAdmin()): ?>
                    <li class="nav-item">
                        <span class="navbar-text me-3">Admin: <?= e(currentUserName()) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm me-2" href="<?= BASE_URL ?>/admin/dashboard.php">Admin Panel</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <span class="navbar-text me-3">Hi, <?= e($firstName) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm me-2" href="<?= BASE_URL ?>/user/dashboard.php">My Reservations</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if ($flash !== null): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
            <?= e($flash['message']) ?>
        </div>
    </div>
<?php endif; ?>

<main class="container py-4">
