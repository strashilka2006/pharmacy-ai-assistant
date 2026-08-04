<?php
require "../app/bootstrap.php";

// Обязательно залогинен, иначе — в логин
if (!isLogged()) {
    header("Location: " . BASE_URL . "/public/login.php?return=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Товар не найден");
}

$product_id = (int)$_GET["id"];

// Проверяем, есть ли такой товар
$stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Товар не найден");
}

if ($product["stock"] <= 0) {
    header("Location: product.php?id=$product_id");
    $_SESSION["flash"] = "Товара нет в наличии";
    exit;
}

// Ищем, есть ли уже в корзине
$stmt = $pdo->prepare("SELECT id, qty FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cart_item) {
    // Уже есть — просто увеличиваем количество
    $new_qty = $cart_item["qty"] + 1;
    if ($new_qty > $product["stock"]) {
        $new_qty = $product["stock"];
    }
    $stmt = $pdo->prepare("UPDATE cart SET qty = ? WHERE id = ?");
    $stmt->execute([$new_qty, $cart_item["id"]]);
} else {
    // Добавляем новую строку
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, qty) VALUES (?, ?, 1)");
    $stmt->execute([$user_id, $product_id]);
}

// Флеш-сообщение (можно будет вывести в header.php или шапке)
$_SESSION["flash"] = "Товар «{$product["name"]}» добавлен в корзину";

// Куда редиректим — на товар или сразу в корзину
$redirect = $_GET["redirect"] ?? "product.php?id=$product_id";
if (!preg_match('~^[a-z0-9_]+\.php(\?[^\s]*)?$~i', $redirect)) {
    $redirect = "product.php?id=$product_id";
}
header("Location: $redirect");
exit;
