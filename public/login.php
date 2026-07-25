<?php
require "../app/bootstrap.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass  = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($pass, $user["password"])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION["email"]   = $user["email"];
        $_SESSION["role"]    = $user["role"];

        if ($user["role"] === "admin") {
            header("Location: /apteka/public/admin/index.php");
            exit;
        }
        header("Location: /apteka/public/index.php");
        exit;
    } else {
        $error = "Неверный email или пароль";
    }
}

$pageTitle = "Вход";
require "layout/header.php";
?>

<style>
.reg-wrap {
    max-width: 480px;
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
    background: #f8fef3;
    border-color: #7AAD3F;
    box-shadow: 0 0 0 3px rgba(122,173,63,.12);
}

.reg-field input::placeholder { color: #bbb; }

.reg-divider {
    border: none;
    border-top: 1px solid #dff0c0;
    margin: 28px 0;
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

.reg-btn:hover { background: #39572e; }

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
            <h1>ВХОД В АККАУНТ</h1>
            <p>Рады снова видеть вас</p>
        </div>

        <?php if ($error): ?>
            <div class="reg-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">

            <div class="reg-field">
                <label>Email</label>
                <input type="email" name="email" required autofocus placeholder="example@mail.ru">
            </div>

            <div class="reg-field">
                <label>Пароль</label>
                <input type="password" name="password" required placeholder="Ваш пароль">
            </div>

            <hr class="reg-divider">

            <button type="submit" class="reg-btn">ВОЙТИ →</button>

        </form>

        <hr class="reg-divider">

        <div style="text-align:center;margin-bottom:16px;">
            <span style="font-size:12px;color:#bbb;font-family:'Fragment Mono',monospace;letter-spacing:1px;">или войти через</span>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:8px;">
            <button type="button" class="social-btn" style="--c:#4285F4;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
            </button>

            <div id="vk-login-btn" style="display:flex;justify-content:center;"></div>
        </div>


        <div class="reg-footer">
            <span>Нет аккаунта?</span>
            <a href="register.php">Создать аккаунт →</a>
        </div>

    </div>
</main>

<script src="https://unpkg.com/@vkid/sdk@<3.0.0/dist-sdk/umd/index.js"></script>
<script>
if ('VKIDSDK' in window) {
    const VKID = window.VKIDSDK;

    VKID.Config.init({
        app: 54568307,
        redirectUrl: 'http://localhost',
        responseMode: VKID.ConfigResponseMode.Callback,
        source: VKID.ConfigSource.LOWCODE,
        scope: '',
    });

    const oneTap = new VKID.OneTap();

    oneTap.render({
        container: document.getElementById('vk-login-btn'),
        lang: 0,
        showAlternativeLogin: false,
        skin: 'secondary',
        styles: { borderRadius: 10, height: 46 }
    })
    .on(VKID.WidgetEvents.ERROR, function(error) {
        console.error('VK ошибка:', error);
    })
    .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, function(payload) {
        VKID.Auth.exchangeCode(payload.code, payload.device_id)
            .then(function(data) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'vk_callback.php';
                [
                    ['vk_access_token', data.access_token],
                    ['vk_user_id',      data.user_id],
                    ['vk_email',        data.email || '']
                ].forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            })
            .catch(err => console.error(err));
    });
}
</script>


<?php require "layout/footer.php"; ?>
