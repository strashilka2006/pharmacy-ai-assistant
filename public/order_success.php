<?php require "layout/header.php"; 
$order_id = $_GET['order_id'] ?? 'неизвестен';
?>
<main class="container py-5 text-center">
    <h1 class="mb-4">Спасибо за заказ!</h1>
    <p class="lead">Заказ №<?= htmlspecialchars($order_id) ?> успешно оформлен.</p>
    <p>Мы свяжемся с вами в ближайшее время.</p>
    <a href="index.php" class="btn btn-primary-custom mt-3">Вернуться в каталог</a>
    <a href="personal.php" class="btn btn-outline-custom mt-3 ms-2">Перейти в личный кабинет</a>
</main>
<?php require "layout/footer.php"; ?>