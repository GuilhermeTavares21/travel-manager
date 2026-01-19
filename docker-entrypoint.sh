#!/bin/bash
set -e

composer install --no-interaction

if [ ! -f "config/jwt.php" ]; then
  echo "📦 Publicando configuração JWT..."
  php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider" --force
fi

if [ ! -f ".env" ]; then
  echo "⚙️ Criando arquivo .env..."
  cp .env.example .env
fi

if [ -z "$(grep '^APP_KEY=' .env | cut -d '=' -f2)" ]; then
  php artisan key:generate
fi

if [ -z "$(grep '^JWT_SECRET=' .env | cut -d '=' -f2)" ]; then
  echo "🔒 Gerando JWT_SECRET..."
  php artisan jwt:secret --force
fi

php artisan config:cache

echo "⏳ Aguardando o banco de dados ficar pronto..."
until php artisan tinker --execute="DB::connection()->getPdo()" 2>/dev/null; do
  sleep 2
done

php artisan migrate --force
php artisan db:seed --force

php artisan serve --host=0.0.0.0 --port=8000
