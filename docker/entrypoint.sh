#!/bin/sh
set -e

# Garante diretório e arquivo para banco SQLite
mkdir -p /app/database
if [ ! -f /app/database/database.sqlite ]; then
    touch /app/database/database.sqlite
fi
chown -R www-data:www-data /app/database 2>/dev/null || true
chmod -R 775 /app/database 2>/dev/null || true

# Garante diretórios de cache e logs do Laravel
mkdir -p /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/framework/cache \
         /app/storage/logs \
         /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null || true
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true

# Garante a existência de uma chave de aplicação (APP_KEY)
if [ -z "$APP_KEY" ]; then
    if [ ! -f /app/.env ]; then
        echo "[Sentinela] Criando arquivo .env a partir de .env.example..."
        cp /app/.env.example /app/.env
    fi
    echo "[Sentinela] Gerando APP_KEY para a aplicacao..."
    php artisan key:generate --force --no-interaction
fi

# Executa migrações no banco SQLite para sessões, cache e tabelas base
echo "[Sentinela] Executando migracoes do banco SQLite..."
php artisan migrate --force --graceful --no-interaction

# Otimizações de cache em ambiente de produção
if [ "$APP_ENV" = "production" ]; then
    echo "[Sentinela] Criando cache de rotas e configuracoes..."
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
fi

echo "[Sentinela] Iniciando FrankenPHP na porta ${PORT:-10000}..."
exec "$@"
