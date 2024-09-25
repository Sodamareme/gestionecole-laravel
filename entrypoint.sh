#!/bin/sh

# Démarre Nginx
service nginx start

# Démarre PHP-FPM
php-fpm

# Affiche les logs d'erreur de Nginx en continu
tail -f /var/log/nginx/error.log
