<?php
require "../app/bootstrap.php";
if (!isLogged()) exit;

$cart_id = (int)($_GET["id"] ?? 0);  // теперь id — это product_id, а не cart.id
$user_id = $_SESSION["user_id"];

if ($cart_id > 0) {
    // Ищем запись в корзине
    $stmt = $pdo->prepare("SELECT cart.qty, products.stock FROM cart JOIN products ON products.id = cart.product_id WHERE cart.user_id = ? AND cart.product_id = ?");
    $stmt->execute([$user_id, $cart_id]);
    $item = $stmt->fetch();

    if ($item && $item['qty'] < $item['stock']) {
        $pdo->prepare("UPDATE cart SET qty = qty + 1 WHERE user_id = ? AND product_id = ?")->execute([$user_id, $cart_id]);
    }
}

$return = $_GET['return'] ?? 'cart.php';
header("Location: $return");
exit;