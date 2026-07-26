<?php
require "../app/bootstrap.php";
if (!isLogged()) { header("Location: " . BASE_URL . "/public/login.php"); exit; }

$order_id = (int)($_GET['order_id'] ?? 0);
$pay_url  = htmlspecialchars(urldecode($_GET['pay_url'] ?? ''));

if (!$order_id || !$pay_url) { header("Location: index.php"); exit; }

require "layout/header.php";
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">

            <div class="card shadow p-4">
                <h2 class="mb-2">Оплата заказа №<?= $order_id ?></h2>
                <p class="text-muted mb-4">Отсканируйте QR-код или нажмите кнопку для оплаты</p>

                <!-- QR-код генерируется прямо в браузере -->
                <div id="qrcode" class="d-flex justify-content-center mb-4"></div>

                <a href="<?= $pay_url ?>" class="btn btn-success btn-lg w-100 mb-3">
                    💳 Перейти к оплате
                </a>
                <a href="cart.php" class="btn btn-outline-secondary w-100">Назад в корзину</a>
            </div>

        </div>
    </div>
</main>

<!-- Библиотека QR-кода (CDN, без установки) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById("qrcode"), {
        text: "<?= $pay_url ?>",
        width: 220,
        height: 220,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
</script>

<?php require "layout/footer.php"; ?>