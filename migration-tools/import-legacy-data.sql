-- ============================================================================
--  Перенос данных из старой базы (apteka) в новую (apteka_laravel).
--
--  Как пользоваться:
--    1. mysql -u root -p -e "CREATE DATABASE apteka_legacy CHARACTER SET utf8mb4"
--    2. mysql -u root -p apteka_legacy < schema.sql        # старый дамп
--    3. php artisan migrate                                # создаём новые таблицы
--    4. mysql -u root -p apteka_laravel < import-legacy-data.sql
--
--  Пароли переносятся как есть: password_hash(PASSWORD_DEFAULT) — это bcrypt,
--  а Laravel по умолчанию проверяет тем же bcrypt. Люди зайдут старыми паролями.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── Бренды ──────────────────────────────────────────────────────────────────
INSERT INTO brands (id, name, description, logo, banner, created_at)
SELECT
    id,
    name,
    description,
    -- в старой базе путь мог быть 'uploads/brands/x.jpg', '/uploads/brands/x.jpg'
    -- или внешним URL. Внешние оставляем, локальные сводим к имени файла.
    CASE WHEN logo REGEXP '^https?://' THEN logo ELSE SUBSTRING_INDEX(logo, '/', -1) END,
    CASE WHEN banner REGEXP '^https?://' THEN banner ELSE SUBSTRING_INDEX(banner, '/', -1) END,
    COALESCE(created_at, NOW())
FROM apteka_legacy.brands;

-- ── Категории ───────────────────────────────────────────────────────────────
INSERT INTO categories (id, name, description)
SELECT id, name, description FROM apteka_legacy.categories;

-- ── Пользователи ────────────────────────────────────────────────────────────
INSERT INTO users (id, email, password, name, phone, role, avatar, address, email_verified_at, created_at)
SELECT
    id, email, password, name, phone, role,
    CASE WHEN avatar IN ('', 'default.png') THEN NULL ELSE SUBSTRING_INDEX(avatar, '/', -1) END,
    address,
    created_at,   -- старые аккаунты считаем подтверждёнными
    COALESCE(created_at, NOW())
FROM apteka_legacy.users;

-- ── Товары ──────────────────────────────────────────────────────────────────
-- Колонки photo и brand (varchar) не переносятся: photo нигде не использовалась,
-- а brand дублировал brand_id. Если в brand есть данные, которых нет в brands —
-- проверь запросом в конце файла ПЕРЕД импортом.
INSERT INTO products (
    id, category_id, brand_id, name, short_description, description, long_description,
    price, supplier, prescription, usage_info, stock, image, label,
    indications, composition, contraindications, drug_interactions, overdose, created_at
)
SELECT
    p.id, p.category_id, p.brand_id, p.name, p.short_description, p.description, p.long_description,
    p.price, p.supplier, p.prescription, p.usage_info, GREATEST(p.stock, 0),
    CASE WHEN p.image REGEXP '^https?://' THEN p.image ELSE SUBSTRING_INDEX(p.image, '/', -1) END,
    NULLIF(p.label, ''),
    p.indications, p.composition, p.contraindications, p.drug_interactions, p.overdose,
    COALESCE(p.created_at, NOW())
FROM apteka_legacy.products p;

-- ── Корзины (cart → cart_items) ─────────────────────────────────────────────
-- В старой таблице не было уникального индекса, поэтому у одного пользователя
-- мог лежать один и тот же товар несколькими строками. Схлопываем в одну.
INSERT INTO cart_items (user_id, product_id, qty, created_at, updated_at)
SELECT user_id, product_id, SUM(qty), MIN(added_at), NOW()
FROM apteka_legacy.cart
GROUP BY user_id, product_id;

-- ── Заказы ──────────────────────────────────────────────────────────────────
INSERT INTO orders (id, user_id, total, status, name, phone, address, payment_id, pay_url, created_at, updated_at)
SELECT
    id, user_id, total, status, name, phone, address, payment_id, pay_url,
    COALESCE(created_at, NOW()),
    COALESCE(updated_at, created_at, NOW())
FROM apteka_legacy.orders;

INSERT INTO order_items (id, order_id, product_id, qty, price)
SELECT id, order_id, product_id, qty, price FROM apteka_legacy.order_items;

-- ── Отзывы ──────────────────────────────────────────────────────────────────
INSERT INTO reviews (id, product_id, user_id, rating, comment, created_at, updated_at)
SELECT id, product_id, user_id, rating, comment,
       COALESCE(created_at, NOW()), COALESCE(created_at, NOW())
FROM apteka_legacy.reviews;

-- ── Купоны (если оставил соответствующую миграцию) ──────────────────────────
INSERT INTO coupons (id, code, discount_percent, expires_at, active)
SELECT id, code, discount_percent, expires_at, active FROM apteka_legacy.coupons;

INSERT INTO used_coupons (id, user_id, coupon_id, used_at)
SELECT id, user_id, coupon_id, used_at FROM apteka_legacy.used_coupons;

-- Коды подтверждения почты НЕ переносим: в старой базе они лежали открытым
-- текстом, в новой схеме поле хранит хеш. Они живут 10 минут, потеря не страшна.

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
--  Проверки ПОСЛЕ импорта
-- ============================================================================
-- SELECT 'users', COUNT(*) FROM users
--  UNION ALL SELECT 'products', COUNT(*) FROM products
--  UNION ALL SELECT 'orders', COUNT(*) FROM orders
--  UNION ALL SELECT 'order_items', COUNT(*) FROM order_items;

-- Товары, у которых текстовый бренд не совпал с brand_id:
-- SELECT p.id, p.name, p.brand AS legacy_brand, b.name AS linked_brand
-- FROM apteka_legacy.products p
-- LEFT JOIN apteka_legacy.brands b ON b.id = p.brand_id
-- WHERE p.brand <> '' AND (b.name IS NULL OR b.name <> p.brand);
