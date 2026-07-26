<?php 
$pageTitle = "Бренд";
require "layout/admin_header.php"; 

$id = (int)($_GET['id'] ?? 0);

$brand = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([$id]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$brand) die("Бренд не найден");
}

$success = $error = "";

// ── Удаление логотипа / баннера (только POST с токеном) ──
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove"]) && $id) {
    checkCsrf();
    $field = $_POST["remove"] === 'banner' ? 'banner' : 'logo';
    $pdo->prepare("UPDATE brands SET `$field` = NULL WHERE id = ?")->execute([$id]);
    header("Location: brand_edit.php?id=$id");
    exit;
}

// ── Сохранение бренда ──
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["remove"])) {
    checkCsrf();

    $name        = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (empty($name)) {
        $error = "Введите название бренда!";
    } else {
        $logo   = $brand['logo']   ?? null;
        $banner = $brand['banner'] ?? null;

        $uploadDir = __DIR__ . "/../../uploads/brands/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $okTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

        // ── Загрузка логотипа ──
        if (!empty($_FILES["logo"]["name"])) {
            $ext      = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
            $realType = @exif_imagetype($_FILES["logo"]["tmp_name"]);

            if (!in_array($ext, $allowed) || !in_array($realType, $okTypes, true)) {
                $error = "Формат логотипа не разрешён";
            } elseif ($_FILES["logo"]["size"] > 10 * 1024 * 1024) {
                $error = "Логотип больше 10 МБ";
            } else {
                $fileName = time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES["logo"]["tmp_name"], $uploadDir . $fileName)) {
                    $logo = "/uploads/brands/" . $fileName;
                } else {
                    $error = "Не удалось сохранить логотип";
                }
            }
        }

        // ── Загрузка баннера ──
        if (!$error && !empty($_FILES["banner"]["name"])) {
            $ext      = strtolower(pathinfo($_FILES["banner"]["name"], PATHINFO_EXTENSION));
            $realType = @exif_imagetype($_FILES["banner"]["tmp_name"]);

            if (!in_array($ext, $allowed) || !in_array($realType, $okTypes, true)) {
                $error = "Формат баннера не разрешён";
            } elseif ($_FILES["banner"]["size"] > 10 * 1024 * 1024) {
                $error = "Баннер больше 10 МБ";
            } else {
                $fileName = 'banner_' . time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES["banner"]["tmp_name"], $uploadDir . $fileName)) {
                    $banner = "/uploads/brands/" . $fileName;
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

                // Перечитываем обновлённые данные
                $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
                $stmt->execute([$id]);
                $brand = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $pdo->prepare("INSERT INTO brands (name, description, logo, banner) VALUES (?, ?, ?, ?)")
                    ->execute([$name, $description, $logo, $banner]);
                header("Location: brand_edit.php?id=" . $pdo->lastInsertId());
                exit;
            }
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
            <div class="alert alert-success rounded-4 mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4 mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?= csrfField() ?>
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
                    <small class="text-muted">JPG, PNG, GIF, WebP — до 10 МБ</small>
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

        <!-- Удаление логотипа / баннера — отдельными формами, чтобы не вкладывать формы друг в друга -->
        <?php if ($id && (!empty($brand['logo']) || !empty($brand['banner']))): ?>
        <div class="mt-4 pt-4 border-top d-flex gap-3">
            <?php if (!empty($brand['logo'])): ?>
            <form method="post" onsubmit="return confirm('Удалить логотип?')">
                <?= csrfField() ?>
                <input type="hidden" name="remove" value="logo">
                <button type="submit" class="btn btn-link text-danger small p-0">удалить логотип</button>
            </form>
            <?php endif; ?>

            <?php if (!empty($brand['banner'])): ?>
            <form method="post" onsubmit="return confirm('Удалить баннер?')">
                <?= csrfField() ?>
                <input type="hidden" name="remove" value="banner">
                <button type="submit" class="btn btn-link text-danger small p-0">удалить баннер</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require "layout/admin_footer.php"; ?>
