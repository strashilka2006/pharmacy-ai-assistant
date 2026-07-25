<?php
// ajax_qty.php
require "../app/bootstrap.php";

if (!isLogged()) {
    exit(json_encode(['success' => false]));
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$action = $_POST['action']; // 'plus' или 'minus'

$stmt = $pdo->prepare("SELECT qty FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$current = $stmt->fetchColumn() ?: 0;

if ($action === 'plus') {
    $new_qty = $current + 1;
} elseif ($action === 'minus' && $current > 0) {
    $new_qty = $current - 1;
}

if ($new_qty > 0) {
    $stmt = $pdo->prepare("REPLACE INTO cart (user_id, product_id, qty) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $product_id, $new_qty]);
} else {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
}

echo json_encode(['success' => true, 'new_qty' => $new_qty]);
exit;