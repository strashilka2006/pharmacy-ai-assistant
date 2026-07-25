<?php
require "../app/bootstrap.php";

$order_id  = (int)($_GET['order_id'] ?? 0);
$shopId    = 'ВАШ_SHOP_ID';
$secretKey = 'test_ВАШТЕСТОВЫЙКЛЮЧ';

if (!$order_id) { header("Location: index.php"); exit; }

// Берём payment_id из БД
$stmt = $pdo->prepare("SELECT payment_id FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$status = 'unknown';

if (!empty($order['payment_id'])) {
    // Проверяем статус платежа в ЮКассе
    $ch = curl_init('https://api.yookassa.ru/v3/payments/' . $order['payment_id']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERPWD        => $shopId . ':' . $secretKey,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $status = $res['status'] ?? 'unknown';

    if ($status === 'succeeded') {
        $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?")
            ->execute([$order_id]);
    } elseif ($status === 'canceled') {
        $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")
            ->execute([$order_id]);
    }
}

header("Location: order_success.php?order_id=$order_id&payment_status=$status");
exit;