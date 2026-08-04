<?php
require __DIR__ . "/../app/bootstrap.php";
requireLogin();

$order_id = (int)($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT status, pay_url FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: index.php");
    exit;
}

if (in_array($order['status'], ['paid', 'cancelled'], true)) {
    header("Location: order_view.php?id=$order_id");
    exit;
}

$pay_url = (string)$order['pay_url'];

$allowedHosts = ['yoomoney.ru', 'www.yoomoney.ru'];
$parts        = parse_url($pay_url);

$urlIsValid = $pay_url !== ''
    && isset($parts['scheme'], $parts['host'])
    && strtolower($parts['scheme']) === 'https'
    && in_array(strtolower($parts['host']), $allowedHosts, true);

if (!$urlIsValid) {
    $_SESSION['flash'] = "Ссылка на оплату недоступна. Свяжитесь с поддержкой.";
    header("Location: order_view.php?id=$order_id");
    exit;
}

$pageTitle = "Оплата заказа №$order_id";
require __DIR__ . "/layout/header.php";
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">

            <div class="card shadow p-4">
                <h2 class="mb-2">Оплата заказа №<?= (int)$order_id ?></h2>
                <p class="text-muted mb-4">Отсканируйте QR-код или нажмите кнопку для оплаты</p>

                <div id="qrcode" class="d-flex justify-content-center mb-4"></div>

                <a href="<?= htmlspecialchars($pay_url, ENT_QUOTES, 'UTF-8') ?>"
                   class="btn btn-success btn-lg w-100 mb-3"
                   rel="noopener">
                    💳 Перейти к оплате
                </a>
                <a href="cart.php" class="btn btn-outline-secondary w-100">Назад в корзину</a>
            </div>

        </div>
    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    var payUrl = <?= json_encode($pay_url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    new QRCode(document.getElementById("qrcode"), {
        text: payUrl,
        width: 220,
        height: 220,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
</script>

<?php require __DIR__ . "/layout/footer.php"; ?>
