FROM php:8.2-fpm-alpine

# Instala dependências do sistema
RUN apk add --no-cache \
    postgresql-dev \
    postgresql-client \
    git \
    zip \
    unzip \
    bash

# Instala extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_pgsql

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copia arquivos de dependências primeiro (cache de build)
COPY composer.json composer.lock* ./

# Instala dependências PHP
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Copia o restante do código
COPY . .

# Regenera autoload com todos os arquivos
RUN composer dump-autoload --optimize

# Copia e configura o entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
