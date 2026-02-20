# phpswoole/swoole:php8.3 is based on php:8.3-cli and adds:
#   - Swoole (install-swoole.sh with mysqlnd, openssl, sockets, brotli, io_uring, swoole-curl, swoole-pgsql, swoole-sqlite)
#   - pdo_mysql (docker-php-ext-install)
#   - redis (PECL, enabled)
# The official php:8.3-cli base includes core extensions (e.g. json, dom, xml, mbstring vary by build).
# Run `docker run --rm phpswoole/swoole:php8.3 php -m` to list extensions in the base image.
FROM phpswoole/swoole:php8.3

ARG APP_ENV=dev
ARG DEBIAN_FRONTEND=noninteractive
ARG TZ=Europe/Moscow

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    espeak-ng \
    git \
    libicu-dev \
    libmemcached-dev \
    && docker-php-ext-install intl mysqli\
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && rm -rf /var/lib/apt/lists/*

# Raise PHP memory_limit (default 128M is too low for view/templates under load)
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/99-memory-limit.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /opt/app

# Copy composer files first for better layer caching
COPY composer.json composer.lock* ./

# Install dependencies based on APP_ENV
# --ignore-platform-reqs: fallback for optional platform requirements
RUN if [ "$APP_ENV" = "prod" ]; then \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs; \
else \
    composer install --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs; \
fi

# Copy application files
COPY . .

# In production, do not ship .local config or development.config.php (use env and non-local config only)
RUN if [ "$APP_ENV" = "prod" ]; then \
    rm -f config/autoload/*.local.php config/development.config.php; \
  
fi

COPY packages/expras /opt/app/vendor/expras

# Regenerate autoloader so vendor/expras is included
RUN if [ "$APP_ENV" = "prod" ]; then \
    composer dump-autoload --optimize --classmap-authoritative --no-dev; \
else \
    composer dump-autoload --optimize; \
fi

EXPOSE 80

# Entrypoint: schema create + fixtures on first run, then start Swoole
COPY docker/php-app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# mezzio-swoole HTTP server (host/port via config/autoload/mezzio-swoole.local.php)
CMD ["php", "vendor/bin/laminas", "mezzio:swoole:start", "--num-workers=8", "--num-task-workers=0"]
