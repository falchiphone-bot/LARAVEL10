###############################################
# Base PHP / FPM
###############################################
ARG PHP_VERSION=8.1
# Para fallback (caso tag bookworm falhe) trocar para -bullseye:
# FROM php:${PHP_VERSION}-fpm-bullseye
FROM php:${PHP_VERSION}-fpm-bookworm

###############################################
# Ambiente / Vars
###############################################
ENV DEBIAN_FRONTEND=noninteractive \
    ACCEPT_EULA=Y \
    APP_ENV=production

WORKDIR /var/www

###############################################
# Instalador de extensões (mlocati) & dependências de sistema
###############################################
ARG IPE_VERSION=2.7.23
# Download robusto (pinado) do instalador de extensões com fallback
RUN set -e; \
    echo "Baixando install-php-extensions v${IPE_VERSION}"; \
    for url in \
        "https://github.com/mlocati/docker-php-extension-installer/releases/download/${IPE_VERSION}/install-php-extensions" \
        "https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/${IPE_VERSION}/install-php-extensions" \
    ; do \
        echo "Tentando: $url"; \
        if curl -fsSL "$url" -o /usr/local/bin/install-php-extensions; then \
            echo "Sucesso em $url"; break; \
        fi; \
    done; \
    if [ ! -s /usr/local/bin/install-php-extensions ]; then \
        echo 'Falha ao baixar install-php-extensions'; exit 1; \
    fi; \
    chmod +x /usr/local/bin/install-php-extensions; \
    apt-get update \
 && apt-get install -y --no-install-recommends \
    git curl unzip nano \
    nginx supervisor \
    libmemcached-dev \
    unixodbc unixodbc-dev \
    ffmpeg \
 && rm -rf /var/lib/apt/lists/*

###############################################
# Extensões PHP (separando sqlsrv para debug se falhar)
###############################################
RUN install-php-extensions mbstring pdo_mysql zip exif pcntl gd memcached \
 && install-php-extensions sqlsrv pdo_sqlsrv

###############################################
# Composer
###############################################
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

###############################################
# Usuário de aplicação (UID/GID 1000)
###############################################
RUN groupadd -g 1000 www && useradd -u 1000 -ms /bin/bash -g www www

###############################################
# Dependências PHP (primeiro só composer.* para cache de layers)
###############################################
COPY --chown=www:www composer.json composer.lock ./
# Garante diretório app/ para copiar helpers antes do install (evita erro de autoload)
RUN mkdir -p app
COPY --chown=www:www app/helpers.php app/helpers.php

# Flags de composer variam conforme ambiente
ARG COMPOSER_FLAGS="--no-interaction --prefer-dist --optimize-autoloader --no-dev"
RUN set -e; \
        if [ "$APP_ENV" != "production" ]; then \
            COMPOSER_FLAGS="--no-interaction --prefer-dist" ; \
        fi; \
        composer install $COMPOSER_FLAGS --no-scripts \
 && echo "Composer install (fase cache) concluído sem scripts" \
 || (echo 'FALHA composer install'; exit 1)

###############################################
# Código da aplicação
###############################################
COPY --chown=www:www . .

# Regera autoloader e executa scripts agora que todo o código está presente
RUN set -e; \
    composer dump-autoload -o; \
    composer run-script post-autoload-dump || true

###############################################
# Permissões e configs
###############################################
RUN chmod -R ug+w storage bootstrap/cache \
 && cp docker/supervisor.conf /etc/supervisord.conf \
 && cp docker/php.ini /usr/local/etc/php/conf.d/app.ini \
 && cp docker/php-fpm-www.conf /usr/local/etc/php-fpm.d/www.conf \
 && cp docker/nginx.conf /etc/nginx/sites-enabled/default \
 && cp docker/nginx-fcgi-cache.conf /etc/nginx/conf.d/fcgi_cache.conf \
 && mkdir -p /var/log/php \
 && touch /var/log/php/errors.log \
 && chmod 777 /var/log/php/errors.log

###############################################
# Script de entrada (supervisord -> php-fpm + nginx)
###############################################
COPY docker/run.sh /usr/local/bin/run.sh
RUN chmod +x /usr/local/bin/run.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/run.sh"]

# Dicas de build:
# docker build -t minha-app .
# docker build --build-arg APP_ENV=local --build-arg PHP_VERSION=8.2 -t minha-app:dev .
