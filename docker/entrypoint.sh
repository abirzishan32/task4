#!/bin/sh
set -e

# Wait for database to be ready
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Waiting for database to be ready..."
  sleep 2
done

# Clear and warm up cache
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --no-warmup
APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf 