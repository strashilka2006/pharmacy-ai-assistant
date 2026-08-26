<?php
// Скопируй этот файл в config.php и впиши свои значения.

// База данных
$host   = getenv('DB_HOST') ?: "localhost";
$dbname = getenv('DB_NAME') ?: "apteka";
$user   = getenv('DB_USER') ?: "";
$pass   = getenv('DB_PASS') ?: "";

// Ollama
$ollama = [
    'url'   => getenv('OLLAMA_URL')   ?: 'http://localhost:11434/api/chat',
    'model' => getenv('OLLAMA_MODEL') ?: 'qwen3:8b',
];

// Почта
$smtp = [
    'host'      => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'user'      => getenv('SMTP_USER') ?: '',
    'pass'      => getenv('SMTP_PASS') ?: '',
    'port'      => (int)(getenv('SMTP_PORT') ?: 587),
    'from_name' => 'AptekaWebSite',
];

// ЮKassa. Ну или что-то своё.
$yookassa = [
    'shop_id'    => getenv('YOOKASSA_SHOP_ID')    ?: '',
    'secret_key' => getenv('YOOKASSA_SECRET_KEY') ?: '',
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('DB error: ' . $e->getMessage());
    die("Ошибка подключения к базе данных");
}
