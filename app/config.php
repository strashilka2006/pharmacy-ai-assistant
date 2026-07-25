<?php
// app/config.php — подключение к БД и настройки почты

$host = "localhost";
$dbname = "apteka";
$user = "root";
$pass = "";

// Почта
$smtp = [
    'host'      => 'smtp.gmail.com',
    'user'      => '',
    'pass'      => '',
    'port'      => 587,
    'from_name' => 'AptekaWebSite',
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}
