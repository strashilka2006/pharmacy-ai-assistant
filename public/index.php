<?php
require "../app/bootstrap.php";

$pageTitle = "Аптека — здоровье в надёжных руках";
$search   = trim($_GET['q'] ?? '');
$sort     = $_GET['sort'] ?? 'new';

$sql = "SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE 1=1";
$params = [];

// Баннеры брендов
$bannerSlides = [];
$bannerBrands = $pdo->query("
    SELECT id, name, banner, description
    FROM brands
    WHERE banner IS NOT NULL AND banner != ''
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($bannerBrands as $b) {
    $stmt = $pdo->prepare("
        SELECT id, name, price, image
        FROM products
        WHERE brand_id = ?
        ORDER BY id DESC
        LIMIT 4
    ");
    $stmt->execute([$b['id']]);
    $prods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($prods)) {
        $bannerSlides[] = ['brand' => $b, 'products' => $prods];
    }
}

if ($search !== '') { $sql .= " AND p.name LIKE ?"; $params[] = "%$search%"; }
$brand = $_GET['brand'] ?? '';
if ($brand !== '') { $sql .= " AND p.brand_id = ?"; $params[] = (int)$brand; }
$priceMin = $_GET['price_min'] ?? '';
$priceMax = $_GET['price_max'] ?? '';
if ($priceMin !== '') { $sql .= " AND p.price >= ?"; $params[] = (int)$priceMin; }
if ($priceMax !== '') { $sql .= " AND p.price <= ?"; $params[] = (int)$priceMax; }

switch ($sort) {
    case 'price_asc':  $sql .= " ORDER BY p.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY p.price DESC"; break;
    case 'name_asc':   $sql .= " ORDER BY p.name ASC"; break;
    case 'brand_asc':  $sql .= " ORDER BY brand_name ASC, p.name ASC"; break;
    default:           $sql .= " ORDER BY p.id DESC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require "layout/header.php"; ?>


<!-- HERO -->
<section class="hero position-relative text-white text-center d-flex align-items-center justify-content-center"
         style="background: linear-gradient(rgba(216, 221, 140, 0.23), rgba(0, 0, 0, 0)), url('<?= BASE_URL ?>/public/uploads/hero.jpg') center/cover no-repeat; height: 70vh; min-height: 520px;">
    <div class="container">
        <h1 class="display-3 fw-bold mb-4">ЗДОРОВЬЕ НАЧИНАЕТСЯ ЗДЕСЬ</h1>
        <p class="lead fs-2 mb-4">БОЛЕЕ 5000 ТОВАРОВ ДЛЯ ВАШЕГО ЗДОРОВЬЯ С ДОСТАВКОЙ ПО ВСЕЙ РОССИИ</p>

        <!-- BRANDS MARQUEE -->
        <div class="brands-marquee-outer mb-4">
            <div class="brands-marquee-track">
                <div class="brands-marquee-inner" id="brandsInner">

                    <div class="brand-item">
                        <!-- Эвалар -->
                        <svg height="28" viewBox="0 0 120 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="Inter, sans-serif" font-size="20" font-weight="700" letter-spacing="2" fill="white">ЭВАЛАР</text>
                        </svg>
                    </div>

                    <span class="brand-sep">✦</span>

                    <div class="brand-item">
                        <!-- Solgar -->
                        <svg height="28" viewBox="0 0 100 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="Georgia, serif" font-size="21" font-weight="400" letter-spacing="3" fill="white">SOLGAR</text>
                        </svg>
                    </div>

                    <span class="brand-sep">✦</span>

                    <div class="brand-item">
                        <!-- La Roche-Posay -->
                        <svg height="28" viewBox="0 0 200 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="Inter, sans-serif" font-size="17" font-weight="300" letter-spacing="2" fill="white">LA ROCHE-POSAY</text>
                        </svg>
                    </div>

                    <span class="brand-sep">✦</span>

                    <div class="brand-item">
                        <!-- Vichy -->
                        <svg height="28" viewBox="0 0 80 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="Georgia, serif" font-size="21" font-weight="400" letter-spacing="4" fill="white">VICHY</text>
                        </svg>
                    </div>

                    <span class="brand-sep">✦</span>

                    <div class="brand-item">
                        <!-- Bayer -->
                        <svg height="28" viewBox="0 0 80 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="Inter, sans-serif" font-size="20" font-weight="700" letter-spacing="2" fill="white">BAYER</text>
                        </svg>
                    </div>

                    <span class="brand-sep">✦</span>

                    <div class="brand-item">
                        <!-- Доппельгерц -->
                        <svg height="28" viewBox="0 0 180 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="Inter, sans-serif" font-size="17" font-weight="300" letter-spacing="2" fill="white">DOPPELHERZ</text>
                        </svg>
                    </div>

                    <span class="brand-sep">✦</span>

                    <div class="brand-item">
                        <!-- Nurofen -->
                        <svg height="28" viewBox="0 0 120 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="Inter, sans-serif" font-size="20" font-weight="700" letter-spacing="1" fill="white">NUROFEN</text>
                        </svg>
                    </div>

                    <span class="brand-sep">✦</span>

                    <div class="brand-item">
                        <!-- Complivit -->
                        <svg height="28" viewBox="0 0 130 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="Georgia, serif" font-size="19" font-weight="400" letter-spacing="2" fill="white">КОМПЛИВИТ</text>
                        </svg>
                    </div>

                    <span class="brand-sep">✦</span>

                    <div class="brand-item">
                        <!-- Centrum -->
                        <svg height="28" viewBox="0 0 110 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="Inter, sans-serif" font-size="20" font-weight="300" letter-spacing="3" fill="white">CENTRUM</text>
                        </svg>
                    </div>

                    <span class="brand-sep">✦</span>

                </div>
                <!-- Дубликат для бесшовного цикла -->
                <div class="brands-marquee-inner" aria-hidden="true">

                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 120 28" fill="none"><text x="0" y="22" font-family="Inter, sans-serif" font-size="20" font-weight="700" letter-spacing="2" fill="white">ЭВАЛАР</text></svg>
                    </div>
                    <span class="brand-sep">✦</span>
                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 100 28" fill="none"><text x="0" y="22" font-family="Georgia, serif" font-size="21" font-weight="400" letter-spacing="3" fill="white">SOLGAR</text></svg>
                    </div>
                    <span class="brand-sep">✦</span>
                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 200 28" fill="none"><text x="0" y="22" font-family="Inter, sans-serif" font-size="17" font-weight="300" letter-spacing="2" fill="white">LA ROCHE-POSAY</text></svg>
                    </div>
                    <span class="brand-sep">✦</span>
                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 80 28" fill="none"><text x="0" y="22" font-family="Georgia, serif" font-size="21" font-weight="400" letter-spacing="4" fill="white">VICHY</text></svg>
                    </div>
                    <span class="brand-sep">✦</span>
                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 80 28" fill="none"><text x="0" y="22" font-family="Inter, sans-serif" font-size="20" font-weight="700" letter-spacing="2" fill="white">BAYER</text></svg>
                    </div>
                    <span class="brand-sep">✦</span>
                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 180 28" fill="none"><text x="0" y="22" font-family="Inter, sans-serif" font-size="17" font-weight="300" letter-spacing="2" fill="white">DOPPELHERZ</text></svg>
                    </div>
                    <span class="brand-sep">✦</span>
                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 120 28" fill="none"><text x="0" y="22" font-family="Inter, sans-serif" font-size="20" font-weight="700" letter-spacing="1" fill="white">NUROFEN</text></svg>
                    </div>
                    <span class="brand-sep">✦</span>
                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 130 28" fill="none"><text x="0" y="22" font-family="Georgia, serif" font-size="19" font-weight="400" letter-spacing="2" fill="white">КОМПЛИВИТ</text></svg>
                    </div>
                    <span class="brand-sep">✦</span>
                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 110 28" fill="none"><text x="0" y="22" font-family="Inter, sans-serif" font-size="20" font-weight="300" letter-spacing="3" fill="white">CENTRUM</text></svg>
                    </div>
                    <span class="brand-sep">✦</span>

                </div>
            </div>
        </div>
        <!-- END BRANDS MARQUEE -->

        <a href="#catalog" class="btn btn-lg px-5 py-2 btn-hero-outline">ПЕРЕЙТИ В КАТАЛОГ</a>
    </div>
</section>

<!-- AI-ВИДЖЕТ -->
<div class="container my-5">
    <div class="rounded-4 p-4 p-md-5 shadow-sm"
         style="background:linear-gradient(135deg, #f7ffee 0%, #f0f8e8 100%);border:1px solid #c8e8a0;">
        <div class="row align-items-center g-4">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:46px;height:46px;background:#a6d175;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"/><path d="M12 16v-4m0-4h.01"/></svg>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" style="color:#2d5a1b;">AI&#8209;консультант</h5>
                        <small class="text-muted">Опишите симптомы — подберём препарат</small>
                    </div>
                </div>
                <p class="text-muted small mb-0">Искусственный интеллект анализирует симптомы и находит подходящие препараты из нашего каталога</p>
            </div>
            <div class="col-md-7">
                <form id="aiQuickForm" class="d-flex gap-2">
                    <input type="text" id="aiQuickInput" class="form-control form-control-lg rounded-3"
                           placeholder="Например: болит голова и температура..."
                           style="border:2px solid #c5dfa0;font-size:.95rem;background:#fff;" autocomplete="off">
                    <button type="submit" class="btn btn-success btn-lg px-4 rounded-3 fw-bold" style="white-space:nowrap;">Спросить</button>
                </form>
                <div class="mt-2 d-flex flex-wrap gap-2" id="aiHints"></div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($bannerSlides)): ?>

<style>
.brand-slider {
    position: relative;
    overflow: hidden;
    background: #f0f8e8;
    margin-bottom: 2.5rem;
    user-select: none;
}

/* Каждый слайд */
.brand-slide {
    display: none;
    flex-direction: column;
    animation: slideIn .55s cubic-bezier(.4,0,.2,1);
}
.brand-slide.active { display: flex; }

@keyframes slideIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Баннер */
.brand-slide-banner {
    width: 100%;
    max-height: 420px;
    object-fit: cover;
    object-position: center;
    display: block;
}

/* Нижняя полоска с товарами */
.brand-slide-footer {
    background: #fff;
    border-top: 2px solid #d4edaa;
    padding: 20px 0 18px;
}
.brand-slide-footer .container {
    display: flex;
    align-items: center;
    gap: 20px;
}
.brand-slide-label {
    font-family: 'Fragment Mono', monospace;
    font-size: 11px;
    color: #7AAD3F;
    letter-spacing: 2px;
    text-transform: uppercase;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    white-space: nowrap;
    flex-shrink: 0;
    opacity: .7;
}
.brand-slide-products {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    flex: 1;
}
@media (max-width: 767px) {
    .brand-slide-products { grid-template-columns: repeat(2, 1fr); }
    .brand-slide-banner   { max-height: 200px; }
    .brand-slide-label    { display: none; }
}

.brand-mini-card {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fef3;
    border: 1.5px solid #e8f5d0;
    border-radius: 10px;
    padding: 8px 10px;
    text-decoration: none;
    color: inherit;
    transition: border-color .18s, box-shadow .18s;
    overflow: hidden;
}
.brand-mini-card:hover {
    border-color: #a6d175;
    box-shadow: 0 2px 10px rgba(122,173,63,.15);
    color: inherit;
}
.brand-mini-card img {
    width: 46px;
    height: 46px;
    object-fit: contain;
    background: #fff;
    border-radius: 6px;
    flex-shrink: 0;
}
.brand-mini-card-info {
    flex: 1;
    min-width: 0;
}
.brand-mini-card-name {
    font-size: 11px;
    color: #333;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 3px;
}
.brand-mini-card-price {
    font-family: 'Fragment Mono', monospace;
    font-size: 12px;
    font-weight: 600;
    color: #2d5a1b;
}

/* Точки-индикаторы */
.brand-slider-dots {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 7px;
    z-index: 10;
}
.brand-slider-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,.5);
    border: 1.5px solid rgba(255,255,255,.8);
    cursor: pointer;
    transition: background .2s, transform .2s;
}
.brand-slider-dot.active {
    background: #fff;
    transform: scale(1.25);
}

/* Прогресс-бар */
.brand-slider-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: #7AAD3F;
    width: 0%;
    transition: width linear;
    z-index: 10;
}

/* Стрелки */
.brand-slider-arrow {
    position: absolute;
    top: calc(50% - 30px); /* половина баннера */
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,.75);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: background .18s;
    backdrop-filter: blur(4px);
}
.brand-slider-arrow:hover { background: rgba(255,255,255,.95); }
.brand-slider-arrow.prev { left: 16px; }
.brand-slider-arrow.next { right: 16px; }
</style>

<div class="brand-slider" id="brandSlider">

    <!-- Стрелки -->
    <button class="brand-slider-arrow prev" onclick="brandSlide(-1)" aria-label="Назад">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2d5a1b" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <button class="brand-slider-arrow next" onclick="brandSlide(1)" aria-label="Вперёд">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2d5a1b" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
    </button>

    <!-- Слайды -->
    <?php foreach ($bannerSlides as $i => $slide): ?>
    <div class="brand-slide <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>">

        <!-- Баннер во всю ширину -->
        <img src="<?= htmlspecialchars($slide['brand']['banner']) ?>"
             class="brand-slide-banner"
             alt="<?= htmlspecialchars($slide['brand']['name']) ?>">

        <!-- Товары бренда -->
        <div class="brand-slide-footer">
            <div class="container">
                <span class="brand-slide-label"><?= htmlspecialchars($slide['brand']['name']) ?></span>
                <div class="brand-slide-products">
                    <?php foreach ($slide['products'] as $p): ?>
                    <a href="<?= BASE_URL ?>/public/product.php?id=<?= $p['id'] ?>" class="brand-mini-card">
                        <img src="<?= htmlspecialchars(imgUrl($p['image'])) ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>">
                        <div class="brand-mini-card-info">
                            <div class="brand-mini-card-name"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="brand-mini-card-price"><?= number_format($p['price'], 0, '', ' ') ?> ₽</div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Точки -->
    <div class="brand-slider-dots" id="brandDots">
        <?php foreach ($bannerSlides as $i => $_): ?>
            <div class="brand-slider-dot <?= $i === 0 ? 'active' : '' ?>"
                 onclick="brandGoTo(<?= $i ?>)"></div>
        <?php endforeach; ?>
    </div>

    <!-- Прогресс -->
    <div class="brand-slider-progress" id="brandProgress"></div>
</div>

<script>
(function () {
    var DURATION   = 5500; // мс на слайд
    var slides     = document.querySelectorAll('.brand-slide');
    var dots       = document.querySelectorAll('.brand-slider-dot');
    var progress   = document.getElementById('brandProgress');
    var current    = 0;
    var timer      = null;
    var progTimer  = null;

    if (slides.length <= 1) return; // один слайд — прячем стрелки
    document.querySelector('.brand-slider-arrow.prev').style.display =
    document.querySelector('.brand-slider-arrow.next').style.display = slides.length > 1 ? '' : 'none';

    function goTo(idx) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = (idx + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
        startProgress();
    }

    function startProgress() {
        clearTimeout(timer);
        clearTimeout(progTimer);
        // Сброс прогресса
        progress.style.transition = 'none';
        progress.style.width = '0%';
        // Запуск через один тик
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                progress.style.transition = 'width ' + DURATION + 'ms linear';
                progress.style.width = '100%';
            });
        });
        timer = setTimeout(function () { goTo(current + 1); }, DURATION);
    }

    // Глобальные функции для кнопок
    window.brandSlide = function (dir) { goTo(current + dir); };
    window.brandGoTo  = function (idx) { goTo(idx); };

    // Пауза при hover на слайдере
    document.getElementById('brandSlider').addEventListener('mouseenter', function () {
        clearTimeout(timer);
        progress.style.transition = 'none'; // стоп
    });
    document.getElementById('brandSlider').addEventListener('mouseleave', function () {
        startProgress(); // продолжить
    });

    startProgress();
})();
</script>

<?php endif; ?>

<!-- ПОИСК + ФИЛЬТРЫ -->
<div class="container mb-5" id="catalog">
    <form id="filterForm">
        <div class="filter-bar">

            <div class="filter-search">
                <div class="input-group">
                    <span class="input-group-text" style="background:#f8fef3;border:1.5px solid #d4edaa;border-right:none;color:#a6d175;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </span>
                    <input type="text" name="q" class="form-control" placeholder="Поиск препарата..."
                           value="<?= htmlspecialchars($search) ?>"
                           style="border:1.5px solid #d4edaa;border-left:none;font-size:.9rem;">
                </div>
            </div>

            <div class="filter-item">
                <select name="brand" class="form-select" style="border:1.5px solid #d4edaa;font-size:.88rem;color:#444;">
                    <option value="">Все бренды</option>
                    <?php
                    $brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();
                    foreach ($brands as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= ($_GET['brand'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-price">
                <div class="d-flex align-items-center gap-1">
                    <input type="number" name="price_min" class="form-control form-control-sm text-center"
                           placeholder="от" value="<?= htmlspecialchars($priceMin) ?>"
                           style="border:1.5px solid #d4edaa;font-size:.88rem;min-width:0;">
                    <span style="color:#aaa;font-size:.9rem;">—</span>
                    <input type="number" name="price_max" class="form-control form-control-sm text-center"
                           placeholder="до" value="<?= htmlspecialchars($priceMax) ?>"
                           style="border:1.5px solid #d4edaa;font-size:.88rem;min-width:0;">
                    <span style="color:#777;font-size:.8rem;white-space:nowrap;">₽</span>
                </div>
            </div>

            <div class="filter-item">
                <select name="sort" class="form-select" style="border:1.5px solid #d4edaa;font-size:.88rem;color:#444;">
                    <option value="new"        <?= $sort=='new'       ?'selected':'' ?>>Сначала новые</option>
                    <option value="price_asc"  <?= $sort=='price_asc' ?'selected':'' ?>>Цена ↑</option>
                    <option value="price_desc" <?= $sort=='price_desc'?'selected':'' ?>>Цена ↓</option>
                    <option value="name_asc"   <?= $sort=='name_asc'  ?'selected':'' ?>>Название А→Я</option>
                    <option value="brand_asc"  <?= $sort=='brand_asc' ?'selected':'' ?>>Бренд А→Я</option>
                </select>
            </div>

            <div class="filter-btns">
                <button type="submit" class="btn btn-success px-4" style="border-radius:10px;font-size:.88rem;white-space:nowrap;">Найти</button>
                <button type="button" id="resetBtn" class="btn" style="border:1.5px solid #d4edaa;color:#777;border-radius:10px;font-size:.88rem;padding:.375rem .65rem;" title="Сбросить">✕</button>
            </div>

        </div>
    </form>
</div>

<!-- КАТАЛОГ -->
<main class="container pb-5">
    <div class="text-center text-muted mb-4" style="font-size:.88rem;">
        Найдено товаров: <strong id="productsCount"><?= count($products) ?></strong>
    </div>
    <div class="row g-4" id="productsGrid">
        <?php foreach ($products as $p): ?>
            <?php
            $in_cart_qty = 0;
            if (isLogged()) {
                $stmt = $pdo->prepare("SELECT qty FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$_SESSION['user_id'], $p['id']]);
                $in_cart_qty = $stmt->fetchColumn() ?: 0;
            }
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 d-flex flex-column position-relative overflow-hidden"
                     style="border:none;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.07);transition:box-shadow .2s;">
                    <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark d-flex flex-column flex-grow-1">
                        <img src="<?= htmlspecialchars($p["image"] ?: 'https://via.placeholder.com/600') ?>"
                             class="card-img-top"
                             style="height:200px;object-fit:contain;background:#f8fef3;padding:1.5rem;"
                             alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                        <div class="card-body d-flex flex-column flex-grow-1" style="padding:1rem 1rem 0.5rem;">
                            <h5 class="card-title mb-1" style="font-size:.88rem;font-weight:600;line-height:1.35;"><?= htmlspecialchars($p["name"]) ?></h5>
                            <?php if (!empty($p['brand_name'])): ?>
                                <p class="text-muted mb-2" style="font-size:.78rem;"><?= htmlspecialchars($p['brand_name']) ?></p>
                            <?php endif; ?>
                            <p class="text-muted flex-grow-1 mb-2" style="font-size:.78rem;line-height:1.4;">
                                <?= htmlspecialchars(mb_strimwidth($p["short_description"] ?: $p["description"] ?: "", 0, 80, "...")) ?>
                            </p>
                            <?php if (!empty($p["label"])): ?>
                                <div class="mb-2"><span class="label-badge"><?= htmlspecialchars(getLabelText($p["label"])) ?></span></div>
                            <?php endif; ?>
                            <div class="fw-bold mt-auto" style="font-size:1.1rem;color:#2d5a1b;"><?= number_format($p["price"], 0, '', ' ') ?> ₽</div>
                        </div>
                    </a>
                    <div style="padding:.75rem 1rem 1rem;">
                        <?php if ($in_cart_qty > 0): ?>
                            <div class="btn btn-outline-custom w-100 position-relative d-flex align-items-center" style="height:42px;padding:0 8px;">
                                <a href="#" class="text-decoration-none position-absolute start-0 top-0 bottom-0 d-flex align-items-center justify-content-center qty-change"
                                   data-id="<?= $p['id'] ?>" data-action="minus" style="width:48px;z-index:2;font-size:1.6rem;font-weight:bold;">−</a>
                                <span class="fw-bold position-absolute start-50 top-50 translate-middle" style="font-size:1rem;z-index:1;"><?= $in_cart_qty ?></span>
                                <a href="#" class="text-decoration-none position-absolute end-0 top-0 bottom-0 d-flex align-items-center justify-content-center qty-change"
                                   data-id="<?= $p['id'] ?>" data-action="plus" style="width:48px;z-index:2;font-size:1.6rem;font-weight:bold;">+</a>
                            </div>
                        <?php else: ?>
                            <a href="add_to_cart.php?id=<?= $p['id'] ?>&redirect=index.php"
                               class="btn btn-outline-custom w-100" style="height:42px;display:flex;align-items:center;justify-content:center;font-size:.85rem;">В корзину</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($products) && $search === ''): ?>
            <div class="text-center py-5">
                <h3 class="text-muted">Товаров пока нет</h3>
                <p class="text-muted">Добавьте первые товары в админ-панели</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
// AJAX фильтрация
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    loadProducts();
});

document.querySelectorAll('#filterForm select').forEach(el => {
    el.addEventListener('change', loadProducts);
});

document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('filterForm').reset();
    loadProducts();
});

function loadProducts() {
    const params = new URLSearchParams(new FormData(document.getElementById('filterForm')));
    document.getElementById('productsGrid').innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border" style="color:#7AAD3F;" role="status"></div>
        </div>`;
    fetch('catalog_ajax.php?' + params.toString())
        .then(r => r.json())
        .then(products => {
            document.getElementById('productsCount').textContent = products.length;
            renderProducts(products);
        });
}

function renderProducts(products) {
    const grid = document.getElementById('productsGrid');
    if (!products.length) {
        grid.innerHTML = `<div class="col-12 text-center py-5"><h3 class="text-muted">Ничего не найдено</h3></div>`;
        return;
    }
    grid.innerHTML = products.map(p => `
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 d-flex flex-column" style="border:none;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.07);transition:box-shadow .2s;">
                <a href="product.php?id=${p.id}" class="text-decoration-none text-dark d-flex flex-column flex-grow-1">
                    <img src="${p.image || 'https://via.placeholder.com/600'}" class="card-img-top"
                         style="height:200px;object-fit:contain;background:#f8fef3;padding:1.5rem;" alt="${escHtml(p.name)}" loading="lazy">
                    <div class="card-body d-flex flex-column flex-grow-1" style="padding:1rem 1rem 0.5rem;">
                        <h5 class="card-title mb-1" style="font-size:.88rem;font-weight:600;line-height:1.35;">${escHtml(p.name)}</h5>
                        ${p.brand_name ? `<p class="text-muted mb-2" style="font-size:.78rem;">${escHtml(p.brand_name)}</p>` : ''}
                        <div class="fw-bold mt-auto" style="font-size:1.1rem;color:#2d5a1b;">${Number(p.price).toLocaleString('ru-RU')} ₽</div>
                    </div>
                </a>
                <div style="padding:.75rem 1rem 1rem;">
                    <a href="add_to_cart.php?id=${p.id}&redirect=index.php"
                       class="btn btn-outline-custom w-100" style="height:42px;display:flex;align-items:center;justify-content:center;font-size:.85rem;">В корзину</a>
                </div>
            </div>
        </div>
    `).join('');
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<!-- МОДАЛКА ЧАТА -->
<div id="aiChatModal" style="display:none;position:fixed;inset:0;z-index:1055;background:rgba(0,0,0,.45);opacity:0;transition:opacity .2s;" onclick="closeAiModal(event)">
    <div style="position:absolute;right:0;top:0;bottom:0;width:min(500px,100vw);background:#fff;display:flex;flex-direction:column;box-shadow:-8px 0 32px rgba(0,0,0,.15);" onclick="event.stopPropagation()">
        <div class="d-flex align-items-center justify-content-between p-4" style="border-bottom:1px solid #e9ecef;background:#f8fef3;flex-shrink:0;">
            <div class="d-flex align-items-center gap-2">
                <div style="width:34px;height:34px;background:#a6d175;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"/><path d="M12 16v-4m0-4h.01"/></svg>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.92rem;">AI&#8209;консультант аптеки</div>
                    <div style="font-size:.72rem;color:#a6d175;">● онлайн</div>
                </div>
            </div>
            <button onclick="closeAiModal()" class="btn-close"></button>
        </div>
        <div id="aiChatMessages" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:14px;">
            <div class="ai-msg bot"><div class="bubble">Здравствуйте! 👋 Опишите ваши симптомы или задайте вопрос о препарате — я помогу подобрать подходящее средство из нашего каталога.</div></div>
        </div>
        <div style="padding:14px;border-top:1px solid #e9ecef;background:#fafafa;flex-shrink:0;">
            <form id="aiModalForm" class="d-flex gap-2">
                <input type="text" id="aiModalInput" class="form-control rounded-3" placeholder="Напишите симптом или вопрос..." autocomplete="off" style="border:1.5px solid #d4edaa;font-size:.88rem;">
                <button type="submit" class="btn btn-success px-3 rounded-3">
                    <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Bootstrap overrides */
.btn-success { background-color:#a6d175!important;border-color:#a6d175!important;color:#fff!important; }
.btn-success:hover,.btn-success:focus { background-color:#5E8F2C!important;border-color:#5E8F2C!important; }
.text-success { color:#a6d175!important; }
.form-control:focus,.form-select:focus { border-color:#a6d175!important;box-shadow:0 0 0 .2rem rgba(122,173,63,.15)!important; }
.form-range::-webkit-slider-thumb { background:#a6d175; }

/* Кнопка В корзину */
.btn-outline-custom {
    border:1.5px solid #c5dfa0!important;
    color:#5E8F2C!important;
    background:#fff!important;
    border-radius:10px!important;
    font-weight:500!important;
    transition:all .18s ease!important;
}
.btn-outline-custom:hover { background:#a6d175!important;color:#fff!important;border-color:#a6d175!important; }

/* Карточки */
.card:hover { box-shadow:0 6px 24px rgba(122,173,63,.15)!important; }

/* Фильтр-бар */
.filter-bar {
    display:flex;
    align-items:center;
    gap:10px;
    background:#fff;
    border:1.5px solid #d4edaa;
    border-radius:14px;
    padding:14px 18px;
    flex-wrap:wrap;
}
.filter-search { flex:2;min-width:200px; }
.filter-item { flex:1;min-width:130px; }
.filter-price { flex:1.5;min-width:170px; }
.filter-btns { display:flex;gap:6px;flex-shrink:0; }

/* Чат */
.ai-msg{display:flex}.ai-msg.user{justify-content:flex-end}.ai-msg.bot{justify-content:flex-start}
.bubble{max-width:85%;padding:10px 14px;border-radius:18px;font-size:.88rem;line-height:1.55}
.ai-msg.user .bubble{background:#a6d175;color:#fff;border-bottom-right-radius:4px}
.ai-msg.bot .bubble{background:#f1f3f5;color:#212529;border-bottom-left-radius:4px}
.product-chip{display:flex;align-items:center;gap:10px;background:#fff;border:1.5px solid #d0e9b0;border-radius:12px;padding:8px 12px;text-decoration:none;color:inherit;width:100%;box-sizing:border-box;margin-top:6px;transition:box-shadow .18s,border-color .18s}
.product-chip:hover{box-shadow:0 4px 16px rgba(122,173,63,.18);border-color:#a6d175}
.product-chip img{width:44px;height:44px;object-fit:contain;border-radius:8px;background:#f8fef3;flex-shrink:0}
.product-chip .pn{font-weight:600;font-size:.85rem;color:#212529}
.product-chip .pp{font-weight:700;font-size:.85rem;color:#5E8F2C;margin-top:2px}
.detail-pills{display:flex;flex-wrap:wrap;gap:6px;width:100%;margin-top:6px}
.detail-pill{display:inline-flex;align-items:center;gap:4px;background:#fff;border:1.5px solid #c5dfa0;border-radius:20px;padding:4px 11px;font-size:.76rem;color:#2d5a1b;cursor:pointer;transition:background .15s,border-color .15s;user-select:none}
.detail-pill:hover{background:#eef6e0;border-color:#a6d175}
.detail-pill.active{background:#a6d175;color:#fff;border-color:#a6d175}
.detail-panel{width:100%;margin-top:6px;background:#f8fef3;border:1.5px solid #d4edaa;border-radius:12px;padding:11px 13px;font-size:.8rem;color:#333;line-height:1.5;animation:fadeIn .15s ease;box-sizing:border-box}
.detail-panel b{display:block;margin-bottom:4px;color:#2d5a1b}
.typing-dots span{display:inline-block;width:7px;height:7px;border-radius:50%;background:#adb5bd;margin:0 2px;animation:tdot 1.2s infinite}
.typing-dots span:nth-child(2){animation-delay:.2s}.typing-dots span:nth-child(3){animation-delay:.4s}
@keyframes tdot{0%,80%,100%{transform:scale(1);opacity:.4}40%{transform:scale(1.3);opacity:1}}
.hint-pill{display:inline-block;background:#fff;border:1px solid #c5dfa0;border-radius:20px;padding:3px 12px;font-size:.76rem;color:#2d5a1b;cursor:pointer;transition:background .15s}
.hint-pill:hover{background:#eef6e0}
@keyframes fadeIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
</style>

<script>
const HINTS=["болит голова и температура","сильный кашель уже 3 дня","болит живот, тошнота","не могу уснуть, стресс","насморк и заложенность","болит горло при глотании"];
function renderHints(){const s=[...HINTS].sort(()=>Math.random()-.5).slice(0,3);document.getElementById('aiHints').innerHTML=s.map(h=>`<span class="hint-pill" onclick="useHint(this)">${h}</span>`).join('')}
renderHints();setInterval(renderHints,5000);
function useHint(el){document.getElementById('aiQuickInput').value=el.textContent;document.getElementById('aiQuickForm').dispatchEvent(new Event('submit'))}

function openAiModal(msg){const m=document.getElementById('aiChatModal');m.style.display='block';requestAnimationFrame(()=>m.style.opacity='1');document.body.style.overflow='hidden';if(msg)sendAiMessage(msg);else document.getElementById('aiModalInput').focus()}
function closeAiModal(e){if(e&&e.target!==document.getElementById('aiChatModal'))return;const m=document.getElementById('aiChatModal');m.style.opacity='0';setTimeout(()=>{m.style.display='none'},200);document.body.style.overflow=''}

document.getElementById('aiQuickForm').addEventListener('submit',function(e){e.preventDefault();const msg=document.getElementById('aiQuickInput').value.trim();if(!msg)return;openAiModal(msg);document.getElementById('aiQuickInput').value=''});
document.getElementById('aiModalForm').addEventListener('submit',function(e){e.preventDefault();const msg=document.getElementById('aiModalInput').value.trim();if(!msg)return;sendAiMessage(msg);document.getElementById('aiModalInput').value=''});

function esc(s){
    return String(s ?? '').replace(/[&<>"']/g, c => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
}
function addMsg(role,text,products){
    const box=document.getElementById('aiChatMessages'),wrap=document.createElement('div');
    wrap.className='ai-msg '+role;
    const inner=document.createElement('div');
    inner.style.cssText='display:flex;flex-direction:column;align-items:flex-start;gap:6px;max-width:85%;';
    const bubble=document.createElement('div');bubble.className='bubble';bubble.style.maxWidth='100%';
    bubble.innerHTML=esc(text).replace(/\n/g,'<br>');inner.appendChild(bubble);
    if(role==='bot'&&products&&products.length){
        products.forEach(p=>{
            const col=document.createElement('div');
            col.style.cssText='display:flex;flex-direction:column;align-items:flex-start;width:100%;';
            const chip=document.createElement('a');chip.href='product.php?id='+p.id;chip.target='_blank';chip.className='product-chip';
            chip.innerHTML=`<img src="${esc(p.image)||'https://placehold.co/48'}" alt="${esc(p.name)}" loading="lazy" width="44" height="44"><div><div class="pn">${esc(p.name)}</div><div class="pp">${Number(p.price).toLocaleString('ru-RU')} ₽</div></div>`;
            col.appendChild(chip);
            const defs=[{key:'usage_info',icon:'💊',label:'Способ употребления'},{key:'composition',icon:'🧪',label:'Состав'},{key:'contraindications',icon:'⚠️',label:'Противопоказания'}].filter(i=>p[i.key]);
            if(defs.length){
                const pw=document.createElement('div');pw.className='detail-pills';
                let ap=null,apl=null;
                defs.forEach(item=>{
                    const pill=document.createElement('span');pill.className='detail-pill';pill.innerHTML=item.icon+' '+item.label;
                    pill.addEventListener('click',()=>{
                        if(ap===pill){pill.classList.remove('active');apl&&apl.remove();ap=null;apl=null;return}
                        ap&&ap.classList.remove('active');apl&&apl.remove();
                        pill.classList.add('active');
                        const panel=document.createElement('div');panel.className='detail-panel';
                        panel.innerHTML=`<b>${item.icon} ${item.label}</b>${esc(p[item.key])}`;
                        pw.after(panel);ap=pill;apl=panel;box.scrollTop=box.scrollHeight;
                    });
                    pw.appendChild(pill);
                });
                col.appendChild(pw);
            }
            inner.appendChild(col);
        });
    }
    wrap.appendChild(inner);box.appendChild(wrap);box.scrollTop=box.scrollHeight;
}

function showTyping(){const box=document.getElementById('aiChatMessages'),el=document.createElement('div');el.className='ai-msg bot';el.id='aiTyping';el.innerHTML='<div class="bubble"><div class="typing-dots"><span></span><span></span><span></span></div></div>';box.appendChild(el);box.scrollTop=box.scrollHeight}

async function sendAiMessage(msg){
    addMsg('user',msg,[]);showTyping();
    document.getElementById('aiModalInput').disabled=true;
    const fd=new FormData();fd.append('message',msg);
    try{
        const res=await fetch('chat_api.php',{method:'POST',body:fd});
        const data=await res.json();
        document.getElementById('aiTyping')?.remove();
        if(data.error==='ollama_unavailable')addMsg('bot','⚠️ Консультант временно недоступен.',[]);
        else addMsg('bot',data.text||'Не удалось получить ответ',data.products||[]);
    }catch{document.getElementById('aiTyping')?.remove();addMsg('bot','⚠️ Ошибка соединения.',[]);}
    finally{document.getElementById('aiModalInput').disabled=false;document.getElementById('aiModalInput').focus()}
}

</script>

<?php require "layout/footer.php"; ?>
