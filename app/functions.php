<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLogged() {
    return isset($_SESSION["user_id"]);
}

function isAdmin() {
    return isLogged() && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
}

function requireLogin() {
    if (!isLogged()) {
        header("Location: " . BASE_URL . "/public/login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        die("Доступ запрещён");
    }
}

// хелпер для картинок + заглушка при пустом поле
function imgUrl(?string $path): string {
    $path = trim((string)$path);
    if ($path === '')                          return BASE_URL . '/uploads/no-photo.jpg';
    if (preg_match('~^https?://~i', $path))    return $path;   // внешняя ссылка из админки
    return BASE_URL . '/' . ltrim($path, '/');
}

function csrfToken() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField() {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrfToken()) . '">';
}

function checkCsrf() {
    $token = $_POST['csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        die("Ошибка безопасности: недействительный токен. Обновите страницу и попробуйте снова.");
    }
}


function getProductLabels() {
    return [
        "" => "— Нет —",
        "bad" => "БАД",
        "imported" => "Импортный товар",
        "strong" => "Сильнодействующее",
        "kids" => "Для детей",
    ];
}

// --- Автообновление статусов заказов (безопасная версия) ---
function updateUserOrderStatuses($pdo, $user_id) {
    $statuses = [
        0 => 'new',
        1 => 'processing',
        2 => 'shipped',
        3 => 'at_hub',
        4 => 'sent_to_pickup',
        5 => 'ready_for_pickup'
    ];

    // Берём все заказы пользователя, включая NULL статусы, исключая cancelled
    $stmt = $pdo->prepare("
        SELECT id, status, created_at
        FROM orders
        WHERE user_id = ?
          AND (status IS NULL OR status <> 'cancelled')
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as $order) {
        // защита от битой даты
        $createdTimestamp = strtotime($order['created_at']);
        if ($createdTimestamp === false) {
            $createdTimestamp = time();
        }

        // diff в секундах; если дата в будущем — ставим 0
        $diff = time() - $createdTimestamp;
        if ($diff < 0) $diff = 0;

        // шаг каждые 10 секунд (тестовый режим). В продакшн заменишь на 60
        $step = (int) floor($diff / 10);
        if ($step < 0) $step = 0;
        if ($step > 5) $step = 5;

        $newStatus = $statuses[$step] ?? 'new';

        // Нормализуем текущее значение из БД
        $currentRaw = $order['status'] ?? '';
        $currentTrim = trim((string)$currentRaw);
        $currentNormalized = $currentTrim === '' ? 'new' : strtolower($currentTrim);

        if ($currentNormalized !== $newStatus) {
            $updateStmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$newStatus, $order['id']]);
        }
    }
}


function getLabelText($label_key) {
    $labels = [
        "bad" => "Внимание: БАД — не является лекарственным средством.",
        "imported" => "Товар произведён за рубежом. Сертификация может отличаться.",
        "strong" => "Сильнодействующее средство. Перед применением требуется консультация врача.",
        "kids" => "Подходит для детей. Использовать строго согласно инструкции.",
    ];
    return $labels[$label_key] ?? "";
}

function getProduct($id) {
    global $pdo;
    $id = (int)$id;
    if ($id <= 0) return false;
    try {
        $q = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $q->execute([$id]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: false;
    } catch (PDOException $e) {
        return false;
    }
}

function getProductRating($id) {
    global $pdo;
    $id = (int)$id;
    if ($id <= 0) return ["avg"=>0,"avg_precise"=>0.0,"count"=>0];
    try {
        $q = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE product_id = ?");
        $q->execute([$id]);
        $res = $q->fetch(PDO::FETCH_ASSOC);
        $avg = $res && $res["avg_rating"] !== null ? (float)$res["avg_rating"] : 0.0;
        $count = $res ? (int)$res["cnt"] : 0;
        return ["avg"=>($avg>0?round($avg):0),"avg_precise"=>($avg>0?round($avg,1):0.0),"count"=>$count];
    } catch (PDOException $e) {
        return ["avg"=>0,"avg_precise"=>0.0,"count"=>0];
    }
}

function getProductReviews($id,$limit=50) {
    global $pdo;
    $id = (int)$id;
    if ($id<=0) return [];
    try {
        $sql = "SELECT r.*, COALESCE(u.name,'Пользователь') AS user_name FROM reviews r LEFT JOIN users u ON r.user_id=u.id WHERE r.product_id=? ORDER BY r.created_at DESC LIMIT ?";
        $q=$pdo->prepare($sql);
        $q->bindValue(1,$id,PDO::PARAM_INT);
        $q->bindValue(2,(int)$limit,PDO::PARAM_INT);
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

function getSimilarProducts($product,$limit=4) {
    global $pdo;
    if(!is_array($product)||empty($product["id"])) return [];
    $productId=(int)$product["id"];
    try {
        if(isset($product["category_id"]) && $product["category_id"]!==null) {
            $catId=(int)$product["category_id"];
            if($catId>0){
                $sql="SELECT id,name,price,image FROM products WHERE category_id=? AND id!=? LIMIT ?";
                $q=$pdo->prepare($sql);
                $q->bindValue(1,$catId,PDO::PARAM_INT);
                $q->bindValue(2,$productId,PDO::PARAM_INT);
                $q->bindValue(3,(int)$limit,PDO::PARAM_INT);
                $q->execute();
                $rows=$q->fetchAll(PDO::FETCH_ASSOC);
                if($rows) return $rows;
            }
        }
        $price=isset($product["price"])?(float)$product["price"]:0.0;
        if($price>0){
            $min=$price*0.7;
            $max=$price*1.3;
            $sql="SELECT id,name,price,image FROM products WHERE price BETWEEN ? AND ? AND id!=? LIMIT ?";
            $q=$pdo->prepare($sql);
            $q->bindValue(1,$min);
            $q->bindValue(2,$max);
            $q->bindValue(3,$productId,PDO::PARAM_INT);
            $q->bindValue(4,(int)$limit,PDO::PARAM_INT);
            $q->execute();
            $rows=$q->fetchAll(PDO::FETCH_ASSOC);
            if($rows) return $rows;
        }
        $sql="SELECT id,name,price,image FROM products WHERE id!=? ORDER BY RAND() LIMIT ?";
        $q=$pdo->prepare($sql);
        $q->bindValue(1,$productId,PDO::PARAM_INT);
        $q->bindValue(2,(int)$limit,PDO::PARAM_INT);
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e){
        return [];
    }
}
?>
