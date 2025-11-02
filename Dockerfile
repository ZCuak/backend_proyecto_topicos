# Imagen base oficial de PHP con FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libpq-dev netcat-traditional \
    nodejs npm \
 && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www

# Copiar archivos de dependencias primero para aprovechar cache
COPY composer.json composer.lock package.json package-lock.json* ./

# Copiar todo el proyecto antes
COPY . .

# Instalar dependencias PHP y JS
RUN composer install --no-dev --optimize-autoloader --prefer-dist \
 && npm install && npm run build

# Ajustar permisos
RUN chown -R www-data:www-data /var/www \
 && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Exponer el puerto FPM
EXPOSE 9000

# Comando por defecto
CMD ["php-fpm"]
