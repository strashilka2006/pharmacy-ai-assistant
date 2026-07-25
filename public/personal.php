<?php
require "../app/bootstrap.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /apteka/public/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

function getOrderStatusText($status) {
    $s = trim((string)$status);
    if ($s === '' || $s === null) return 'Товар сформирован';
    return match(strtolower($s)) {
        'new'             => 'Товар сформирован',
        'processing'      => 'Собран на складе',
        'shipped'         => 'Отправлен в сортировочный центр',
        'at_hub'          => 'Прибыл в сортировочный центр',
        'sent_to_pickup'  => 'Отправлен в пункт выдачи',
        'ready_for_pickup', 'delivered' => 'Готов к выдаче',
        'cancelled'       => 'Отменён',
        default           => 'В обработке'
    };
}

function getOrderStatusColor($status) {
    $s = trim((string)$status);
    return match(strtolower($s)) {
        'cancelled'                     => ['bg' => '#fff5f5', 'border' => '#f5c0c0', 'color' => '#c0392b'],
        'ready_for_pickup', 'delivered' => ['bg' => '#f3fbea', 'border' => '#a6d175', 'color' => '#2d5a1b'],
        default                         => ['bg' => '#f8fef3', 'border' => '#d4edaa', 'color' => '#555']
    };
}

function sklonenie($number, $forms) {
    $n = abs($number);
    if ($n % 10 == 1 && $n % 100 != 11) return $forms[0];
    if ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20)) return $forms[1];
    return $forms[2];
}

function mb_ucfirst($str) {
    return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
}

$stmt = $pdo->prepare("SELECT name, phone, avatar, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    $file = $_FILES['avatar'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        $filename = time() . "_" . bin2hex(random_bytes(8)) . ".$ext";
        $path = __DIR__ . "/uploads/" . $filename;
        if (move_uploaded_file($file['tmp_name'], $path)) {
            if (!empty($user['avatar']) && $user['avatar'] !== 'default.png' && file_exists(__DIR__ . "/uploads/" . $user['avatar'])) {
                @unlink(__DIR__ . "/uploads/" . $user['avatar']);
            }
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$filename, $user_id]);
            $user['avatar'] = $filename;
        }
    }
}

$products = [];
if (!empty($_COOKIE['viewed'])) {
    $ids = array_filter(explode(',', $_COOKIE['viewed']));
    $ids = array_slice(array_reverse($ids), 0, 8);
    if ($ids) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

updateUserOrderStatuses($pdo, $user_id);

$stmt = $pdo->prepare("SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка сохранения профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $address, $user_id]);

    // Обновляем данные в массиве
    $user['name']    = $name;
    $user['phone']   = $phone;
    $user['address'] = $address;

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


$pageTitle = "Профиль";
require "layout/header.php";
?>

<style>
/* ── общий контейнер страницы ── */
.prof-wrap {
    max-width: 1380px;
    margin: 0 auto;
    padding: 56px 24px 80px;
}

/* ── заголовок секции ── */
.reg-header {
    border-bottom: 1.5px solid #d4edaa;
    padding-bottom: 28px;
    margin-bottom: 40px;
}
.reg-header .label {
    font-family: 'Fragment Mono', monospace;
    font-size: 11px;
    color: #7AAD3F;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 12px;
}
.reg-header h1 {
    font-family: 'Fragment Mono', monospace;
    font-size: clamp(1.5rem, 4vw, 2rem);
    font-weight: 400;
    color: #1a3a0a;
    line-height: 1.2;
    letter-spacing: -0.5px;
    margin: 0;
}

/* ── раздел-блок ── */
.prof-section {
    border: 1.5px solid #d4edaa;
    border-radius: 12px;
    background: #fff;
    padding: 28px 28px 24px;
    margin-bottom: 28px;
}
.prof-section-title {
    font-family: 'Fragment Mono', monospace;
    font-size: 11px;
    color: #7AAD3F;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 20px;
}

/* ── аватар ── */
.prof-avatar-wrap {
    display: flex;
    align-items: center;
    gap: 28px;
    flex-wrap: wrap;
}
.prof-avatar-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #d4edaa;
    flex-shrink: 0;
}
.prof-avatar-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
    min-width: 200px;
}

/* ── поля формы (из login) ── */
.reg-field {
    margin-bottom: 20px;
}
.reg-field label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #2d5a1b;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
    font-family: 'Fragment Mono', monospace;
}
.reg-field input,
.reg-field textarea {
    width: 100%;
    border: 1.5px solid #d4edaa;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    color: #1a1a1a;
    background: #f8fef3;
    outline: none;
    transition: border-color .18s, box-shadow .18s;
    resize: vertical;
}
.reg-field input:focus,
.reg-field textarea:focus {
    border-color: #7AAD3F;
    box-shadow: 0 0 0 3px rgba(122,173,63,.12);
}
.reg-field input::placeholder,
.reg-field textarea::placeholder { color: #bbb; }

/* ── инфо-строки (просмотр, не редактирование) ── */
.prof-info-row {
    display: flex;
    gap: 16px;
    padding: 10px 0;
    border-bottom: 1px solid #eef6e0;
    font-size: 14px;
    align-items: flex-start;
}
.prof-info-row:last-child { border-bottom: none; }
.prof-info-key {
    font-family: 'Fragment Mono', monospace;
    font-size: 11px;
    color: #7AAD3F;
    letter-spacing: 1px;
    text-transform: uppercase;
    min-width: 100px;
    padding-top: 2px;
}
.prof-info-val {
    color: #1a1a1a;
    flex: 1;
}

/* ── кнопки (из login) ── */
.reg-btn {
    background: #2d5a1b;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 13px 20px;
    font-family: 'Fragment Mono', monospace;
    font-size: 13px;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: background .18s;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}
.reg-btn:hover { background: #39572e; color: #fff; }
.reg-btn.outline {
    background: transparent;
    color: #2d5a1b;
    border: 1.5px solid #d4edaa;
}
.reg-btn.outline:hover { background: #f3fbea; }
.reg-btn.danger {
    background: transparent;
    color: #c0392b;
    border: 1.5px solid #f5c0c0;
}
.reg-btn.danger:hover { background: #fff5f5; }
.reg-btn.sm {
    padding: 8px 16px;
    font-size: 11px;
    border-radius: 8px;
}

/* ── файловый инпут ── */
.reg-file-input {
    width: 100%;
    border: 1.5px solid #d4edaa;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    color: #555;
    background: #f8fef3;
    cursor: pointer;
}

/* ── разделитель (из login) ── */
.reg-divider {
    border: none;
    border-top: 1px solid #dff0c0;
    margin: 24px 0;
}

/* ── подзаголовок секции ── */
.prof-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

/* ── таблица заказов ── */
.orders-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.orders-table th {
    font-family: 'Fragment Mono', monospace;
    font-size: 10px;
    color: #7AAD3F;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 0 12px 12px;
    border-bottom: 1.5px solid #d4edaa;
    font-weight: 400;
    text-align: left;
}
.orders-table td {
    padding: 14px 12px;
    border-bottom: 1px solid #eef6e0;
    vertical-align: middle;
    color: #1a1a1a;
}
.orders-table tr:last-child td { border-bottom: none; }
.orders-table tr:hover td { background: #f8fef3; }
.orders-table tr { cursor: pointer; transition: background .15s; }

.order-id {
    font-family: 'Fragment Mono', monospace;
    font-size: 13px;
    color: #2d5a1b;
    text-decoration: none;
    font-weight: 600;
}
.order-id:hover { text-decoration: underline; }

.order-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-family: 'Fragment Mono', monospace;
    border: 1.5px solid;
    letter-spacing: 0.3px;
}

.order-sum {
    font-family: 'Fragment Mono', monospace;
    font-size: 14px;
    font-weight: 600;
    color: #1a3a0a;
}

/* ── пустой стейт ── */
.prof-empty {
    text-align: center;
    padding: 40px 20px;
    color: #aaa;
    font-size: 13px;
}
.prof-empty-icon {
    font-size: 2.5rem;
    margin-bottom: 12px;
    display: block;
    color: #d4edaa;
}

/* ── карточки «вы смотрели» ── */
.viewed-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
}
.viewed-card {
    border: 1.5px solid #d4edaa;
    border-radius: 10px;
    background: #fff;
    text-decoration: none;
    color: inherit;
    overflow: hidden;
    transition: box-shadow .18s, border-color .18s;
    display: flex;
    flex-direction: column;
}
.viewed-card:hover {
    border-color: #7AAD3F;
    box-shadow: 0 4px 16px rgba(122,173,63,.15);
}
.viewed-card img {
    width: 100%;
    height: 130px;
    object-fit: contain;
    background: #f8fef3;
    padding: 12px;
}
.viewed-card-body {
    padding: 10px 12px 14px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.viewed-card-name {
    font-size: 12px;
    color: #333;
    line-height: 1.4;
    margin-bottom: 6px;
    flex: 1;
}
.viewed-card-price {
    font-family: 'Fragment Mono', monospace;
    font-size: 13px;
    font-weight: 600;
    color: #2d5a1b;
}
/* ── карта адреса ── */
.addr-map-container {
    margin-top: 14px;
    border-radius: 10px;
    overflow: hidden;
    border: 1.5px solid #d4edaa;
    display: none;
}
#addr-map {
    width: 100%;
    height: 320px;
}
.addr-selected-box {
    display: none;
    background: #f3fbea;
    border: 1.5px solid #a6d175;
    border-radius: 8px;
    padding: 12px 16px;
    margin-top: 12px;
    font-size: 13px;
    color: #2d5a1b;
    font-family: 'Inter', sans-serif;
    align-items: flex-start;
    gap: 10px;
}
.addr-selected-box span { flex: 1; line-height: 1.5; }
.addr-reset-btn {
    font-family: 'Fragment Mono', monospace;
    font-size: 11px;
    color: #7AAD3F;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    text-decoration: underline;
    white-space: nowrap;
    flex-shrink: 0;
}
.addr-details {
    display: none;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 12px;
}
.addr-details .reg-field { margin-bottom: 0; }
.addr-map-hint {
    font-size: 11px;
    color: #aaa;
    font-family: 'Fragment Mono', monospace;
    margin-top: 8px;
    text-align: center;
    letter-spacing: 0.5px;
}
</style>

<main style="background:#f8fef3; min-height:calc(100vh - 58px);">
<div class="prof-wrap">

    <!-- Шапка страницы -->
    <div class="reg-header">
        <div class="label">Аккаунт</div>
        <h1>ЛИЧНЫЙ КАБИНЕТ</h1>
    </div>

    <!-- Аватар -->
    <div class="prof-section">
        <div class="prof-section-title">Фото профиля</div>
        <div class="prof-avatar-wrap">
            <img src="/apteka/public/uploads/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>"
                 class="prof-avatar-img" alt="Аватар">
            <form method="post" enctype="multipart/form-data" class="prof-avatar-form">
                <input type="file" name="avatar" accept="image/*" class="reg-file-input">
                <button type="submit" class="reg-btn outline">Сохранить фото</button>
            </form>
        </div>
    </div>

    <!-- Данные пользователя -->
    <div class="prof-section">
        <div class="prof-section-header">
            <div class="prof-section-title" style="margin:0;">Ваши данные</div>
            <?php if (!isset($_GET['edit'])): ?>
                <a href="?edit=1" class="reg-btn outline sm">Редактировать</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['edit'])): ?>
            <form method="POST">
                <div class="reg-field">
                    <label>ФИО</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required placeholder="Иванов Иван Иванович">
                </div>
                <div class="reg-field">
                    <label>Телефон</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+7 (999) 999-99-99">
                </div>
                <?php
                // Разбиваем сохранённый адрес на части
                $savedAddr  = $user['address'] ?? '';
                $savedApt   = '';
                $savedFloor = '';
                // Парсим "..., кв. 12, эт. 3"
                if (preg_match('/^(.*?),\s*кв\.\s*(.+?),\s*эт\.\s*(.+)$/u', $savedAddr, $m)) {
                    $savedAddr  = trim($m[1]);
                    $savedApt   = trim($m[2]);
                    $savedFloor = trim($m[3]);
                } elseif (preg_match('/^(.*?),\s*кв\.\s*(.+)$/u', $savedAddr, $m)) {
                    $savedAddr = trim($m[1]);
                    $savedApt  = trim($m[2]);
                }
                ?>

                <div class="reg-field">
                    <label>Адрес доставки — выберите на карте</label>

                    <!-- Suggest-поле -->
                    <input type="text"
                        id="addr-suggest"
                        placeholder="Начните вводить улицу и дом..."
                        autocomplete="off"
                        value="<?= htmlspecialchars($savedAddr) ?>">

                    <!-- Карта -->
                    <div class="addr-map-container" id="addr-map-container">
                        <div id="addr-map"></div>
                        <div class="addr-map-hint">Кликните на карту или перетащите метку для уточнения</div>
                    </div>

                    <!-- Выбранный адрес -->
                    <div class="addr-selected-box" id="addr-selected-box"
                        <?= $savedAddr ? 'style="display:flex;"' : '' ?>>
                        <span id="addr-selected-text"><?= htmlspecialchars($savedAddr) ?></span>
                        <button type="button" class="addr-reset-btn" onclick="addrReset()">Изменить</button>
                    </div>

                    <!-- Квартира + этаж -->
                    <div class="addr-details" id="addr-details"
                        <?= $savedAddr ? 'style="display:grid;"' : '' ?>>
                        <div class="reg-field">
                            <label>Квартира / офис</label>
                            <input type="text" id="addr-apt" placeholder="12" value="<?= htmlspecialchars($savedApt) ?>">
                        </div>
                        <div class="reg-field">
                            <label>Этаж</label>
                            <input type="text" id="addr-floor" placeholder="3" value="<?= htmlspecialchars($savedFloor) ?>">
                        </div>
                    </div>

                    <!-- Скрытый input, который уходит в форму -->
                    <input type="hidden" name="address" id="addr-full"
                        value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                </div>
                <hr class="reg-divider">
                <div style="display:flex;gap:10px;">
                    <button type="submit" name="save_profile" class="reg-btn" style="flex:1;">Сохранить изменения →</button>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="reg-btn outline">Отмена</a>
                </div>
            </form>

        <?php else: ?>
            <div class="prof-info-row">
                <span class="prof-info-key">ФИО</span>
                <span class="prof-info-val"><?= htmlspecialchars($user['name'] ?? '—') ?></span>
            </div>
            <div class="prof-info-row">
                <span class="prof-info-key">Email</span>
                <span class="prof-info-val"><?= htmlspecialchars($_SESSION['email'] ?? '—') ?></span>
            </div>
            <div class="prof-info-row">
                <span class="prof-info-key">Телефон</span>
                <span class="prof-info-val"><?= htmlspecialchars($user['phone'] ?: 'Не указан') ?></span>
            </div>
            <div class="prof-info-row">
                <span class="prof-info-key">Адрес</span>
                <span class="prof-info-val"><?= htmlspecialchars($user['address'] ?: 'Не указан') ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Мои заказы -->
    <div class="prof-section">
        <div class="prof-section-title">Мои заказы</div>

        <?php if (empty($orders)): ?>
            <div class="prof-empty">
                <span class="prof-empty-icon">🧺</span>
                У вас пока нет заказов
                <br><br>
                <a href="/apteka/public/" class="reg-btn sm">Перейти в каталог →</a>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Заказ</th>
                            <th>Дата</th>
                            <th>Товаров / Сумма</th>
                            <th>Статус</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order):
                        $stmt = $pdo->prepare("SELECT SUM(qty) as total_qty, COUNT(*) as positions FROM order_items WHERE order_id = ?");
                        $stmt->execute([$order['id']]);
                        $items     = $stmt->fetch(PDO::FETCH_ASSOC);
                        $totalQty  = $items['total_qty'] ?? 1;
                        $positions = $items['positions'] ?? 1;
                        $displayQty = $totalQty == $positions ? $positions : $totalQty;
                        $sc = getOrderStatusColor($order['status']);
                    ?>
                        <tr onclick="window.location='order_view.php?id=<?= $order['id'] ?>'">
                            <td>
                                <a href="order_view.php?id=<?= $order['id'] ?>" class="order-id" onclick="event.stopPropagation();">
                                    #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
                                </a>
                            </td>
                            <td style="color:#888;font-size:12px;">
                                <?= date('d.m.Y', strtotime($order['created_at'])) ?><br>
                                <?= date('H:i', strtotime($order['created_at'])) ?>
                            </td>
                            <td>
                                <div style="font-size:11px;color:#aaa;margin-bottom:2px;">
                                    <?= $displayQty ?> <?= sklonenie($displayQty, ['товар','товара','товаров']) ?>
                                </div>
                                <div class="order-sum"><?= number_format($order['total'], 0, '', ' ') ?> ₽</div>
                            </td>
                            <td>
                                <span class="order-badge"
                                      style="background:<?= $sc['bg'] ?>;border-color:<?= $sc['border'] ?>;color:<?= $sc['color'] ?>;">
                                    <?= getOrderStatusText($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_view.php?id=<?= $order['id'] ?>"
                                   class="reg-btn outline sm"
                                   onclick="event.stopPropagation();">Подробнее</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="text-align:center;margin-top:14px;font-size:12px;color:#bbb;font-family:'Fragment Mono',monospace;">
                <?= count($orders) ?> <?= sklonenie(count($orders), ['заказ','заказа','заказов']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Вы смотрели -->
    <?php if ($products): ?>
    <div class="prof-section">
        <div class="prof-section-title">Вы смотрели</div>
        <div class="viewed-grid">
            <?php foreach ($products as $p): ?>
                <a href="product.php?id=<?= $p['id'] ?>" class="viewed-card">
                    <img src="<?= htmlspecialchars($p['image'] ?: '/apteka/public/uploads/no-photo.jpg') ?>"
                         alt="<?= htmlspecialchars($p['name']) ?>">
                    <div class="viewed-card-body">
                        <div class="viewed-card-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="viewed-card-price"><?= number_format($p['price'], 0, '', ' ') ?> ₽</div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Выход -->
    <div style="text-align:right;">
        <a href="/apteka/public/logout.php" class="reg-btn danger">Выйти из аккаунта</a>
    </div>

</div>
</main>

<!-- Замени YOUR_API_KEY на ключ с https://developer.tech.yandex.ru -->
<script src="https://api-maps.yandex.ru/2.1/?apikey=383779b4-2e75-4fb0-b172-be5c47d66133&lang=ru_RU"></script>
<script>
(function () {
    // ── DOM-элементы ──
    var suggestInput  = document.getElementById('addr-suggest');
    var mapContainer  = document.getElementById('addr-map-container');
    var selectedBox   = document.getElementById('addr-selected-box');
    var selectedText  = document.getElementById('addr-selected-text');
    var detailsBlock  = document.getElementById('addr-details');
    var aptInput      = document.getElementById('addr-apt');
    var floorInput    = document.getElementById('addr-floor');
    var hiddenAddr    = document.getElementById('addr-full');

    var myMap, myPlacemark, suggestView;
    var addrConfirmed = !!hiddenAddr.value; // уже был сохранённый адрес

    // ── Инициализация карты ──
    ymaps.ready(function () {
        myMap = new ymaps.Map('addr-map', {
            center: [55.751574, 37.573856], // Москва по умолчанию
            zoom: 10
        }, {
            searchControlProvider: 'yandex#search'
        });

        // Suggest привязываем к input
        suggestView = new ymaps.SuggestView('addr-suggest', {
            results: 5,
            boundedBy: [[41.0, 19.6], [82.0, 180.0]] // Россия+СНГ
        });

        // Когда выбрали подсказку
        suggestView.events.add('select', function (e) {
            var val = e.get('item').value;
            geocodeAndPlace(val);
        });

        // Клик по карте
        myMap.events.add('click', function (e) {
            var coords = e.get('coords');
            placeOrMoveMark(coords);
            reverseGeocode(coords);
        });

        // Если адрес уже был сохранён — показываем метку
        if (addrConfirmed && selectedText.textContent.trim()) {
            geocodeAndPlace(selectedText.textContent.trim(), false);
        }
    });

    // ── Геокодинг строки → координаты → метка ──
    function geocodeAndPlace(addressStr, openMap) {
        ymaps.geocode(addressStr, { results: 1 }).then(function (res) {
            var obj    = res.geoObjects.get(0);
            if (!obj) return;
            var coords = obj.geometry.getCoordinates();
            var bounds = obj.properties.get('boundedBy');

            myMap.setBounds(bounds, { checkZoomRange: true, duration: 400 }).then(function () {
                if (myMap.getZoom() > 17) myMap.setZoom(17);
            });

            placeOrMoveMark(coords);

            // Получаем точный адрес (до дома)
            var full = obj.getAddressLine();
            confirmAddress(full);
        });

        if (openMap !== false) showMap();
    }

    // ── Обратное геокодирование координат → адрес ──
    function reverseGeocode(coords) {
        ymaps.geocode(coords, { results: 1, kind: 'house' }).then(function (res) {
            var obj = res.geoObjects.get(0);
            if (!obj) return;
            confirmAddress(obj.getAddressLine());
        });
    }

    // ── Поставить / переместить метку ──
    function placeOrMoveMark(coords) {
        if (myPlacemark) {
            myPlacemark.geometry.setCoordinates(coords);
        } else {
            myPlacemark = new ymaps.Placemark(coords, {
                iconCaption: '📍'
            }, {
                preset: 'islands#greenDotIconWithCaption',
                draggable: true
            });
            myPlacemark.events.add('dragend', function () {
                reverseGeocode(myPlacemark.geometry.getCoordinates());
            });
            myMap.geoObjects.add(myPlacemark);
        }
    }

    // ── Зафиксировать адрес ──
    function confirmAddress(addrStr) {
        selectedText.textContent = addrStr;
        suggestInput.value       = addrStr;

        selectedBox.style.display  = 'flex';
        detailsBlock.style.display = 'grid';
        addrConfirmed = true;

        buildHidden();
    }

    // ── Показать карту ──
    function showMap() {
        mapContainer.style.display = 'block';
        // ymaps.Map нужен rerender после display:none
        if (myMap) {
            setTimeout(function () { myMap.container.fitToViewport(); }, 50);
        }
    }

    // ── Сброс выбора ──
    window.addrReset = function () {
        addrConfirmed = false;
        suggestInput.value = '';
        hiddenAddr.value   = '';
        selectedBox.style.display  = 'none';
        detailsBlock.style.display = 'none';
        mapContainer.style.display = 'none';
        if (myPlacemark && myMap) {
            myMap.geoObjects.remove(myPlacemark);
            myPlacemark = null;
        }
        suggestInput.focus();
    };

    // ── Сборка скрытого поля перед отправкой формы ──
    function buildHidden() {
        var base  = selectedText.textContent.trim();
        var apt   = aptInput.value.trim();
        var floor = floorInput.value.trim();
        var full  = base;
        if (apt)   full += ', кв. ' + apt;
        if (floor) full += ', эт. ' + floor;
        hiddenAddr.value = full;
    }

    // Обновляем hidden при вводе в квартиру/этаж
    aptInput.addEventListener('input', buildHidden);
    floorInput.addEventListener('input', buildHidden);

    // Показываем карту при фокусе на suggest (если не подтверждён)
    suggestInput.addEventListener('focus', function () {
        if (!addrConfirmed) showMap();
    });

    // Перед отправкой формы финально пишем в hidden
    var form = suggestInput.closest('form');
    if (form) {
        form.addEventListener('submit', function () {
            buildHidden();
            // Если адрес не выбран с карты, берём то что написали в инпуте
            if (!addrConfirmed && suggestInput.value.trim()) {
                hiddenAddr.value = suggestInput.value.trim();
            }
        });
    }
})();
</script>

<?php require "layout/footer.php"; ?>