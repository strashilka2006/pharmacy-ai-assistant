<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../app/bootstrap.php";

if (!isLogged()) {
    header("Location: /apteka/public/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Данные пользователя для формы
$stmt = $pdo->prepare("SELECT name, phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Товары в корзине
$stmt = $pdo->prepare("
    SELECT cart.id AS cart_id, cart.qty, 
           products.id AS product_id, products.name, products.price, products.image
    FROM cart
    JOIN products ON products.id = cart.product_id
    WHERE cart.user_id = ?
    ORDER BY cart.added_at DESC
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['qty'];
}

// Обработка оформления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (empty($items)) {
        $error = "Корзина пуста";
    } else {
        $name    = trim($_POST['name'] ?? $user['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? $user['phone'] ?? '');
        $address = trim($_POST['address'] ?? $user['address'] ?? '');

        if ($name && $phone && $address) {
            $pdo->beginTransaction();
            try {
                // Создаём заказ
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, name, phone, address) 
                                       VALUES (?, ?, 'new', ?, ?, ?)");
                $stmt->execute([$user_id, $total, $name, $phone, $address]);
                $order_id = $pdo->lastInsertId();

                // Переносим товары в order_items
                foreach ($items as $item) {
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) 
                                           VALUES (?, ?, ?, ?)");
                    $stmt->execute([$order_id, $item['product_id'], $item['qty'], $item['price']]);
                }

                // Очищаем корзину
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$user_id]);

                $pdo->commit();

                // === ЮКАССА: создаём платёж ===
                $shopId    = '1343344';    // из личного кабинета → Настройки
                $secretKey = 'test_Pkb0BgtKZneMvQ-JIB3-7FR4vBTYlGhO_EwgAp2YFIE'; // Интеграция → Ключи API

                $paymentData = json_encode([
                    'amount' => [
                        'value'    => number_format($total, 2, '.', ''),
                        'currency' => 'RUB',
                    ],
                    'capture' => true,
                    'confirmation' => [
                        'type'       => 'redirect',
                        'return_url' => 'http://localhost/apteka/public/payment_return.php?order_id=' . $order_id,
                    ],
                    'description' => 'Заказ №' . $order_id . ' — Аптека',
                    'metadata'    => ['order_id' => $order_id],
                ], JSON_UNESCAPED_UNICODE);

                $ch = curl_init('https://api.yookassa.ru/v3/payments');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERPWD        => $shopId . ':' . $secretKey,
                    CURLOPT_HTTPHEADER     => [
                        'Content-Type: application/json',
                        'Idempotence-Key: ' . uniqid('order_' . $order_id . '_', true),
                    ],
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $paymentData,
                ]);
                $response = json_decode(curl_exec($ch), true);
                curl_close($ch);

                if (!empty($response['confirmation']['confirmation_url'])) {
                    // Сохраняем payment_id от ЮКассы в заказ
                    $stmt = $pdo->prepare("UPDATE orders SET payment_id = ? WHERE id = ?");
                    $stmt->execute([$response['id'], $order_id]);

                    // Редирект на страницу оплаты с QR-кодом
                    $confirmUrl = urlencode($response['confirmation']['confirmation_url']);
                    header("Location: payment_pending.php?order_id=$order_id&pay_url=$confirmUrl");
                    exit;
                } else {
                    // Если API не ответил — откат заказа
                    $pdo->beginTransaction();
                    $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$order_id]);
                    $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$order_id]);
                    $pdo->commit();
                    $error = "Ошибка при создании платежа. Попробуйте ещё раз.";
                }
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Произошла ошибка при оформлении заказа. Попробуйте позже.";
            }
        } else {
            $error = "Заполните все обязательные поля";
        }
    }
}

require "layout/header.php";
?>

<main class="container py-5">
    <h1 class="mb-5">Корзина и оформление заказа</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="text-center py-5">
            <h3 class="text-muted">Корзина пуста</h3>
            <a href="index.php" class="btn btn-outline-custom mt-3">Перейти в каталог</a>
        </div>
    <?php else: ?>
        <div class="row g-5">
            <!-- Список товаров -->
            <div class="col-lg-8">
                <h3 class="mb-4">Ваши товары</h3>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Фото</th>
                                <th>Товар</th>
                                <th class="text-center">Количество</th>
                                <th class="text-end">Цена</th>
                                <th class="text-end">Сумма</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php $sum = $item['price'] * $item['qty']; ?>
                                <tr>
                                    <td>
                                        <img src="<?= htmlspecialchars($item['image'] ?: 'https://via.placeholder.com/100') ?>"
                                             class="rounded shadow-sm" width="90" height="90" style="object-fit: contain;">
                                    </td>
                                    <td class="fw-600"><?= htmlspecialchars($item['name']) ?></td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-custom btn-sm px-3 qty-change-cart"
                                                    data-id="<?= $item['product_id'] ?>" data-action="minus">–</button>
                                            <span class="fw-bold fs-5 mx-3"><?= $item['qty'] ?></span>
                                            <button type="button" class="btn btn-outline-custom btn-sm px-3 qty-change-cart"
                                                    data-id="<?= $item['product_id'] ?>" data-action="plus">+</button>
                                        </div>
                                    </td>
                                    <td class="text-end fw-600"><?= number_format($item['price'], 0, '', ' ') ?> ₽</td>
                                    <td class="text-end fw-bold fs-5"><?= number_format($sum, 0, '', ' ') ?> ₽</td>
                                    <td>
                                        <a href="remove_from_cart.php?id=<?= $item['cart_id'] ?>"
                                           class="btn btn-outline-danger btn-sm"
                                           onclick="return confirm('Удалить товар?')">Удалить</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <h2>Итого: <?= number_format($total, 0, '', ' ') ?> ₽</h2>
                </div>
            </div>

            <!-- Форма оформления -->
            <div class="col-lg-4">
                <h3 class="mb-4">Данные для доставки</h3>
                <div class="card p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-600">ФИО *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Телефон *</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Адрес доставки *</label>
                            <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="place_order" class="btn btn-primary-custom w-100 py-3">
                            Оформить заказ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require "layout/footer.php"; ?>