# Базовый образ: PHP 8.2 с уже настроенным Apache внутри.
# Всё, что ниже, — это слои поверх него.
FROM php:8.2-apache

# Расширения. mbstring и curl в официальном образе уже есть,
# а вот pdo_mysql и exif надо доставить руками.
# exif нужен для exif_imagetype() — проверки картинок при загрузке.
RUN apt-get update && apt-get install -y --no-install-recommends \
        unzip git \
    && docker-php-ext-install pdo_mysql exif \
    && rm -rf /var/lib/apt/lists/*

# mod_rewrite — на будущее (ЧПУ, единая точка входа)
RUN a2enmod rewrite

# Корень сайта — public/, а не корень проекта.
# Так app/, vendor/ и schema.sql физически недоступны из браузера.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Composer забираем готовым бинарником из его официального образа
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Сначала только манифесты и установка зависимостей — отдельным слоем.
# Пока composer.json не меняется, Docker переиспользует кеш
# и не качает пакеты заново при каждой пересборке.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Теперь остальной код
COPY . .

RUN composer dump-autoload --optimize \
    # config.php лежит в .gitignore, поэтому в образе его нет — создаём из примера
    && cp -n app/config.example.php app/config.php \
    # Apache работает от www-data, ему нужны права на запись в папки загрузок
    && chown -R www-data:www-data public/uploads

EXPOSE 80
