<?php 
$pageTitle = "Бренды";
require "layout/admin_header.php"; 
?>

<div class="mb-5 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-1">Управление брендами</h1>
        <p class="text-muted mb-0">Создание, редактирование и удаление брендов</p>
    </div>
    <a href="brand_edit.php" class="btn btn-dark px-5 py-3 rounded-pill shadow-sm">
        + Добавить бренд
    </a>
</div>

<?php
// Удаление бренда
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM brands WHERE id = ?")->execute([$id]);
    echo '<div class="alert alert-success mb-4">Бренд удалён</div>';
}

$brands = $pdo->query("SELECT * FROM brands ORDER BY name")->fetchAll();
?>

<?php if (empty($brands)): ?>
    <div class="text-center py-5">
        <div class="bg-light rounded-4 py-5 px-4">
            <h3 class="text-muted mb-4">Брендов пока нет</h3>
            <a href="brand_edit.php" class="btn btn-dark px-5 py-3 rounded-pill">
                Создать первый бренд
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($brands as $b): ?>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden hover-shadow transition-all">
                    <?php if ($b['logo']): ?>
                        <img src="<?= htmlspecialchars($b['logo']) ?>" 
                             class="card-img-top" 
                             style="height:180px; object-fit:contain; background:#fafafa; padding:1.5rem;">
                    <?php else: ?>
                        <div class="bg-light border-bottom" style="height:180px; display:flex; align-items:center; justify-content:center;">
                            <span class="text-muted fs-4 opacity-50">Без логотипа</span>
                        </div>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2"><?= htmlspecialchars($b['name']) ?></h5>
                        
                        <?php if ($b['description']): ?>
                            <p class="text-muted small flex-grow-1 mb-3">
                                <?= htmlspecialchars(mb_strimwidth($b['description'], 0, 100, '...')) ?>
                            </p>
                        <?php endif; ?>

                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">
                                    Товаров: 
                                    <strong>
                                        <?= $pdo->query("SELECT COUNT(*) FROM products WHERE brand_id = " . (int)$b['id'])->fetchColumn() ?>
                                    </strong>
                                </small>
                            </div>

                            <div class="d-grid gap-2 d-md-flex">
                                <a href="brand_edit.php?id=<?= $b['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm rounded-pill flex-fill">
                                    Редактировать
                                </a>
                                <a href="?delete=<?= $b['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill flex-fill"
                                   onclick="return confirm('Удалить бренд «<?= htmlspecialchars($b['name'], ENT_QUOTES) ?>»?')">
                                    Удалить
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require "layout/admin_footer.php"; ?>