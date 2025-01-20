#!/bin/bash

# Esperar si es necesario
sleep 5

# Preparar la aplicación
php artisan key:generate --no-interaction
php artisan migrate:fresh --seed --no-interaction
php artisan jwt:secret --force --no-interaction
php artisan config:cache
php artisan route:cache

# Iniciar servidor
php artisan serve --host=0.0.0.0 --port=8000