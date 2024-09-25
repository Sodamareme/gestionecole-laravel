# Utiliser l'image officielle de Nginx
FROM nginx:alpine

# Copier le fichier de configuration personnalisé de Nginx
COPY ./nginx/default.conf /etc/nginx/sites-available/default

# Exposer le port 80 pour le serveur Nginx
EXPOSE 80 8080

# Commande de démarrage Nginx
CMD ["nginx", "-g", "daemon off;"]