<?php
require "../app/bootstrap.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

$orderId = (int)($_GET['id'] ?? 0);
$userId  = $_SESSION['user_id'];

if (!$orderId) {
    die("Нет ID заказа");
}

// Берём заказ + юзера
$stmt = $pdo->prepare("
    SELECT 
        o.id, o.user_id, o.total, o.status, o.name, o.phone, o.address,
        o.created_at, o.updated_at,
        u.name AS user_name, u.phone AS user_phone, u.address AS user_address
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");

$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Заказ не найден или доступ запрещён.");
}

// Мапа шагов
$statusMap = [
    'new'              => 0,
    'processing'       => 1,
    'shipped'          => 2,
    'at_hub'           => 3,
    'sent_to_pickup'   => 4,
    'ready_for_pickup' => 5,
    'delivered'        => 5,
    'cancelled'        => -1
];

// Текущий шаг
$currentStep = $statusMap[$order['status']] ?? 0;

$progressPercent = ($currentStep >= 0) ? ($currentStep / 5) * 100 : 0;

// Отмена заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel']) && $order['status'] === 'new') {
    checkCsrf();
    $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?")
        ->execute([$orderId]);

    $order['status'] = "cancelled";
    $currentStep = -1;

    header("Location: order_view.php?id=" . (int)$orderId);
    exit;
}

// Товары
$stmt = $pdo->prepare("
    SELECT 
        oi.qty, oi.price, p.name,
        COALESCE(p.image, p.photo) as image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

require "layout/header.php";
?>

<main class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">

            <a href="personal.php" class="btn btn-outline-secondary btn-sm mb-4">← Назад</a>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white py-4">
                    <h2 class="h4 mb-0">
                        Заказ №<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
                    </h2>
                    <div class="text-muted small">
                        Оформлен: <?= date('d.m.Y в H:i', strtotime($order['created_at'])) ?>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">

                    <!-- Трекер -->
                    <?php 
                    $labels = [
                        "Товар сформирован",
                        "Собран на складе",
                        "Отправлен в СЦ",
                        "Прибыл в СЦ",
                        "В пути к ПВЗ",
                        "Готов к выдаче"
                    ];
                    ?>

                    <div class="order-tracker my-5" style="--progress: <?= $progressPercent ?>%;">
                        <style>
                            .order-tracker{ max-width:1000px; margin:auto; padding:2rem 0; }
                            .tracker-steps{ display:flex; justify-content:space-between; position:relative; }
                            .tracker-steps::before{ content:''; position:absolute; top:24px; left:0; right:0; height:6px; background:#ddd; }
                            .tracker-steps::after{ content:''; position:absolute; top:24px; left:0; height:6px; width:var(--progress); background:#000; transition:width .8s; }
                            .tracker-step{ text-align:center; z-index:2; }
                            .step-circle{ width:52px; height:52px; border-radius:50%; border:5px solid #ddd; background:#fff;
                                display:flex; justify-content:center; align-items:center; font-size:1.3rem; margin:auto 0 10px; }
                            .completed .step-circle{ background:#000; color:#fff; border-color:#000; }
                            .active .step-circle{ border-color:#000; animation:pulseBorder 2s infinite; }
                            .pulse-dot{ width:18px; height:18px; border-radius:50%; background:#000; animation:pulseAnim 1.8s infinite; }
                            @keyframes pulseAnim{0%{box-shadow:0 0 0 0 rgba(0,0,0,.4);}70%{box-shadow:0 0 0 16px rgba(0,0,0,0);}100%{box-shadow:0 0 0 0 rgba(0,0,0,0);} }
                            @keyframes pulseBorder{0%,100%{border-color:#000;}50%{border-color:#444;} }
                        </style>

                        <div class="tracker-steps">
                        <?php foreach ($labels as $i => $label): ?>
                            <?php
                                $completed = ($currentStep > $i);
                                $active    = ($currentStep === $i);
                            ?>
                            <div class="tracker-step <?= $completed?'completed':'' ?> <?= $active?'active':'' ?>">
                                <div class="step-circle">
                                    <?php if ($completed): ?>✓
                                    <?php elseif ($active): ?><div class="pulse-dot"></div>
                                    <?php else: ?><?= $i+1 ?>
                                    <?php endif; ?>
                                </div>
                                <div class="step-label"><?= $label ?></div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($order['status'] === 'cancelled'): ?>
                        <div class="alert alert-danger text-center fs-5">Заказ отменён</div>
                    <?php endif; ?>

                    <hr class="my-5">

                    <!-- Получатель -->
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-2">Получатель</h5>
                            <p><strong>ФИО:</strong> <?= htmlspecialchars($order['name'] ?: $order['user_name']) ?></p>
                            <p><strong>Телефон:</strong> <?= htmlspecialchars($order['phone'] ?: $order['user_phone']) ?></p>
                        </div>

                        <div class="col-md-6">
                            <h5 class="fw-bold mb-2">Доставка</h5>
                            <p><strong>Адрес:</strong><br><?= htmlspecialchars($order['address'] ?: $order['user_address']) ?></p>
                        </div>
                    </div>

                    <hr class="my-5">

                    <!-- Товары -->
                    <h4 class="mb-4">Товары</h4>

                    <?php foreach ($items as $item): ?>
                        <div class="d-flex gap-4 align-items-center border-bottom pb-4 mb-4">
                            <img src="<?= htmlspecialchars($item['image']) ?>" style="width:100px;height:100px;object-fit:cover;" class="rounded">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                <div class="text-muted small">Цена: <?= number_format($item['price'], 0, '', ' ') ?> ₽</div>
                            </div>
                            <div class="text-end">
                                <strong><?= $item['qty'] ?> шт.</strong>
                            </div>
                            <div class="text-end ms-4">
                                <strong><?= number_format($item['qty'] * $item['price'], 0, '', ' ') ?> ₽</strong>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Итог -->
                    <div class="text-end mt-5 pt-4 border-top">
                        <h3>Итого: <strong><?= number_format($order['total'], 0, '', ' ') ?> ₽</strong></h3>
                    </div>

                    <!-- Кнопка отмены -->
                    <?php if ($order['status'] === 'new'): ?>
                        <div class="text-center mt-5">
                            <form method="post">
                                <?= csrfField() ?>
                                <button name="cancel" class="btn btn-lg btn-outline-dark">Отменить заказ</button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="text-center mt-4">
                <button onclick="startDemo()" class="btn btn-sm btn-outline-dark">
                    Показать анимацию доставки
                </button>
                <div class="text-muted small mt-2">
                    Демонстрация трекера: реальные статусы обновляются по таймеру
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function startDemo() {
    let step = <?= $currentStep ?>;
    const total = 5;

    const interval = setInterval(() => {
        if (step < total) step++;
        else return clearInterval(interval);

        const progress = Math.round((step / total) * 100);
        document.querySelector('.order-tracker').style.setProperty('--progress', progress + '%');

        document.querySelectorAll('.tracker-step').forEach((el, i) => {
            el.classList.toggle('completed', i < step);
            el.classList.toggle('active', i === step);
        });
    }, 1000);
}
</script>

<?php require "layout/footer.php"; ?>
