# Utiliser l'image de base PHP 8.3
FROM php:8.3-fpm

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd zip pdo pdo_pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www

# Copier le contenu de l'application dans le conteneur
COPY . .

# Installer les dépendances PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copier le fichier de configuration de l'environnement
COPY .env.example .env

# Générer la clé d'application
RUN php artisan key:generate

# Exposer le port 9000
EXPOSE 9000

# Démarrer le serveur PHP
CMD ["php-fpm"]
