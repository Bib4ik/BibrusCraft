FROM php:8.2-apache
# Устанавливаем расширения для работы с БД
RUN docker-php-ext-install mysqli pdo pdo_mysql
# Копируем файлы проекта в папку сервера
COPY . /var/www/html/