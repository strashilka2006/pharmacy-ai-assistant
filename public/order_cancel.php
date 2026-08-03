<?php
require "../app/bootstrap.php";
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: personal.php");
    exit;
}
checkCsrf();

$order_id = (int)($_POST['id'] ?? 0);
$user_id  = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['flash'] = "Заказ не найден";
    header("Location: personal.php");
    exit;
}

$cancellable = ['new', 'processing', 'pending'];
if (!in_array(strtolower(trim((string)$order['status'])), $cancellable, true)) {
    $_SESSION['flash'] = "Этот заказ уже нельзя отменить";
    header("Location: order_view.php?id=$order_id");
    exit;
}

$pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?")
    ->execute([$order_id]);

$_SESSION['flash'] = "Заказ №$order_id отменён";
header("Location: personal.php");
exit;
