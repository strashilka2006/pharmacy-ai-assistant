<?php 
$pageTitle = "Редактировать товар";
require "layout/admin_header.php"; 

$id = (int)($_GET["id"] ?? 0);
if (!$id) die("ID товара не указан");

$product = $pdo->query("SELECT * FROM products WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
if (!$product) die("Товар не найден");

$labels = getProductLabels();
$success = "";

// Все бренды из таблицы brands
$brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name              = trim($_POST["name"] ?? "");
    $price             = (float)str_replace([' ', ','], ['', '.'], $_POST["price"] ?? "0");
    $short_description = trim($_POST["short_description"] ?? "");
    $description       = trim($_POST["description"] ?? "");
    $long_description  = trim($_POST["long_description"] ?? "");
    $supplier          = trim($_POST["supplier"] ?? "");
    $prescription      = isset($_POST["rx"]) ? 1 : 0;
    $usage_info        = trim($_POST["usage_info"] ?? "");
    $stock             = (int)($_POST["stock"] ?? 0);
    $label             = $_POST["label"] ?? "";
    $brand_id          = !empty($_POST["brand_id"]) ? (int)$_POST["brand_id"] : null;

    $all_fields = [
        'indications'       => trim($_POST["indications"] ?? ""),
        'composition'       => trim($_POST["composition"] ?? ""),
        'contraindications' => trim($_POST["contraindications"] ?? ""),
        'drug_interactions' => trim($_POST["drug_interactions"] ?? ""),
        'overdose'          => trim($_POST["overdose"] ?? "")
    ];

    // Фото
    $image = $product["image"];
    if (!empty($_POST["photo_url"])) {
        $image = trim($_POST["photo_url"]);
    } elseif (!empty($_FILES["photo"]["name"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/AptekaWebSite/uploads/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $dir . $fileName)) {
            $image = "/AptekaWebSite/uploads/" . $fileName;
        }
    }

    // Обновление
    $sql = "UPDATE products SET 
            name=?, price=?, short_description=?, description=?, long_description=?,
            supplier=?, prescription=?, usage_info=?, stock=?, image=?, label=?, brand_id=?
            WHERE id=?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $name, $price, $short_description, $description, $long_description,
        $supplier, $prescription, $usage_info, $stock, $image, $label, $brand_id, $id
    ]);

    foreach ($all_fields as $field => $value) {
        if ($pdo->query("SHOW COLUMNS FROM products LIKE '$field'")->rowCount() > 0) {
            $pdo->prepare("UPDATE products SET `$field` = ? WHERE id = ?")->execute([$value, $id]);
        }
    }

    $product = $pdo->query("SELECT * FROM products WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
    $success = "Изменения сохранены!";
}
?>

<div class="mb-4">
    <a href="../product.php?id=<?= $id ?>" class="btn btn-outline-dark rounded-pill" target="_blank">
        Просмотр на сайте
    </a>
    <a href="products.php" class="btn btn-outline-secondary rounded-pill ms-3">
        ← Все товары
    </a>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-dark text-white py-4">
        <h3 class="mb-0">Редактирование товара #<?= $id ?> — <?= htmlspecialchars($product["name"]) ?></h3>
    </div>

    <div class="card-body p-5">
        <?php if ($success): ?>
            <div class="alert alert-success rounded-4 mb-4">Изменения сохранены!</div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="row g-4">
                <!-- Название + Цена -->
                <div class="col-lg-8">
                    <label class="form-label fw-bold">Название товара</label>
                    <input type="text" name="name" class="form-control form-control-lg" value="<?= htmlspecialchars($product["name"]) ?>" required>
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold">Цена (₽)</label>
                    <input type="text" name="price" class="form-control form-control-lg" value="<?= number_format($product["price"], 0, '', ' ') ?>" required>
                </div>

                <!-- Бренд (уже нормальный, из таблицы brands) -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Бренд</label>
                    <select name="brand_id" class="form-select">
                        <option value="">— Без бренда —</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= ($product['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Краткое описание</label>
                    <textarea name="short_description" class="form-control" rows="2"><?= htmlspecialchars($product["short_description"] ?? '') ?></textarea>
                </div>

                <!-- Фото -->
                <div class="col-lg-6">
                    <label class="form-label fw-bold">URL изображения</label>
                    <input type="url" name="photo_url" class="form-control" placeholder="https://...">
                </div>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">Или загрузить файл</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>

                <?php if ($product["image"]): ?>
                    <div class="col-12 text-center">
                        <img src="<?= htmlspecialchars($product["image"]) ?>" class="img-fluid rounded-4 shadow-sm" style="max-height:400px;">
                    </div>
                <?php endif; ?>

                <hr class="my-5">

                <!-- Остальные поля — без изменений -->
                <div class="col-md-4"><label class="form-label fw-bold">Поставщик</label><input type="text" name="supplier" class="form-control" value="<?= htmlspecialchars($product["supplier"] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-bold">На складе</label><input type="number" name="stock" class="form-control" value="<?= $product["stock"] ?>"></div>
                <div class="col-md-4"><label class="form-label fw-bold">Метка</label>
                    <select name="label" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($labels as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($product["label"] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="rx" id="rx" <?= $product["prescription"] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="rx">Требуется рецепт</label>
                    </div>
                </div>

                <div class="col-12"><hr><h4 class="mb-4 fw-bold">Медицинская информация</h4></div>
                <div class="col-12"><label class="form-label fw-bold">Показания</label><textarea name="indications" class="form-control" rows="4"><?= htmlspecialchars($product["indications"] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label fw-bold">Применение</label><textarea name="usage_info" class="form-control" rows="4"><?= htmlspecialchars($product["usage_info"] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label fw-bold">Состав</label><textarea name="composition" class="form-control" rows="4"><?= htmlspecialchars($product["composition"] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label fw-bold">Противопоказания</label><textarea name="contraindications" class="form-control" rows="4"><?= htmlspecialchars($product["contraindications"] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label fw-bold">Взаимодействие</label><textarea name="drug_interactions" class="form-control" rows="4"><?= htmlspecialchars($product["drug_interactions"] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label fw-bold">Передозировка</label><textarea name="overdose" class="form-control" rows="4"><?= htmlspecialchars($product["overdose"] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label fw-bold">Описание</label><textarea name="description" class="form-control" rows="6"><?= htmlspecialchars($product["description"] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label fw-bold">Полное описание</label><textarea name="long_description" class="form-control" rows="8"><?= htmlspecialchars($product["long_description"] ?? '') ?></textarea></div>

                <div class="col-12 text-center mt-5">
                    <button type="submit" class="btn btn-dark btn-lg px-5 py-3 rounded-pill shadow">
                        Сохранить изменения
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require "layout/admin_footer.php"; ?>