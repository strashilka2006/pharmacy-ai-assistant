<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . "/../../../app/bootstrap.php";
requireAdmin();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . " — Админка" : "Админка" ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css?v=<?= time() ?>">
</head>
<body>

<!-- ─── Основной navbar (копия из header.php) ─── -->
<nav class="navbar navbar-expand-lg fixed-top" style="background:#f8fef3;border-bottom:1px solid #e0ebc6;padding:0.3rem 0;z-index:1031;">
    <div class="container">
        <a class="navbar-brand nav-link" href="<?= BASE_URL ?>/public/index.php"
           style="color:#333;font-size:1.45rem;letter-spacing:2.5px;">
            АПТЕКА
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-center" style="gap:2.25rem;">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/personal.php" style="color:#333;font-size:.9rem;padding:.4rem 1rem;">ПРОФИЛЬ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/cart.php" style="color:#333;font-size:.9rem;padding:.4rem 1rem;">КОРЗИНА</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-600" href="<?= BASE_URL ?>/public/admin/products.php" style="color:#a6d175;font-size:.9rem;padding:.4rem 1rem;">АДМИНКА</a>
                </li>
                <li class="nav-item ms-2">
                    <a href="<?= BASE_URL ?>/public/logout.php" style="background:#eef6e0;border:1.5px solid #c5dfa0;color:#3a6b1f;border-radius:20px;padding:.3rem 1.1rem;font-size:.82rem;text-decoration:none;font-weight:500;">ВЫЙТИ</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ─── Админ-navbar: висит НИЖЕ основного (top: 58px) ─── -->
<nav class="navbar navbar-expand-lg fixed-top shadow-sm"
     style="top:58px;background:#eef6e0;border-bottom:1px solid #c5dfa0;padding:0.25rem 0;z-index:1030;">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold" style="color:#3a6b1f;font-size:.95rem;letter-spacing:1px;">
            ⚙ ПАНЕЛЬ УПРАВЛЕНИЯ
        </span>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon" style="filter:invert(30%) sepia(50%) saturate(400%) hue-rotate(70deg);"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="gap:.5rem;">
                <li class="nav-item">
                    <a class="nav-link<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? ' fw-semibold' : '' ?>"
                       href="index.php"
                       style="color:#2d5a16;font-size:.85rem;">ГЛАВНАЯ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= basename($_SERVER['PHP_SELF']) === 'products.php' ? ' fw-semibold' : '' ?>"
                       href="products.php"
                       style="color:#2d5a16;font-size:.85rem;">ТОВАРЫ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= basename($_SERVER['PHP_SELF']) === 'brands.php' ? ' fw-semibold' : '' ?>"
                       href="brands.php"
                       style="color:#2d5a16;font-size:.85rem;">БРЕНДЫ</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Отступ под оба navbar (58px основной + ~46px админский) -->
<div style="height:104px;"></div>

<main class="flex-shrink-0">
    <div class="container py-4">
