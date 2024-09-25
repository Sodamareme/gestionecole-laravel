# Utiliser l'image PHP-FPM de base
FROM php:8.3-fpm

# Installer les dépendances nécessaires, y compris GD et zip
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libpng-dev \
    libzip-dev \  
    zip \
    unzip \
    nginx \
    && docker-php-ext-install pdo pdo_pgsql gd zip  # Installer GD et zip ici

# Installer Composer globalement
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer le répertoire de l'application
RUN mkdir -p /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers composer et installer les dépendances
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copier le reste des fichiers de l'application
COPY . .

# Installer les dépendances du projet avec autoload optimisé
RUN composer install --optimize-autoloader --no-dev

# Définir les permissions pour les répertoires de stockage et de cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copier la configuration Nginx
COPY ./nginx/default.conf /etc/nginx/conf.d/default.conf

# Exposer les ports pour Nginx et PHP-FPM
EXPOSE 80 9000

# Démarrer PHP-FPM et Nginx
CMD ["sh", "-c", "service nginx start && php-fpm"]
