<?php
// app/bootstrap.php — финальная рабочая версия

// Запускаем сессию ТОЛЬКО если её ещё нет
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключаем конфиг (в нём уже есть $pdo)
require_once __DIR__ . '/config.php';

// Подключаем функции, но с защитой от двойного вызова session_start
if (!function_exists('isLogged')) {  // если функции ещё не объявлены
    require_once __DIR__ . '/functions.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; // если через composer
// или require_once __DIR__ . '/PHPMailer/src/PHPMailer.php'; если вручную

function sendVerificationCode(string $email, string $code): bool {
    global $smtp;                    // <- вот эта строка новая

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['user'];
        $mail->Password   = $smtp['pass'];
        $mail->SMTPSecure = 'tls';
        $mail->Port       = $smtp['port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($smtp['user'], $smtp['from_name']);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Код подтверждения';
        $mail->Body    = "Ваш код: <b style='font-size:24px;'>$code</b><br><br>Действует 10 минут.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}



// если пользователь залогинен — обновляем его заказы
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // защита: существование $pdo и функция
    if (isset($pdo) && function_exists('updateUserOrderStatuses')) {
        try {
            updateUserOrderStatuses($pdo, $_SESSION['user_id']);
        } catch (Throwable $e) {
            // логируем при необходимости, но не ломаем страницу
            // error_log('updateUserOrderStatuses error: ' . $e->getMessage());
        }
    }
}
