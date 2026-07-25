<?php
if (!isset($pdo)) {
    header('Location: /apteka/public/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Аптека') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Fragment+Mono&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/apteka/css/style.css?v=<?= time() ?>" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<canvas id="cursorTrail"></canvas>

<nav class="navbar navbar-expand-lg fixed-top" style="background: #f8fef3;border-bottom:1px solid #e0ebc6;padding:0.3rem 0;">
    <div class="container">
        <a class="navbar-brand nav-link" href="/apteka/public/index.php"
           style="color: #333;font-size:1.45rem;letter-spacing: 2.5px;">
            АПТЕКА
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="filter:invert(30%) sepia(50%) saturate(400%) hue-rotate(70deg);"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center" style="gap:2.25rem;">
                <?php if (isLogged()): ?>
                    <li class="nav-item"><a class="nav-link" href="/apteka/public/personal.php" style="color: #333;font-size:.9rem;padding:.4rem 1rem;">ПРОФИЛЬ</a></li>
                    <li class="nav-item"><a class="nav-link" href="/apteka/public/cart.php" style="color: #333;font-size:.9rem;padding:.4rem 1rem;">КОРЗИНА</a></li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link fw-600" href="/apteka/public/admin/products.php" style="color: #a6d175;font-size:.9rem;padding:.4rem 1rem;">АДМИНКА</a></li>
                    <?php endif; ?>
                    <li class="nav-item ms-2">
                        <a href="/apteka/public/logout.php" style="background:#eef6e0;border:1.5px solid #c5dfa0;color: #3a6b1f;border-radius:20px;padding:.3rem 1.1rem;font-size:.82rem;text-decoration:none;font-weight:500;">ВЫЙТИ</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/apteka/public/login.php" style="color: #333;font-size:.9rem;padding:.4rem 1rem;">ВОЙТИ</a></li>
                    <li class="nav-item ms-2">
                        <a href="/apteka/public/register.php" style="background:#a6d175;color: #fff;border-radius:20px;padding:.3rem 1.1rem;font-size:.82rem;text-decoration:none;font-weight:500;">РЕГИСТРАЦИЯ</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div style="height:58px;"></div>
