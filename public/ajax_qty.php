<?php
require "../app/bootstrap.php";
header('Content-Type: application/json');

if (!isLogged()) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'auth']));
}
checkCsrf();

$user_id    = $_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);
$action     = $_POST['action'] ?? '';

if ($product_id <= 0 || !in_array($action, ['plus', 'minus'], true)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'bad_request']));
}

$stmt = $pdo->prepare("
    SELECT COALESCE(c.qty, 0) AS qty, p.stock
    FROM products p
    LEFT JOIN cart c ON c.product_id = p.id AND c.user_id = ?
    WHERE p.id = ?
");
$stmt->execute([$user_id, $product_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    exit(json_encode(['success' => false, 'error' => 'not_found']));
}

$current = (int)$row['qty'];
$stock   = (int)$row['stock'];
$new_qty = $action === 'plus' ? min($current + 1, $stock) : max($current - 1, 0);

if ($new_qty > 0) {
    $pdo->prepare("REPLACE INTO cart (user_id, product_id, qty) VALUES (?, ?, ?)")
        ->execute([$user_id, $product_id, $new_qty]);
} else {
    $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")
        ->execute([$user_id, $product_id]);
}

echo json_encode(['success' => true, 'new_qty' => $new_qty, 'max' => $stock]);
