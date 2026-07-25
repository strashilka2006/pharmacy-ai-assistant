<?php
require "../app/config.php";
requireLogin();

$pdo->beginTransaction();

$user = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT c.product_id, c.qty, p.price
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id=?
");
$stmt->execute([$user]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($items as $i) {
    $total += $i["price"] * $i["qty"];
}

// ВОТ ЭТА СТРОКА ДОЛЖНА БЫТЬ ИМЕННО ТАКОЙ!
$pdo->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, 'new', NOW())")
    ->execute([$user, $total]);

$order_id = $pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
foreach ($items as $i) {
    $stmt->execute([$order_id, $i["product_id"], $i["qty"], $i["price"]]);
}

$pdo->prepare("DELETE FROM cart WHERE user_id=?")->execute([$user]);

$pdo->commit();

header("Location: order_success.php?order_id=" . $order_id);
exit;