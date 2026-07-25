<?php
require "../app/bootstrap.php"; // ВАЖНО: именно bootstrap, а не config

// Полностью чистим данные сессии
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// На всякий случай гасим ключ авторизации
unset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['role']);

// Редирект на логин
header("Location: /apteka/public/login.php");
exit;
