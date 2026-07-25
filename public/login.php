<?php
require "../app/bootstrap.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    checkCsrf();
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
            <?= csrfField() ?>

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

        <div class="reg-footer">
            <span>Нет аккаунта?</span>
            <a href="register.php">Создать аккаунт →</a>
        </div>

    </div>
</main>

<?php require "layout/footer.php"; ?>
