#!/usr/bin/env sh
set -eu

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan storage:link
php artisan migrate --force

# Ensure public/hot is removed so Laravel doesn't look for Vite dev server
rm -f public/hot
