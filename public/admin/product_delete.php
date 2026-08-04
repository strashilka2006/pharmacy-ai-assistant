<?php
require __DIR__ . "/../../app/bootstrap.php";
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products.php");
    exit;
}

checkCsrf();

$id = (int)($_POST["id"] ?? 0);

if ($id > 0) {
    try {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $_SESSION['flash'] = "Товар удалён";
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $pdo->prepare("UPDATE products SET stock = 0 WHERE id = ?")->execute([$id]);
            $_SESSION['flash'] = "Товар есть в оформленных заказах — удалить нельзя. Остаток обнулён, из продажи снят.";
        } else {
            throw $e;
        }
    }
}

header("Location: products.php");
exit;
