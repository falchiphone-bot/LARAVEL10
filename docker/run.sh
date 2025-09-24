#!/bin/sh

cd /var/www

# php artisan migrate:fresh --seed
php artisan cache:clear || true
# Em desenvolvimento, não faça route:cache para permitir hot-reload de rotas/controladores
if [ "${APP_ENV}" = "production" ]; then
	php artisan route:cache || true
else
	php artisan route:clear || true
fi

/usr/bin/supervisord -c /etc/supervisord.conf
