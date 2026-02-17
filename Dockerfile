# 1) Image PHP avec Apache (plus simple pour Render car tout est inclus)
FROM php:8.2-apache

# 2) Installer les dépendances système et les extensions PHP pour Symfony
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql zip intl opcache

# 3) Configuration d'Apache pour pointer sur le dossier /public de Symfony
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# 4) Dossier de travail
WORKDIR /var/www/html

# 5) Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 6) Copier les fichiers du projet
COPY . .

# 7) Installer les dépendances Symfony (mode production)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# 8) Droits sur les dossiers de cache et de logs (Crucial pour Symfony)
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var/cache var/log \
    && chmod -R 777 var/cache var/log

# 9) Render utilise le port 80 par défaut avec Apache dans ce container
EXPOSE 80

# 10) Commande de démarrage : on vide le cache et on lance Apache
# Le script attend que la DB soit prête si besoin
CMD php bin/console cache:clear --env=prod && apache2-foreground