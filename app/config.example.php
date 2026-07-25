<?php
// Скопируй этот файл в config.php и впиши свои значения

// База данных
$host   = "localhost";
$dbname = "apteka";
$user   = "";
$pass   = "";

// Почта
$smtp = [
    'host'      => 'smtp.gmail.com',
    'user'      => '',
    'pass'      => '',
    'port'      => 587,
    'from_name' => 'AptekaWebSite',
];

// ЮKassa
$yookassa = [
    'shop_id'    => '',
    'secret_key' => '',
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('DB error: ' . $e->getMessage());
    die("Ошибка подключения к базе данных");
}
