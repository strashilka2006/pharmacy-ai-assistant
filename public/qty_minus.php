<?php
require "../app/bootstrap.php";
if (!isLogged()) exit;

$product_id = (int)($_GET["id"] ?? 0);
$user_id = $_SESSION["user_id"];

if ($product_id > 0) {
    $stmt = $pdo->prepare("SELECT qty FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch();

    if ($item) {
        if ($item['qty'] > 1) {
            $pdo->prepare("UPDATE cart SET qty = qty - 1 WHERE user_id = ? AND product_id = ?")->execute([$user_id, $product_id]);
        } else {
            $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$user_id, $product_id]);
        }
    }
}

$return = $_GET['return'] ?? 'cart.php';
header("Location: $return");
exit;