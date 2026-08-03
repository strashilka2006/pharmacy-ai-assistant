<?php
require "../app/bootstrap.php";
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit;
}
checkCsrf();

$user = $_SESSION["user_id"];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT c.product_id, c.qty, p.price, p.stock, p.name
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
        FOR UPDATE
    ");
    $stmt->execute([$user]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        $pdo->rollBack();
        $_SESSION['flash'] = "Корзина пуста";
        header("Location: cart.php");
        exit;
    }

    $total = 0;
    foreach ($items as $i) {
        if ($i['qty'] > $i['stock']) {
            $pdo->rollBack();
            $_SESSION['flash'] = "Товара «{$i['name']}» не хватает на складе";
            header("Location: cart.php");
            exit;
        }
        $total += $i["price"] * $i["qty"];
    }

    $pdo->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, 'new', NOW())")
        ->execute([$user, $total]);
    $order_id = $pdo->lastInsertId();

    $ins  = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
    $dec  = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    foreach ($items as $i) {
        $ins->execute([$order_id, $i["product_id"], $i["qty"], $i["price"]]);
        $dec->execute([$i["qty"], $i["product_id"]]);
    }

    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user]);
    $pdo->commit();

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Order error: ' . $e->getMessage());
    $_SESSION['flash'] = "Ошибка оформления заказа";
    header("Location: cart.php");
    exit;
}

header("Location: order_success.php?order_id=" . $order_id);
exit;
