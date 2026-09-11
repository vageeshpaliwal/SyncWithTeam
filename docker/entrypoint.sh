#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
fi

php artisan config:clear
php artisan migrate --force
php artisan db:seed --class=PermissionSeeder --force

exec "$@"
