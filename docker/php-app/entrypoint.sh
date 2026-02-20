#!/bin/sh
set -e

# Clear config cache so Doctrine connection uses current MYSQL_* env (e.g. from env_file)
rm -f data/cache/config-cache-web.php data/cache/config-cache-cli.php 2>/dev/null || true

# Remove leftover Unix socket so Swoole can bind (avoids "Address already in use")
rm -f /run/findwords.sock 2>/dev/null || true 

# On first run: create DB schema and load fixtures (when schema-tool:create succeeds)
php vendor/bin/mezzio-sf-console orm:schema-tool:create --no-interaction 2>/dev/null && \
  php vendor/bin/mezzio-sf-console doctrine:fixtures:load --no-interaction 2>/dev/null || true

exec "$@"
