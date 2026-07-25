<?php
require "../app/bootstrap.php";


// --------------------------
// 1. "Вы смотрели"
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];


    $viewed = $_COOKIE['viewed'] ?? '';
    $ids    = $viewed ? explode(',', $viewed) : [];


    $ids = array_diff($ids, [$product_id]);
    array_unshift($ids, $product_id);
    $ids = array_slice($ids, 0, 20);


    setcookie('viewed', implode(',', $ids), time() + 30*24*3600, "/");
}


// --------------------------
// 2. Получаем сам товар
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Товар не найден");
}
$id = (int)$_GET["id"];


$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) die("Товар не найден");


// --------------------------
// 3. Данные бренда (если есть)
$brand = null;
if (!empty($product['brand_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ? LIMIT 1");
    $stmt->execute([$product['brand_id']]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);
}


// --------------------------
// 4. Похожие товары

// 4.1. Товары ЭТОГО ЖЕ бренда
$brand_similar = [];
$show_brand_section = false;

if (!empty($product['brand_id'])) {
    $stmt = $pdo->prepare("
        SELECT id, name, price, image, brand_id 
        FROM products 
        WHERE brand_id = ? AND id != ? 
        ORDER BY RAND() 
        LIMIT 6
    ");
    $stmt->execute([$product['brand_id'], $id]);
    $brand_similar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($brand_similar) {
        $show_brand_section = true;
    }
}

// 4.2. Рекомендуемые товары (рандом / категория)
$recommended = [];

// по категории, если есть
if (!empty($product['category_id'])) {
    $exclude = [$id];
    if ($brand_similar) {
        foreach ($brand_similar as $s) $exclude[] = $s['id'];
    }

    $placeholders = str_repeat('?,', count($exclude) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT id, name, price, image, brand_id 
        FROM products 
        WHERE category_id = ? AND id NOT IN ($placeholders) 
        ORDER BY RAND() 
        LIMIT 6
    ");
    $stmt->execute(array_merge([$product['category_id']], $exclude));
    $recommended = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// если и по категории пусто – просто рандом
if (empty($recommended)) {
    $exclude = [$id];
    if ($brand_similar) {
        foreach ($brand_similar as $s) $exclude[] = $s['id'];
    }

    $placeholders = str_repeat('?,', count($exclude) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT id, name, price, image, brand_id 
        FROM products 
        WHERE id NOT IN ($placeholders) 
        ORDER BY RAND() 
        LIMIT 6
    ");
    $stmt->execute($exclude);
    $recommended = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


$pageTitle = htmlspecialchars($product["name"]);
require "layout/header.php";
?>


    <main class="container py-5">
        <a href="index.php" class="btn btn-outline-custom mb-4">Назад в каталог</a>

        <div class="row g-5 align-items-start">
            <!-- Фото -->
            <div class="col-lg-6">
                <img src="<?= htmlspecialchars($product["image"] ?: '/apteka/uploads/no-photo.jpg') ?>"
                    class="img-fluid product-image w-100 rounded-4 shadow"
                    alt="<?= htmlspecialchars($product["name"]) ?>">
            </div>

            <!-- Инфа и кнопка -->
            <div class="col-lg-6">

                <!-- Название + Цена в одной строке -->
                <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                    <h1 class="mb-0"><?= htmlspecialchars($product["name"]) ?></h1>
                    <div class="price fs-3 fw-bold text-nowrap"><?= number_format($product["price"], 0, '', ' ') ?> ₽</div>
                </div>

                <?php if ($product["short_description"]): ?>
                    <p class="lead text-muted fs-5 mb-4"><?= nl2br(htmlspecialchars($product["short_description"])) ?></p>
                <?php endif; ?>

                <?php if ($product["label"] === 'bad'): ?>
                    <div class="label-disclaimer">
                        БАД, не является лекарственным средством
                    </div>
                <?php elseif ($product["label"]): ?>
                    <?php
                    $icons = ['imported' => '✈', 'strong' => '⚕', 'kids' => '✓'];
                    $icon = $icons[$product["label"]] ?? '';
                    ?>
                    <div class="label-badge label-<?= htmlspecialchars($product['label']) ?> mb-4">
                        <?= $icon ?> <?= htmlspecialchars(getLabelText($product["label"])) ?>
                    </div>
                <?php endif; ?>

                <div class="text-muted mb-5">
                    <p><strong>Поставщик:</strong> <?= htmlspecialchars($product["supplier"] ?: "—") ?></p>
                    <p><strong>Рецепт:</strong> <?= $product["prescription"] ? "Требуется" : "Без рецепта" ?></p>
                    <p><strong>На складе:</strong> <?= $product["stock"] ?> шт.</p>
                </div>

                <!-- Кнопка в корзину -->
                <?php
                $in_cart_qty = 0;
                if (isLogged()) {
                    $stmt = $pdo->prepare("SELECT qty FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $id]);
                    $in_cart_qty = $stmt->fetchColumn() ?: 0;
                }
                ?>

                <?php if ($in_cart_qty > 0): ?>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <a href="qty_minus.php?id=<?= $id ?>&return=product.php?id=<?= $id ?>"
                        class="btn btn-outline-dark rounded-pill px-4 fs-4">–</a>
                        <span class="fw-bold fs-3"><?= $in_cart_qty ?></span>
                        <a href="qty_plus.php?id=<?= $id ?>&return=product.php?id=<?= $id ?>"
                        class="btn btn-outline-dark rounded-pill px-4 fs-4">+</a>
                    </div>
                <?php else: ?>
                    <a href="add_to_cart.php?id=<?= $product["id"] ?>&redirect=product.php?id=<?= $id ?>"
                    class="btn btn-outline-custom btn-lg px-5 py-3">
                        В корзину
                    </a>
                <?php endif; ?>

                <?php if (isAdmin()): ?>
                    <a href="admin/product_edit.php?id=<?= $id ?>" class="btn btn-outline-warning btn-sm ms-3">
                        Редактировать
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- БРЕНД — под товаром -->
        <?php if ($brand): ?>
            <div class="mt-5 p-4 bg-light rounded-4 border">
                <div class="row align-items-center">
                    <?php if (!empty($brand["logo"])): ?>
                        <div class="col-auto">
                            <a href="brand.php?id=<?= $brand["id"] ?>">
                                <img src="<?= htmlspecialchars($brand["logo"]) ?>"
                                    alt="<?= htmlspecialchars($brand["name"]) ?>"
                                    class="img-fluid rounded shadow-sm"
                                    style="width: 90px; height: 90px; object-fit: contain;">
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="col">
                        <h3 class="mb-2">
                            <a href="brand.php?id=<?= $brand["id"] ?>"
                               class="text-dark text-decoration-none hover-underline">
                                <?= htmlspecialchars($brand["name"]) ?>
                            </a>
                        </h3>
                        <?php if (!empty($brand["description"])): ?>
                            <p class="text-muted mb-0 small">
                                <?= nl2br(htmlspecialchars($brand["description"])) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <!-- Вкладки с инфо -->
    <?php if ($product["indications"] || $product["usage_info"] || $product["description"] || $product["composition"] || $product["contraindications"] || $product["drug_interactions"] || $product["overdose"]): ?>
    <hr class="my-5">
    <ul class="nav nav-tabs mb-4" id="productTabs">
        <?php 
        $tabs = [
            'indications'       => 'Показания',
            'usage_info'        => 'Применение',
            'composition'       => 'Состав',
            'contraindications' => 'Противопоказания',
            'drug_interactions' => 'Взаимодействие',
            'overdose'          => 'Передозировка',
            'description'       => 'Описание'
        ];
        $first = true;
        foreach ($tabs as $field => $title):
            if (!empty($product[$field]) || ($field === 'description' && (!empty($product["description"]) || !empty($product["long_description"])))):
        ?>
            <li class="nav-item">
                <button class="nav-link <?= $first ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-<?= $field ?>">
                    <?= $title ?>
                </button>
            </li>
        <?php $first = false; endif; endforeach; ?>
    </ul>


    <div class="tab-content">
        <?php $first = true; foreach ($tabs as $field => $title): 
            if (!empty($product[$field]) || ($field === 'description' && (!empty($product["description"]) || !empty($product["long_description"])))): ?>
            <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="tab-<?= $field ?>">
                <?= nl2br(htmlspecialchars($product[$field] ?? ($field === 'description' ? ($product["description"] . "\n\n" . $product["long_description"]) : ''))) ?>
            </div>
        <?php $first = false; endif; endforeach; ?>
    </div>
    <?php endif; ?>


    <!-- ТОВАРЫ БРЕНДА (если есть) -->
    <?php if ($show_brand_section && $brand_similar): ?>
    <hr class="my-5">
    <h3 class="mb-4">Другие товары бренда <strong><?= htmlspecialchars($brand["name"]) ?></strong></h3>

    <div class="row g-4">
        <?php foreach ($brand_similar as $s): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="product.php?id=<?= $s['id'] ?>" class="text-decoration-none">
                    <div class="card h-100">
                        <img src="<?= htmlspecialchars($s["image"] ?: '/apteka/uploads/no-photo.jpg') ?>"
                             class="card-img-top"
                             style="height:220px; object-fit:contain; background:#fafafa; padding:1.5rem;">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($s["name"]) ?></h6>
                            <div class="price mt-auto"><?= number_format($s["price"], 0, '', ' ') ?> ₽</div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>


    <!-- ВАМ МОЖЕТ ПОНРАВИТЬСЯ (всегда если есть) -->
    <?php if ($recommended): ?>
    <hr class="my-5">
    <h3 class="mb-4">Вам может понравиться</h3>

    <div class="row g-4">
        <?php foreach ($recommended as $s): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="product.php?id=<?= $s['id'] ?>" class="text-decoration-none">
                    <div class="card h-100">
                        <img src="<?= htmlspecialchars($s["image"] ?: '/apteka/uploads/no-photo.jpg') ?>"
                             class="card-img-top"
                             style="height:220px; object-fit:contain; background:#fafafa; padding:1.5rem;">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($s["name"]) ?></h6>
                            <div class="price mt-auto"><?= number_format($s["price"], 0, '', ' ') ?> ₽</div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>


<?php require "layout/footer.php"; ?>
