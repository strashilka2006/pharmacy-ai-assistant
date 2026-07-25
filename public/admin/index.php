<?php 
$pageTitle = "Админ-панель";
require "layout/admin_header.php"; 
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Панель администратора</h1>
    <p class="text-muted mb-0">Управление интернет-аптекой</p>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <a href="products.php" class="text-decoration-none">
            <div class="bg-white rounded-4 shadow-sm p-4 h-100 hover-shadow transition-all">
                <div class="fs-1 mb-2">💊</div>
                <h3 class="h5 mb-2">Товары</h3>
                <p class="text-muted mb-0">Просмотр, добавление и редактирование товаров каталога.</p>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="brands.php" class="text-decoration-none">
            <div class="bg-white rounded-4 shadow-sm p-4 h-100 hover-shadow transition-all">
                <div class="fs-1 mb-2">🏷️</div>
                <h3 class="h5 mb-2">Бренды</h3>
                <p class="text-muted mb-0">Управление брендами и их логотипами.</p>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="../index.php" class="text-decoration-none">
            <div class="bg-white rounded-4 shadow-sm p-4 h-100 hover-shadow transition-all">
                <div class="fs-1 mb-2">🏠</div>
                <h3 class="h5 mb-2">На сайт</h3>
                <p class="text-muted mb-0">Перейти на главную страницу для клиентов.</p>
            </div>
        </a>
    </div>
</div>

<?php require "layout/admin_footer.php"; ?>
