# Dockerfile para Laravel
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    postgresql-client \
    libpq-dev

# Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos de composer
COPY composer.json composer.lock ./

# Instalar dependencias de PHP (sin scripts post-install)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el resto de la aplicación
COPY . .

# Ejecutar scripts de Composer después de copiar todos los archivos
RUN composer dump-autoload --optimize

# Instalar dependencias de Node.js
RUN npm install

# Crear directorio de base de datos
RUN mkdir -p database

# Configurar permisos
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Exponer puerto
EXPOSE 8000

# Comando por defecto
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
