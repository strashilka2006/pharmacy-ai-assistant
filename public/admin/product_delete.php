<?php
require __DIR__ . "/../../app/bootstrap.php";
requireAdmin();

$id = (int)($_GET["id"] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: products.php");
exit;
