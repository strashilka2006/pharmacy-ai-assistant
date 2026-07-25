<?php
require "../app/bootstrap.php";

if (!isLogged()) {
    header("Location: login.php");
    exit;
}

$order_id = $_GET['id'] ?? 0;

// Получаем заказ пользователя
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    die("Заказ не найден.");
}

// Загружаем товары заказа
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require "layout/header.php"; ?>

<div class="container py-5">

    <h2 class="mb-4">Заказ #<?= $order['id'] ?></h2>

    <p><b>Дата:</b> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
    <p><b>Статус:</b> 
        <span class="badge <?= getStatusBadgeClass($order['status']) ?>">
            <?= htmlspecialchars($order['status']) ?>
        </span>
    </p>

    <hr>

    <h4 class="mb-3">Состав заказа</h4>

    <?php foreach ($items as $it): ?>
        <div class="d-flex align-items-center mb-3 p-3 bg-white shadow-sm rounded">

            <img src="<?= htmlspecialchars($it['image']) ?>"
                 style="width: 80px; height: 80px; object-fit: contain;" class="me-3">

            <div class="flex-grow-1">
                <div class="fw-bold"><?= htmlspecialchars($it['name']) ?></div>
                <div class="text-muted small">Количество: <?= $it['qty'] ?></div>
            </div>

            <div class="fw-bold fs-5">
                <?= number_format($it['price'], 0, '', ' ') ?> ₽
            </div>
        </div>
    <?php endforeach; ?>

    <hr>

    <h4 class="text-end">Итого: <?= number_format($order['total'], 0, '', ' ') ?> ₽</h4>

    <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
        <form action="order_cancel.php" method="POST" class="mt-4">
            <input type="hidden" name="id" value="<?= $order['id'] ?>">
            <button class="btn btn-danger">Отменить заказ</button>
        </form>
    <?php endif; ?>

</div>

<?php require "layout/footer.php"; ?>
