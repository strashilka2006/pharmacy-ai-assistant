<?php
require "../app/bootstrap.php";

if (!isset($_GET["id"])) {
    die("Бренд не указан");
}

$brand_id = (int)$_GET["id"];

$stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
$stmt->execute([$brand_id]);
$brand = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$brand) {
    die("Бренд не найден");
}

// Товары бренда
$stmt = $pdo->prepare("SELECT * FROM products WHERE brand_id = ? ORDER BY name");
$stmt->execute([$brand_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = htmlspecialchars($brand["name"]) . " — официальный бренд";
require "layout/header.php";
?>

<main class="container py-5">
    <div class="row g-5">
        <div class="col-lg-4">
            <?php if (!empty($brand["logo"])): ?>
                <img src="<?= htmlspecialchars($brand["logo"]) ?>" 
                     alt="<?= htmlspecialchars($brand["name"]) ?>" 
                     class="img-fluid rounded shadow mb-4">
            <?php endif; ?>
            
            <h1 class="h2"><?= htmlspecialchars($brand["name"]) ?></h1>
            
            <?php if (!empty($brand["description"])): ?>
                <p class="text-muted"><?= nl2br(htmlspecialchars($brand["description"])) ?></p>
            <?php endif; ?>
        </div>

        <div class="col-lg-8">
            <h2>Все товары бренда (<?= count($products) ?>)</h2>
            
            <?php if ($products): ?>
                <div class="row g-4">
                    <?php foreach ($products as $p): ?>
                        <div class="col-6 col-md-4 col-lg-4">
                            <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark">
                                <div class="card h-100 shadow-sm">
                                    <img src="<?= htmlspecialchars($p["image"] ?: 'https://via.placeholder.com/600') ?>" 
                                         class="card-img-top" 
                                         style="height:220px; object-fit:contain; background:#fafafa; padding:2rem;">
                                    <div class="card-body">
                                        <h6><?= htmlspecialchars($p["name"]) ?></h6>
                                        <div class="price fw-bold"><?= number_format($p["price"], 0, '', ' ') ?> ₽</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Товаров этого бренда пока нет.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require "layout/footer.php"; ?>