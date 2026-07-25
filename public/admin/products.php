<?php 
$pageTitle = "Товары";
require "layout/admin_header.php";
?>

<div class="mb-5 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-1">Все товары</h1>
        <p class="text-muted mb-0">Управление каталогом аптеки</p>
    </div>
    <a href="product_add.php" class="btn btn-dark px-5 py-3 rounded-pill shadow-sm">
        Добавить товар
    </a>
</div>

<?php
$stmt = $pdo->query("SELECT id, name, price, image, stock FROM products ORDER BY id DESC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (empty($items)): ?>
    <div class="text-center py-5">
        <div class="bg-light rounded-4 py-5">
            <h3 class="text-muted mb-4">Товаров пока нет</h3>
            <a href="product_add.php" class="btn btn-dark px-5 py-3 rounded-pill">Добавить первый товар</a>
        </div>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white rounded-4 shadow-sm overflow-hidden">
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Фото</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>На складе</th>
                    <th class="text-end pe-4">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $p): ?>
                    <tr>
                        <td class="ps-4 fw-bold">#<?= $p["id"] ?></td>
                        <td>
                            <?php if ($p["image"]): ?>
                                <img src="<?= htmlspecialchars($p["image"]) ?>" 
                                     class="rounded" style="width:60px; height:60px; object-fit:cover;">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                     style="width:60px; height:60px;">
                                    <span class="text-muted small">—</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="fw-semibold"><?= htmlspecialchars($p["name"]) ?></td>
                        <td class="fw-bold"><?= number_format($p["price"], 0, '', ' ') ?> ₽</td>
                        <td>
                            <span class="badge <?= $p['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                <?= $p["stock"] ?> шт.
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="product_edit.php?id=<?= $p["id"] ?>" 
                               class="btn btn-outline-primary btn-sm rounded-pill px-3">Редактировать</a>
                            <a href="product_delete.php?id=<?= $p["id"] ?>" 
                               class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-2"
                               onclick="return confirm('Удалить товар «<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>»?')">
                                Удалить
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require "layout/admin_footer.php"; ?>