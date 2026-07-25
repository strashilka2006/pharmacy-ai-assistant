<?php
include "header.php";

$id = $_GET["id"];
$stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
$stmt->execute([$id]);

header("Location: products.php");
