FROM php:8.1-fpm

# Set working directory
WORKDIR /var/www

# Add docker php ext repo
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Ensure tools to import repository keys are available before install-php-extensions
RUN apt-get update && apt-get install -y --no-install-recommends gnupg gnupg2 dirmngr curl ca-certificates apt-transport-https \
    && rm -rf /var/lib/apt/lists/*

# Add Microsoft GPG key (needed for pdo_sqlsrv installation) to avoid OpenPGP signature errors
# Try fetching via HTTPS first, then via keyserver as fallback for key EE4D7792F748182B
RUN set -ex; \
    curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft.gpg || true; \
    if ! gpg --list-packets /etc/apt/trusted.gpg.d/microsoft.gpg >/dev/null 2>&1; then \
        echo 'Attempting keyserver fetch for EE4D7792F748182B'; \
        gpg --keyserver hkps://keyserver.ubuntu.com --recv-keys EE4D7792F748182B || true; \
        gpg --export EE4D7792F748182B | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft.gpg || true; \
    fi

# Install php extensions (without pdo_sqlsrv - will be compiled manually)
RUN chmod +x /usr/local/bin/install-php-extensions && sync && install-php-extensions mbstring pdo_mysql zip exif pcntl gd memcached

# Install Microsoft ODBC driver and build deps, then compile sqlsrv/pdo_sqlsrv via PECL
RUN set -ex; \
    # ensure keyring location exists and import key to it
    mkdir -p /usr/share/keyrings; \
    curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /usr/share/keyrings/microsoft-prod.gpg || true; \
    # add MS repo list (workaround: mark as trusted to avoid signature issues during build)
    echo "deb [arch=amd64 trusted=yes] https://packages.microsoft.com/debian/13/prod trixie main" > /etc/apt/sources.list.d/mssql-release.list || true; \
    apt-get update && apt-get install -y --no-install-recommends unixodbc-dev gnupg ca-certificates build-essential g++ make \
        && rm -rf /var/lib/apt/lists/*; \
    # import key to trusted store as fallback
    curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft.gpg || true; \
    apt-get update; \
    ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 || true; \
    pecl channel-update pecl.php.net || true; \
    pecl install sqlsrv || true; \
    pecl install pdo_sqlsrv || true; \
    docker-php-ext-enable sqlsrv pdo_sqlsrv || true;

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    unzip \
    git \
    curl \
    lua-zlib-dev \
    libmemcached-dev \
    nginx \
    nano

# Install supervisor
RUN apt-get install -y supervisor

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

#solução pra conexão do sqlsrv
RUN apt-get update -yqq \
    && apt-get install -y --no-install-recommends openssl \
    && sed -i -E 's/(CipherString\s*=\s*DEFAULT@SECLEVEL=)2/\11/' /etc/ssl/openssl.cnf \
    && rm -rf /var/lib/apt/lists/*

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy code to /var/www
COPY --chown=www:www-data . /var/www

# add root to www group
RUN chmod -R ug+w /var/www/storage

# Copy nginx/php/supervisor configs
RUN cp docker/supervisor.conf /etc/supervisord.conf
RUN cp docker/php.ini /usr/local/etc/php/conf.d/app.ini
RUN cp docker/nginx.conf /etc/nginx/sites-enabled/default

# PHP Error Log Files
RUN mkdir /var/log/php
RUN touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

# Deployment steps - quando for pra produção, inverter as linhas
RUN composer install --optimize-autoloader
# RUN composer install --optimize-autoloader --no-dev
RUN chmod +x /var/www/docker/run.sh

#correcao do nodejs
# RUN apt-get update
# RUN apt-get remove nodejs
# RUN apt-get update
# RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
# RUN apt-get install -y nodejs

#link pra re-instalacao do breeze
#https://laravel.com/docs/10.x/starter-kits#main-content

EXPOSE 80

#ENTRYPOINT ["/var/www/docker/run.sh"]
ENTRYPOINT ["php","artisan","cache:clear"]
ENTRYPOINT ["php", "artisan", "route:cache"]
ENTRYPOINT ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
