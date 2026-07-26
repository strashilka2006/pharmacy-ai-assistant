<?php
require "../app/bootstrap.php";

if (!isLogged()) exit;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit;
}

checkCsrf();

$cart_id = (int)($_POST["id"] ?? 0);
$user_id = $_SESSION["user_id"];

$pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")->execute([$cart_id, $user_id]);

header("Location: cart.php");
exit;
