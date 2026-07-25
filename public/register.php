<?php
require "../app/bootstrap.php";

$error = "";

// ====== AJAX: отправка кода ======
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "send_code") {
    header('Content-Type: application/json');
    $email = trim($_POST["email"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["ok" => false, "error" => "Некорректный email"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(["ok" => false, "error" => "Почта уже занята"]);
        exit;
    }

    // Генерируем код
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Удаляем старые коды
    $pdo->prepare("DELETE FROM email_verifications WHERE email = ?")->execute([$email]);

    // Сохраняем новый
    $stmt = $pdo->prepare("INSERT INTO email_verifications (email, code, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$email, $code]);

    if (sendVerificationCode($email, $code)) {
        echo json_encode(["ok" => true]);
    } else {
        echo json_encode(["ok" => false, "error" => "Не удалось отправить письмо"]);
    }
    exit;
}

// ====== AJAX: проверка кода ======
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "verify_code") {
    header('Content-Type: application/json');
    $email = trim($_POST["email"] ?? "");
    $code  = trim($_POST["code"] ?? "");

    $stmt = $pdo->prepare("SELECT id FROM email_verifications 
                           WHERE email = ? AND code = ? AND verified = 0 
                           AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
    $stmt->execute([$email, $code]);

    if ($stmt->rowCount() > 0) {
        $pdo->prepare("UPDATE email_verifications SET verified = 1 WHERE email = ?")->execute([$email]);
        echo json_encode(["ok" => true]);
    } else {
        echo json_encode(["ok" => false, "error" => "Неверный или устаревший код"]);
    }
    exit;
}

// ====== ОСНОВНАЯ РЕГИСТРАЦИЯ ======
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["action"])) {
    checkCsrf();

    $email  = trim($_POST["email"] ?? "");
    $name   = trim($_POST["name"] ?? "");
    $pass   = $_POST["password"] ?? "";
    $pass2  = $_POST["password2"] ?? "";
    $phone  = trim($_POST["phone"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный email";
    } elseif (empty($name)) {
        $error = "Укажите имя";
    } elseif (empty($phone)) {
        $error = "Укажите номер телефона";
    } elseif (strlen($pass) < 6) {
        $error = "Пароль должен быть не менее 6 символов";
    } elseif ($pass !== $pass2) {
        $error = "Пароли не совпадают";
    } elseif (empty($_POST["policy"])) {
        $error = "Необходимо согласиться с политикой конфиденциальности";
    } elseif (empty($_POST["email_verified"])) {
        $error = "Подтвердите email — введите код из письма";
    } else {
        // Проверяем, что код действительно был подтверждён
        $stmt = $pdo->prepare("SELECT id FROM email_verifications WHERE email = ? AND verified = 1");
        $stmt->execute([$email]);
        if ($stmt->rowCount() === 0) {
            $error = "Email не подтверждён. Запросите код повторно.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = "Почта уже занята";
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (email, name, password, phone) VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, $name, $hash, $phone]);

                // Удаляем использованный код
                $pdo->prepare("DELETE FROM email_verifications WHERE email = ?")->execute([$email]);

                header("Location: login.php");
                exit;
            }
        }
    }
}

$pageTitle = "Регистрация";
require "layout/header.php";
?>

<style>
.reg-wrap {
    max-width: 600px;
    margin: 0 auto;
    padding: 56px 24px 80px;
}
.reg-header {
    border-bottom: 1.5px solid #d4edaa;
    padding-bottom: 28px;
    margin-bottom: 40px;
}
.reg-header .label {
    font-family: 'Fragment Mono', monospace;
    font-size: 11px;
    color: #7AAD3F;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 12px;
}
.reg-header h1 {
    font-family: 'Fragment Mono', monospace;
    font-size: clamp(1.5rem, 4vw, 2rem);
    font-weight: 400;
    color: #1a3a0a;
    line-height: 1.2;
    letter-spacing: -0.5px;
    margin-bottom: 10px;
}
.reg-header p {
    font-size: 13px;
    color: #888;
    margin: 0;
}
.reg-field {
    margin-bottom: 24px;
}
.reg-field label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #2d5a1b;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
    font-family: 'Fragment Mono', monospace;
}
.reg-field input {
    width: 100%;
    border: 1.5px solid #d4edaa;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    color: #1a1a1a;
    background: #f8fef3;
    outline: none;
    transition: border-color .18s, box-shadow .18s;
}
.reg-field input:focus {
    border-color: #2d5a1b;
    box-shadow: 0 0 0 3px rgba(122,173,63,.12);
}
.reg-field input::placeholder { color: #bbb; }
.reg-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}
.reg-divider {
    border: none;
    border-top: 1px solid #dff0c0;
    margin: 28px 0;
}
.reg-check {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 28px;
}
.reg-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    accent-color: #7AAD3F;
    flex-shrink: 0;
    cursor: pointer;
}
.reg-check label {
    font-size: 13px;
    color: #555;
    cursor: pointer;
    line-height: 1.5;
}
.reg-check a {
    color: #2d5a1b;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.reg-btn {
    width: 100%;
    background: #2d5a1b;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 14px;
    font-family: 'Fragment Mono', monospace;
    font-size: 13px;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: background .18s;
}
.reg-btn:hover { background: #446436; }
.reg-footer {
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid #dff0c0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.reg-footer span {
    font-size: 13px;
    color: #999;
}
.reg-footer a {
    font-family: 'Fragment Mono', monospace;
    font-size: 12px;
    color: #7AAD3F;
    text-decoration: none;
    letter-spacing: 0.3px;
}
.reg-footer a:hover { text-decoration: underline; }
.reg-error {
    background: #fff5f5;
    border: 1.5px solid #f5c0c0;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 13px;
    color: #c0392b;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<main style="background:#f8fef3;min-height:calc(100vh - 58px);">
    <div class="reg-wrap">

        <div class="reg-header">
            <div class="label">Аккаунт</div>
            <h1>СОЗДАТЬ АККАУНТ</h1>
            <p>Заполните данные — это займёт меньше минуты</p>
        </div>

        <?php if ($error): ?>
            <div class="reg-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" id="regForm">
            <?= csrfField() ?>

            <div class="reg-field">
                <label>Email</label>
                <div style="display:flex;gap:8px;">
                    <input type="email" name="email" id="email" required autofocus placeholder="example@mail.ru" style="flex:1;">
                    <button type="button" id="sendCodeBtn" onclick="sendCode()" 
                            style="white-space:nowrap;padding:12px 16px;background:#7AAD3F;color:#fff;border:none;border-radius:8px;cursor:pointer;">
                        Отправить код
                    </button>
                </div>
            </div>

            <div class="reg-field" id="codeField" style="display:none;">
                <label>Код из письма</label>
                <input type="text" name="verification_code" id="verificationCode" maxlength="6" placeholder="000000">
                <small style="color:#888;">Код действителен 10 минут</small>
            </div>

            <div class="reg-field">
                <label>Имя</label>
                <input type="text" name="name" required placeholder="Как вас зовут">
            </div>

            <div class="reg-field">
                <label>Телефон</label>
                <input type="tel" name="phone" required placeholder="+7 (___) ___-__-__">
            </div>

            <hr class="reg-divider">

            <div class="reg-row">
                <div class="reg-field" style="margin-bottom:0;">
                    <label>Пароль</label>
                    <input type="password" name="password" required minlength="6" placeholder="Минимум 6 символов">
                </div>
                <div class="reg-field" style="margin-bottom:0;">
                    <label>Повтор пароля</label>
                    <input type="password" name="password2" required minlength="6" placeholder="Повторите пароль">
                </div>
            </div>

            <hr class="reg-divider">

            <div class="reg-check">
                <input type="checkbox" id="policy" name="policy" required>
                <label for="policy">
                    Регистрируясь, я принимаю условия
                    <a href="privacy.php" target="_blank">политики конфиденциальности</a>
                    и соглашаюсь на обработку персональных данных в соответствии с законодательством РФ
                </label>
            </div>

            <button type="submit" class="reg-btn">СОЗДАТЬ АККАУНТ →</button>

        </form>

        <div class="reg-footer">
            <span>Уже есть аккаунт?</span>
            <a href="login.php">ВОЙТИ →</a>
        </div>

    </div>
</main>

<script>
let verifiedEmail = null;
let codeVerified = false;

async function sendCode() {
    const email = document.getElementById('email').value;
    const btn = document.getElementById('sendCodeBtn');

    if (!email || !email.includes('@')) {
        alert('Введите корректный email');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Отправка...';

    const res = await fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=send_code&email=' + encodeURIComponent(email)
    });
    const data = await res.json();

    if (data.ok) {
        document.getElementById('codeField').style.display = 'block';
        btn.textContent = 'Код отправлен';
        startTimer(btn);
    } else {
        alert(data.error);
        btn.disabled = false;
        btn.textContent = 'Отправить код';
    }
}

function startTimer(btn) {
    let sec = 60;
    const timer = setInterval(() => {
        btn.textContent = 'Повторить через ' + sec + 's';
        sec--;
        if (sec < 0) {
            clearInterval(timer);
            btn.disabled = false;
            btn.textContent = 'Отправить код';
        }
    }, 1000);
}

// Перехватываем отправку формы
document.getElementById('regForm').addEventListener('submit', async function(e) {
    const email = document.getElementById('email').value;
    const code  = document.getElementById('verificationCode').value;

    // Если email ещё не верифицирован — проверяем код
    if (!codeVerified || verifiedEmail !== email) {
        e.preventDefault();

        if (!code) {
            alert('Введите код подтверждения из письма');
            return;
        }

        const res = await fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=verify_code&email=' + encodeURIComponent(email) + '&code=' + encodeURIComponent(code)
        });
        const data = await res.json();

        if (data.ok) {
            verifiedEmail = email;
            codeVerified = true;

            // Добавляем скрытое поле
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'email_verified';
            input.value = '1';
            this.appendChild(input);

            // Повторная отправка формы
            this.submit();
        } else {
            alert(data.error);
        }
    }
});
</script>

<?php require "layout/footer.php"; ?>
