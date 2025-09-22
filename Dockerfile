# Use Debian 12 (bookworm) — compatível com msodbcsql18/sqlsrv 05-09-2025 - 18:40
FROM php:8.1-fpm-bookworm

# Ambiente não-interativo + aceitar EULA da MS p/ ODBC
ENV DEBIAN_FRONTEND=noninteractive \
    ACCEPT_EULA=Y

WORKDIR /var/www

# Baixa o instalador de extensões do mlocati (IPE)
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

# Dependências do sistema (nginx, supervisor, etc.)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip nano \
    nginx supervisor \
    libmemcached-dev \
    unixodbc unixodbc-dev \
    ffmpeg \
    && rm -rf /var/lib/apt/lists/*

# Extensões PHP (o IPE resolve libs e PECL automaticamente)
# Separei sqlsrv/pdo_sqlsrv para facilitar troubleshooting se algo falhar
RUN install-php-extensions mbstring pdo_mysql zip exif pcntl gd memcached
RUN install-php-extensions sqlsrv pdo_sqlsrv

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Usuário de aplicação
RUN groupadd -g 1000 www && useradd -u 1000 -ms /bin/bash -g www www

# ...
COPY --chown=www:www composer.json composer.lock ./
# (opcional) baixar vendors sem autoloader/scripts – apenas cache
RUN composer install --no-interaction --prefer-dist --no-autoloader --no-scripts || true

# Agora copie o restante do código (inclui app/helpers.php)
COPY --chown=www:www . .

# Gere autoloader e rode scripts com o código já presente
RUN composer dump-autoload -o \
 && composer install --no-interaction --prefer-dist
# ...

# Agora copie o restante do código
COPY --chown=www:www . .

# Permissões de storage/logs
RUN chmod -R ug+w storage bootstrap/cache

# Configs
RUN cp docker/supervisor.conf /etc/supervisord.conf \
 && cp docker/php.ini /usr/local/etc/php/conf.d/app.ini \
 && cp docker/php-fpm-www.conf /usr/local/etc/php-fpm.d/www.conf \
 && cp docker/nginx.conf /etc/nginx/sites-enabled/default \
 && cp docker/nginx-fcgi-cache.conf /etc/nginx/conf.d/fcgi_cache.conf

# Logs PHP
RUN mkdir -p /var/log/php && touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

# Script de entrada: roda comandos do artisan e sobe o supervisord (que gerencia php-fpm + nginx)
COPY docker/run.sh /usr/local/bin/run.sh
RUN chmod +x /usr/local/bin/run.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/run.sh"]
