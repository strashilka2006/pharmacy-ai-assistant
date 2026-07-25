<?php 
$pageTitle = "Бренд";
require "layout/admin_header.php"; 
?>

<?php
$id    = (int)($_GET['id'] ?? 0);
$brand = $id ? $pdo->query("SELECT * FROM brands WHERE id = $id")->fetch() : null;
if ($id && !$brand) die("Бренд не найден");

// Удаление логотипа
if (isset($_GET['remove_logo']) && $id) {
    $pdo->prepare("UPDATE brands SET logo = NULL WHERE id = ?")->execute([$id]);
    header("Location: brand_edit.php?id=$id");
    exit;
}

// Удаление баннера
if (isset($_GET['remove_banner']) && $id) {
    $pdo->prepare("UPDATE brands SET banner = NULL WHERE id = ?")->execute([$id]);
    header("Location: brand_edit.php?id=$id");
    exit;
}

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name        = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (empty($name)) {
        $error = "Введите название бренда!";
    } else {
        $logo   = $brand['logo']   ?? null;
        $banner = $brand['banner'] ?? null;

        $uploadDir = __DIR__ . "/../../uploads/brands/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // ── Загрузка логотипа ──
        if (!empty($_FILES["logo"]["name"])) {
            $ext     = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

            if (!in_array($ext, $allowed)) {
                $error = "Формат логотипа не разрешён: .$ext";
            } elseif ($_FILES["logo"]["size"] > 10 * 1024 * 1024) {
                $error = "Логотип больше 10 МБ";
            } else {
                $fileName = time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES["logo"]["tmp_name"], $uploadDir . $fileName)) {
                    $logo = "/apteka/uploads/brands/" . $fileName;
                } else {
                    $error = "Не удалось сохранить логотип";
                }
            }
        }

        // ── Загрузка баннера ──
        if (!$error && !empty($_FILES["banner"]["name"])) {
            $ext     = strtolower(pathinfo($_FILES["banner"]["name"], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = "Формат баннера не разрешён: .$ext";
            } elseif ($_FILES["banner"]["size"] > 10 * 1024 * 1024) {
                $error = "Баннер больше 10 МБ";
            } else {
                $fileName = 'banner_' . time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES["banner"]["tmp_name"], $uploadDir . $fileName)) {
                    $banner = "/apteka/uploads/brands/" . $fileName;
                } else {
                    $error = "Не удалось сохранить баннер";
                }
            }
        }

        // ── Сохранение в БД ──
        if (!$error) {
            if ($id) {
                $pdo->prepare("UPDATE brands SET name = ?, description = ?, logo = ?, banner = ? WHERE id = ?")
                    ->execute([$name, $description, $logo, $banner, $id]);
                $success = "Бренд обновлён!";
            } else {
                $pdo->prepare("INSERT INTO brands (name, description, logo, banner) VALUES (?, ?, ?, ?)")
                    ->execute([$name, $description, $logo, $banner]);
                header("Location: brand_edit.php?id=" . $pdo->lastInsertId());
                exit;
            }
            // Перечитываем обновлённые данные
            $brand = $pdo->query("SELECT * FROM brands WHERE id = $id")->fetch();
        }
    }
}
?>

<div class="mb-4">
    <a href="brands.php" class="btn btn-outline-secondary rounded-pill px-4">← Все бренды</a>
</div>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="card-header bg-dark text-white py-4">
        <h3 class="mb-0"><?= $id ? "Редактировать бренд" : "Новый бренд" ?></h3>
    </div>

    <div class="card-body p-5">
        <?php if ($success): ?>
            <div class="alert alert-success rounded-4 mb-4"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4 mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="row g-4">

                <!-- Название -->
                <div class="col-lg-8">
                    <label class="form-label fw-bold">Название бренда *</label>
                    <input type="text" name="name" class="form-control form-control-lg"
                           value="<?= htmlspecialchars($brand['name'] ?? '') ?>" required autofocus>
                </div>

                <!-- Описание -->
                <div class="col-12">
                    <label class="form-label fw-bold">Описание бренда</label>
                    <textarea name="description" class="form-control" rows="6"><?= htmlspecialchars($brand['description'] ?? '') ?></textarea>
                </div>

                <!-- Логотип: загрузка -->
                <div class="col-lg-6">
                    <label class="form-label fw-bold">Логотип бренда</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    <small class="text-muted">JPG, PNG, GIF, WebP, SVG — до 10 МБ</small>
                </div>

                <!-- Баннер: загрузка -->
                <div class="col-lg-6">
                    <label class="form-label fw-bold">
                        Баннер бренда
                        <small class="text-muted fw-normal">(широкое фото или GIF, ~1400×400)</small>
                    </label>
                    <input type="file" name="banner" class="form-control" accept="image/*">
                    <small class="text-muted">JPG, PNG, GIF, WebP — до 10 МБ</small>
                </div>

                <!-- Логотип: превью -->
                <?php if (!empty($brand['logo'])): ?>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">Текущий логотип</label>
                    <div class="mt-2">
                        <img src="<?= htmlspecialchars($brand['logo']) ?>"
                             class="img-fluid rounded-4 shadow-sm" style="max-height:220px;">
                        <div class="mt-2">
                            <a href="?id=<?= $id ?>&remove_logo=1" class="text-danger small"
                               onclick="return confirm('Удалить логотип?')">удалить логотип</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Баннер: превью -->
                <?php if (!empty($brand['banner'])): ?>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">Текущий баннер</label>
                    <div class="mt-2">
                        <img src="<?= htmlspecialchars($brand['banner']) ?>"
                             class="img-fluid rounded-4 shadow-sm"
                             style="max-height:120px;width:100%;object-fit:cover;">
                        <div class="mt-2">
                            <a href="?id=<?= $id ?>&remove_banner=1" class="text-danger small"
                               onclick="return confirm('Удалить баннер?')">удалить баннер</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Кнопка -->
                <div class="col-12 text-center mt-5">
                    <button type="submit" class="btn btn-dark btn-lg px-5 py-3 rounded-pill shadow">
                        <?= $id ? "Сохранить изменения" : "Создать бренд" ?>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<?php require "layout/admin_footer.php"; ?>