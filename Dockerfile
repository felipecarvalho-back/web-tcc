# ==============================================================================
# Estágio 1: Build dos assets de frontend com Node.js (Vite + Tailwind CSS v4)
# ==============================================================================
FROM node:22-bookworm-slim AS assets-builder

WORKDIR /app

# Copia arquivos de dependência do npm
COPY package.json package-lock.json ./

# Instala dependências do frontend
RUN npm ci

# Copia arquivos necessários para o Tailwind v4 e Vite rastrearem as classes
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY app ./app

# Executa o build de produção dos assets do frontend
RUN npm run build

# ==============================================================================
# Estágio 2: Ambiente de Produção com FrankenPHP
# ==============================================================================
FROM dunglas/frankenphp:php8.5-bookworm AS runner

# Define variáveis de ambiente essenciais
ENV APP_ENV="production" \
    APP_DEBUG="false" \
    PHP_INI_DIR="/usr/local/etc/php" \
    COMPOSER_ALLOW_SUPERUSER=1

# Instala dependências do sistema necessárias para o Composer
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instala o binário oficial do Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instala extensões PHP necessárias para Laravel e SQLite
RUN install-php-extensions \
    pcntl \
    pdo_sqlite \
    sqlite3 \
    bcmath \
    intl \
    zip \
    opcache

# Configuração otimizada do PHP para produção e Opcache
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    { \
        echo "opcache.enable=1"; \
        echo "opcache.enable_cli=1"; \
        echo "opcache.memory_consumption=128"; \
        echo "opcache.interned_strings_buffer=16"; \
        echo "opcache.max_accelerated_files=10000"; \
        echo "opcache.validate_timestamps=0"; \
        echo "upload_max_filesize=32M"; \
        echo "post_max_size=32M"; \
        echo "memory_limit=256M"; \
        echo "variables_order=EGPCS"; \
    } >> "$PHP_INI_DIR/conf.d/custom-laravel.ini"

WORKDIR /app

# Copia dependências do Composer para aproveitar cache de camadas
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Copia o restante do código-fonte
COPY . .

# Copia os assets compilados do estágio do Node.js
COPY --from=assets-builder /app/public/build ./public/build

# Conclui autoload do Composer otimizado para produção
RUN composer dump-autoload --optimize --no-dev

# Copia configuração do Caddy e script de entrada
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Garante permissões de execução e remove possíveis quebras de linha Windows (CRLF)
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

# Garante diretórios essenciais e permissões para www-data
RUN mkdir -p /app/storage/framework/sessions \
             /app/storage/framework/views \
             /app/storage/framework/cache \
             /app/storage/logs \
             /app/bootstrap/cache \
             /app/database && \
    touch /app/database/database.sqlite && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/database && \
    chmod -R 775 /app/storage /app/bootstrap/cache /app/database

# Portas suportadas (80 padrão / 10000 Render)
EXPOSE 80 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
