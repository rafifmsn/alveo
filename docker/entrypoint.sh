#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

if [ ! -d "public/build" ]; then
    echo "Building frontend assets..."
    npm install
    npm run build
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache

if [ "${DB_CONNECTION}" = "pgsql" ]; then
    echo "Waiting for PostgreSQL at ${DB_HOST:-db}:${DB_PORT:-5432}..."
    until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-alveo}" >/dev/null 2>&1; do
        sleep 1
    done
fi

php artisan migrate --force
php artisan db:seed --force

exec "$@"

