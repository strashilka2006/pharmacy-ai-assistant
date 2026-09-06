#!/usr/bin/env bash
# Раскладывает старую свалку public/uploads по дискам Laravel.
#
# Было: всё вперемешку в public/uploads (товары, аватары, hero-картинки)
#       плюс public/uploads/brands.
# Стало: storage/app/public/{products,brands,avatars} + public/images для статики.
#
# Запускать из корня НОВОГО проекта, передав путь к старому:
#   ./migration-tools/move-uploads.sh ~/old/pharmacy-ai-assistant

set -euo pipefail

OLD="${1:?Укажи путь к старому проекту}"
SRC="$OLD/public/uploads"

[ -d "$SRC" ] || { echo "Не нашёл $SRC"; exit 1; }

mkdir -p storage/app/public/products storage/app/public/brands storage/app/public/avatars public/images

# Бренды — отдельная папка, переносим целиком
if [ -d "$SRC/brands" ]; then
    cp -n "$SRC"/brands/* storage/app/public/brands/ 2>/dev/null || true
fi

# Статика главной страницы
for f in hero.jpg hero.png her1o.jpg no-photo.jpg; do
    [ -f "$SRC/$f" ] && cp -n "$SRC/$f" public/images/
done

# Всё остальное из корня uploads — товарные картинки и аватары.
# Аватары в старой схеме лежали там же, поэтому раскидываем по имени из базы:
# сначала копируем всё в products, затем скрипт ниже перекладывает аватары.
find "$SRC" -maxdepth 1 -type f \
    ! -name 'hero.*' ! -name 'her1o.jpg' ! -name 'no-photo.jpg' \
    -exec cp -n {} storage/app/public/products/ \;

echo "Файлы разложены. Дальше:"
echo "  php artisan storage:link"
echo "  php artisan tinker --execute=\"App\\Models\\User::whereNotNull('avatar')->each(function(\\\$u){ \\\$f='storage/app/public/products/'.\\\$u->avatar; if(file_exists(base_path(\\\$f))) rename(base_path(\\\$f), base_path('storage/app/public/avatars/'.\\\$u->avatar)); });\""
