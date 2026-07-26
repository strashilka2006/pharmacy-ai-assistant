<?php 
$pageTitle = "Добавить товар";
require "layout/admin_header.php"; 

$labels = getProductLabels();
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    checkCsrf();
    $name              = trim($_POST["name"] ?? "");
    $price             = (float)str_replace([' ', ','], ['', '.'], $_POST["price"] ?? "0");
    $short_description = trim($_POST["short_description"] ?? "");
    $description       = trim($_POST["description"] ?? "");
    $long_description  = trim($_POST["long_description"] ?? "");
    $supplier          = trim($_POST["supplier"] ?? "");
    $prescription      = isset($_POST["rx"]) ? 1 : 0;
    $usage_info        = trim($_POST["usage_info"] ?? "");
    $stock             = max(0, (int)($_POST["stock"] ?? 0));
    $label             = $_POST["label"] ?? "";
    $brand_id          = !empty($_POST["brand_id"]) ? (int)$_POST["brand_id"] : null;

    // Медицинские поля
    $indications       = trim($_POST["indications"] ?? "");
    $composition       = trim($_POST["composition"] ?? "");
    $contraindications = trim($_POST["contraindications"] ?? "");
    $drug_interactions = trim($_POST["drug_interactions"] ?? "");
    $overdose          = trim($_POST["overdose"] ?? "");

    if (empty($name) || $price <= 0) {
        $error = "Заполните название и цену!";
    } else {
        $image = null;

        // URL или файл
        if (!empty($_POST["photo_url"])) {
            $image = trim($_POST["photo_url"]);
        } elseif (!empty($_FILES["photo"]["name"])) {
            $dir = __DIR__ . "/../../uploads/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];

            $realType = @exif_imagetype($_FILES["photo"]["tmp_name"]);
            $okTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

            if (!in_array($ext, $allowed) || !in_array($realType, $okTypes, true)) {
                $error = "Недопустимый формат изображения!";
            } else {
                $fileName = time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], $dir . $fileName)) {
                    $image = "/uploads/" . $fileName;
                } else {
                    $error = "Ошибка загрузки файла.";
                }
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("
                INSERT INTO products 
                (name, price, short_description, description, long_description, supplier, prescription, 
                 usage_info, stock, image, label, brand_id, indications, composition, contraindications, 
                 drug_interactions, overdose)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name, $price, $short_description, $description, $long_description, $supplier, $prescription,
                $usage_info, $stock, $image, $label, $brand_id, 
                $indications, $composition, $contraindications, $drug_interactions, $overdose
            ]);

            $success = "Товар успешно добавлен!";
            $_POST = []; // очистка формы
        }
    }
}

// Все бренды для выпадашки
$brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-4">
    <a href="products.php" class="btn btn-outline-secondary rounded-pill">
        ← Все товары
    </a>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-dark text-white py-4">
        <h3 class="mb-0">Добавить новый товар</h3>
    </div>

    <div class="card-body p-5">
        <?php if ($success): ?>
            <div class="alert alert-success rounded-4 mb-4"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4 mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="row g-4">
                <!-- Название + Цена -->
                <div class="col-lg-8">
                    <label class="form-label fw-bold">Название товара *</label>
                    <input type="text" name="name" class="form-control form-control-lg" 
                           value="<?= htmlspecialchars($_POST["name"] ?? "") ?>" required>
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold">Цена (₽) *</label>
                    <input type="text" name="price" class="form-control form-control-lg" 
                           value="<?= htmlspecialchars($_POST["price"] ?? "") ?>" required>
                </div>

                <!-- Бренд -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Бренд</label>
                    <select name="brand_id" class="form-select">
                        <option value="">— Без бренда —</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>" 
                                <?= ($b['id'] ?? '') == ($_POST["brand_id"] ?? '') ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Краткое описание -->
                <div class="col-12">
                    <label class="form-label fw-bold">Краткое описание</label>
                    <textarea name="short_description" class="form-control" rows="2">
<?= htmlspecialchars($_POST["short_description"] ?? "") ?></textarea>
                </div>

                <!-- Фото -->
                <div class="col-lg-6">
                    <label class="form-label fw-bold">URL изображения</label>
                    <input type="url" name="photo_url" class="form-control" placeholder="https://..." 
                           value="<?= htmlspecialchars($_POST["photo_url"] ?? "") ?>">
                </div>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">Или загрузить файл</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>

                <hr class="my-5">

                <!-- Основные поля -->
                <div class="col-md-4">
                    <label class="form-label fw-bold">Поставщик</label>
                    <input type="text" name="supplier" class="form-control" 
                           value="<?= htmlspecialchars($_POST["supplier"] ?? "") ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">На складе</label>
                    <input type="number" name="stock" class="form-control" 
                           value="<?= $_POST["stock"] ?? "0" ?>" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Метка</label>
                    <select name="label" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($labels as $k => $v): ?>
                            <option value="<?= $k ?>" 
                                <?= ($k === ($_POST["label"] ?? "")) ? "selected" : "" ?>>
                                <?= $v ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Рецепт -->
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="rx" id="rx" 
                               <?= isset($_POST["rx"]) ? "checked" : "" ?>>
                        <label class="form-check-label fw-bold" for="rx">Требуется рецепт</label>
                    </div>
                </div>

                <div class="col-12">
                    <hr>
                    <h4 class="mb-4 fw-bold">Медицинская информация</h4>
                </div>

                <!-- Медицинские поля -->
                <div class="col-12">
                    <label class="form-label fw-bold">Показания</label>
                    <textarea name="indications" class="form-control" rows="4">
<?= htmlspecialchars($_POST["indications"] ?? "") ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Применение</label>
                    <textarea name="usage_info" class="form-control" rows="4">
<?= htmlspecialchars($_POST["usage_info"] ?? "") ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Состав</label>
                    <textarea name="composition" class="form-control" rows="4">
<?= htmlspecialchars($_POST["composition"] ?? "") ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Противопоказания</label>
                    <textarea name="contraindications" class="form-control" rows="4">
<?= htmlspecialchars($_POST["contraindications"] ?? "") ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Взаимодействие</label>
                    <textarea name="drug_interactions" class="form-control" rows="4">
<?= htmlspecialchars($_POST["drug_interactions"] ?? "") ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Передозировка</label>
                    <textarea name="overdose" class="form-control" rows="4">
<?= htmlspecialchars($_POST["overdose"] ?? "") ?></textarea>
                </div>

                <!-- Описания -->
                <div class="col-12">
                    <label class="form-label fw-bold">Описание</label>
                    <textarea name="description" class="form-control" rows="6">
<?= htmlspecialchars($_POST["description"] ?? "") ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Полное описание</label>
                    <textarea name="long_description" class="form-control" rows="8">
<?= htmlspecialchars($_POST["long_description"] ?? "") ?></textarea>
                </div>

                <div class="col-12 text-center mt-5">
                    <button type="submit" class="btn btn-dark btn-lg px-5 py-3 rounded-pill shadow">
                        Добавить товар
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require "layout/admin_footer.php"; ?>
